<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\OtherIncome::query();
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date_received', [$request->query('start_date'), $request->query('end_date')]);
        }

        $incomes = $query->orderBy('date_received', 'desc')->get();
        return response()->json($incomes);
    }

    public function getSources()
    {
        $sources = \App\Models\IncomeSource::orderBy('name')->get();
        return response()->json($sources);
    }

    public function storeSource(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_sources,name'
        ]);

        $source = \App\Models\IncomeSource::create($validated);
        return response()->json(['message' => 'Chanzo kimesajiliwa kikamilifu', 'source' => $source], 201);
    }

    public function destroySource($id)
    {
        $source = \App\Models\IncomeSource::findOrFail($id);
        $source->delete();
        return response()->json(['message' => 'Chanzo kimefutwa']);
    }

    public function updateSource(Request $request, $id)
    {
        $source = \App\Models\IncomeSource::findOrFail($id);
        $oldName = $source->name;

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_sources,name,' . $id
        ]);

        $source->update($validated);
        $newName = $source->name;

        // Cascade the name change to existing records
        if ($oldName !== $newName) {
            \App\Models\OtherIncome::where('source_name', $oldName)->update(['source_name' => $newName]);
        }

        return response()->json(['message' => 'Chanzo kimebadilishwa kikamilifu', 'source' => $source]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date_received' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // assuming no auth for now, or you could do auth()->id() if available
        $validated['recorded_by'] = null;

        $income = \App\Models\OtherIncome::create($validated);

        return response()->json(['message' => 'Mapato yamesajiliwa kikamilifu', 'income' => $income], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'source_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date_received' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $income = \App\Models\OtherIncome::findOrFail($id);
        $income->update($validated);

        return response()->json(['message' => 'Mapato yamebadilishwa kikamilifu', 'income' => $income]);
    }

    public function destroy($id)
    {
        $income = \App\Models\OtherIncome::findOrFail($id);
        $income->delete();
        return response()->json(['message' => 'Mapato yamefutwa']);
    }
}
