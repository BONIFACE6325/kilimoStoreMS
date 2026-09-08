<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Http\Request;

trait HasTenantScope
{
    /**
     * Resolve the active Tenant ID based on HTTP headers, query parameters, or default fallback.
     */
    protected function getTenantId(Request $request)
    {
        $tenantHeader = $request->header('X-Tenant-ID') ?? $request->query('tenant_id');
        
        if ($tenantHeader) {
            // Map default admin tenant header to main admin tenant record
            if ($tenantHeader === 'tenant_kigoma' || $tenantHeader === 'kilimo') {
                $kigomaTenant = Tenant::where('subdomain', 'kilimo')
                    ->orWhere('id', 'tenant_kigoma')
                    ->orWhere('subdomain', 'kigoma')
                    ->first();
                if ($kigomaTenant) {
                    return $kigomaTenant->id;
                }
            }

            $tenant = Tenant::where('id', $tenantHeader)
                ->orWhere('subdomain', $tenantHeader)
                ->first();

            if ($tenant) {
                return $tenant->id;
            }

            // Create tenant record on demand if registered dynamically
            $newTenant = Tenant::firstOrCreate(
                ['id' => $tenantHeader],
                [
                    'name' => 'Warehouse ' . substr($tenantHeader, -6),
                    'subdomain' => $tenantHeader,
                    'status' => 'active'
                ]
            );

            return $newTenant->id;
        }

        $first = Tenant::first();
        if (!$first) {
            $first = Tenant::create([
                'id' => '01a01c5d-8a75-7027-8528-4ca6a7fbc57c',
                'name' => 'Kigoma Grain Mills Ltd',
                'subdomain' => 'kilimo',
                'status' => 'active'
            ]);
        }

        return $first->id;
    }
}
