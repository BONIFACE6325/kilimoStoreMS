<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\OtherIncome;
use App\Models\SettlementDeduction;
use App\Models\Invoice;
use App\Models\Settlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function getFinancialSummary(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date') ? $request->query('end_date') . ' 23:59:59' : null;

        // 1. Storage & Service Fee Revenues collected via Settlements
        $serviceDeductionQuery = SettlementDeduction::query();
        if ($startDate && $endDate) {
            $serviceDeductionQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $storageFees = (float) (clone $serviceDeductionQuery)->where('deduction_type', 'storage_fee')->sum('amount');
        $dryingFees = (float) (clone $serviceDeductionQuery)->where('deduction_type', 'drying_fee')->sum('amount');
        $millingFees = (float) (clone $serviceDeductionQuery)->where('deduction_type', 'milling_fee')->sum('amount');
        $gradingFees = (float) (clone $serviceDeductionQuery)->where('deduction_type', 'grading_fee')->sum('amount');

        $totalServiceFeeRevenue = $storageFees + $dryingFees + $millingFees + $gradingFees;

        // 2. Other Incomes
        $otherIncomeQuery = OtherIncome::query();
        if ($startDate && $endDate) {
            $otherIncomeQuery->whereBetween('date_received', [$request->query('start_date'), $request->query('end_date')]);
        }
        $totalOtherIncome = (float) (clone $otherIncomeQuery)->sum('amount');

        // Total Revenue
        $totalRevenue = $totalServiceFeeRevenue + $totalOtherIncome;

        // 3. Operating Expenses (OPEX)
        $expenseQuery = Expense::query();
        if ($startDate && $endDate) {
            $expenseQuery->whereBetween('date_incurred', [$request->query('start_date'), $request->query('end_date')]);
        }
        $totalExpenses = (float) (clone $expenseQuery)->sum('amount');

        // Net Operating Profit
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;
        $costToIncomeRatio = $totalRevenue > 0 ? round(($totalExpenses / $totalRevenue) * 100, 1) : 0.0;

        // 4. Expenses Breakdown by Category
        $expensesByCategory = (clone $expenseQuery)
            ->select('category_name', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category_name')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($item) use ($totalExpenses) {
                $amt = (float) $item->total_amount;
                return [
                    'category_name' => $item->category_name ?: 'Matumizi Mengineyo',
                    'total_amount' => $amt,
                    'percentage' => $totalExpenses > 0 ? round(($amt / $totalExpenses) * 100, 1) : 0.0
                ];
            });

        // 5. Income Breakdown by Source
        $incomeBreakdown = [
            [
                'source_name' => 'Ada za Hifadhi ya Ghala (Storage)',
                'total_amount' => $storageFees,
                'percentage' => $totalRevenue > 0 ? round(($storageFees / $totalRevenue) * 100, 1) : 0.0
            ],
            [
                'source_name' => 'Ada za Ukoboaji (Milling)',
                'total_amount' => $millingFees,
                'percentage' => $totalRevenue > 0 ? round(($millingFees / $totalRevenue) * 100, 1) : 0.0
            ],
            [
                'source_name' => 'Ada za Ukaushaji (Drying)',
                'total_amount' => $dryingFees,
                'percentage' => $totalRevenue > 0 ? round(($dryingFees / $totalRevenue) * 100, 1) : 0.0
            ],
            [
                'source_name' => 'Ada za Upangaji Daraja (Grading)',
                'total_amount' => $gradingFees,
                'percentage' => $totalRevenue > 0 ? round(($gradingFees / $totalRevenue) * 100, 1) : 0.0
            ],
        ];

        // Add other income sources breakdown
        $otherIncomeSources = (clone $otherIncomeQuery)
            ->select('source_name', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('source_name')
            ->orderByDesc('total_amount')
            ->get();

        foreach ($otherIncomeSources as $ois) {
            $amt = (float) $ois->total_amount;
            $incomeBreakdown[] = [
                'source_name' => $ois->source_name ?: 'Mapato Mengineyo',
                'total_amount' => $amt,
                'percentage' => $totalRevenue > 0 ? round(($amt / $totalRevenue) * 100, 1) : 0.0
            ];
        }

        // Sort income breakdown by amount descending
        usort($incomeBreakdown, function ($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });

        // 6. Professional Executive Insights Generation
        $topRevenueDriver = !empty($incomeBreakdown[0]) && $incomeBreakdown[0]['total_amount'] > 0 
            ? $incomeBreakdown[0] 
            : null;

        $topCostCenter = count($expensesByCategory) > 0 
            ? $expensesByCategory[0] 
            : null;

        // Health Status Evaluation
        if ($profitMargin >= 30) {
            $healthStatus = 'Healthy';
            $healthBadge = '🟢 Afya ya Kifedha ni Imara Sana (Healthy Profitability)';
            $recommendation = 'Ghalani linaendesha shughuli zake kwa faida kubwa ya margin ya ' . $profitMargin . '%. Pendekezo: Endelea kuboresha huduma za ukoboaji na hifadhi ili kuvutia wakulima wengi zaidi.';
        } elseif ($profitMargin >= 10) {
            $healthStatus = 'Moderate';
            $healthBadge = '🟡 Afya ya Kifedha ni ya Kati (Moderate Performance)';
            $recommendation = 'Kiwango cha faida kiko vizuri (' . $profitMargin . '%). Hata hivyo, hakikisha unadhibiti gharama za uendeshaji hasa ' . ($topCostCenter ? $topCostCenter['category_name'] : 'matumizi makubwa') . ' ili kuongeza faida halisi.';
        } else {
            $healthStatus = 'Warning';
            $healthBadge = '🔴 Tahadhari ya Kifedha (Low Profit Margin / High OPEX Risk)';
            $recommendation = 'Kiwango cha gharama kinafikia ' . $costToIncomeRatio . '% ya mapato yote. Inapendekezwa kufanya ukaguzi wa kina wa matumizi ya ' . ($topCostCenter ? $topCostCenter['category_name'] : 'uendeshaji') . ' ili kupunguza matumizi yasiyo ya lazima.';
        }

        return response()->json([
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_service_fee_revenue' => $totalServiceFeeRevenue,
                'total_other_income' => $totalOtherIncome,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'profit_margin_pct' => $profitMargin,
                'cost_to_income_ratio_pct' => $costToIncomeRatio,
            ],
            'income_breakdown' => $incomeBreakdown,
            'expenses_breakdown' => $expensesByCategory,
            'executive_insights' => [
                'top_revenue_driver' => $topRevenueDriver,
                'top_cost_center' => $topCostCenter,
                'health_status' => $healthStatus,
                'health_badge' => $healthBadge,
                'recommendation' => $recommendation,
            ]
        ]);
    }
}
