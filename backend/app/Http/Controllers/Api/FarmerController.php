<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FarmerController extends Controller
{
    public function index(Request $request)
    {
        $query = Farmer::query();

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

            $globalTotal = Farmer::count();
            $globalActive = Farmer::where('status', 'active')->count();
            $globalLoans = Farmer::whereHas('loans', function($q) {
                $q->whereIn('status', ['active', 'overdue']);
            })->count();
            $globalRegions = Farmer::whereNotNull('region')->distinct('region')->count('region');

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
            'created_at' => $farmer->created_at->format('Y-m-d H:i'),
        ];
    }

    public function store(Request $request)
    {
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

        // Auto-resolve tenant
        $tenantId = \App\Models\Tenant::first()->id;

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

        return response()->json([
            'success' => true,
            'message' => 'Farmer registered successfully',
            'farmer' => $farmer
        ], 201);
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

        // Append applied_services to each batch for frontend, and gather all services
        $batches->transform(function ($batch) use (&$services) {
            if ($batch->current_weight_mt <= 0 && $batch->status !== 'transformed') {
                $batch->status = 'sold';
                $batch->current_weight_mt = 0;
                $batch->save();
            }

            $appliedServices = [];

            foreach ($batch->dryingJobs as $job) {
                if ($job->service_id) $appliedServices[] = $job->service_id;
                $services->push([
                    'id' => $job->id,
                    'job_id' => $job->id,
                    'service_id' => $job->service_id,
                    'batch_code' => $batch->batch_code,
                    'type' => 'Drying',
                    'service_name' => $job->service ? $job->service->name_sw : ($job->machine_id ?? 'Kukausha'),
                    'fee_amount' => $job->fee_amount,
                    'status' => $job->status,
                    'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            foreach ($batch->millingJobs as $job) {
                if ($job->service_id) $appliedServices[] = $job->service_id;
                $services->push([
                    'id' => $job->id,
                    'job_id' => $job->id,
                    'service_id' => $job->service_id,
                    'batch_code' => $batch->batch_code,
                    'type' => 'Milling',
                    'service_name' => $job->service ? $job->service->name_sw : ($job->machine_id ?? 'Kukoboa'),
                    'fee_amount' => $job->fee_amount,
                    'status' => $job->status,
                    'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            foreach ($batch->gradingRecords as $job) {
                if ($job->service_id) $appliedServices[] = $job->service_id;
                $services->push([
                    'id' => $job->id,
                    'job_id' => $job->id,
                    'service_id' => $job->service_id,
                    'batch_code' => $batch->batch_code,
                    'type' => 'Grading',
                    'service_name' => 'Kupanga / Grading',
                    'fee_amount' => $job->fee_amount,
                    'status' => $job->status,
                    'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            // Also attach it directly to the model as an attribute before toArray()
            $batch->setAttribute('applied_services', $appliedServices);
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

        return response()->json([
            'success' => true,
            'message' => 'Farmer profile updated successfully',
            'farmer' => $farmer
        ]);
    }
}
