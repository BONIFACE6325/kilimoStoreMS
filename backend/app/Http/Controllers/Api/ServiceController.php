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

        // Auto-patch missing crop_type for existing services directly in database
        try {
            \Illuminate\Support\Facades\DB::statement("UPDATE services SET crop_type = 'Mpunga' WHERE (LOWER(name_sw) LIKE '%mpunga%' OR LOWER(name_en) LIKE '%paddy%' OR LOWER(name_sw) LIKE '%kuanika%')");
            \Illuminate\Support\Facades\DB::statement("UPDATE services SET crop_type = 'Mchele' WHERE (LOWER(name_sw) LIKE '%mchele%' OR LOWER(name_en) LIKE '%rice%' OR LOWER(name_sw) LIKE '%giredi%' OR LOWER(name_sw) LIKE '%doloti%')");
            \Illuminate\Support\Facades\DB::statement("UPDATE services SET crop_type = 'Mahindi' WHERE (LOWER(name_sw) LIKE '%mahindi%' OR LOWER(name_en) LIKE '%maize%')");
        } catch (\Throwable $e) {}

        if (Service::where('tenant_id', $tenantId)->count() === 0) {
            $defaultServices = [
                ['name_sw' => 'Kukoboa (Sembe/Mpunga)', 'name_en' => 'Milling (Flour/Paddy)', 'rate' => 70.00, 'unit' => 'kg', 'crop_type' => 'Mpunga/Mahindi', 'description' => 'Ada ya kukoboa nafaka kwa kilo.'],
                ['name_sw' => 'Kusogeza kwenye kinu', 'name_en' => 'Handling & Bagging', 'rate' => 300.00, 'unit' => 'gunia', 'crop_type' => 'Zote', 'description' => 'Ada ya kubeba na kusogeza gunia kwenye kinu.'],
                ['name_sw' => 'Kuanika mpunga (Drying)', 'name_en' => 'Paddy Drying', 'rate' => 1000.00, 'unit' => 'gunia', 'crop_type' => 'Mpunga', 'description' => 'Ada ya kuanika mpunga juani kwa gunia.'],
                ['name_sw' => 'Kugiredi (Grading)', 'name_en' => 'Rice Grading', 'rate' => 8.00, 'unit' => 'kg', 'crop_type' => 'Mchele', 'description' => 'Ada ya kupambanua daraja la mchele.'],
                ['name_sw' => 'Kudoloti (Color sorting)', 'name_en' => 'Color Sorting', 'rate' => 22.00, 'unit' => 'kg', 'crop_type' => 'Mchele', 'description' => 'Kutenganisha mchele mweusi/mwekundu kwa mashine ya rangi.'],
                ['name_sw' => 'Kuanika + Kuchanganya', 'name_en' => 'Drying + Mixing', 'rate' => 1500.00, 'unit' => 'gunia', 'crop_type' => 'Mpunga', 'description' => 'Ada ya kuanika na kuchanganya mpunga.'],
                ['name_sw' => 'Kuchanganya Mchele na Mafuta', 'name_en' => 'Polishing + Oil Mix', 'rate' => 2.50, 'unit' => 'kg', 'crop_type' => 'Mchele', 'description' => 'Polishing na kurutubisha mchele.'],
                ['name_sw' => 'Kupanga stoko (Warehouse)', 'name_en' => 'Warehouse Stacking', 'rate' => 700.00, 'unit' => 'gunia', 'crop_type' => 'Zote', 'description' => 'Ada ya kupanga magunia ghalani.'],
                ['name_sw' => 'Wafanyakazi (Labor)', 'name_en' => 'Labor Charges', 'rate' => 1000.00, 'unit' => 'gunia', 'crop_type' => 'Zote', 'description' => 'Gharama za vibarua vya kinu.']
            ];

            foreach ($defaultServices as $ds) {
                Service::create(array_merge($ds, ['tenant_id' => $tenantId]));
            }
        }

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
