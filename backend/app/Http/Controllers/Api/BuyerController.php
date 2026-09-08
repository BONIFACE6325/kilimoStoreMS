<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Settlement;
use App\Models\Tenant;
use App\Traits\HasTenantScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuyerController extends Controller
{
    use HasTenantScope;

    public function index(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $buyers = Buyer::where('tenant_id', $tenantId)->withCount('invoices')
            ->orderBy('created_at', 'desc')
            ->get();

        $result = $buyers->map(function ($buyer) {
            $invoices = Invoice::where('buyer_id', $buyer->id)->get();
            $invoiceIds = $invoices->pluck('id');
            
            $totalSpent = $invoices->sum('subtotal');
            $unpaidAmount = $invoices->where('status', 'unpaid')->sum('subtotal');

            $items = InvoiceItem::with(['batch.farmer'])->whereIn('invoice_id', $invoiceIds)->get();
            
            $totalQuantity = $items->sum('quantity_mt');
            
            $crops = $items->map(function ($item) {
                return $item->batch ? $item->batch->crop_type : null;
            })->filter()->unique()->values()->all();

            $farmers = $items->map(function ($item) {
                return ($item->batch && $item->batch->farmer) ? $item->batch->farmer->name : null;
            })->filter()->unique()->values()->all();

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
                'total_quantity_mt' => floatval($totalQuantity),
                'crops' => $crops,
                'farmers' => $farmers,
                'created_at' => $buyer->created_at ? $buyer->created_at->format('Y-m-d H:i') : null,
            ];
        });

        return response()->json($result);
    }

            ->select('invoices.buyer_id', DB::raw('SUM(invoice_items.quantity_mt) as total_qty'), DB::raw('SUM(invoice_items.total_price) as total_spent'))
            ->groupBy('invoices.buyer_id')
            ->orderByDesc('total_qty')
            ->first();

        $topBuyer = null;
        if ($topBuyerItem) {
            $buyerModel = Buyer::find($topBuyerItem->buyer_id);
            if ($buyerModel) {
                $topBuyer = [
                    'id' => $buyerModel->id,
                    'name' => $buyerModel->name,
                    'total_quantity' => floatval($topBuyerItem->total_qty),
                    'total_spent' => floatval($topBuyerItem->total_spent),
                ];
            }
        }

        return response()->json([
            'total_sales_revenue' => $totalSalesRevenue,
            'total_deductions' => $totalDeductions,
            'total_buyers_count' => $totalBuyersCount,
            'total_volume_sold' => $totalVolumeSold,
            'top_buyer' => $topBuyer,
        ]);
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
        $buyer = Buyer::with(['invoices.items.batch.farmer'])->findOrFail($id);
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
        $invoices = Invoice::with(['items.batch.farmer'])
            ->where('buyer_id', $buyer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $invoicesFormatted = $invoices->map(function ($inv) {
            $farmerNames = $inv->items->map(function ($item) {
                return ($item->batch && $item->batch->farmer) ? $item->batch->farmer->name : null;
            })->filter()->unique()->values()->all();

            $cropTypes = $inv->items->map(function ($item) {
                return $item->batch ? $item->batch->crop_type : 'Mazao Ghalani';
            })->filter()->unique()->values()->all();

            $totalQty = $inv->items->sum('quantity_mt');

            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'subtotal' => floatval($inv->subtotal),
                'total_amount' => floatval($inv->subtotal),
                'status' => $inv->status,
                'created_at' => $inv->created_at ? $inv->created_at->format('Y-m-d H:i') : null,
                'farmer_names' => !empty($farmerNames) ? implode(', ', $farmerNames) : 'N/A',
                'crop_types' => !empty($cropTypes) ? implode(', ', $cropTypes) : 'Mazao Ghalani',
                'total_quantity' => floatval($totalQty),
            ];
        });

        return response()->json([
            'buyer' => $buyer,
            'invoices' => $invoicesFormatted
        ]);
    }
}
