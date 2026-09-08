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
            $tenant = Tenant::where('id', $tenantHeader)
                ->orWhere('subdomain', $tenantHeader)
                ->first();

            if ($tenant) {
                return $tenant->id;
            }

            // Create tenant record on demand if registered dynamically
            $newTenant = Tenant::create([
                'id' => $tenantHeader,
                'name' => 'Warehouse ' . substr($tenantHeader, -6),
                'subdomain' => $tenantHeader,
                'status' => 'active'
            ]);

            return $newTenant->id;
        }

        $first = Tenant::first();
        if (!$first) {
            $first = Tenant::create([
                'id' => 'tenant_kigoma',
                'name' => 'Kigoma Grain Mills Ltd',
                'subdomain' => 'kigoma',
                'status' => 'active'
            ]);
        }

        return $first->id;
    }
}
