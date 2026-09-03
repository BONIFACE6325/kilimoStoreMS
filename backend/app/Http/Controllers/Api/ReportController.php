<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Farmer;
use App\Models\Loan;
use App\Models\Settlement;
use App\Models\Invoice;
use App\Models\DryingJob;
use App\Models\MillingJob;
use App\Models\GradingRecord;
use App\Models\SettlementDeduction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date') ? $request->query('end_date') . ' 23:59:59' : null;

        $activeWeight = Batch::where('status', 'stored')->sum('current_weight_mt');
        $farmersCount = Farmer::where('status', 'active')->count();
        $outstandingLoans = Loan::whereIn('status', ['active', 'overdue'])->sum('current_balance');
        $activeLoansCount = Loan::where('status', 'active')->count();
        $overdueLoansCount = Loan::where('status', 'overdue')->count();

        // Total revenue collected via deductions and machine service job logs
        $deductionQuery = SettlementDeduction::query();
        $dryingQuery = DryingJob::query();
        $millingQuery = MillingJob::query();
        $gradingQuery = GradingRecord::query();
        $otherIncomeQuery = \App\Models\OtherIncome::query();
        $loanDisbursedQuery = Loan::query();
        $expenseQuery = \App\Models\Expense::query();

        if ($startDate && $endDate) {
            $deductionQuery->whereBetween('created_at', [$startDate, $endDate]);
            $dryingQuery->whereBetween('created_at', [$startDate, $endDate]);
            $millingQuery->whereBetween('created_at', [$startDate, $endDate]);
            $gradingQuery->whereBetween('created_at', [$startDate, $endDate]);
            $otherIncomeQuery->whereBetween('date_received', [$request->query('start_date'), $request->query('end_date')]);
            $loanDisbursedQuery->whereBetween('created_at', [$startDate, $endDate]);
            $expenseQuery->whereBetween('date_incurred', [$request->query('start_date'), $request->query('end_date')]);
        }

        // Dynamic breakdown grouped by EXACT registered services in database
        $dynamicServiceBreakdown = [];
        $services = \App\Models\Service::all();

        foreach ($services as $srv) {
            $serviceName = $srv->name_sw ?: $srv->name_en;
            
            $dFee = (float) (clone $dryingQuery)->where('service_id', $srv->id)->sum('fee_amount');
            $mFee = (float) (clone $millingQuery)->where('service_id', $srv->id)->sum('fee_amount');
            $gFee = (float) (clone $gradingQuery)->where('service_id', $srv->id)->sum('fee_amount');

            $tot = $dFee + $mFee + $gFee;
            if ($tot > 0) {
                $dynamicServiceBreakdown[$serviceName] = $tot;
            }
        }

        $storageRev = (float) (clone $deductionQuery)->where('deduction_type', 'storage_fee')->sum('amount');
        if ($storageRev > 0) {
            $dynamicServiceBreakdown['Ada ya Hifadhi (Storage)'] = $storageRev;
        }

        $totalMappedSrv = array_sum($dynamicServiceBreakdown);
        $totalDeductionsSrv = (float) (clone $deductionQuery)->whereIn('deduction_type', ['drying_fee', 'milling_fee', 'grading_fee', 'storage_fee'])->sum('amount');
        if ($totalDeductionsSrv > $totalMappedSrv) {
            $unmappedDiff = $totalDeductionsSrv - $totalMappedSrv;
            if ($unmappedDiff > 0) {
                $dynamicServiceBreakdown['Huduma za Mauzo (Settlements)'] = $unmappedDiff;
            }
        }

        $totalServiceFeeRevenue = max(array_sum($dynamicServiceBreakdown), $totalDeductionsSrv);

        // Bins occupancy vs capacity
        $totalCapacity = \App\Models\Bin::sum('capacity_mt') ?: 1;
        $totalOccupied = \App\Models\Bin::sum('current_occupancy_mt');
        $occupancyPercentage = round(($totalOccupied / $totalCapacity) * 100, 1);

        // Machine utilization counts
        $dryingJobsCount = DryingJob::count();
        $dryingVolumeBag = DryingJob::sum('quantity');
        $activeDrying = DryingJob::whereIn('status', ['queued', 'processing'])->count();
        $completedDrying = DryingJob::where('status', 'completed')->count();

        $millingJobsCount = MillingJob::count();
        $millingVolumeKg = MillingJob::sum('quantity');
        $activeMilling = MillingJob::whereIn('status', ['queued', 'processing'])->count();
        $completedMilling = MillingJob::where('status', 'completed')->count();

        $gradingRecordsCount = GradingRecord::count();
        $gradingVolumeKg = GradingRecord::sum('weight_kg');

        // Estimated stock valuation (in TZS)
        $maizeWeight = Batch::where('crop_type', 'Maize')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        $riceWeight = Batch::where('crop_type', 'Rice')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        $beansWeight = Batch::where('crop_type', 'Beans')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        $stockValuation = ($maizeWeight * 800) + ($riceWeight * 1500) + ($beansWeight * 2000);

        // Monthly trends from database (Last 6 Months)
        $monthlyRevenue = [];
        $monthlyExpenses = [];
        $monthlyIntake = [];
        $monthlyDispatch = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            
            // Revenue (Total settled deductions + Other incomes)
            $revSum = Settlement::whereYear('settled_at', $date->year)
                ->whereMonth('settled_at', $date->month)
                ->sum('total_deductions')
                + \App\Models\OtherIncome::whereYear('date_received', $date->year)
                ->whereMonth('date_received', $date->month)
                ->sum('amount');
            $monthlyRevenue[$monthName] = (float)$revSum;

            // Monthly Expenses
            $expSum = \App\Models\Expense::whereYear('date_incurred', $date->year)
                ->whereMonth('date_incurred', $date->month)
                ->sum('amount');
            $monthlyExpenses[$monthName] = (float)$expSum;
            
            // Intake (Sum of batch initial weight in Kg)
            $intakeSum = Batch::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('initial_weight_mt');
            $monthlyIntake[$monthName] = (float)$intakeSum * 1000;

            // Dispatch (Sum of sold batch current weight in Kg)
            $dispatchSum = Batch::where('status', 'sold')
                ->whereYear('updated_at', $date->year)
                ->whereMonth('updated_at', $date->month)
                ->sum('current_weight_mt');
            $monthlyDispatch[$monthName] = (float)$dispatchSum * 1000;
        }

        // Crop distribution
        $cropDistribution = [
            'Maize' => Batch::where('crop_type', 'Maize')->where('status', 'stored')->sum('current_weight_mt'),
            'Rice' => Batch::where('crop_type', 'Rice')->where('status', 'stored')->sum('current_weight_mt'),
            'Beans' => Batch::where('crop_type', 'Beans')->where('status', 'stored')->sum('current_weight_mt'),
        ];

        // Financial Metrics requested by Owner:
        $totalCropSales = (float) Settlement::sum('gross_amount');
        if ($totalCropSales <= 0) {
            $totalCropSales = (float) Invoice::sum('total_amount');
        }

        // 1. Store Boss Retained Inflows ONLY (Service Fees + Loan Recoveries + Other Incomes - Excluding Farmer's Net Sales Share)
        $totalLoansRecovered = (float) (clone $deductionQuery)->where('deduction_type', 'loan_principal')->sum('amount');
        $otherIncomeTotal = (float) (clone $otherIncomeQuery)->sum('amount');
        
        // Hela yote inayobaki na kuingia kwa Boss/Store (Bila kuweka kiasi kinachoingia kwa Mkulima)
        $grossStoreInflows = $totalServiceFeeRevenue + $totalLoansRecovered + $otherIncomeTotal;

        // 2. Total Loan Principal Disbursed
        $totalLoansDisbursed = (float) (clone $loanDisbursedQuery)->sum('principal_amount');

        // 3. Total Outstanding Loans Unrecovered (Always Current Portfolio)
        $outstandingLoans = Loan::whereIn('status', ['active', 'overdue'])->sum('current_balance');

        // 4. Total Net Service & Operational Profit (Ada za Huduma + Mapato Mengineyo - Matumizi)
        $totalExpenses = (float) (clone $expenseQuery)->sum('amount');
        $totalNetServiceProfit = ($totalServiceFeeRevenue + $otherIncomeTotal) - $totalExpenses;

        // Grouped Other Incomes (Magari, Ukodishaji, Kodi ya Eneo, nk)
        $groupedOtherIncomes = (clone $otherIncomeQuery)
            ->selectRaw('source_name, SUM(amount) as total')
            ->groupBy('source_name')
            ->orderByDesc('total')
            ->get();
            
        $otherIncomeMap = [];
        foreach ($groupedOtherIncomes as $inc) {
            $otherIncomeMap[$inc->source_name] = (float)$inc->total;
        }

        // Grouped Expenses (Mafuta, Matengenezo, Chakula, nk)
        $groupedExpenses = (clone $expenseQuery)
            ->selectRaw('category_name, description, SUM(amount) as total')
            ->groupBy('category_name', 'description')
            ->orderByDesc('total')
            ->get();

        $expensesMap = [];
        foreach ($groupedExpenses as $exp) {
            $label = $exp->category_name ?: ucwords(str_replace(['_', '-'], ' ', $exp->description ?: 'Matumizi Nyinginezo'));
            $expensesMap[$label] = (float)$exp->total;
        }

        return response()->json([
            'stats' => [
                'total_weight_stored_mt' => $activeWeight,
                'registered_farmers' => $farmersCount,
                'total_crop_sales_tzs' => $totalCropSales,
                'gross_all_inflows_tzs' => $grossStoreInflows,
                'total_loans_disbursed_tzs' => $totalLoansDisbursed,
                'total_loans_recovered_tzs' => $totalLoansRecovered,
                'loan_portfolio_value' => $outstandingLoans,
                'total_revenue_tzs' => $totalServiceFeeRevenue,
                'total_other_income_tzs' => $otherIncomeTotal,
                'total_net_service_profit_tzs' => $totalNetServiceProfit,
                'total_expenses_tzs' => $totalExpenses,
                'stock_valuation_tzs' => $stockValuation,
                'active_loans_count' => $activeLoansCount,
                'overdue_loans_count' => $overdueLoansCount,
            ],
            'warehouse' => [
                'capacity_mt' => $totalCapacity,
                'occupied_mt' => $totalOccupied,
                'occupancy_pct' => $occupancyPercentage,
            ],
            'service_breakdown' => $dynamicServiceBreakdown,
            'other_income_breakdown' => $otherIncomeMap,
            'expenses_breakdown' => $expensesMap,
            'machine_stats' => [
                'drying_jobs' => $dryingJobsCount,
                'drying_active' => $activeDrying,
                'drying_completed' => $completedDrying,
                'drying_qty' => $dryingVolumeBag,
                'milling_jobs' => $millingJobsCount,
                'milling_active' => $activeMilling,
                'milling_completed' => $completedMilling,
                'milling_qty' => $millingVolumeKg,
                'grading_jobs' => $gradingRecordsCount,
                'grading_qty' => $gradingVolumeKg,
            ],
            'trends' => [
                'months' => array_keys($monthlyRevenue),
                'revenue' => array_values($monthlyRevenue),
                'expenses' => array_values($monthlyExpenses),
                'intake' => array_values($monthlyIntake),
                'dispatch' => array_values($monthlyDispatch),
            ],
            'crop_distribution' => $cropDistribution
        ]);
    }

    public function profitLossReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $deductionsQuery = SettlementDeduction::query();
        $otherIncomeQuery = \App\Models\OtherIncome::query();
        $expensesQuery = \App\Models\Expense::query();

        if ($startDate && $endDate) {
            // Deductions use created_at, other income uses date_received, expense uses date_incurred
            // Standardize end date to include end of day
            $endDateTime = $endDate . ' 23:59:59';
            $deductionsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDateTime]);
            $otherIncomeQuery->whereBetween('date_received', [$startDate, $endDate]);
            $expensesQuery->whereBetween('date_incurred', [$startDate, $endDate]);
        }

        // Calculate revenues from deductions
        $storageRev = (clone $deductionsQuery)->where('deduction_type', 'storage_fee')->sum('amount');
        $dryingRev = (clone $deductionsQuery)->where('deduction_type', 'drying_fee')->sum('amount');
        $millingRev = (clone $deductionsQuery)->where('deduction_type', 'milling_fee')->sum('amount');
        $gradingRev = (clone $deductionsQuery)->where('deduction_type', 'grading_fee')->sum('amount');

        // Calculate other incomes
        $otherIncomeTotal = (clone $otherIncomeQuery)->sum('amount');
        
        $groupedOtherIncomes = (clone $otherIncomeQuery)->selectRaw('source_name, SUM(amount) as total')
            ->groupBy('source_name')
            ->orderByDesc('total')
            ->get();
            
        $otherIncomeMap = [];
        foreach ($groupedOtherIncomes as $inc) {
            $otherIncomeMap[$inc->source_name] = (float)$inc->total;
        }

        // Fetch expenses grouped by category
        $groupedExpenses = $expensesQuery->selectRaw('category_name, SUM(amount) as total')
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->get();
            
        $expensesMap = [];
        $totalExpenses = 0;
        foreach ($groupedExpenses as $exp) {
            $expensesMap[$exp->category_name] = (float)$exp->total;
            $totalExpenses += (float)$exp->total;
        }

        $totalRevenue = $storageRev + $dryingRev + $millingRev + $gradingRev + $otherIncomeTotal;
        $netProfit = $totalRevenue - $totalExpenses;
        $otherIncomeNetProfit = $otherIncomeTotal - $totalExpenses; // Salio la mapato mengineyo

        return response()->json([
            'revenue' => [
                'storage_fees' => $storageRev,
                'drying_fees' => $dryingRev,
                'milling_fees' => $millingRev,
                'grading_fees' => $gradingRev,
                'other_income' => $otherIncomeTotal,
                'other_income_breakdown' => $otherIncomeMap,
                'total' => $totalRevenue,
            ],
            'expenses' => [
                'breakdown' => $expensesMap,
                'total' => $totalExpenses,
            ],
            'net_profit' => $netProfit,
            'other_income_net_profit' => $otherIncomeNetProfit,
        ]);
    }
}
