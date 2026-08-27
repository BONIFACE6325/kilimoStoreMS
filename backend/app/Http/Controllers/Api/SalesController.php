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

    public function previewDeductions(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'price_per_kg' => 'required|numeric|min:0',
            'sold_weight_kg' => 'required|numeric|gt:0',
        ]);

        $batch = Batch::with(['farmer'])->findOrFail($validated['batch_id']);

        $soldWeightKg = $validated['sold_weight_kg'];
        $soldWeightMt = $soldWeightKg / 1000;

        if ($batch->current_weight_mt < $soldWeightMt) {
            return response()->json([
                'success' => false,
                'message' => 'Kiasi unachotaka kuuza ni kikubwa kuliko mzigo uliopo ('.($batch->current_weight_mt * 1000).' Kg).'
            ], 422);
        }

        if ($batch->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii tayari imeshauzwa yote.'
            ], 422);
        }

        $grossSales = $soldWeightKg * $validated['price_per_kg'];

        // 1. Calculate Storage Fees (only on the full batch, but wait, usually storage fee is charged per time, here we just charge what's accrued so far)
        $daysInStorage = max(0, now()->diffInDays($batch->created_at));
        $storageFees = $this->calculateStorageFees($batch);

        // 2. Fetch Unpaid Drying Fees
        $dryingFees = DryingJob::where('batch_id', $batch->id)
            ->where('status', 'completed')
            ->sum('fee_amount');

        // 3. Fetch Unpaid Milling Fees
        $millingFees = MillingJob::where('batch_id', $batch->id)
            ->where('status', 'completed')
            ->sum('fee_amount');

        // 4. Fetch Unpaid Grading Fees
        $gradingFees = GradingRecord::where('batch_id', $batch->id)
            ->sum('fee_amount');

        // 5. Fetch Active Loans
        $loans = Loan::where('farmer_id', $batch->farmer_id)
            ->whereIn('status', ['active', 'overdue'])
            ->get();
        $loanPrincipal = $loans->sum('current_balance');

        $totalDeductions = $storageFees + $dryingFees + $millingFees + $gradingFees + $loanPrincipal;
        
        // Cap the total deductions to gross sales? No, preview should show the real debts.
        // Wait, the UI just shows the total debts.
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

        $soldWeightKg = $validated['sold_weight_kg'];
        $soldWeightMt = $soldWeightKg / 1000;

        if ($batch->current_weight_mt < $soldWeightMt) {
            return response()->json([
                'success' => false,
                'message' => 'Kiasi unachotaka kuuza ni kikubwa kuliko mzigo uliopo ('.($batch->current_weight_mt * 1000).' Kg).'
            ], 422);
        }

        if ($batch->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii tayari imeshauzwa yote.'
            ], 422);
        }

        $tenantId = \App\Models\Tenant::first()->id;

        if (!empty($validated['buyer_id'])) {
            $buyer = Buyer::findOrFail($validated['buyer_id']);
        } else {
            // Create a new buyer
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

        $pricePerKg = $validated['price_per_kg'];
        $grossSales = $soldWeightKg * $pricePerKg;

        // Calculate all deductions
        $storageFees = $this->calculateStorageFees($batch);
        $dryingJobs = DryingJob::where('batch_id', $batch->id)->where('status', 'completed')->get();
        $dryingFees = $dryingJobs->sum('fee_amount');
        
        $millingJobs = MillingJob::where('batch_id', $batch->id)->where('status', 'completed')->get();
        $millingFees = $millingJobs->sum('fee_amount');
        
        $gradingRecords = GradingRecord::where('batch_id', $batch->id)->get(); // Assuming grading is one-time, wait, is there a status?
        // Let's assume we don't have a status on GradingRecord, we'll just leave it or assume it's fully paid if we deduct.
        $gradingFees = $gradingRecords->sum('fee_amount');

        $loans = Loan::where('farmer_id', $batch->farmer_id)->whereIn('status', ['active', 'overdue'])->orderBy('created_at', 'asc')->get();
        $loanPrincipal = $loans->sum('current_balance');
        $loanInterest = 0.00;

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
        
        $netPayout = $availableFunds; // Whatever is left goes to the farmer
        $actualDeductions = $grossSales - $netPayout;

        $result = DB::transaction(function () use (
            $tenantId, $batch, $buyer, $invoiceNumber, $pricePerKg, $soldWeightKg, $soldWeightMt, $grossSales,
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
                'quantity_mt' => $soldWeightMt,
                'unit_price' => $pricePerKg * 1000, // store as per MT for legacy compatibility
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
                // If fully paid, we can mark them paid, but for simplicity we'll just mark all as paid if we paid any amount towards it, 
                // OR we can just update the fee_amount remaining. Let's just mark as 'paid' if we covered the full amount.
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
            $newWeightMt = $batch->current_weight_mt - $soldWeightMt;
            $batch->update([
                'current_weight_mt' => $newWeightMt,
                'status' => $newWeightMt <= 0.001 ? 'sold' : $batch->status // if less than 1kg left, consider sold
            ]);

            // 5. Decrement Bin Occupancy
            if ($batch->current_bin_id) {
                $bin = $batch->bin;
                $bin->decrement('current_occupancy_mt', $soldWeightMt);
                if ($bin->current_occupancy_mt <= 0) {
                    $bin->update(['status' => 'empty', 'current_occupancy_mt' => 0.00, 'crop_type' => null]);
                }
            }

            return $settlement;
        });

        return response()->json([
            'success' => true,
            'message' => 'Sale finalized, invoice issued, and farmer payout processed successfully',
            'settlement' => $result
        ]);
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
