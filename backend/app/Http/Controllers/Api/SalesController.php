<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Batch;
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

    public function previewDeductions(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'price_per_kg' => 'required|numeric|min:0',
            'sold_weight_kg' => 'required|numeric|gt:0',
        ]);

        $batch = Batch::with(['farmer'])->findOrFail($validated['batch_id']);

        $soldWeightKg = $validated['sold_weight_kg'];
        $availQty = floatval($batch->intake_quantity > 0 ? $batch->intake_quantity : $batch->current_weight_mt);

        if ($availQty < $soldWeightKg - 0.001) {
            return response()->json([
                'success' => false,
                'message' => 'Kiasi unachotaka kuuza ni kikubwa kuliko mzigo uliopo ('.$availQty.' '.($batch->intake_unit ?? 'Units').').'
            ], 422);
        }

        if ($batch->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii tayari imeshauzwa yote.'
            ], 422);
        }

        $grossSales = $soldWeightKg * $validated['price_per_kg'];
        $batchIds = $this->getRelatedBatchIds($batch);

        // 1. Calculate Storage Fees
        $daysInStorage = max(0, now()->diffInDays($batch->created_at));
        $storageFees = $this->calculateStorageFees($batch);

        // 2. Fetch Unpaid Drying Fees across processing tree
        $dryingJobs = DryingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $dryingFees = $dryingJobs->sum(function($j) {
            return $j->fee_amount > 0 ? $j->fee_amount : 0;
        });

        // 3. Fetch Unpaid Milling Fees across processing tree
        $millingJobs = MillingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $millingFees = $millingJobs->sum(function($j) {
            if ($j->fee_amount > 0 && $j->fee_amount < 500) {
                $weightKg = ($j->output_weight_mt > 0 ? $j->output_weight_mt : $j->input_weight_mt) * 1000;
                return $j->fee_amount * ($weightKg > 0 ? $weightKg : 1);
            }
            return $j->fee_amount ?: 0;
        });

        // 4. Fetch Unpaid Grading Fees across processing tree
        $gradingRecords = GradingRecord::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
        $gradingFees = $gradingRecords->sum('fee_amount');

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
            'net_payout' => $netPayout,
            'days_in_storage' => $daysInStorage,
        ]);
    }

    public function confirmSale(Request $request)
    {
        try {
            $validated = $request->validate([
                'batch_id' => 'required|exists:batches,id',
                'buyer_id' => 'nullable|exists:buyers,id',
                'buyer_name' => 'nullable|string|max:255',
                'price_per_kg' => 'required|numeric|min:0',
                'sold_weight_kg' => 'required|numeric|gt:0',
            ]);
            
            if (empty($validated['buyer_id']) && empty($validated['buyer_name'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tafadhali chagua mnunuzi au ingiza jina la mnunuzi mpya.'
                ], 422);
            }

            $batch = Batch::findOrFail($validated['batch_id']);

            $soldWeightKg = floatval($validated['sold_weight_kg']);
            $availQty = floatval($batch->intake_quantity > 0 ? $batch->intake_quantity : $batch->current_weight_mt);

            if ($availQty < $soldWeightKg - 0.001) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kiasi unachotaka kuuza ni kikubwa kuliko mzigo uliopo ('.$availQty.' '.($batch->intake_unit ?? 'Units').').'
                ], 422);
            }

            if ($batch->status === 'sold') {
                return response()->json([
                    'success' => false,
                    'message' => 'Shehena hii tayari imeshauzwa yote.'
                ], 422);
            }

            $tenant = \App\Models\Tenant::first() ?? \App\Models\Tenant::create(['name' => 'Garanoki Main Store', 'subdomain' => 'garanoki-store', 'status' => 'active']);
            $tenantId = $tenant->id;

            if (!empty($validated['buyer_id'])) {
                $buyer = Buyer::findOrFail($validated['buyer_id']);
            } else {
                $buyer = Buyer::create([
                    'tenant_id' => $tenantId,
                    'name' => $validated['buyer_name'],
                    'phone' => null,
                    'status' => 'active'
                ]);
            }

            // Auto-generate invoice number
            $lastInvoice = Invoice::orderBy('created_at', 'desc')->first();
            $nextNumber = 1001;
            if ($lastInvoice) {
                preg_match('/INV-(\d+)/', $lastInvoice->invoice_number, $matches);
                if (!empty($matches[1])) {
                    $nextNumber = intval($matches[1]) + 1;
                }
            }
            $invoiceNumber = 'INV-' . $nextNumber;

            $pricePerKg = floatval($validated['price_per_kg']);
            $grossSales = $soldWeightKg * $pricePerKg;

            $batchIds = $this->getRelatedBatchIds($batch);

            // Calculate all deductions across processing tree
            $storageFees = $this->calculateStorageFees($batch);
            $dryingJobs = DryingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $dryingFees = $dryingJobs->sum(function($j) {
                return $j->fee_amount > 0 ? $j->fee_amount : 0;
            });
            
            $millingJobs = MillingJob::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $millingFees = $millingJobs->sum(function($j) {
                if ($j->fee_amount > 0 && $j->fee_amount < 500) {
                    $weightKg = ($j->output_weight_mt > 0 ? $j->output_weight_mt : $j->input_weight_mt) * 1000;
                    return $j->fee_amount * ($weightKg > 0 ? $weightKg : 1);
                }
                return $j->fee_amount ?: 0;
            });
            
            $gradingRecords = GradingRecord::whereIn('batch_id', $batchIds)->where('status', '!=', 'paid')->get();
            $gradingFees = $gradingRecords->sum('fee_amount');

            $loans = Loan::where('farmer_id', $batch->farmer_id)->whereIn('status', ['active', 'overdue'])->orderBy('created_at', 'asc')->get();
            $loanPrincipal = $loans->sum('current_balance');

            $totalDeductions = $storageFees + $dryingFees + $millingFees + $gradingFees + $loanPrincipal;
            
            // Waterfall payment logic to figure out what actually gets paid
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
            $paidLoansTotal = 0;
            foreach ($loans as $loan) {
                if ($availableFunds <= 0) break;
                $payAmount = min($availableFunds, $loan->current_balance);
                $paidLoansTotal += $payAmount;
                $availableFunds -= $payAmount;
            }
            
            $netPayout = $availableFunds;
            $actualDeductions = $grossSales - $netPayout;

            $result = DB::transaction(function () use (
                $tenantId, $batch, $buyer, $invoiceNumber, $pricePerKg, $soldWeightKg, $availQty, $grossSales,
                $paidStorage, $paidDrying, $paidMilling, $paidGrading,
                $loans, $actualDeductions, $netPayout, $fundsBeforeLoans,
                $dryingJobs, $millingJobs
            ) {
                // 1. Create Invoice
                $invoice = Invoice::create([
                    'tenant_id' => $tenantId,
                    'buyer_id' => $buyer->id,
                    'invoice_number' => $invoiceNumber,
                    'subtotal' => $grossSales,
                    'vat_amount' => $grossSales * 0.18, // 18% VAT
                    'total_amount' => $grossSales * 1.18,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(30),
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'batch_id' => $batch->id,
                    'quantity_mt' => $soldWeightKg,
                    'unit_price' => $pricePerKg,
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

                // 4. Update Batch status and weight
                $newQty = max(0, $availQty - $soldWeightKg);
                $isFullySold = $newQty <= 0.001;

                $batch->update([
                    'current_weight_mt' => $newQty,
                    'intake_quantity' => $newQty,
                    'status' => $isFullySold ? 'sold' : $batch->status
                ]);

                // 5. Decrement Bin Occupancy
                if ($batch->current_bin_id && $batch->bin) {
                    $bin = $batch->bin;
                    $soldWeightMt = $soldWeightKg / 1000;
                    if ($bin->current_occupancy_mt > 0) {
                        $bin->decrement('current_occupancy_mt', min($bin->current_occupancy_mt, $soldWeightMt));
                    }
                    if ($bin->current_occupancy_mt <= 0) {
                        $bin->update(['status' => 'empty', 'current_occupancy_mt' => 0.00, 'crop_type' => null]);
                    }
                }

                // 6. Sync Farmer Active Status (If all stock sold out, mark inactive)
                $farmerId = $batch->farmer_id;
                $hasActiveStock = \App\Models\Batch::where('farmer_id', $farmerId)
                    ->where('status', '!=', 'sold')
                    ->where('current_weight_mt', '>', 0.001)
                    ->exists();

                if (!$hasActiveStock) {
                    \App\Models\Farmer::where('id', $farmerId)->update(['status' => 'inactive']);
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
        // Fetch storage service from registered services (category 'stock' or name containing hifadhi/storage)
        $storageService = \App\Models\Service::where(function ($q) {
            $q->where('category', 'stock')
              ->orWhere('name_sw', 'like', '%hifadhi%')
              ->orWhere('name_en', 'like', '%storage%');
        })->first();

        if (!$storageService) {
            return 0.00;
        }

        $daysInStorage = max(0, now()->diffInDays($batch->created_at));
        $years = floor($daysInStorage / 365);

        if ($years > 0) {
            $qty = $this->calculateQuantityForUnit($batch->current_weight_mt, $storageService->unit);
            return $years * $storageService->rate * $qty;
        }

        return 0.00;
    }

    private function calculateQuantityForUnit($weightMt, $unit)
    {
        $unit = strtolower($unit);
        if ($unit === 'kg' || $unit === 'kilo') {
            return $weightMt * 1000;
        } elseif ($unit === 'gunia' || $unit === 'bag' || $unit === 'bags') {
            return $weightMt * 10;
        } elseif ($unit === 'roba') {
            return $weightMt * 20;
        } else {
            return $weightMt; // 'mt' or 'ton' or default
        }
    }
}
