<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FarmerController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\ExpenseController;

use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\AccountingController;

// Group under v1
Route::prefix('v1')->group(function () {
    // Financial Summary & Accounting P&L Analysis
    Route::get('/accounting/financial-summary', [AccountingController::class, 'getFinancialSummary']);

    // Buyers CRUD
    Route::get('buyers/stats', [BuyerController::class, 'stats']);
    Route::get('buyers/{id}/history', [BuyerController::class, 'history']);
    Route::apiResource('buyers', BuyerController::class);

    // Services catalog CRUD
    Route::apiResource('services', ServiceController::class);
    
    // Operating Expenses
    Route::get('expenses/categories', [ExpenseController::class, 'getCategories']);
    Route::post('expenses/categories', [ExpenseController::class, 'storeCategory']);
    Route::put('expenses/categories/{id}', [ExpenseController::class, 'updateCategory']);
    Route::delete('expenses/categories/{id}', [ExpenseController::class, 'destroyCategory']);
    Route::apiResource('expenses', ExpenseController::class);

    // Other Incomes
    Route::get('incomes/sources', [IncomeController::class, 'getSources']);
    Route::post('incomes/sources', [IncomeController::class, 'storeSource']);
    Route::put('incomes/sources/{id}', [IncomeController::class, 'updateSource']);
    Route::delete('incomes/sources/{id}', [IncomeController::class, 'destroySource']);
    Route::apiResource('incomes', IncomeController::class);

    // Dashboard & Reports
    Route::get('/dashboard/stats', [ReportController::class, 'getDashboardStats']);
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLossReport']);
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf']);
    Route::post('/system/reset-data', [ReportController::class, 'resetAllData']);

    // Farmers
    Route::apiResource('farmers', FarmerController::class);

    // Batches & Warehouse Bins
    Route::get('/inventory/summary', [BatchController::class, 'getInventorySummary']);
    Route::get('/inventory/analytics', [ReportController::class, 'getInventoryAnalytics']);
    Route::apiResource('batches', BatchController::class);
    Route::get('/bins/map', [BatchController::class, 'binsMap']);
    Route::post('/batches/{id}/move', [BatchController::class, 'moveBatch']);
    Route::post('/batches/{id}/processing', [BatchController::class, 'updateProcessing']);
    Route::delete('/processing/{jobType}/{jobId}', [BatchController::class, 'deleteProcessingJob']);
    Route::delete('/processing-jobs/{id}', [BatchController::class, 'deleteProcessingJob']);

    // Loans
    Route::apiResource('loans', LoanController::class);
    Route::post('/loans/{id}/approve', [LoanController::class, 'approve']);
    Route::post('/loans/{id}/repay', [LoanController::class, 'repay']);

    // Sales & Deductions
    Route::get('/sales/buyers', [SalesController::class, 'getBuyers']);
    Route::get('/sales/invoices', [SalesController::class, 'indexInvoices']);
    Route::put('/sales/invoices/{id}/pay', [SalesController::class, 'markInvoicePaid']);
    Route::delete('/sales/invoices/{id}', [SalesController::class, 'deleteInvoice']);
    Route::get('/sales/settlements', [SalesController::class, 'indexSettlements']);
    Route::post('/sales/preview-deductions', [SalesController::class, 'previewDeductions']);
    Route::post('/sales/confirm', [SalesController::class, 'confirmSale']);
});
