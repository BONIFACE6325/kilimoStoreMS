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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function getBuyers()
    {
        $buyers = Buyer::where('status', 'active')->orderBy('name')->get();
        return response()->json($buyers);
    }

    public function indexInvoices()
    {
        $invoices = Invoice::with(['buyer'])->orderBy('created_at', 'desc')->get();
        return response()->json($invoices);
    }

    public function indexSettlements()
    {
        $settlements = Settlement::with(['farmer', 'invoice.buyer', 'deductions'])->orderBy('created_at', 'desc')->get();
        return response()->json($settlements);
    }

    private function getRelatedBatchIds($batch)
    {
        $ids = [$batch->id];
        if ($batch->parent_batch_id) {
            $ids[] = $batch->parent_batch_id;
            $siblingIds = Batch::where('parent_batch_id', $batch->parent_batch_id)->pluck('id')->toArray();
            $ids = array_merge($ids, $siblingIds);
        }
        $childIds = Batch::where('parent_batch_id', $batch->id)->pluck('id')->toArray();
        $ids = array_merge($ids, $childIds);

        return array_unique($ids);
    }

    private function getBatchRawQuantity($batch)
    {
        if (!$batch) return 0.0;
        $qty = floatval($batch->intake_quantity ?? 0);
        if ($qty > 0) return $qty;
        return floatval($batch->current_weight_mt ?? $batch->initial_weight_mt ?? 0);
    }

    private function calculateJobFee($job, $batch)
    {
        if (!$job) return 0.0;
        
        $rawQty = $this->getBatchRawQuantity($batch);
        if ($rawQty <= 0) $rawQty = 1.0;

        $rate = floatval($job->rate ?? $job->unit_price ?? 0);
        if ($rate <= 0) {
            $rate = floatval($job->fee_amount ?? $job->fee ?? $job->cost ?? 0);
        }

        if ($rate > 0) {
            return $rate * $rawQty;
        }

        return floatval($job->fee_amount ?? 0);
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
        $daysInStorage = max(0, now()->diffInDays($batch->created_at));
        $storageFees = $this->calculateStorageFees($batch);

        // 2. Fetch Unpaid Drying Fees
        $dryingJobs = DryingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $dryingFees = $dryingJobs->sum(function($j) use ($batch) {
            return $this->calculateJobFee($j, $batch);
        });

        // 3. Fetch Unpaid Milling Fees
        $millingJobs = MillingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $millingFees = $millingJobs->sum(function($j) use ($batch) {
            return $this->calculateJobFee($j, $batch);
        });

        // 4. Fetch Unpaid Grading Fees
        $gradingRecords = GradingRecord::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $gradingFees = $gradingRecords->sum(function($j) use ($batch) {
            return $this->calculateJobFee($j, $batch);
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
            $lastInvoice = Invoice::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->first();
            $nextNumber = 1001;
            if ($lastInvoice) {
                preg_match('/INV-(\d+)/', $lastInvoice->invoice_number, $matches);
                if (!empty($matches[1])) {
                    $nextNumber = intval($matches[1]) + 1;
                }
            }
            $invoiceNumber = 'INV-' . $nextNumber;

            $pricePerUnit = floatval($validated['price_per_kg']);
            $grossSales = $soldQty * $pricePerUnit;

            $batchIds = $this->getRelatedBatchIds($batch);

            // Calculate all deductions across processing tree
            $storageFees = $this->calculateStorageFees($batch);
            $dryingJobs = DryingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $dryingFees = $dryingJobs->sum(function($j) use ($batch) {
                return $this->calculateJobFee($j, $batch);
            });
            
            $millingJobs = MillingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $millingFees = $millingJobs->sum(function($j) use ($batch) {
                return $this->calculateJobFee($j, $batch);
            });
            
            $gradingRecords = GradingRecord::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $gradingFees = $gradingRecords->sum(function($j) use ($batch) {
                return $this->calculateJobFee($j, $batch);
            });

            $loans = Loan::where('farmer_id', $batch->farmer_id)->whereIn('status', ['active', 'overdue'])->orderBy('created_at', 'asc')->get();
            $loanPrincipal = $loans->sum('current_balance');

            $totalDeductions = $storageFees + $dryingFees + $millingFees + $gradingFees + $loanPrincipal;
            
            // Waterfall payment logic
            $availableFunds = $grossSales;
            
            $paidStorage = min($availableFunds, $storageFees);
            $availableFunds -= $paidStorage;
            
            $paidDrying = min($availableFunds, $dryingFees);
            $availableFunds -= $paidDrying;
            
            $paidMilling = min($availableFunds, $millingFees);
            $availableFunds -= $paidMilling;
            
            $paidGrading = min($availableFunds, $gradingFees);
            $availableFunds -= $paidGrading;
            
            $fundsBeforeLoans = $availableFunds;
            
            $netPayout = $availableFunds;
            $actualDeductions = $grossSales - $netPayout;

            $result = DB::transaction(function () use (
                $tenantId, $batch, $buyer, $invoiceNumber, $pricePerUnit, $soldQty, $availQty, $grossSales,
                $paidStorage, $paidDrying, $paidMilling, $paidGrading,
                $loans, $actualDeductions, $netPayout, $fundsBeforeLoans,
                $dryingJobs, $millingJobs, $gradingRecords
            ) {
                // 1. Create Invoice
                $invoice = Invoice::create([
                    'tenant_id' => $tenantId,
                    'buyer_id' => $buyer->id,
                    'invoice_number' => $invoiceNumber,
                    'subtotal' => $grossSales,
                    'vat_amount' => $grossSales * 0.18,
                    'total_amount' => $grossSales * 1.18,
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
                        'amount' => $paidStorage,
                    ]);
                }
                if ($paidDrying > 0) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'drying_fee',
                        'amount' => $paidDrying,
                    ]);
                    if ($paidDrying >= $dryingJobs->sum('fee_amount')) {
                        foreach($dryingJobs as $dj) $dj->update(['status' => 'paid']);
                    }
                }
                if ($paidMilling > 0) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'milling_fee',
                        'amount' => $paidMilling,
                    ]);
                    if ($paidMilling >= $millingJobs->sum('fee_amount')) {
                        foreach($millingJobs as $mj) $mj->update(['status' => 'paid']);
                    }
                }
                if ($paidGrading > 0) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'grading_fee',
                        'amount' => $paidGrading,
                    ]);
                    if ($paidGrading >= $gradingRecords->sum('fee_amount')) {
                        foreach($gradingRecords as $gr) $gr->update(['status' => 'paid']);
                    }
                }
                
                // Loans waterfall
                $loanFunds = $fundsBeforeLoans;
                foreach ($loans as $loan) {
                    if ($loanFunds <= 0) break;
                    
                    $payAmount = min($loanFunds, $loan->current_balance);
                    $loanFunds -= $payAmount;
                    
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'loan_principal',
                        'source_reference_id' => $loan->id,
                        'amount' => $payAmount,
                    ]);

                    $newBalance = $loan->current_balance - $payAmount;
                    $loan->update([
                        'current_balance' => $newBalance,
                        'status' => $newBalance <= 0 ? 'paid' : 'active',
                    ]);
                    
                    \App\Models\LoanTransaction::create([
                        'loan_id' => $loan->id,
                        'transaction_type' => 'payment',
                        'amount' => $payAmount,
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
