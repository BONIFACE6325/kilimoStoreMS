<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    public function index()
    {
        $buyers = Buyer::withCount('invoices')
            ->orderBy('created_at', 'desc')
            ->get();

        $buyers->transform(function ($buyer) {
            $totalSpent = Invoice::where('buyer_id', $buyer->id)->sum('total_amount');
            $unpaidAmount = Invoice::where('buyer_id', $buyer->id)->where('status', 'unpaid')->sum('total_amount');
            
            return [
                'id' => $buyer->id,
                'name' => $buyer->name,
                'contact_person' => $buyer->contact_person,
                'phone' => $buyer->phone,
                'email' => $buyer->email,
                'tax_number' => $buyer->tax_number,
                'status' => $buyer->status,
                'invoices_count' => $buyer->invoices_count,
                'total_spent' => floatval($totalSpent),
                'unpaid_amount' => floatval($unpaidAmount),
                'created_at' => $buyer->created_at ? $buyer->created_at->format('Y-m-d H:i') : null,
            ];
        });

        return response()->json($buyers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
        ]);

        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Garanoki Main Store',
            'subdomain' => 'garanoki-store',
            'status' => 'active'
        ]);

        $buyer = Buyer::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'tax_number' => $validated['tax_number'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mnunuzi amesajiliwa kikamilifu',
            'buyer' => $buyer
        ], 201);
    }

    public function show($id)
    {
        $buyer = Buyer::with(['invoices.items.batch'])->findOrFail($id);
        return response()->json($buyer);
    }

    public function update(Request $request, $id)
    {
        $buyer = Buyer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $buyer->update([
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? $buyer->contact_person,
            'phone' => $validated['phone'] ?? $buyer->phone,
            'email' => $validated['email'] ?? $buyer->email,
            'tax_number' => $validated['tax_number'] ?? $buyer->tax_number,
            'status' => $validated['status'] ?? $buyer->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Taarifa za mnunuzi zimesasishwa kikamilifu',
            'buyer' => $buyer
        ]);
    }

    public function destroy($id)
    {
        $buyer = Buyer::findOrFail($id);

        if ($buyer->invoices()->count() > 0) {
            return response()->json([
                'error' => 'Huwezi kufuta mnunuzi mwenye ankara zilizosajiliwa kwnye mfumo!'
            ], 422);
        }

        $buyer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mnunuzi umefutwa kikamilifu'
        ]);
    }

    public function history($id)
    {
        $buyer = Buyer::findOrFail($id);
        $invoices = Invoice::with(['items.batch'])
            ->where('buyer_id', $buyer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'buyer' => $buyer,
            'invoices' => $invoices
        ]);
    }
}
