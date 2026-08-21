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
            'negotiated_price_per_mt' => 'required|numeric|min:0',
        ]);

        $batch = Batch::with(['farmer'])->findOrFail($validated['batch_id']);

        if ($batch->current_weight_mt <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii haina uzito unaojulikana. Tafadhali ipe huduma ya kukoboa kwanza kupata uzito.'
            ], 422);
        }

        if ($batch->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii tayari imeshauzwa.'
            ], 422);
        }

        $pricePerMt = $validated['negotiated_price_per_mt'];
        
        $grossSales = $batch->current_weight_mt * $pricePerMt;

        // 1. Calculate Storage Fees (fetched from service catalog, charged only per year)
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

        // 5. Fetch Active Loans for the Farmer collateralized by this batch or general active loans
        $loans = Loan::where('farmer_id', $batch->farmer_id)
            ->whereIn('status', ['active', 'overdue'])
            ->get();

        $loanPrincipal = $loans->sum('current_balance');
        $loanInterest = 0.00;

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
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'buyer_id' => 'required|exists:buyers,id',
            'negotiated_price_per_mt' => 'required|numeric|gt:0',
        ]);

        $batch = Batch::findOrFail($validated['batch_id']);

        if ($batch->current_weight_mt <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii haina uzito unaojulikana. Tafadhali ipe huduma ya kukoboa kwanza kupata uzito.'
            ], 422);
        }

        if ($batch->status === 'sold') {
            return response()->json([
                'success' => false,
                'message' => 'Shehena hii tayari imeshauzwa.'
            ], 422);
        }

        $buyer = Buyer::findOrFail($validated['buyer_id']);
        $tenantId = \App\Models\Tenant::first()->id;

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

        $pricePerMt = $validated['negotiated_price_per_mt'];
        $grossSales = $batch->current_weight_mt * $pricePerMt;

        // Calculate all deductions (same logic as preview)
        $storageFees = $this->calculateStorageFees($batch);

        $dryingFees = DryingJob::where('batch_id', $batch->id)->where('status', 'completed')->sum('fee_amount');
        $millingFees = MillingJob::where('batch_id', $batch->id)->where('status', 'completed')->sum('fee_amount');
        $gradingFees = GradingRecord::where('batch_id', $batch->id)->sum('fee_amount');

        $loans = Loan::where('farmer_id', $batch->farmer_id)->whereIn('status', ['active', 'overdue'])->get();
        $loanPrincipal = $loans->sum('current_balance');
        $loanInterest = 0.00;

        $totalDeductions = $storageFees + $dryingFees + $millingFees + $gradingFees + $loanPrincipal;
        $netPayout = max(0, $grossSales - $totalDeductions);

        $result = DB::transaction(function () use (
            $tenantId, $batch, $buyer, $invoiceNumber, $pricePerMt, $grossSales,
            $storageFees, $dryingFees, $millingFees, $gradingFees,
            $loanPrincipal, $loanInterest, $totalDeductions, $netPayout, $loans
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
                'quantity_mt' => $batch->current_weight_mt,
                'unit_price' => $pricePerMt,
                'total_price' => $grossSales,
            ]);

            // 2. Create Settlement
            $settlement = Settlement::create([
                'tenant_id' => $tenantId,
                'farmer_id' => $batch->farmer_id,
                'invoice_id' => $invoice->id,
                'gross_amount' => $grossSales,
                'total_deductions' => $totalDeductions,
                'net_payout' => $netPayout,
                'payment_method' => 'mobile_money',
                'payment_status' => 'settled',
                'payment_reference' => 'TXN-' . rand(100000000, 999999999),
                'settled_at' => now(),
            ]);

            // 3. Record Deductions Breakdown
            if ($storageFees > 0) {
                SettlementDeduction::create([
                    'settlement_id' => $settlement->id,
                    'deduction_type' => 'storage_fee',
                    'amount' => $storageFees,
                ]);
            }
            if ($dryingFees > 0) {
                SettlementDeduction::create([
                    'settlement_id' => $settlement->id,
                    'deduction_type' => 'drying_fee',
                    'amount' => $dryingFees,
                ]);
            }
            if ($millingFees > 0) {
                SettlementDeduction::create([
                    'settlement_id' => $settlement->id,
                    'deduction_type' => 'milling_fee',
                    'amount' => $millingFees,
                ]);
            }
            if ($gradingFees > 0) {
                SettlementDeduction::create([
                    'settlement_id' => $settlement->id,
                    'deduction_type' => 'grading_fee',
                    'amount' => $gradingFees,
                ]);
            }
            if ($loanPrincipal > 0) {
                foreach ($loans as $loan) {
                    SettlementDeduction::create([
                        'settlement_id' => $settlement->id,
                        'deduction_type' => 'loan_principal',
                        'source_reference_id' => $loan->id,
                        'amount' => $loan->current_balance,
                    ]);

                    // Settle Loan in DB
                    $loan->update([
                        'current_balance' => 0.00,
                        'accrued_interest' => 0.00,
                        'status' => 'settled',
                    ]);
                }
            }

            // 4. Update Batch status to Sold
            $batch->update(['status' => 'sold']);

            // 5. Decrement Bin Occupancy
            if ($batch->current_bin_id) {
                $bin = $batch->bin;
                $bin->decrement('current_occupancy_mt', $batch->current_weight_mt);
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
