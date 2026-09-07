<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        // Auto-patch missing crop_type for existing services directly in database
        try {
            \Illuminate\Support\Facades\DB::statement("UPDATE services SET crop_type = 'Mpunga' WHERE (LOWER(name_sw) LIKE '%mpunga%' OR LOWER(name_en) LIKE '%paddy%' OR LOWER(name_sw) LIKE '%kuanika%')");
            \Illuminate\Support\Facades\DB::statement("UPDATE services SET crop_type = 'Mchele' WHERE (LOWER(name_sw) LIKE '%mchele%' OR LOWER(name_en) LIKE '%rice%' OR LOWER(name_sw) LIKE '%giredi%' OR LOWER(name_sw) LIKE '%doloti%')");
            \Illuminate\Support\Facades\DB::statement("UPDATE services SET crop_type = 'Mahindi' WHERE (LOWER(name_sw) LIKE '%mahindi%' OR LOWER(name_en) LIKE '%maize%')");
        } catch (\Throwable $e) {}

        $query = Service::query();
        $services = $query->orderBy('name_sw')->get();
        return response()->json($services);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_sw' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',

            'crop_type' => 'nullable|string|max:100',
            'rate' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $tenant = Tenant::first() ?? Tenant::create(['name' => 'Garanoki Main Store', 'subdomain' => 'garanoki-store', 'status' => 'active']);
        $tenantId = $tenant->id;

        $service = Service::create(array_merge($validated, [
            'tenant_id' => $tenantId
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Service registered successfully',
            'service' => $service
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name_sw' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',

            'crop_type' => 'nullable|string|max:100',
            'rate' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $service->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'service' => $service
        ]);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ]);
    }
}
