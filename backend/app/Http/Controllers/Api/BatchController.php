<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Bin;
use App\Models\Farmer;
use App\Models\Branch;
use App\Models\BatchMovement;
use App\Models\DryingJob;
use App\Models\MillingJob;
use App\Models\GradingRecord;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::with(['farmer', 'bin', 'dryingJobs', 'millingJobs']);

        if ($request->has('crop_type')) {
            $query->where('crop_type', $request->input('crop_type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('farmer_id')) {
            $query->where('farmer_id', $request->input('farmer_id'));
        }

        $batches = $query->orderBy('created_at', 'desc')->get();

        $result = $batches->map(function ($batch) {
            $appliedServices = [];
            foreach ($batch->dryingJobs as $job) {
                if ($job->service_id) {
                    $appliedServices[] = $job->service_id;
                }
            }
            foreach ($batch->millingJobs as $job) {
                if ($job->service_id) {
                    $appliedServices[] = $job->service_id;
                }
            }
            return [
                'id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'farmer_name' => $batch->farmer->name,
                'crop_type' => $batch->crop_type,
                'variety' => $batch->variety,
                'intake_quantity' => $batch->intake_quantity,
                'intake_unit' => $batch->intake_unit,
                'parent_batch_id' => $batch->parent_batch_id,
                'current_weight' => $batch->current_weight_mt,
                'initial_weight' => $batch->initial_weight_mt,
                'moisture' => $batch->current_moisture,
                'bin_name' => $batch->bin ? $batch->bin->name : 'N/A',
                'status' => $batch->status,
                'created_at' => $batch->created_at->format('Y-m-d H:i'),
                'applied_services' => $appliedServices,
                'days_stored' => max(0, now()->diffInDays($batch->created_at)),
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'crop_type' => 'required|string|max:100',
            'variety' => 'nullable|string|max:100',
            'initial_moisture' => 'nullable|numeric',
            'initial_weight_mt' => 'required|numeric',
            'intake_quantity' => 'nullable|numeric',
            'intake_unit' => 'nullable|string|max:50',
            'bin_id' => 'nullable|exists:bins,id',
        ]);

        $tenantId = \App\Models\Tenant::first()->id;
        $branchId = Branch::first()->id;

        // Auto-generate batch code
        $lastBatch = Batch::orderBy('created_at', 'desc')->first();
        $nextNumber = 1143;
        if ($lastBatch) {
            preg_match('/BCH-(\d+)/', $lastBatch->batch_code, $matches);
            if (!empty($matches[1])) {
                $nextNumber = intval($matches[1]) + 1;
            }
        }
        $batchCode = 'BCH-' . $nextNumber;

        $batch = DB::transaction(function () use ($validated, $tenantId, $branchId, $batchCode) {
            $batch = Batch::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'farmer_id' => $validated['farmer_id'],
                'batch_code' => $batchCode,
                'crop_type' => $validated['crop_type'],
                'variety' => $validated['variety'] ?? null,
                'intake_quantity' => $validated['intake_quantity'] ?? null,
                'intake_unit' => $validated['intake_unit'] ?? null,
                'initial_moisture' => $validated['initial_moisture'] ?? 12.0,
                'current_moisture' => $validated['initial_moisture'] ?? 12.0,
                'initial_weight_mt' => $validated['initial_weight_mt'],
                'current_weight_mt' => $validated['initial_weight_mt'],
                'current_bin_id' => $validated['bin_id'] ?? null,
                'status' => 'received',
            ]);

            // If a bin is selected, update bin occupancy
            if (!empty($validated['bin_id'])) {
                $bin = Bin::find($validated['bin_id']);
                $bin->increment('current_occupancy_mt', $validated['initial_weight_mt']);
                $bin->update(['status' => 'occupied', 'crop_type' => $validated['crop_type']]);

                // Record movement
                BatchMovement::create([
                    'batch_id' => $batch->id,
                    'source_bin_id' => null,
                    'destination_bin_id' => $bin->id,
                    'quantity_mt' => $validated['initial_weight_mt'],
                    'reason' => 'Initial Intake Placement',
                ]);
            }

            // Moisture check and auto-drying removed as per user requirement

            return $batch;
        });

        return response()->json([
            'success' => true,
            'message' => 'Grain batch received and recorded successfully',
            'batch' => $batch
        ], 201);
    }

    public function binsMap()
    {
        // Get bins list
        $bins = Bin::orderBy('name')->get();
        return response()->json($bins);
    }

    public function moveBatch(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);
        $validated = $request->validate([
            'destination_bin_id' => 'required|exists:bins,id',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($batch, $validated) {
            $sourceBinId = $batch->current_bin_id;
            $destBinId = $validated['destination_bin_id'];

            // Decrement source bin
            if ($sourceBinId) {
                $sourceBin = Bin::find($sourceBinId);
                $sourceBin->decrement('current_occupancy_mt', $batch->current_weight_mt);
                if ($sourceBin->current_occupancy_mt <= 0) {
                    $sourceBin->update(['status' => 'empty', 'current_occupancy_mt' => 0.00, 'crop_type' => null]);
                }
            }

            // Increment destination bin
            $destBin = Bin::find($destBinId);
            $destBin->increment('current_occupancy_mt', $batch->current_weight_mt);
            $destBin->update(['status' => 'occupied', 'crop_type' => $batch->crop_type]);

            // Update batch
            $batch->update(['current_bin_id' => $destBinId]);

            // Record movement
            BatchMovement::create([
                'batch_id' => $batch->id,
                'source_bin_id' => $sourceBinId,
                'destination_bin_id' => $destBinId,
                'quantity_mt' => $batch->current_weight_mt,
                'reason' => $validated['reason'] ?? 'Internal Transfer',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Batch moved to bin successfully',
            'batch' => $batch
        ]);
    }

    public function updateProcessing(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);
        $type = $request->input('type'); // 'drying', 'milling', 'grading'
        $tableName = 'milling_jobs';
        if (strtolower($type) === 'drying') {
            $tableName = 'drying_jobs';
        } elseif (strtolower($type) === 'grading') {
            $tableName = 'grading_records';
        }

        $validated = $request->validate([
            'status' => 'required|string',
            'final_value' => 'nullable|numeric',
            'input_used' => 'nullable|numeric',
            'fee' => 'nullable|numeric',
            'job_id' => 'nullable|exists:' . $tableName . ',id',
        ]);

        $serviceId = $request->input('service_id');
        $service = $serviceId && $serviceId !== 'undefined' ? Service::find($serviceId) : null;
        $serviceName = $service ? $service->name_sw : $request->input('service_name');
        
        $jobId = $request->input('job_id');

        if (strtolower($type) === 'drying') {
            $job = $jobId ? DryingJob::find($jobId) : DryingJob::where('batch_id', $batch->id)->orderBy('created_at', 'desc')->first();
            if (!$job) {
                $job = DryingJob::create([
                    'batch_id' => $batch->id,
                    'initial_moisture' => $batch->current_moisture ?? 12.0,
                    'weight_before_mt' => $batch->current_weight_mt,
                    'status' => 'queued',
                    'service_id' => $serviceId !== 'undefined' ? $serviceId : null,
                ]);
            }
            $job->update([
                'status' => $validated['status'],
                'final_moisture' => $validated['final_value'] ?? $job->final_moisture,
                'fee_amount' => $validated['fee'] ?? $job->fee_amount,
                'machine_id' => $serviceName ?? $job->machine_id,
                'service_id' => ($serviceId !== 'undefined' ? $serviceId : null) ?? $job->service_id,
                'end_time' => now(),
            ]);
            if ($validated['status'] === 'completed') {
                $inputUsed = $request->input('input_used') ?? $batch->current_weight_mt;
                $finalValue = $validated['final_value'] ?? $inputUsed;
                $newWeight = max(0, $batch->current_weight_mt - $inputUsed + $finalValue);
                $batch->update([
                    'current_weight_mt' => $newWeight,
                    'status' => $batch->status === 'received' ? 'received' : 'stored'
                ]);
            }
        } elseif (strtolower($type) === 'milling') {
            $job = $jobId ? MillingJob::find($jobId) : MillingJob::where('batch_id', $batch->id)->orderBy('created_at', 'desc')->first();
            if (!$job) {
                $job = MillingJob::create([
                    'batch_id' => $batch->id,
                    'input_weight_mt' => $batch->current_weight_mt,
                    'service_id' => $serviceId !== 'undefined' ? $serviceId : null,
                ]);
            }
            $job->update([
                'status' => $validated['status'],
                'output_weight_mt' => $validated['final_value'] ?? $job->output_weight_mt,
                'fee_amount' => $validated['fee'] ?? $job->fee_amount,
                'machine_id' => $serviceName ?? $job->machine_id,
                'service_id' => ($serviceId !== 'undefined' ? $serviceId : null) ?? $job->service_id,
                'end_time' => now(),
            ]);
            
            if ($validated['status'] === 'completed') {
                $inputUsed = $request->input('input_used') ?? $batch->current_weight_mt;
                $outputCrop = $request->input('output_crop');
                $outputUnit = $request->input('output_unit', 'Kilo'); 
                $finalValue = $validated['final_value'] ?? 0;
                
                $cropChanged = !empty($outputCrop) && strtolower(trim($outputCrop)) !== strtolower(trim($batch->crop_type));

                if ($cropChanged) {
                    $batch->decrement('current_weight_mt', $inputUsed);
                    
                    Batch::create([
                        'tenant_id' => $batch->tenant_id,
                        'branch_id' => $batch->branch_id,
                        'farmer_id' => $batch->farmer_id,
                        'parent_batch_id' => $batch->id,
                        'batch_code' => $batch->batch_code . '-PR1',
                        'crop_type' => $outputCrop,
                        'variety' => $batch->variety,
                        'intake_quantity' => $finalValue,
                        'intake_unit' => $outputUnit,
                        'initial_moisture' => $batch->current_moisture ?? 12.0,
                        'current_moisture' => $batch->current_moisture ?? 12.0,
                        'initial_weight_mt' => $finalValue,
                        'current_weight_mt' => $finalValue,
                        'current_bin_id' => null,
                        'status' => 'stored'
                    ]);

                    if ($batch->current_weight_mt <= 0.001) {
                        $batch->update(['status' => 'transformed', 'current_weight_mt' => 0]);
                    }
                } else {
                    $batch->update(['status' => $batch->status === 'received' ? 'received' : 'stored']);
                }
                
                $byProductCrop = $request->input('by_product_crop');
                if (!empty($byProductCrop)) {
                    $byProductValue = $request->input('by_product_value', 0);
                    $byProductUnit = $request->input('by_product_unit', 'Kilo');
                    
                    Batch::create([
                        'tenant_id' => $batch->tenant_id,
                        'branch_id' => $batch->branch_id,
                        'farmer_id' => $batch->farmer_id,
                        'parent_batch_id' => $batch->id,
                        'batch_code' => $batch->batch_code . '-PR2',
                        'crop_type' => $byProductCrop,
                        'variety' => $batch->variety,
                        'intake_quantity' => $byProductValue,
                        'intake_unit' => $byProductUnit,
                        'initial_moisture' => $batch->current_moisture ?? 12.0,
                        'current_moisture' => $batch->current_moisture ?? 12.0,
                        'initial_weight_mt' => $byProductValue,
                        'current_weight_mt' => $byProductValue,
                        'current_bin_id' => null,
                        'status' => 'stored'
                    ]);
                }
            }
        } elseif (strtolower($type) === 'grading') {
            $job = $jobId ? GradingRecord::find($jobId) : GradingRecord::where('batch_id', $batch->id)->orderBy('created_at', 'desc')->first();
            if (!$job) {
                $job = GradingRecord::create([
                    'batch_id' => $batch->id,
                    'status' => 'queued',
                    'service_id' => $serviceId !== 'undefined' ? $serviceId : null,
                    'moisture_pct' => $batch->current_moisture ?? 12.0,
                    'foreign_matter_pct' => 1.5,
                    'broken_kernels_pct' => 2.0,
                    'grade_assigned' => 'A',
                    'fee_amount' => $validated['fee'] ?? 0,
                ]);
            }
            $job->update([
                'status' => $validated['status'],
                'fee_amount' => $validated['fee'] ?? $job->fee_amount,
                'service_id' => ($serviceId !== 'undefined' ? $serviceId : null) ?? $job->service_id,
                'grade_assigned' => $request->input('grade') ?? $job->grade_assigned,
            ]);
            
            if ($validated['status'] === 'completed') {
                $inputUsed = $request->input('input_used') ?? $batch->current_weight_mt;
                $outputCrop = $request->input('output_crop');
                $outputUnit = $request->input('output_unit', 'Kilo'); 
                $finalValue = $validated['final_value'] ?? 0;
                
                $cropChanged = !empty($outputCrop) && strtolower(trim($outputCrop)) !== strtolower(trim($batch->crop_type));

                if ($cropChanged) {
                    $batch->decrement('current_weight_mt', $inputUsed);
                    
                    Batch::create([
                        'tenant_id' => $batch->tenant_id,
                        'branch_id' => $batch->branch_id,
                        'farmer_id' => $batch->farmer_id,
                        'parent_batch_id' => $batch->id,
                        'batch_code' => $batch->batch_code . '-GRD1',
                        'crop_type' => $outputCrop,
                        'variety' => $batch->variety,
                        'intake_quantity' => $finalValue,
                        'intake_unit' => $outputUnit,
                        'initial_moisture' => $batch->current_moisture ?? 12.0,
                        'current_moisture' => $batch->current_moisture ?? 12.0,
                        'initial_weight_mt' => $finalValue,
                        'current_weight_mt' => $finalValue,
                        'current_bin_id' => null,
                        'status' => 'stored'
                    ]);

                    if ($batch->current_weight_mt <= 0.001) {
                        $batch->update(['status' => 'transformed', 'current_weight_mt' => 0]);
                    }
                } else {
                    $batch->update(['status' => $batch->status === 'received' ? 'received' : 'stored']);
                }
                
                $byProductCrop = $request->input('by_product_crop');
                if (!empty($byProductCrop)) {
                    $byProductValue = $request->input('by_product_value', 0);
                    $byProductUnit = $request->input('by_product_unit', 'Kilo');
                    
                    Batch::create([
                        'tenant_id' => $batch->tenant_id,
                        'branch_id' => $batch->branch_id,
                        'farmer_id' => $batch->farmer_id,
                        'parent_batch_id' => $batch->id,
                        'batch_code' => $batch->batch_code . '-GRD2',
                        'crop_type' => $byProductCrop,
                        'variety' => $batch->variety,
                        'intake_quantity' => $byProductValue,
                        'intake_unit' => $byProductUnit,
                        'initial_moisture' => $batch->current_moisture ?? 12.0,
                        'current_moisture' => $batch->current_moisture ?? 12.0,
                        'initial_weight_mt' => $byProductValue,
                        'current_weight_mt' => $byProductValue,
                        'current_bin_id' => null,
                        'status' => 'stored'
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Processing status updated successfully',
            'batch' => $batch
        ]);
    }

    public function update(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        $validated = $request->validate([
            'crop_type' => 'required|string|max:100',
            'current_weight_mt' => 'required|numeric',
            'current_moisture' => 'required|numeric',
            'status' => 'required|string|max:50',
            'intake_quantity' => 'nullable|numeric',
            'intake_unit' => 'nullable|string|max:50',
        ]);

        $batch->update([
            'crop_type' => $validated['crop_type'],
            'current_weight_mt' => $validated['current_weight_mt'],
            'current_moisture' => $validated['current_moisture'],
            'status' => $validated['status'],
            'intake_quantity' => $validated['intake_quantity'] ?? $batch->intake_quantity,
            'intake_unit' => $validated['intake_unit'] ?? $batch->intake_unit,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Batch updated successfully',
            'batch' => $batch
        ]);
    }

    public function destroy($id)
    {
        $batch = Batch::findOrFail($id);
        $batch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Batch deleted successfully'
        ]);
    }
}
