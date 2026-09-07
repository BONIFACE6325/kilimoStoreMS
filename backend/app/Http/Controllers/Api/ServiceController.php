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
        // Auto-patch crop_type for default services if null or empty
        Service::where(function($q) {
            $q->whereNull('crop_type')->orWhere('crop_type', '');
        })->get()->each(function ($s) {
            $name = strtolower($s->name_sw . ' ' . $s->name_en);
            if (str_contains($name, 'mpunga') || str_contains($name, 'paddy')) {
                $s->update(['crop_type' => 'Mpunga']);
            } elseif (str_contains($name, 'mchele') || str_contains($name, 'rice')) {
                $s->update(['crop_type' => 'Mchele']);
            } elseif (str_contains($name, 'mahindi') || str_contains($name, 'maize') || str_contains($name, 'sembe')) {
                $s->update(['crop_type' => 'Mahindi']);
            }
        });

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
