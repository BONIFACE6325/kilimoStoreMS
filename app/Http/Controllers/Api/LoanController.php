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
                'farmer_name' => $loan->farmer->name,
                'collateral_batch' => $loan->collateralBatch ? $loan->collateralBatch->batch_code : 'N/A',
                'principal_amount' => $loan->principal_amount,
                'current_balance' => $loan->current_balance,
                'interest_rate' => $loan->interest_rate_annual,
                'accrued_interest' => $loan->accrued_interest,
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
            'principal_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after:today',
        ]);

        $batch = Batch::findOrFail($validated['collateral_batch_id']);
        if ($batch->farmer_id !== $validated['farmer_id']) {
            return response()->json(['error' => 'Selected batch does not belong to the selected farmer'], 422);
        }

        $tenantId = \App\Models\Tenant::first()->id;

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

        $loan = Loan::create([
            'tenant_id' => $tenantId,
            'farmer_id' => $validated['farmer_id'],
            'collateral_batch_id' => $validated['collateral_batch_id'],
            'loan_code' => $loanCode,
            'principal_amount' => $validated['principal_amount'],
            'interest_rate_annual' => 0.00, // default rate (no interest)
            'current_balance' => $validated['principal_amount'],
            'due_date' => $validated['due_date'],
            'status' => 'pending_approval',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan application submitted successfully',
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
