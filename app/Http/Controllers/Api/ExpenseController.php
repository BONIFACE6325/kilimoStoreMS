<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function getCategories()
    {
        $categories = \App\Models\ExpenseCategory::orderBy('name')->get();
        return response()->json($categories);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name'
        ]);
        $category = \App\Models\ExpenseCategory::create($validated);
        return response()->json(['message' => 'Aina ya gharama imesajiliwa kikamilifu', 'category' => $category], 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = \App\Models\ExpenseCategory::findOrFail($id);
        $oldName = $category->name;

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $id
        ]);
        $category->update($validated);
        $newName = $category->name;

        if ($oldName !== $newName) {
            \App\Models\Expense::where('category_name', $oldName)->update(['category_name' => $newName]);
        }

        return response()->json(['message' => 'Aina ya gharama imebadilishwa kikamilifu', 'category' => $category]);
    }

    public function destroyCategory($id)
    {
        $category = \App\Models\ExpenseCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Aina ya gharama imefutwa']);
    }

    public function index(Request $request)
    {
        $query = \App\Models\Expense::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date_incurred', [$request->query('start_date'), $request->query('end_date')]);
        }

        $expenses = $query->orderBy('date_incurred', 'desc')->get();
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date_incurred' => 'required|date',
            'description' => 'nullable|string',
        ]);
        $validated['recorded_by'] = null;

        $expense = \App\Models\Expense::create($validated);
        return response()->json(['message' => 'Gharama imesajiliwa kikamilifu', 'expense' => $expense], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date_incurred' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $expense = \App\Models\Expense::findOrFail($id);
        $expense->update($validated);

        return response()->json(['message' => 'Gharama imebadilishwa kikamilifu', 'expense' => $expense]);
    }

    public function destroy($id)
    {
        $expense = \App\Models\Expense::findOrFail($id);
        $expense->delete();
        return response()->json(['message' => 'Gharama imefutwa']);
    }
}
