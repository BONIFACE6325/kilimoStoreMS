<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Batch;
use App\Models\Farmer;
use App\Models\Loan;
use App\Models\Settlement;
use App\Models\SettlementDeduction;
use App\Models\DryingJob;
use App\Models\MillingJob;
use App\Models\GradingRecord;
use App\Traits\HasTenantScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    use HasTenantScope;

    public function getBuyers(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $buyers = Buyer::where('tenant_id', $tenantId)->where('status', 'active')->orderBy('name')->get();
        return response()->json($buyers);
    }

    public function indexInvoices(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $invoices = Invoice::where('tenant_id', $tenantId)->with(['buyer', 'items.batch.farmer'])->orderBy('created_at', 'desc')->get();
        return response()->json($invoices);
    }

    public function markInvoicePaid($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'paid']);

        return response()->json([
            'success' => true,
            'message' => 'Ankara imetiwa alama ya kulipwa (Paid)',
            'invoice' => $invoice
        ]);
    }

    public function deleteInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->items()->delete();
        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ankara imefutwa kikamilifu'
        ]);
    }

    public function indexSettlements(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $settlements = Settlement::where('tenant_id', $tenantId)->with(['farmer', 'invoice.buyer', 'deductions'])->orderBy('created_at', 'desc')->get();
        return response()->json($settlements);
    }

    private function getRelatedBatchIds($batch)
    {
        if (!$batch) return [];

        // 1. Find root parent batch
        $root = $batch;
        while (!empty($root->parent_batch_id)) {
            $parent = Batch::find($root->parent_batch_id);
            if (!$parent) break;
            $root = $parent;
        }

        // 2. Traverse all descendants from root
        $allIds = [$root->id];
        $queue = [$root->id];
        while (!empty($queue)) {
            $childIds = Batch::whereIn('parent_batch_id', $queue)->pluck('id')->toArray();
            $newChildren = array_diff($childIds, $allIds);
            if (empty($newChildren)) break;
            $allIds = array_merge($allIds, $newChildren);
            $queue = $newChildren;
        }

        return array_values(array_unique($allIds));
    }

    private function getBatchRawQuantity($batch)
    {
        if (!$batch) return 0.0;
        $qty = floatval($batch->intake_quantity ?? 0);
        if ($qty > 0) return $qty;
        return floatval($batch->current_weight_mt ?? $batch->initial_weight_mt ?? 0);
    }

    private function getJobUnpaidFee($job)
    {
        if (!$job || $job->status === 'paid') {
            return 0.0;
        }

        $totalFee = floatval($job->fee_amount ?? 0);
        if ($totalFee <= 0) {
            return 0.0;
        }

        $alreadyPaid = floatval(SettlementDeduction::where('source_reference_id', $job->id)->sum('amount'));
        $unpaid = max(0.0, $totalFee - $alreadyPaid);

        if ($unpaid <= 0.001) {
            if ($job->status !== 'paid') {
                $job->update(['status' => 'paid']);
            }
            return 0.0;
        }

        return $unpaid;
    }

    public function previewDeductions(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'price_per_kg' => 'required|numeric|min:0',
            'sold_weight_kg' => 'required|numeric|gt:0',
        ]);

        $batch = Batch::with(['farmer'])->findOrFail($validated['batch_id']);

        $soldQty = floatval($validated['sold_weight_kg']);
        $availQty = $this->getBatchRawQuantity($batch);

        if ($availQty < $soldQty - 0.001) {
            return response()->json([
                'success' => false,
                'message' => 'Kiasi unachotaka kuuza ('.number_format($soldQty).' '.($batch->intake_unit ?? 'Units').') ni kikubwa kuliko mzigo uliopo ghalani ('.number_format($availQty).' '.($batch->intake_unit ?? 'Units').').'
            ], 422);
        }

        if ($batch->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii tayari imeshauzwa yote.'
            ], 422);
        }

        $grossSales = $soldQty * floatval($validated['price_per_kg']);
        $batchIds = $this->getRelatedBatchIds($batch);

        // 1. Calculate Storage Fees
        $storageFees = $this->calculateStorageFees($batch);

        // 2. Fetch Unpaid Drying Fees
        $dryingJobs = DryingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $dryingFees = $dryingJobs->sum(function($j) {
            return $this->getJobUnpaidFee($j);
        });

        // 3. Fetch Unpaid Milling Fees
        $millingJobs = MillingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $millingFees = $millingJobs->sum(function($j) {
            return $this->getJobUnpaidFee($j);
        });

        // 4. Fetch Unpaid Grading Fees
        $gradingRecords = GradingRecord::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $gradingFees = $gradingRecords->sum(function($j) {
            return $this->getJobUnpaidFee($j);
        });

        // 5. Fetch Active Loans
        $loans = Loan::where('farmer_id', $batch->farmer_id)
            ->whereIn('status', ['active', 'overdue'])
            ->get();
        $loanPrincipal = $loans->sum('current_balance');

        $totalDeductions = $storageFees + $dryingFees + $millingFees + $gradingFees + $loanPrincipal;
        $netPayout = max(0, $grossSales - $totalDeductions);

        return response()->json([
            'farmer_name' => $batch->farmer->name,
            'crop_type' => $batch->crop_type,
            'weight_mt' => $batch->current_weight_mt,
            'gross_sales' => $grossSales,
            'deductions' => [
                'storage_fees' => $storageFees,
                'drying_fees' => $dryingFees,
                'milling_fees' => $millingFees,
                'grading_fees' => $gradingFees,
                'loan_principal' => $loanPrincipal,
                'loan_interest' => 0.00,
            ],
            'total_deductions' => $totalDeductions,
            'net_payout' => $netPayout
        ]);
    }

    public function confirmSale(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'batch_id' => 'required|exists:batches,id',
            'buyer_name' => 'required|string|max:255',
            'price_per_kg' => 'required|numeric|min:0',
            'sold_weight_kg' => 'required|numeric|gt:0',
        ]);

        $batch = Batch::with(['farmer'])->findOrFail($validated['batch_id']);

        $soldQty = floatval($validated['sold_weight_kg']);
        $availQty = $this->getBatchRawQuantity($batch);

        if ($availQty < $soldQty - 0.001) {
            return response()->json([
                'success' => false,
                'message' => 'Kiasi unachotaka kuuza ('.number_format($soldQty).' '.($batch->intake_unit ?? 'Units').') ni kikubwa kuliko mzigo uliopo ghalani ('.number_format($availQty).' '.($batch->intake_unit ?? 'Units').').'
            ], 422);
        }

        if ($batch->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii tayari imeshauzwa yote.'
            ], 422);
        }

        try {
            $buyerName = $validated['buyer_name'] ?? 'Mnunuzi wa Jumla';
            $buyer = Buyer::firstOrCreate(
                ['name' => $buyerName, 'tenant_id' => $batch->tenant_id],
                ['status' => 'active']
            );

            $tenantId = $batch->tenant_id;
            $allInvoiceNumbers = Invoice::where('tenant_id', $tenantId)->pluck('invoice_number');
            $maxNum = 1000;
            foreach ($allInvoiceNumbers as $numStr) {
                if (preg_match('/INV-(\d+)/', $numStr, $matches)) {
                    $val = intval($matches[1]);
                    if ($val > $maxNum) {
                        $maxNum = $val;
                    }
                }
            }
            $nextNumber = $maxNum + 1;
            $invoiceNumber = 'INV-' . $nextNumber;
            while (Invoice::where('tenant_id', $tenantId)->where('invoice_number', $invoiceNumber)->exists()) {
                $nextNumber++;
                $invoiceNumber = 'INV-' . $nextNumber;
            }

            $pricePerUnit = floatval($validated['price_per_kg']);
            $grossSales = $soldQty * $pricePerUnit;

            $batchIds = $this->getRelatedBatchIds($batch);

            // Calculate all deductions across processing tree
            $storageFees = $this->calculateStorageFees($batch);
            $dryingJobs = DryingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $millingJobs = MillingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $gradingRecords = GradingRecord::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $loans = Loan::where('farmer_id', $batch->farmer_id)->whereIn('status', ['active', 'overdue'])->orderBy('created_at', 'asc')->get();

            // Waterfall payment logic
            $availableFunds = $grossSales;

            // 1. Storage Fees
            $paidStorage = 0.0;
            if ($storageFees > 0 && $availableFunds > 0) {
                $paidStorage = min($availableFunds, $storageFees);
                $availableFunds -= $paidStorage;
            }

            // 2. Drying Jobs Waterfall
            $dryingPayments = [];
            foreach ($dryingJobs as $job) {
                if ($availableFunds <= 0) break;
                $unpaid = $this->getJobUnpaidFee($job);
                if ($unpaid <= 0) continue;
                $pay = min($availableFunds, $unpaid);
                $availableFunds -= $pay;
                $dryingPayments[] = [
                    'job' => $job,
                    'amount' => $pay,
                    'is_full' => ($pay >= $unpaid - 0.001)
                ];
            }

            // 3. Milling Jobs Waterfall
            $millingPayments = [];
            foreach ($millingJobs as $job) {
                if ($availableFunds <= 0) break;
                $unpaid = $this->getJobUnpaidFee($job);
                if ($unpaid <= 0) continue;
                $pay = min($availableFunds, $unpaid);
                $availableFunds -= $pay;
                $millingPayments[] = [
                    'job' => $job,
                    'amount' => $pay,
                    'is_full' => ($pay >= $unpaid - 0.001)
                ];
            }

            // 4. Grading Jobs Waterfall
            $gradingPayments = [];
            foreach ($gradingRecords as $job) {
                if ($availableFunds <= 0) break;
                $unpaid = $this->getJobUnpaidFee($job);
                if ($unpaid <= 0) continue;
                $pay = min($availableFunds, $unpaid);
                $availableFunds -= $pay;
                $gradingPayments[] = [
                    'job' => $job,
                    'amount' => $pay,
                    'is_full' => ($pay >= $unpaid - 0.001)
                ];
            }

            // 5. Loans Waterfall
            $loanPayments = [];
            foreach ($loans as $loan) {
                if ($availableFunds <= 0) break;
                $bal = floatval($loan->current_balance);
                if ($bal <= 0) continue;
                $pay = min($availableFunds, $bal);
                $availableFunds -= $pay;
                $loanPayments[] = [
                    'loan' => $loan,
                    'amount' => $pay,
                    'new_balance' => max(0, $bal - $pay)
                ];
            }

            $netPayout = $availableFunds;
            $actualDeductions = $grossSales - $netPayout;

            $result = DB::transaction(function () use (
                $tenantId, $batch, $buyer, $invoiceNumber, $pricePerUnit, $soldQty, $availQty, $grossSales,
                $paidStorage, $dryingPayments, $millingPayments, $gradingPayments, $loanPayments,
                $actualDeductions, $netPayout
            ) {
                // 1. Create Invoice
                $invoice = Invoice::create([
                    'tenant_id' => $tenantId,
                    'buyer_id' => $buyer->id,
                    'invoice_number' => $invoiceNumber,
                    'subtotal' => $grossSales,
                    'vat_amount' => 0.00,
                    'total_amount' => $grossSales,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(30),
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'batch_id' => $batch->id,
                    'quantity_mt' => $soldQty,
                    'unit_price' => $pricePerUnit,
                    'total_price' => $grossSales,
                ]);

                // 2. Create Settlement
                $settlement = Settlement::create([
                    'tenant_id' => $tenantId,
                    'farmer_id' => $batch->farmer_id,
                    'invoice_id' => $invoice->id,
                    'gross_amount' => $grossSales,
                    'total_deductions' => $actualDeductions,
                    'net_payout' => $netPayout,
                    'payment_method' => 'mobile_money',
                    'payment_status' => 'settled',
                    'payment_reference' => 'TXN-' . rand(100000000, 999999999),
                    'settled_at' => now(),
                ]);

                // 3. Record Deductions Breakdown and Update DB statuses
                if ($paidStorage > 0) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'storage_fee',
                        'source_reference_id' => $batch->id,
                        'amount' => $paidStorage,
                    ]);
                }

                foreach ($dryingPayments as $dp) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'drying_fee',
                        'source_reference_id' => $dp['job']->id,
                        'amount' => $dp['amount'],
                    ]);
                    if ($dp['is_full']) {
                        $dp['job']->update(['status' => 'paid']);
                    }
                }

                foreach ($millingPayments as $mp) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'milling_fee',
                        'source_reference_id' => $mp['job']->id,
                        'amount' => $mp['amount'],
                    ]);
                    if ($mp['is_full']) {
                        $mp['job']->update(['status' => 'paid']);
                    }
                }

                foreach ($gradingPayments as $gp) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'grading_fee',
                        'source_reference_id' => $gp['job']->id,
                        'amount' => $gp['amount'],
                    ]);
                    if ($gp['is_full']) {
                        $gp['job']->update(['status' => 'paid']);
                    }
                }

                foreach ($loanPayments as $lp) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'loan_principal',
                        'source_reference_id' => $lp['loan']->id,
                        'amount' => $lp['amount'],
                    ]);
                    $lp['loan']->update([
                        'current_balance' => $lp['new_balance'],
                        'status' => $lp['new_balance'] <= 0.001 ? 'paid' : 'active',
                    ]);
                    \App\Models\LoanTransaction::create([
                        'loan_id' => $lp['loan']->id,
                        'transaction_type' => 'payment',
                        'amount' => $lp['amount'],
                        'reference_number' => 'SETT-' . $settlement->id,
                    ]);
                }

                // 4. Update Batch status and remaining weight/quantity
                $newQty = max(0, $availQty - $soldQty);
                $isFullySold = $newQty <= 0.001;

                $batch->update([
                    'intake_quantity' => $newQty,
                    'current_weight_mt' => $newQty,
                    'status' => $isFullySold ? 'sold' : $batch->status
                ]);

                // 5. Decrement Bin Occupancy
                if ($batch->current_bin_id && $batch->bin) {
                    $bin = $batch->bin;
                    $soldWeightMt = $soldQty / 1000;
                    if ($bin->current_occupancy_mt > 0) {
                        $bin->decrement('current_occupancy_mt', min($bin->current_occupancy_mt, $soldWeightMt));
                    }
                    if ($bin->current_occupancy_mt <= 0) {
                        $bin->update(['status' => 'empty', 'current_occupancy_mt' => 0.00, 'crop_type' => null]);
                    }
                }

                // 6. Sync Farmer Active Status
                $farmerId = $batch->farmer_id;
                $hasActiveStock = Batch::where('farmer_id', $farmerId)
                    ->where('status', '!=', 'sold')
                    ->where('current_weight_mt', '>', 0.001)
                    ->exists();

                if (!$hasActiveStock) {
                    Farmer::where('id', $farmerId)->update(['status' => 'inactive']);
                }

                return $settlement;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sale finalized, invoice issued, and farmer payout processed successfully',
                'settlement' => $result
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Confirm sale error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Imeshindwa kukamilisha mauzo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateStorageFees($batch)
    {
        $hasCategory = \Illuminate\Support\Facades\Schema::hasColumn('services', 'category');
        $storageService = \App\Models\Service::where(function ($q) use ($hasCategory) {
            if ($hasCategory) {
                $q->where('category', 'stock');
            }
            $q->orWhere('name_sw', 'like', '%hifadhi%')
              ->orWhere('name_en', 'like', '%storage%');
        })->first();

        if (!$storageService) {
            return 0.00;
        }

        $daysInStorage = max(0, now()->diffInDays($batch->created_at));
        $years = floor($daysInStorage / 365);

        if ($years > 0) {
            $rawQty = $this->getBatchRawQuantity($batch);
            return $years * $storageService->rate * $rawQty;
        }

        return 0.00;
    }
}
