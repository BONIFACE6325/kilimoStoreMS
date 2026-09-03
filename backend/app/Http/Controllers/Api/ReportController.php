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
use App\Models\InvoiceItem;
use App\Models\Buyer;
use App\Models\LoanTransaction;
use App\Models\BatchMovement;
use App\Models\OtherIncome;
use App\Models\Expense;
use App\Models\Bin;
use App\Models\Service;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
        $endDate = $request->query('end_date') ? $request->query('end_date') . ' 23:59:59' : null;

        $activeWeight = (float) Batch::whereIn('status', ['stored', 'received', 'processing'])->sum('current_weight_mt');
        $totalIntakeWeight = (float) Batch::sum('initial_weight_mt');
        $farmersCount = Farmer::where('status', 'active')->count();
        $outstandingLoans = (float) Loan::whereIn('status', ['active', 'overdue'])->sum('current_balance');
        $activeLoansCount = Loan::where('status', 'active')->count();
        $overdueLoansCount = Loan::where('status', 'overdue')->count();

        $serviceMetrics = $this->calculateServiceMetrics($startDate, $endDate);
        $totalServiceFeeRevenue = $serviceMetrics['total_revenue'];
        $dynamicServiceBreakdown = $serviceMetrics['breakdown'];

        $totalLoansRecovered = $this->getSumByDateRange(SettlementDeduction::query()->where('deduction_type', 'loan_principal'), 'created_at', $startDate, $endDate);
        $otherIncomeTotal = $this->getSumByDateRange(OtherIncome::query(), 'date_received', $request->query('start_date'), $request->query('end_date'));
        $totalLoansDisbursed = $this->getSumByDateRange(Loan::query(), 'created_at', $startDate, $endDate, 'principal_amount');
        $totalExpenses = $this->getSumByDateRange(Expense::query(), 'date_incurred', $request->query('start_date'), $request->query('end_date'));

        $grossStoreInflows = $totalServiceFeeRevenue + $totalLoansRecovered + $otherIncomeTotal;
        $totalNetServiceProfit = ($totalServiceFeeRevenue + $otherIncomeTotal) - $totalExpenses;

        $settlementSalesQuery = Settlement::query();
        if ($startDate && $endDate) {
            $settlementSalesQuery->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('settled_at', [$startDate, $endDate])
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->whereNull('settled_at')->whereBetween('created_at', [$startDate, $endDate]);
                  });
            });
        }
        $totalCropSales = (float) $settlementSalesQuery->sum('gross_amount');
        if ($totalCropSales <= 0) {
            $invoiceSalesQuery = Invoice::query();
            if ($startDate && $endDate) {
                $invoiceSalesQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $totalCropSales = (float) $invoiceSalesQuery->sum('total_amount');
        }

        $totalCapacity = Bin::sum('capacity_mt') ?: 1;
        $totalOccupied = Bin::sum('current_occupancy_mt');
        $occupancyPercentage = round(($totalOccupied / $totalCapacity) * 100, 1);

        $otherIncomeMap = $this->getGroupedMap(OtherIncome::query(), 'source_name', 'date_received', $request->query('start_date'), $request->query('end_date'));
        $expensesMap = $this->getGroupedMap(Expense::query(), 'category_name', 'date_incurred', $request->query('start_date'), $request->query('end_date'));

        $trends = $this->getMonthlyTrends();

        return response()->json([
            'stats' => [
                'total_weight_stored_mt' => $activeWeight,
                'total_intake_mt' => $totalIntakeWeight,
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
                'stock_valuation_tzs' => $this->getStockValuation(),
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
                'drying_jobs' => DryingJob::count(),
                'drying_active' => DryingJob::whereIn('status', ['queued', 'processing'])->count(),
                'drying_completed' => DryingJob::where('status', 'completed')->count(),
                'drying_qty' => DryingJob::sum('weight_before_mt'),
                'milling_jobs' => MillingJob::count(),
                'milling_active' => MillingJob::whereIn('status', ['queued', 'processing'])->count(),
                'milling_completed' => MillingJob::where('status', 'completed')->count(),
                'milling_qty' => MillingJob::sum('input_weight_mt'),
                'grading_jobs' => GradingRecord::count(),
                'grading_qty' => GradingRecord::count(),
            ],
            'trends' => $trends,
            'crop_distribution' => [
                'Mpunga / Rice' => (float) Batch::whereIn('crop_type', ['Rice', 'Mpunga', 'Paddy', 'mchele', 'Mchele'])->whereIn('status', ['stored', 'received', 'processing'])->sum('current_weight_mt'),
                'Mahindi / Maize' => (float) Batch::whereIn('crop_type', ['Maize', 'Mahindi', 'Sembe'])->whereIn('status', ['stored', 'received', 'processing'])->sum('current_weight_mt'),
                'Maharage / Beans' => (float) Batch::whereIn('crop_type', ['Beans', 'Maharage'])->whereIn('status', ['stored', 'received', 'processing'])->sum('current_weight_mt'),
            ]
        ]);
        } catch (\Throwable $e) {
            Log::error('getDashboardStats failed: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    private function getSumByDateRange($query, string $dateColumn, ?string $start, ?string $end, string $sumColumn = 'amount'): float
    {
        if ($start && $end) {
            $query->whereBetween($dateColumn, [$start, $end]);
        }
        return (float) $query->sum($sumColumn);
    }

    private function getGroupedMap($query, string $groupColumn, string $dateColumn, ?string $start, ?string $end): array
    {
        if ($start && $end) {
            $query->whereBetween($dateColumn, [$start, $end]);
        }
        $records = $query->selectRaw("{$groupColumn}, SUM(amount) as total")
            ->groupBy($groupColumn)
            ->orderByDesc('total')
            ->get();

        $map = [];
        foreach ($records as $r) {
            $key = $r->$groupColumn ?: 'General';
            $map[$key] = (float) $r->total;
        }
        return $map;
    }

    private function calculateServiceMetrics(?string $start, ?string $end): array
    {
        $dynamicServiceBreakdown = [];
        $services = Service::all();

        foreach ($services as $srv) {
            $serviceName = $srv->name_sw ?: $srv->name_en;
            
            $dQuery = DryingJob::where('service_id', $srv->id);
            $mQuery = MillingJob::where('service_id', $srv->id);
            $gQuery = GradingRecord::where('service_id', $srv->id);

            if ($start && $end) {
                $dQuery->whereBetween('created_at', [$start, $end]);
                $mQuery->whereBetween('created_at', [$start, $end]);
                $gQuery->whereBetween('created_at', [$start, $end]);
            }

            $tot = (float)$dQuery->sum('fee_amount') + (float)$mQuery->sum('fee_amount') + (float)$gQuery->sum('fee_amount');
            if ($tot > 0) {
                $dynamicServiceBreakdown[$serviceName] = $tot;
            }
        }

        $deductionQuery = SettlementDeduction::query();
        if ($start && $end) {
            $deductionQuery->whereBetween('created_at', [$start, $end]);
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

        return [
            'total_revenue' => max(array_sum($dynamicServiceBreakdown), $totalDeductionsSrv),
            'breakdown' => $dynamicServiceBreakdown,
        ];
    }

    private function getStockValuation(): float
    {
        $maizeWeight = Batch::where('crop_type', 'Maize')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        $riceWeight = Batch::where('crop_type', 'Rice')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        $beansWeight = Batch::where('crop_type', 'Beans')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        return ($maizeWeight * 800) + ($riceWeight * 1500) + ($beansWeight * 2000);
    }

    private function getMonthlyTrends(): array
    {
        $monthlyRevenue = [];
        $monthlyExpenses = [];
        $monthlyIntake = [];
        $monthlyDispatch = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            
            $revSum = Settlement::whereYear('settled_at', $date->year)
                ->whereMonth('settled_at', $date->month)
                ->sum('total_deductions')
                + OtherIncome::whereYear('date_received', $date->year)
                ->whereMonth('date_received', $date->month)
                ->sum('amount');
            $monthlyRevenue[$monthName] = (float)$revSum;

            $expSum = Expense::whereYear('date_incurred', $date->year)
                ->whereMonth('date_incurred', $date->month)
                ->sum('amount');
            $monthlyExpenses[$monthName] = (float)$expSum;
            
            $intakeSum = Batch::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('initial_weight_mt');
            $monthlyIntake[$monthName] = (float)$intakeSum * 1000;

            $dispatchSum = Batch::where('status', 'sold')
                ->whereYear('updated_at', $date->year)
                ->whereMonth('updated_at', $date->month)
                ->sum('current_weight_mt');
            $monthlyDispatch[$monthName] = (float)$dispatchSum * 1000;
        }

        return [
            'months' => array_keys($monthlyRevenue),
            'revenue' => array_values($monthlyRevenue),
            'expenses' => array_values($monthlyExpenses),
            'intake' => array_values($monthlyIntake),
            'dispatch' => array_values($monthlyDispatch),
        ];
    }

    public function profitLossReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $deductionsQuery = SettlementDeduction::query();
        $otherIncomeQuery = OtherIncome::query();
        $expensesQuery = Expense::query();

        if ($startDate && $endDate) {
            $endDateTime = $endDate . ' 23:59:59';
            $deductionsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDateTime]);
            $otherIncomeQuery->whereBetween('date_received', [$startDate, $endDate]);
            $expensesQuery->whereBetween('date_incurred', [$startDate, $endDate]);
        }

        $storageRev = (clone $deductionsQuery)->where('deduction_type', 'storage_fee')->sum('amount');
        $dryingRev = (clone $deductionsQuery)->where('deduction_type', 'drying_fee')->sum('amount');
        $millingRev = (clone $deductionsQuery)->where('deduction_type', 'milling_fee')->sum('amount');
        $gradingRev = (clone $deductionsQuery)->where('deduction_type', 'grading_fee')->sum('amount');

        $otherIncomeTotal = (clone $otherIncomeQuery)->sum('amount');
        
        $groupedOtherIncomes = (clone $otherIncomeQuery)->selectRaw('source_name, SUM(amount) as total')
            ->groupBy('source_name')
            ->orderByDesc('total')
            ->get();
            
        $otherIncomeMap = [];
        foreach ($groupedOtherIncomes as $inc) {
            $otherIncomeMap[$inc->source_name] = (float)$inc->total;
        }

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
        $otherIncomeNetProfit = $otherIncomeTotal - $totalExpenses;

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

    public function resetAllData(Request $request)
    {
        try {
            Schema::disableForeignKeyConstraints();

            SettlementDeduction::truncate();
            Settlement::truncate();
            InvoiceItem::truncate();
            Invoice::truncate();
            Buyer::truncate();
            LoanTransaction::truncate();
            Loan::truncate();
            GradingRecord::truncate();
            MillingJob::truncate();
            DryingJob::truncate();
            BatchMovement::truncate();
            Batch::truncate();
            Farmer::truncate();
            OtherIncome::truncate();
            Expense::truncate();

            Bin::query()->update([
                'current_occupancy_mt' => 0,
                'crop_type' => null,
                'status' => 'empty'
            ]);

            Schema::enableForeignKeyConstraints();

            return response()->json([
                'success' => true,
                'message' => 'Operational data wiped successfully. Users, Tenants, Branches & Services preserved.'
            ]);
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
