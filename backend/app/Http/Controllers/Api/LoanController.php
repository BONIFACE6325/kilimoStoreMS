<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\Batch;
use App\Models\Farmer;
use App\Traits\HasTenantScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    use HasTenantScope;

    public function index(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $query = Loan::where('tenant_id', $tenantId)->with(['farmer', 'collateralBatch', 'transactions']);

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
                'farmer_phone' => $loan->farmer ? $loan->farmer->phone : 'N/A',
                'collateral_batch' => $loan->collateralBatch ? $loan->collateralBatch->batch_code : 'N/A',
                'principal_amount' => $loan->principal_amount,
                'current_balance' => $loan->current_balance,
                'interest_rate' => 0.00, // Strictly 0.00% (No Interest)
                'accrued_interest' => 0.00,
                'due_date' => $loan->due_date,
                'status' => $loan->status,
                'created_at' => $loan->created_at->format('Y-m-d H:i'),
                'transactions' => $loan->transactions->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'transaction_type' => $t->transaction_type,
                        'amount' => $t->amount,
                        'reference_number' => $t->reference_number,
                        'created_at' => $t->created_at->format('Y-m-d H:i'),
                    ];
                }),
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        try {
            $tenantId = $this->getTenantId($request);

            $validated = $request->validate([
                'farmer_id' => 'nullable|exists:farmers,id',
                'new_borrower_name' => 'nullable|string|max:255',
                'new_borrower_phone' => 'nullable|string|max:50',
                'new_borrower_nida' => 'nullable|string|max:100',
                'new_borrower_address' => 'nullable|string|max:255',
                'collateral_batch_id' => 'nullable|exists:batches,id',
                'principal_amount' => 'required|numeric|min:1',
                'due_date' => 'nullable|date',
            ]);

            if (empty($validated['farmer_id']) && empty($validated['new_borrower_name'])) {
                return response()->json([
                    'error' => 'Tafadhali chagua mkulima au ingiza jina la mkopaji mpya!'
                ], 422);
            }

            $collateralBatchId = $validated['collateral_batch_id'] ?? null;

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

            $loan = DB::transaction(function () use ($tenantId, $validated, $collateralBatchId, $loanCode) {
                $farmerId = $validated['farmer_id'] ?? null;

                if (!$farmerId) {
                    $nextFrmNum = 1;
                    do {
                        $farmerCode = 'FRM-' . str_pad($nextFrmNum, 3, '0', STR_PAD_LEFT);
                        $exists = Farmer::where('tenant_id', $tenantId)->where('farmer_code', $farmerCode)->exists();
                        if ($exists) {
                            $nextFrmNum++;
                        }
                    } while ($exists);

                    $newFarmer = Farmer::create([
                        'tenant_id' => $tenantId,
                        'farmer_code' => $farmerCode,
                        'name' => trim($validated['new_borrower_name']),
                        'phone' => $validated['new_borrower_phone'] ?? null,
                        'national_id' => $validated['new_borrower_nida'] ?? null,
                        'region' => $validated['new_borrower_address'] ?? null,
                        'status' => 'inactive',
                    ]);
                    $farmerId = $newFarmer->id;
                } else {
                    $farmer = Farmer::findOrFail($farmerId);
                }

                if ($collateralBatchId) {
                    $batch = Batch::findOrFail($collateralBatchId);
                    if ($batch->farmer_id !== $farmerId) {
                        throw new \InvalidArgumentException('Batch iliyochaguliwa haimhusu mkulima huyu!');
                    }
                    if ($batch->status === 'sold') {
                        throw new \InvalidArgumentException('Huwezi kumpa mkopo mkulima kwa batch iliyouzwa!');
                    }
                }

                $l = Loan::create([
                    'tenant_id' => $tenantId,
                    'farmer_id' => $farmerId,
                    'collateral_batch_id' => $collateralBatchId,
                    'loan_code' => $loanCode,
                    'principal_amount' => $validated['principal_amount'],
                    'interest_rate_annual' => 0.00, // Strictly 0.00%
                    'current_balance' => $validated['principal_amount'],
                    'due_date' => $validated['due_date'] ?? now()->addYear()->format('Y-m-d'),
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

            try {
                event(new \App\Events\StoreDataUpdated($tenantId, 'loan', 'created'));
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => 'Mkopo umesajiliwa na kutolewa kikamilifu bila riba (0% Interest)',
                'loan' => $loan
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $errs = implode(', ', \Illuminate\Support\Arr::flatten($ve->errors()));
            return response()->json([
                'error' => 'Taarifa za fomu zina kasoro: ' . $errs,
                'errors' => $ve->errors()
            ], 422);
        } catch (\InvalidArgumentException $iae) {
            return response()->json([
                'error' => $iae->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Loan creation failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Kosa la Server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status === 'settled' || floatval($loan->current_balance) <= 0) {
            return response()->json([
                'error' => 'Huwezi kufanya marekebisho kwa mkopo ambao umeshalipwa na kukamilika!'
            ], 422);
        }

        $validated = $request->validate([
            'principal_amount' => 'required|numeric|min:1',
            'due_date' => 'nullable|date',
        ]);

        $diff = floatval($validated['principal_amount']) - floatval($loan->principal_amount);
        $newBalance = max(0, floatval($loan->current_balance) + $diff);

        $loan->update([
            'principal_amount' => $validated['principal_amount'],
            'current_balance' => $newBalance,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Taarifa za mkopo zimesasishwa kikamilifu',
            'loan' => $loan
        ]);
    }

    public function destroy($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status === 'settled' || floatval($loan->current_balance) <= 0) {
            return response()->json([
                'error' => 'Huwezi kufuta mkopo ambao umeshalipwa na kukamilika!'
            ], 422);
        }

        // Delete associated transactions first
        LoanTransaction::where('loan_id', $loan->id)->delete();
        $loan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mkopo umefutwa kikamilifu'
        ]);
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
