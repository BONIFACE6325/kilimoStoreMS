<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\Batch;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $query = Loan::with(['farmer', 'collateralBatch']);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('farmer_id')) {
            $query->where('farmer_id', $request->input('farmer_id'));
        }

        $loans = $query->orderBy('created_at', 'desc')->get();

        $result = $loans->map(function ($loan) {
            return [
                'id' => $loan->id,
                'loan_code' => $loan->loan_code,
                'farmer_name' => $loan->farmer ? $loan->farmer->name : 'N/A',
                'collateral_batch' => $loan->collateralBatch ? $loan->collateralBatch->batch_code : 'N/A',
                'principal_amount' => $loan->principal_amount,
                'current_balance' => $loan->current_balance,
                'interest_rate' => 0.00, // Strictly 0.00% (No Interest)
                'accrued_interest' => 0.00,
                'due_date' => $loan->due_date,
                'status' => $loan->status,
                'created_at' => $loan->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'collateral_batch_id' => 'required|exists:batches,id',
            'principal_amount' => 'required|numeric|min:1',
            'due_date' => 'nullable|date',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);
        $batch = Batch::findOrFail($validated['collateral_batch_id']);

        if ($batch->farmer_id !== $validated['farmer_id']) {
            return response()->json(['error' => 'Batch iliyochaguliwa haimhusu mkulima huyu!'], 422);
        }

        // 1. RULE: Farmer must have active stock ghalani (status != sold and weight > 0)
        $batchWeightKg = floatval($batch->current_weight_mt ?? 0) * 1000;
        if ($batch->status === 'sold' || $batchWeightKg <= 0) {
            return response()->json([
                'error' => 'Huwezi kumpa mkopo mkulima kwa batch iliyoezwa au isiyo na mzigo ghalani!'
            ], 422);
        }

        // 2. RULE: Loan cannot exceed 50% of the collateral crop value (Nusu ya thamani ya mzigo ghalani)
        // Baseline price per Kg = TZS 1,000 (Max Loan per Kg = TZS 500)
        $estimatedCropValue = $batchWeightKg * 1000;
        $maxLoanAllowed = $estimatedCropValue * 0.50; // 50% limit

        if ($validated['principal_amount'] > $maxLoanAllowed) {
            return response()->json([
                'error' => 'Kiasi cha mkopo unachoomba (Tsh ' . number_format($validated['principal_amount']) . ') kinazidi kikomo cha 50% ya thamani ya mzigo ghalani! Kikomo cha juu kwa batch hii ni Tsh ' . number_format($maxLoanAllowed) . ' (kulingana na Kg ' . number_format($batchWeightKg) . ' zilizopo ghalani).'
            ], 422);
        }

        $tenant = \App\Models\Tenant::first() ?? \App\Models\Tenant::create(['name' => 'Garanoki Main Store', 'subdomain' => 'garanoki-store', 'status' => 'active']);
        $tenantId = $tenant->id;

        // Auto-generate code
        $lastLoan = Loan::orderBy('created_at', 'desc')->first();
        $nextNumber = 2042;
        if ($lastLoan) {
            preg_match('/LN-(\d+)/', $lastLoan->loan_code, $matches);
            if (!empty($matches[1])) {
                $nextNumber = intval($matches[1]) + 1;
            }
        }
        $loanCode = 'LN-' . $nextNumber;

        $loan = DB::transaction(function () use ($tenantId, $validated, $loanCode) {
            $l = Loan::create([
                'tenant_id' => $tenantId,
                'farmer_id' => $validated['farmer_id'],
                'collateral_batch_id' => $validated['collateral_batch_id'],
                'loan_code' => $loanCode,
                'principal_amount' => $validated['principal_amount'],
                'interest_rate_annual' => 0.00, // Strictly 0.00%
                'current_balance' => $validated['principal_amount'],
                'due_date' => $validated['due_date'] ?? now()->format('Y-m-d'),
                'status' => 'active',
                'disbursed_at' => now(),
            ]);

            LoanTransaction::create([
                'loan_id' => $l->id,
                'transaction_type' => 'disbursement',
                'amount' => $l->principal_amount,
                'reference_number' => 'DISB-' . rand(1000, 9999),
            ]);

            return $l;
        });

        return response()->json([
            'success' => true,
            'message' => 'Mkopo umesajiliwa na kutolewa kikamilifu bila riba (0% Interest)',
            'loan' => $loan
        ], 201);
    }

    public function approve($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'pending_approval') {
            return response()->json(['error' => 'Only pending loans can be approved'], 422);
        }

        DB::transaction(function () use ($loan) {
            $loan->update([
                'status' => 'active',
                'disbursed_at' => now(),
            ]);

            // Record transaction
            LoanTransaction::create([
                'loan_id' => $loan->id,
                'transaction_type' => 'disbursement',
                'amount' => $loan->principal_amount,
                'reference_number' => 'DISB-' . rand(1000, 9999),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Loan approved and disbursed successfully',
            'loan' => $loan
        ]);
    }

    public function repay(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reference_number' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($loan, $validated) {
            $amount = $validated['amount'];

            if ($amount > 0) {
                $loan->current_balance = max(0, $loan->current_balance - $amount);
            }

            if ($loan->current_balance <= 0) {
                $loan->status = 'settled';
            }

            $loan->save();

            // Record transaction
            LoanTransaction::create([
                'loan_id' => $loan->id,
                'transaction_type' => 'payment',
                'amount' => $validated['amount'],
                'reference_number' => $validated['reference_number'] ?? 'PAY-' . rand(1000, 9999),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'loan' => $loan
        ]);
    }
}
