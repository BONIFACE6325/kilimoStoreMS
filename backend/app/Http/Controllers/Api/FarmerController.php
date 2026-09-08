<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Traits\HasTenantScope;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FarmerController extends Controller
{
    use HasTenantScope;

    public function index(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $query = Farmer::where('tenant_id', $tenantId);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('page') || $request->has('per_page')) {
            $perPage = $request->input('per_page', 15);
            $paginated = $query->orderBy('name')->paginate($perPage);

            $result = $paginated->getCollection()->map(function ($farmer) {
                return self::mapFarmerStats($farmer);
            });

            $paginatedArray = $paginated->toArray();
            $paginatedArray['data'] = $result;

            $globalTotal = Farmer::where('tenant_id', $tenantId)->count();
            $globalActive = Farmer::where('tenant_id', $tenantId)->where('status', 'active')->count();
            $globalLoans = Farmer::where('tenant_id', $tenantId)->whereHas('loans', function($q) {
                $q->whereIn('status', ['active', 'overdue']);
            })->count();
            $globalRegions = Farmer::where('tenant_id', $tenantId)->whereNotNull('region')->distinct('region')->count('region');

            $paginatedArray['stats'] = [
                'total' => $globalTotal,
                'active' => $globalActive,
                'inactive' => $globalTotal - $globalActive,
                'with_loans' => $globalLoans,
                'regions' => $globalRegions
            ];

            return response()->json($paginatedArray);
        } else {
            $farmers = $query->orderBy('name')->get();
            $result = $farmers->map(function ($farmer) {
                return self::mapFarmerStats($farmer);
            });
            return response()->json($result);
        }
    }

    private static function mapFarmerStats($farmer) {
        $batches = $farmer->batches()->get();
        $loans = $farmer->loans()->get();
        $settlements = $farmer->settlements()->get();

        $activeStock = $batches->where('status', '!=', 'sold')->sum('current_weight_mt');
        $expectedStatus = ($activeStock > 0.001) ? 'active' : 'inactive';

        if ($farmer->status !== $expectedStatus) {
            $farmer->update(['status' => $expectedStatus]);
        }

        return [
            'id' => $farmer->id,
            'farmer_code' => $farmer->farmer_code,
            'name' => $farmer->name,
            'phone' => $farmer->phone,
            'national_id' => $farmer->national_id,
            'region' => $farmer->region,
            'district' => $farmer->district,
            'ward' => $farmer->ward,
            'village' => $farmer->village,
            'street' => $farmer->street,
            'status' => $expectedStatus,
            'total_deposited' => $batches->whereNull('parent_batch_id')->sum('initial_weight_mt'),
            'active_stock' => $activeStock,
            'active_loans' => $loans->where('status', 'active')->count(),
            'loan_balance' => $loans->whereIn('status', ['active', 'overdue'])->sum('current_balance'),
            'total_net_payout' => $settlements->sum('net_payout'),
            'created_at' => $farmer->created_at->format('Y-m-d H:i'),
        ];
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:50',
                'national_id' => 'nullable|string|max:100',
                'region' => 'nullable|string|max:100',
                'district' => 'nullable|string|max:100',
                'ward' => 'nullable|string|max:100',
                'village' => 'nullable|string|max:100',
                'street' => 'nullable|string|max:100',
            ]);

            // Auto-resolve tenant safely from request header / context
            $tenantId = $this->getTenantId($request);

            // Auto-generate code uniquely without collisions
            $nextNumber = 1;
            do {
                $farmerCode = 'FRM-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                $exists = Farmer::where('tenant_id', $tenantId)->where('farmer_code', $farmerCode)->exists();
                if ($exists) {
                    $nextNumber++;
                }
            } while ($exists);

            $farmer = Farmer::create(array_merge($validated, [
                'tenant_id' => $tenantId,
                'farmer_code' => $farmerCode,
                'status' => 'inactive',
            ]));

            try {
                event(new \App\Events\StoreDataUpdated($tenantId, 'farmer', 'created'));
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => 'Mkulima amesajiliwa kikamilifu',
                'farmer' => $farmer
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $errs = implode(', ', \Illuminate\Support\Arr::flatten($ve->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Taarifa za fomu zina kasoro: ' . $errs,
                'errors' => $ve->errors()
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Farmer registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kosa la Server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $farmer = Farmer::findOrFail($id);
        $batches = $farmer->batches()
            ->with(['dryingJobs.service', 'millingJobs.service', 'gradingRecords'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $loans = $farmer->loans()->with(['collateralBatch', 'transactions'])->orderBy('created_at', 'desc')->get();
        $settlements = $farmer->settlements()->with(['invoice.buyer', 'invoice.items.batch', 'deductions'])->orderBy('created_at', 'desc')->get();

        $services = collect();
        $batchIds = $batches->pluck('id');
        $allInvoiceItems = \App\Models\InvoiceItem::whereIn('batch_id', $batchIds)
            ->with(['invoice.buyer', 'invoice.settlement.deductions'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('batch_id');

        // Append applied_services and sales_summary to each batch for frontend, and gather all services
        $batches->transform(function ($batch) use (&$services, $allInvoiceItems) {
            if ($batch->current_weight_mt <= 0 && $batch->status !== 'transformed') {
                $batch->status = 'sold';
                $batch->current_weight_mt = 0;
                $batch->save();
            }

            $appliedServices = [];

            foreach ($batch->dryingJobs as $job) {
                $alreadyPaid = (float) \App\Models\SettlementDeduction::where('source_reference_id', $job->id)->sum('amount');
                $feeAmount = (float) ($job->fee_amount ?? 0);
                $unpaidFee = max(0.0, $feeAmount - $alreadyPaid);
                if ($job->status !== 'paid' && $feeAmount > 0 && $unpaidFee <= 0.001) {
                    $job->update(['status' => 'paid']);
                }

                if ($job->service_id) $appliedServices[] = $job->service_id;
                $services->push([
                    'id' => $job->id,
                    'job_id' => $job->id,
                    'service_id' => $job->service_id,
                    'batch_code' => $batch->batch_code,
                    'batch_id' => $batch->id,
                    'type' => 'Drying',
                    'service_name' => $job->service ? $job->service->name_sw : ($job->machine_id ?? 'Kukausha'),
                    'fee_amount' => $feeAmount,
                    'already_paid' => $alreadyPaid,
                    'unpaid_fee' => $unpaidFee,
                    'rate' => $job->service ? $job->service->rate : null,
                    'unit' => $job->service ? $job->service->unit : 'gunia',
                    'status' => $job->status,
                    'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            foreach ($batch->millingJobs as $job) {
                $alreadyPaid = (float) \App\Models\SettlementDeduction::where('source_reference_id', $job->id)->sum('amount');
                $feeAmount = (float) ($job->fee_amount ?? 0);
                $unpaidFee = max(0.0, $feeAmount - $alreadyPaid);
                if ($job->status !== 'paid' && $feeAmount > 0 && $unpaidFee <= 0.001) {
                    $job->update(['status' => 'paid']);
                }

                if ($job->service_id) $appliedServices[] = $job->service_id;
                $services->push([
                    'id' => $job->id,
                    'job_id' => $job->id,
                    'service_id' => $job->service_id,
                    'batch_code' => $batch->batch_code,
                    'batch_id' => $batch->id,
                    'type' => 'Milling',
                    'service_name' => $job->service ? $job->service->name_sw : ($job->machine_id ?? 'Kukoboa'),
                    'fee_amount' => $feeAmount,
                    'already_paid' => $alreadyPaid,
                    'unpaid_fee' => $unpaidFee,
                    'rate' => $job->service ? $job->service->rate : null,
                    'unit' => $job->service ? $job->service->unit : 'gunia',
                    'status' => $job->status,
                    'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            foreach ($batch->gradingRecords as $job) {
                $alreadyPaid = (float) \App\Models\SettlementDeduction::where('source_reference_id', $job->id)->sum('amount');
                $feeAmount = (float) ($job->fee_amount ?? 0);
                $unpaidFee = max(0.0, $feeAmount - $alreadyPaid);
                if ($job->status !== 'paid' && $feeAmount > 0 && $unpaidFee <= 0.001) {
                    $job->update(['status' => 'paid']);
                }

                if ($job->service_id) $appliedServices[] = $job->service_id;
                $services->push([
                    'id' => $job->id,
                    'job_id' => $job->id,
                    'service_id' => $job->service_id,
                    'batch_code' => $batch->batch_code,
                    'batch_id' => $batch->id,
                    'type' => 'Grading',
                    'service_name' => 'Kupanga / Grading',
                    'fee_amount' => $feeAmount,
                    'already_paid' => $alreadyPaid,
                    'unpaid_fee' => $unpaidFee,
                    'rate' => null,
                    'unit' => 'gunia',
                    'status' => $job->status,
                    'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            // Compute sales summary for this batch
            $items = $allInvoiceItems->get($batch->id, collect());
            $totalSoldQty = (float) $items->sum('quantity_mt');
            $totalGrossSales = (float) $items->sum('total_price');

            $isTransformed = ($batch->status === 'transformed');
            $isSold = ($batch->status === 'sold');
            
            if ($isTransformed || $isSold || (float) $batch->current_weight_mt <= 0.001) {
                $currentQty = 0.0;
            } else {
                $currentQty = (float) ($batch->intake_quantity > 0 ? $batch->intake_quantity : ($batch->current_weight_mt ?? 0));
            }

            if ($totalSoldQty > 0) {
                $originalQty = $currentQty + $totalSoldQty;
            } else {
                $originalQty = (float) ($batch->intake_quantity > 0 ? $batch->intake_quantity : ($batch->initial_weight_mt ?? $batch->current_weight_mt ?? 0));
            }

            $salesRecords = [];
            foreach ($items as $item) {
                $inv = $item->invoice;
                $settlement = $inv ? $inv->settlement : null;
                $settledAt = null;
                if ($settlement && $settlement->settled_at) {
                    $settledAt = $settlement->settled_at instanceof \DateTimeInterface 
                        ? $settlement->settled_at->format('Y-m-d H:i') 
                        : (string) $settlement->settled_at;
                } elseif ($item->created_at) {
                    $settledAt = $item->created_at instanceof \DateTimeInterface 
                        ? $item->created_at->format('Y-m-d H:i') 
                        : (string) $item->created_at;
                }

                $salesRecords[] = [
                    'id' => $item->id,
                    'invoice_id' => $item->invoice_id,
                    'invoice_number' => $inv ? $inv->invoice_number : null,
                    'buyer_name' => ($inv && $inv->buyer) ? $inv->buyer->name : 'Mteja wa Jumla',
                    'quantity_sold' => (float) $item->quantity_mt,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                    'gross_amount' => $settlement ? (float) $settlement->gross_amount : (float) $item->total_price,
                    'total_deductions' => $settlement ? (float) $settlement->total_deductions : 0,
                    'net_payout' => $settlement ? (float) $settlement->net_payout : (float) $item->total_price,
                    'payment_status' => $settlement ? $settlement->payment_status : 'settled',
                    'payment_method' => $settlement ? $settlement->payment_method : 'mobile_money',
                    'payment_reference' => $settlement ? $settlement->payment_reference : null,
                    'settled_at' => $settledAt,
                ];
            }

            $isFullySold = ($batch->status === 'sold' || (count($salesRecords) > 0 && $currentQty <= 0.001));
            $isPartiallySold = (count($salesRecords) > 0 && $currentQty > 0.001);

            $salesSummary = [
                'has_sales' => count($salesRecords) > 0,
                'total_sold_qty' => $totalSoldQty,
                'total_sales_amount' => $totalGrossSales,
                'original_quantity' => $originalQty,
                'remaining_quantity' => $currentQty,
                'is_fully_sold' => $isFullySold,
                'is_partially_sold' => $isPartiallySold,
                'records' => $salesRecords,
            ];

            // Also attach it directly to the model as an attribute before toArray()
            $batch->setAttribute('applied_services', $appliedServices);
            $batch->setAttribute('sales_summary', $salesSummary);
            return $batch;
        });

        $services = $services->sortByDesc('created_at')->values();

        return response()->json([
            'farmer' => $farmer,
            'batches' => $batches,
            'loans' => $loans,
            'settlements' => $settlements,
            'services' => $services,
        ]);
    }

    public function update(Request $request, $id)
    {
        $farmer = Farmer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:50',
            'national_id' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'street' => 'nullable|string|max:100',
            'status' => 'sometimes|required|string|in:active,inactive',
        ]);

        $farmer->update($validated);

        try {
            event(new \App\Events\StoreDataUpdated($farmer->tenant_id, 'farmer', 'updated'));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Farmer profile updated successfully',
            'farmer' => $farmer
        ]);
    }

    public function destroy($id)
    {
        $farmer = Farmer::findOrFail($id);
        $tenantId = $farmer->tenant_id;

        \Illuminate\Support\Facades\DB::transaction(function () use ($farmer) {
            $batchIds = $farmer->batches()->pluck('id')->toArray();

            if (!empty($batchIds)) {
                // Delete processing jobs for these batches
                \App\Models\DryingJob::whereIn('batch_id', $batchIds)->delete();
                \App\Models\MillingJob::whereIn('batch_id', $batchIds)->delete();
                \App\Models\GradingRecord::whereIn('batch_id', $batchIds)->delete();
                \App\Models\BatchMovement::whereIn('batch_id', $batchIds)->delete();
                
                // Unlink invoice items
                \App\Models\InvoiceItem::whereIn('batch_id', $batchIds)->delete();

                // Delete batches
                \App\Models\Batch::whereIn('id', $batchIds)->delete();
            }

            // Delete loans & transactions
            $loanIds = $farmer->loans()->pluck('id')->toArray();
            if (!empty($loanIds)) {
                \App\Models\LoanTransaction::whereIn('loan_id', $loanIds)->delete();
                \App\Models\Loan::whereIn('id', $loanIds)->delete();
            }

            // Delete settlements & deductions & invoices
            $settlementIds = $farmer->settlements()->pluck('id')->toArray();
            if (!empty($settlementIds)) {
                $invoiceIds = \App\Models\Settlement::whereIn('id', $settlementIds)->whereNotNull('invoice_id')->pluck('invoice_id')->toArray();
                
                \App\Models\SettlementDeduction::whereIn('settlement_id', $settlementIds)->delete();
                \App\Models\Settlement::whereIn('id', $settlementIds)->delete();

                if (!empty($invoiceIds)) {
                    \App\Models\InvoiceItem::whereIn('invoice_id', $invoiceIds)->delete();
                    \App\Models\Invoice::whereIn('id', $invoiceIds)->delete();
                }
            }

            // Finally delete the farmer
            $farmer->delete();
        });

        try {
            event(new \App\Events\StoreDataUpdated($tenantId, 'farmer', 'deleted'));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Mkulima na kumbukumbu zake zote zimefutwa kikamilifu.'
        ]);
    }
}
