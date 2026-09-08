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
            'initial_weight_mt' => 'nullable|numeric',
            'intake_quantity' => 'required|numeric',
            'intake_unit' => 'required|string|max:50',
            'bin_id' => 'nullable|exists:bins,id',
        ]);

        $tenant = \App\Models\Tenant::first() ?? \App\Models\Tenant::create(['name' => 'Garanoki Main Store', 'subdomain' => 'garanoki-store', 'status' => 'active']);
        $branch = Branch::first() ?? Branch::create(['tenant_id' => $tenant->id, 'name' => 'Arusha Main Branch', 'code' => 'BR-001', 'status' => 'active']);
        $tenantId = $tenant->id;
        $branchId = $branch->id;

        // Calculate MT dynamically if not directly provided
        $unitLower = strtolower(trim($validated['intake_unit']));
        $qty = floatval($validated['intake_quantity']);

        if (!empty($validated['initial_weight_mt']) && floatval($validated['initial_weight_mt']) > 0) {
            $computedMt = floatval($validated['initial_weight_mt']);
        } elseif (in_array($unitLower, ['kilo', 'kg', 'kilogram', 'kilograms'])) {
            $computedMt = $qty / 1000.0;
        } elseif (in_array($unitLower, ['gunia', 'bags', 'bag', 'mifuko'])) {
            $computedMt = $qty * 0.1; // 1 Bag = 100kg = 0.1 MT
        } else {
            $computedMt = $qty; // Default MT / Tons
        }

        // Auto-generate unique batch code
        $maxNum = 1143;
        $allCodes = Batch::where('tenant_id', $tenantId)->pluck('batch_code');
        foreach ($allCodes as $code) {
            if (preg_match('/BCH-(\d+)/', $code, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        $nextNumber = $maxNum + 1;
        $batchCode = 'BCH-' . $nextNumber;

        while (Batch::where('tenant_id', $tenantId)->where('batch_code', $batchCode)->exists()) {
            $nextNumber++;
            $batchCode = 'BCH-' . $nextNumber;
        }

        $batch = DB::transaction(function () use ($validated, $tenantId, $branchId, $batchCode, $computedMt) {
            $batch = Batch::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'farmer_id' => $validated['farmer_id'],
                'batch_code' => $batchCode,
                'crop_type' => $validated['crop_type'],
                'variety' => $validated['variety'] ?? null,
                'intake_quantity' => $validated['intake_quantity'],
                'intake_unit' => $validated['intake_unit'],
                'initial_moisture' => $validated['initial_moisture'] ?? 12.0,
                'current_moisture' => $validated['initial_moisture'] ?? 12.0,
                'initial_weight_mt' => $computedMt,
                'current_weight_mt' => $computedMt,
                'current_bin_id' => $validated['bin_id'] ?? null,
                'status' => 'received',
            ]);

            // If a bin is selected, update bin occupancy
            if (!empty($validated['bin_id'])) {
                $bin = Bin::find($validated['bin_id']);
                $bin->increment('current_occupancy_mt', $computedMt);
                $bin->update(['status' => 'occupied', 'crop_type' => $validated['crop_type']]);

                // Record movement
                BatchMovement::create([
                    'batch_id' => $batch->id,
                    'source_bin_id' => null,
                    'destination_bin_id' => $bin->id,
                    'quantity_mt' => $computedMt,
                    'reason' => 'Initial Intake Placement',
                ]);
            }

            // Auto-activate farmer status upon receiving crop batch
            Farmer::where('id', $validated['farmer_id'])->update(['status' => 'active']);

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
        try {
            $batch = Batch::find($id);
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mzigo huu haupo kwenye mfumo (pengine database ilisafishwa). Tafadhali fanya Refresh kwenye ukurasa wako kisha jaribu tena.'
                ], 404);
            }

            $jobId = $request->input('job_id');
            $availableQty = floatval($batch->current_weight_mt > 0 ? $batch->current_weight_mt : $batch->intake_quantity);
            if (!$jobId && (in_array($batch->status, ['sold', 'transformed']) || $availableQty <= 0)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mzigo huu tayari umeshauzwa au kubadilishwa wote! Huwezi kupanga huduma mpya kwenye mzigo huu.'
                ], 422);
            }

            $type = $request->input('type') ?: 'milling'; // Default to milling if not specified
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
                'job_id' => 'nullable',
            ]);

            $serviceId = $request->input('service_id');
            $service = $serviceId && $serviceId !== 'undefined' ? Service::find($serviceId) : null;
            $serviceName = $service ? $service->name_sw : $request->input('service_name');
            
            $jobId = $request->input('job_id');
            $incomingFee = $request->input('fee') ?? $request->input('fee_amount');

            // Helper to generate unique child batch code
            $makeUniqueCode = function ($baseCode, $prefix) {
                $code = $baseCode . '-' . $prefix;
                $attempt = 1;
                while (Batch::where('batch_code', $code)->exists()) {
                    $code = $baseCode . '-' . $prefix . rand(10, 99);
                    $attempt++;
                }
                return $code;
            };

            if (strtolower($type) === 'drying') {
                $job = $jobId ? DryingJob::find($jobId) : null;
                if (!$job) {
                    $job = DryingJob::create([
                        'batch_id' => $batch->id,
                        'initial_moisture' => $batch->current_moisture ?? 12.0,
                        'weight_before_mt' => $batch->current_weight_mt ?: ($batch->initial_weight_mt ?: 0.5),
                        'status' => 'queued',
                        'service_id' => ($serviceId && $serviceId !== 'undefined') ? $serviceId : null,
                        'fee_amount' => $incomingFee ?: 0,
                    ]);
                }
                $finalFee = ($incomingFee && floatval($incomingFee) > 0) ? floatval($incomingFee) : ($job->fee_amount ?: 0);
                $job->update([
                    'status' => $validated['status'],
                    'final_moisture' => $validated['final_value'] ?? $job->final_moisture,
                    'fee_amount' => $finalFee,
                    'machine_id' => $serviceName ?? $job->machine_id,
                    'service_id' => ($serviceId !== 'undefined' ? $serviceId : null) ?? $job->service_id,
                    'end_time' => now(),
                ]);
                if ($validated['status'] === 'completed') {
                    $inputUsed = $request->input('input_used') ?? $batch->current_weight_mt;
                    $finalValue = $validated['final_value'] ?? $inputUsed;
                    $newWeight = max(0, $batch->current_weight_mt - $inputUsed + $finalValue);
                    $batch->update([
                        'current_weight_mt' => $newWeight > 0 ? $newWeight : ($batch->initial_weight_mt ?: 0.5),
                        'status' => $batch->status === 'received' ? 'received' : 'stored'
                    ]);
                }
            } elseif (strtolower($type) === 'milling') {
                $job = $jobId ? MillingJob::find($jobId) : null;
                if (!$job) {
                    $job = MillingJob::create([
                        'batch_id' => $batch->id,
                        'input_weight_mt' => $batch->current_weight_mt ?: ($batch->initial_weight_mt ?: 0.5),
                        'service_id' => ($serviceId && $serviceId !== 'undefined') ? $serviceId : null,
                        'fee_amount' => $incomingFee ?: 0,
                    ]);
                }
                $finalFee = ($incomingFee && floatval($incomingFee) > 0) ? floatval($incomingFee) : ($job->fee_amount ?: 0);
                $job->update([
                    'status' => $validated['status'],
                    'output_weight_mt' => $validated['final_value'] ?? $job->output_weight_mt,
                    'fee_amount' => $finalFee,
                    'machine_id' => $serviceName ?? $job->machine_id,
                    'service_id' => ($serviceId !== 'undefined' ? $serviceId : null) ?? $job->service_id,
                    'end_time' => now(),
                ]);
                
                if ($validated['status'] === 'completed') {
                    $inputUsed = $request->input('input_used') ?? ($batch->current_weight_mt > 0 ? $batch->current_weight_mt : $batch->intake_quantity);
                    $outputCrop = $request->input('output_crop');
                    $outputUnit = $request->input('output_unit', 'Kilo'); 
                    $finalValue = $validated['final_value'] ?? 0;
                    
                    $cropChanged = !empty($outputCrop) && strtolower(trim($outputCrop)) !== strtolower(trim($batch->crop_type));

                    if ($cropChanged) {
                        if ($batch->current_weight_mt > 0) {
                            $batch->decrement('current_weight_mt', min($batch->current_weight_mt, $inputUsed));
                        }
                        
                        $rawOutputQty = $request->input('output_quantity');
                        $intakeQtyVal = ($rawOutputQty && floatval($rawOutputQty) > 0) ? floatval($rawOutputQty) : ($finalValue > 0 ? $finalValue : 1);

                        Batch::create([
                            'tenant_id' => $batch->tenant_id,
                            'branch_id' => $batch->branch_id,
                            'farmer_id' => $batch->farmer_id,
                            'parent_batch_id' => $batch->id,
                            'batch_code' => $makeUniqueCode($batch->batch_code, 'PR1'),
                            'crop_type' => $outputCrop,
                            'variety' => $batch->variety,
                            'intake_quantity' => $intakeQtyVal,
                            'intake_unit' => $outputUnit,
                            'initial_moisture' => $batch->current_moisture ?? 12.0,
                            'current_moisture' => $batch->current_moisture ?? 12.0,
                            'initial_weight_mt' => $intakeQtyVal,
                            'current_weight_mt' => $intakeQtyVal,
                            'current_bin_id' => null,
                            'status' => 'stored'
                        ]);

                        if ($batch->current_weight_mt <= 0.001) {
                            $batch->update(['status' => 'transformed', 'current_weight_mt' => 0]);
                            MillingJob::where('batch_id', $batch->id)->whereIn('status', ['queued', 'in_progress'])->update(['status' => 'completed']);
                            DryingJob::where('batch_id', $batch->id)->whereIn('status', ['queued', 'in_progress'])->update(['status' => 'completed']);
                            GradingRecord::where('batch_id', $batch->id)->whereIn('status', ['queued', 'in_progress'])->update(['status' => 'completed']);
                        }
                    } else {
                        $restoredWeight = $batch->current_weight_mt > 0 ? $batch->current_weight_mt : ($batch->initial_weight_mt ?: 0.5);
                        $batch->update([
                            'status' => $batch->status === 'received' ? 'received' : 'stored',
                            'current_weight_mt' => $restoredWeight
                        ]);
                    }
                    
                    $byProductCrop = $request->input('by_product_crop');
                    if (!empty($byProductCrop)) {
                        $byProductQty = $request->input('by_product_quantity', 0);
                        $byProductValue = floatval($byProductQty) > 0 ? floatval($byProductQty) : floatval($request->input('by_product_value', 0));
                        $byProductUnit = $request->input('by_product_unit', 'Kilo');
                        
                        Batch::create([
                            'tenant_id' => $batch->tenant_id,
                            'branch_id' => $batch->branch_id,
                            'farmer_id' => $batch->farmer_id,
                            'parent_batch_id' => $batch->id,
                            'batch_code' => $makeUniqueCode($batch->batch_code, 'PR2'),
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
                $job = $jobId ? GradingRecord::find($jobId) : null;
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
                    $inputUsed = $request->input('input_used') ?? ($batch->current_weight_mt > 0 ? $batch->current_weight_mt : $batch->intake_quantity);
                    $outputCrop = $request->input('output_crop');
                    $outputUnit = $request->input('output_unit', 'Kilo'); 
                    $finalValue = $validated['final_value'] ?? 0;
                    
                    $cropChanged = !empty($outputCrop) && strtolower(trim($outputCrop)) !== strtolower(trim($batch->crop_type));

                    if ($cropChanged) {
                        if ($batch->current_weight_mt > 0) {
                            $batch->decrement('current_weight_mt', min($batch->current_weight_mt, $inputUsed));
                        }
                        
                        $rawOutputQtyGrd = $request->input('output_quantity');
                        $rawOutputQtyGrd = ($rawOutputQtyGrd && floatval($rawOutputQtyGrd) > 0) ? floatval($rawOutputQtyGrd) : ($finalValue > 0 ? $finalValue : 1);
                        
                        Batch::create([
                            'tenant_id' => $batch->tenant_id,
                            'branch_id' => $batch->branch_id,
                            'farmer_id' => $batch->farmer_id,
                            'parent_batch_id' => $batch->id,
                            'batch_code' => $makeUniqueCode($batch->batch_code, 'GRD1'),
                            'crop_type' => $outputCrop,
                            'variety' => $batch->variety,
                            'intake_quantity' => $rawOutputQtyGrd,
                            'intake_unit' => $outputUnit,
                            'initial_moisture' => $batch->current_moisture ?? 12.0,
                            'current_moisture' => $batch->current_moisture ?? 12.0,
                            'initial_weight_mt' => $rawOutputQtyGrd,
                            'current_weight_mt' => $rawOutputQtyGrd,
                            'current_bin_id' => null,
                            'status' => 'stored'
                        ]);

                        if ($batch->current_weight_mt <= 0.001) {
                            $batch->update(['status' => 'transformed', 'current_weight_mt' => 0]);
                            MillingJob::where('batch_id', $batch->id)->whereIn('status', ['queued', 'in_progress'])->update(['status' => 'completed']);
                            DryingJob::where('batch_id', $batch->id)->whereIn('status', ['queued', 'in_progress'])->update(['status' => 'completed']);
                            GradingRecord::where('batch_id', $batch->id)->whereIn('status', ['queued', 'in_progress'])->update(['status' => 'completed']);
                        }
                    } else {
                        $batch->update(['status' => $batch->status === 'received' ? 'received' : 'stored']);
                    }
                    
                    $byProductCrop = $request->input('by_product_crop');
                    if (!empty($byProductCrop)) {
                        $byProductQty = $request->input('by_product_quantity', 0);
                        $byProductValue = floatval($byProductQty) > 0 ? floatval($byProductQty) : floatval($request->input('by_product_value', 0));
                        $byProductUnit = $request->input('by_product_unit', 'Kilo');
                        
                        Batch::create([
                            'tenant_id' => $batch->tenant_id,
                            'branch_id' => $batch->branch_id,
                            'farmer_id' => $batch->farmer_id,
                            'parent_batch_id' => $batch->id,
                            'batch_code' => $makeUniqueCode($batch->batch_code, 'GRD2'),
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Update processing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Imeshindwa kukamilisha huduma: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        $validated = $request->validate([
            'crop_type' => 'nullable|string|max:100',
            'current_weight_mt' => 'nullable|numeric',
            'initial_weight_mt' => 'nullable|numeric',
            'current_moisture' => 'nullable|numeric',
            'status' => 'nullable|string|max:50',
            'intake_quantity' => 'nullable|numeric',
            'intake_unit' => 'nullable|string|max:50',
        ]);

        $updateData = array_filter([
            'crop_type' => $request->input('crop_type'),
            'current_weight_mt' => $request->input('current_weight_mt'),
            'initial_weight_mt' => $request->input('initial_weight_mt'),
            'current_moisture' => $request->input('current_moisture'),
            'status' => $request->input('status'),
            'intake_quantity' => $request->input('intake_quantity'),
            'intake_unit' => $request->input('intake_unit'),
        ], function ($val) {
            return !is_null($val);
        });

        $batch->update($updateData);

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

    public function deleteProcessingJob(Request $request, $jobType, $jobId)
    {
        $type = strtolower($jobType);
        $deleted = false;

        if ($type === 'milling' || $type === 'millingjob') {
            $job = MillingJob::find($jobId);
            if ($job) {
                $job->delete();
                $deleted = true;
            }
        } elseif ($type === 'drying' || $type === 'dryingjob') {
            $job = DryingJob::find($jobId);
            if ($job) {
                $job->delete();
                $deleted = true;
            }
        } elseif ($type === 'grading' || $type === 'gradingrecord') {
            $job = GradingRecord::find($jobId);
            if ($job) {
                $job->delete();
                $deleted = true;
            }
        }

        // Fallback search across all job tables if specific type lookup missed
        if (!$deleted) {
            $m = MillingJob::find($jobId);
            if ($m) { $m->delete(); $deleted = true; }
            else {
                $d = DryingJob::find($jobId);
                if ($d) { $d->delete(); $deleted = true; }
                else {
                    $g = GradingRecord::find($jobId);
                    if ($g) { $g->delete(); $deleted = true; }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Assigned processing service deleted successfully'
        ]);
    }

    private function getCanonicalCropName($raw)
    {
        $lower = strtolower(trim($raw));
        if (in_array($lower, ['rice', 'mchele', 'mchele super', 'mchele 1', 'mchele 2'])) {
            return 'Rice / Mchele';
        }
        if (in_array($lower, ['maize', 'mahindi', 'unga', 'flour'])) {
            return 'Maize / Mahindi';
        }
        if (in_array($lower, ['paddy', 'mpunga', 'mpunga wet', 'mpunga dry'])) {
            return 'Paddy / Mpunga';
        }
        if (in_array($lower, ['pumba', 'bran', 'husk'])) {
            return 'Pumba / Bran';
        }
        if (in_array($lower, ['beans', 'maharage', 'legumes'])) {
            return 'Beans / Maharage';
        }
        return ucfirst(trim($raw));
    }

    public function getInventorySummary()
    {
        $totalBatchesCount = Batch::count();

        $intakeBatches = Batch::all();
        $storedBatches = Batch::whereNotIn('status', ['sold', 'transformed'])->get();
        $soldBatches = Batch::where('status', 'sold')->get();

        $storedUnitSums = [];
        foreach ($storedBatches as $b) {
            $u = $b->intake_unit ?: 'Gunia';
            $q = (float) ($b->intake_quantity > 0 ? $b->intake_quantity : $b->current_weight_mt);
            $storedUnitSums[$u] = ($storedUnitSums[$u] ?? 0) + $q;
        }

        $soldUnitSums = [];
        foreach ($soldBatches as $b) {
            $u = $b->intake_unit ?: 'Gunia';
            $q = (float) ($b->intake_quantity > 0 ? $b->intake_quantity : $b->current_weight_mt);
            $soldUnitSums[$u] = ($soldUnitSums[$u] ?? 0) + $q;
        }

        $intakeUnitSums = [];
        foreach ($intakeBatches as $b) {
            $u = $b->intake_unit ?: 'Gunia';
            $q = (float) ($b->intake_quantity > 0 ? $b->intake_quantity : $b->initial_weight_mt);
            $intakeUnitSums[$u] = ($intakeUnitSums[$u] ?? 0) + $q;
        }

        $formatUnitMap = function($map) {
            if (empty($map)) return '0';
            $parts = [];
            foreach ($map as $u => $q) {
                $parts[] = number_format($q, 0) . ' ' . $u;
            }
            return implode(' / ', $parts);
        };

        $storedStockFormatted = $formatUnitMap($storedUnitSums);
        $soldStockFormatted = $formatUnitMap($soldUnitSums);
        $intakeStockFormatted = $formatUnitMap($intakeUnitSums);

        $groupedMap = [];
        $totalBatchesCountForPct = max($totalBatchesCount, 1);

        foreach ($intakeBatches as $c) {
            $cropName = $c->crop_type ?: 'General';
            $unit = $c->intake_unit ?: 'Gunia';
            $key = "{$cropName} ({$unit})";

            if (!isset($groupedMap[$key])) {
                $groupedMap[$key] = [
                    'crop_type' => $cropName,
                    'unit' => $unit,
                    'received_qty' => 0.0,
                    'stored_qty' => 0.0,
                    'batch_count' => 0
                ];
            }

            $qtyReceived = (float) ($c->intake_quantity > 0 ? $c->intake_quantity : $c->initial_weight_mt);
            $qtyStored = !in_array($c->status, ['sold', 'transformed']) 
                ? (float) ($c->intake_quantity > 0 ? $c->intake_quantity : $c->current_weight_mt)
                : 0.0;

            $groupedMap[$key]['received_qty'] += $qtyReceived;
            $groupedMap[$key]['stored_qty'] += $qtyStored;
            $groupedMap[$key]['batch_count']++;
        }

        $cropBreakdown = [];
        foreach ($groupedMap as $key => $data) {
            $pct = number_format(($data['batch_count'] / $totalBatchesCountForPct) * 100, 1);
            $cropBreakdown[] = [
                'crop_type' => $data['crop_type'],
                'unit' => $data['unit'],
                'received_qty' => $data['received_qty'],
                'stored_qty' => $data['stored_qty'],
                'batch_count' => $data['batch_count'],
                'percentage' => floatval($pct)
            ];
        }

        $rawBins = Bin::orderBy('name')->get();
        $totalCapacityMt = floatval($rawBins->sum('capacity_mt'));
        $totalOccupancyMt = 0.0;

        $bins = [];
        foreach ($rawBins as $bin) {
            $activeBatches = Batch::where('current_bin_id', $bin->id)
                ->whereNotIn('status', ['transformed', 'sold'])
                ->get();

            $liveOccupancyMt = floatval($activeBatches->sum('current_weight_mt'));
            $totalOccupancyMt += $liveOccupancyMt;

            $cropNames = $activeBatches->pluck('crop_type')->unique()->implode(', ');

            $bins[] = [
                'id' => $bin->id,
                'name' => $bin->name,
                'capacity_mt' => floatval($bin->capacity_mt),
                'current_occupancy_mt' => $liveOccupancyMt,
                'crop_type' => !empty($cropNames) ? $cropNames : 'Empty / Available',
                'status' => $liveOccupancyMt >= ($bin->capacity_mt * 0.9) ? 'full' : ($liveOccupancyMt > 0 ? 'occupied' : 'empty')
            ];
        }

        $utilizationPct = $totalCapacityMt > 0 ? number_format(($totalOccupancyMt / $totalCapacityMt) * 100, 1) : '0.0';

        $periodAnalytics = [
            'this_week' => $this->buildPeriodAnalytics(now()->startOfWeek()),
            'this_month' => $this->buildPeriodAnalytics(now()->startOfMonth()),
            'all_time' => $this->buildPeriodAnalytics(null),
        ];

        return response()->json([
            'total_batches' => $totalBatchesCount,
            'stored_stock_formatted' => $storedStockFormatted,
            'sold_stock_formatted' => $soldStockFormatted,
            'intake_stock_formatted' => $intakeStockFormatted,
            'warehouse_capacity_mt' => $totalCapacityMt,
            'warehouse_occupancy_mt' => $totalOccupancyMt,
            'utilization_pct' => floatval($utilizationPct),
            'crop_breakdown' => $cropBreakdown,
            'bins' => $bins,
            'analytics' => $periodAnalytics
        ]);
    }

    private function buildPeriodAnalytics($startDate = null)
    {
        $derivedQuery = Batch::whereNotNull('parent_batch_id');
        if ($startDate) {
            $derivedQuery->where('created_at', '>=', $startDate);
        }
        $derivedBatches = $derivedQuery->get();

        $transformMap = [];
        foreach ($derivedBatches as $d) {
            $crop = $d->crop_type ?: 'General';
            $unit = $d->intake_unit ?: 'Gunia';
            $key = "{$crop} ({$unit})";
            $qty = floatval($d->intake_quantity > 0 ? $d->intake_quantity : $d->initial_weight_mt);
            if (!isset($transformMap[$key])) {
                $transformMap[$key] = ['crop_type' => $crop, 'unit' => $unit, 'qty' => 0.0];
            }
            $transformMap[$key]['qty'] += $qty;
        }

        $transformOutputs = array_values($transformMap);

        $soldQuery = Batch::where('status', 'sold');
        if ($startDate) {
            $soldQuery->where('updated_at', '>=', $startDate);
        }
        $soldBatches = $soldQuery->get();

        $salesMap = [];
        foreach ($soldBatches as $s) {
            $crop = $s->crop_type ?: 'General';
            $unit = $s->intake_unit ?: 'Gunia';
            $key = "{$crop} ({$unit})";
            $qty = floatval($s->intake_quantity > 0 ? $s->intake_quantity : $s->initial_weight_mt);
            if (!isset($salesMap[$key])) {
                $salesMap[$key] = ['crop_type' => $crop, 'unit' => $unit, 'qty' => 0.0];
            }
            $salesMap[$key]['qty'] += $qty;
        }

        $cropSales = array_values($salesMap);

        return [
            'transform_outputs' => $transformOutputs,
            'crop_sales' => $cropSales
        ];
    }
}
