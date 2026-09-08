<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Tenant;
use App\Traits\HasTenantScope;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use HasTenantScope;

    public function index(Request $request)
    {
        $tenantId = $this->getTenantId($request);

        $services = Service::where('tenant_id', $tenantId)->orderBy('name_sw')->get();
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

        $tenantId = $this->getTenantId($request);

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
        $tenantId = $this->getTenantId($request);
        $service = Service::where('tenant_id', $tenantId)->findOrFail($id);

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

    public function destroy(Request $request, $id)
    {
        $tenantId = $this->getTenantId($request);
        $service = Service::where('tenant_id', $tenantId)->findOrFail($id);
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ]);
    }
}
