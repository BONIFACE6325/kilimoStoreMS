<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\OtherIncome;
use App\Models\SettlementDeduction;
use App\Models\Service;
use App\Models\MillingJob;
use App\Models\DryingJob;
use App\Models\GradingRecord;
use App\Traits\HasTenantScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    use HasTenantScope;

    public function getFinancialSummary(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date') ? $request->query('end_date') . ' 23:59:59' : null;

        // 1. Storage & Service Fee Revenues collected via Settlements
        $serviceDeductionQuery = SettlementDeduction::whereHas('settlement', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });
        if ($startDate && $endDate) {
            $serviceDeductionQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $storageFees = (float) (clone $serviceDeductionQuery)->where('deduction_type', 'storage_fee')->sum('amount');
        $dryingFees = (float) (clone $serviceDeductionQuery)->where('deduction_type', 'drying_fee')->sum('amount');
        $millingFees = (float) (clone $serviceDeductionQuery)->where('deduction_type', 'milling_fee')->sum('amount');
        $gradingFees = (float) (clone $serviceDeductionQuery)->where('deduction_type', 'grading_fee')->sum('amount');

        $totalServiceFeeRevenue = $storageFees + $dryingFees + $millingFees + $gradingFees;

        // 2. Other Incomes
        $otherIncomeQuery = OtherIncome::where('tenant_id', $tenantId);
        if ($startDate && $endDate) {
            $otherIncomeQuery->whereBetween('date_received', [$request->query('start_date'), $request->query('end_date')]);
        }
        $totalOtherIncome = (float) (clone $otherIncomeQuery)->sum('amount');

        // Total Revenue
        $totalRevenue = $totalServiceFeeRevenue + $totalOtherIncome;

        // 3. Operating Expenses (OPEX)
        $expenseQuery = Expense::where('tenant_id', $tenantId);
        if ($startDate && $endDate) {
            $expenseQuery->whereBetween('date_incurred', [$request->query('start_date'), $request->query('end_date')]);
        }
        $totalExpenses = (float) (clone $expenseQuery)->sum('amount');

        // Net Operating Profit
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;
        $costToIncomeRatio = $totalRevenue > 0 ? round(($totalExpenses / $totalRevenue) * 100, 1) : 0.0;

        // 4. Expenses Breakdown by Category (From Database)
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

        // 5. Dynamic Income Breakdown from Registered Services & Other Income Sources
        $registeredServices = Service::where('tenant_id', $tenantId)->get();
        $incomeBreakdown = [];

        // Map revenue per service_id
        $serviceRevenues = [];
        foreach ($registeredServices as $svc) {
            $serviceRevenues[$svc->id] = 0.0;
        }

        // 5a. Sum revenue per service from MillingJobs
        $millingServiceSum = 0.0;
        $millingDeductions = DB::table('settlement_deductions')
            ->join('milling_jobs', 'settlement_deductions.source_reference_id', '=', 'milling_jobs.id')
            ->where('settlement_deductions.deduction_type', 'milling_fee')
            ->whereNotNull('milling_jobs.service_id');
        if ($startDate && $endDate) {
            $millingDeductions->whereBetween('settlement_deductions.created_at', [$startDate, $endDate]);
        }
        foreach ($millingDeductions->select('milling_jobs.service_id', DB::raw('SUM(settlement_deductions.amount) as total_amount'))->groupBy('milling_jobs.service_id')->get() as $item) {
            if (isset($serviceRevenues[$item->service_id])) {
                $amt = (float) $item->total_amount;
                $serviceRevenues[$item->service_id] += $amt;
                $millingServiceSum += $amt;
            }
        }

        // 5b. Sum revenue per service from DryingJobs
        $dryingServiceSum = 0.0;
        $dryingDeductions = DB::table('settlement_deductions')
            ->join('drying_jobs', 'settlement_deductions.source_reference_id', '=', 'drying_jobs.id')
            ->where('settlement_deductions.deduction_type', 'drying_fee')
            ->whereNotNull('drying_jobs.service_id');
        if ($startDate && $endDate) {
            $dryingDeductions->whereBetween('settlement_deductions.created_at', [$startDate, $endDate]);
        }
        foreach ($dryingDeductions->select('drying_jobs.service_id', DB::raw('SUM(settlement_deductions.amount) as total_amount'))->groupBy('drying_jobs.service_id')->get() as $item) {
            if (isset($serviceRevenues[$item->service_id])) {
                $amt = (float) $item->total_amount;
                $serviceRevenues[$item->service_id] += $amt;
                $dryingServiceSum += $amt;
            }
        }

        // 5c. Sum revenue per service from GradingRecords
        $gradingServiceSum = 0.0;
        $gradingDeductions = DB::table('settlement_deductions')
            ->join('grading_records', 'settlement_deductions.source_reference_id', '=', 'grading_records.id')
            ->where('settlement_deductions.deduction_type', 'grading_fee')
            ->whereNotNull('grading_records.service_id');
        if ($startDate && $endDate) {
            $gradingDeductions->whereBetween('settlement_deductions.created_at', [$startDate, $endDate]);
        }
        foreach ($gradingDeductions->select('grading_records.service_id', DB::raw('SUM(settlement_deductions.amount) as total_amount'))->groupBy('grading_records.service_id')->get() as $item) {
            if (isset($serviceRevenues[$item->service_id])) {
                $amt = (float) $item->total_amount;
                $serviceRevenues[$item->service_id] += $amt;
                $gradingServiceSum += $amt;
            }
        }

        // 5d. Distribute any unassigned category fees ONCE to a matching service without duplicating!
        $unassignedMilling = max(0, $millingFees - $millingServiceSum);
        $unassignedDrying = max(0, $dryingFees - $dryingServiceSum);
        $unassignedGrading = max(0, $gradingFees - $gradingServiceSum);
        $unassignedStorage = $storageFees;

        if ($unassignedMilling > 0) {
            foreach ($registeredServices as $svc) {
                $cat = strtolower($svc->category ?? '');
                $nameLower = strtolower(($svc->name_sw ?? '') . ' ' . ($svc->name_en ?? ''));
                if (str_contains($nameLower, 'kobo') || str_contains($nameLower, 'mill') || $cat === 'milling') {
                    $serviceRevenues[$svc->id] += $unassignedMilling;
                    $unassignedMilling = 0; // Assigned once to single service!
                    break;
                }
            }
        }

        if ($unassignedDrying > 0) {
            foreach ($registeredServices as $svc) {
                $cat = strtolower($svc->category ?? '');
                $nameLower = strtolower(($svc->name_sw ?? '') . ' ' . ($svc->name_en ?? ''));
                if (str_contains($nameLower, 'ukaush') || str_contains($nameLower, 'dry') || $cat === 'drying') {
                    $serviceRevenues[$svc->id] += $unassignedDrying;
                    $unassignedDrying = 0;
                    break;
                }
            }
        }

        if ($unassignedGrading > 0) {
            foreach ($registeredServices as $svc) {
                $cat = strtolower($svc->category ?? '');
                $nameLower = strtolower(($svc->name_sw ?? '') . ' ' . ($svc->name_en ?? ''));
                if (str_contains($nameLower, 'daraja') || str_contains($nameLower, 'grade') || $cat === 'grading') {
                    $serviceRevenues[$svc->id] += $unassignedGrading;
                    $unassignedGrading = 0;
                    break;
                }
            }
        }

        if ($unassignedStorage > 0) {
            foreach ($registeredServices as $svc) {
                $cat = strtolower($svc->category ?? '');
                $nameLower = strtolower(($svc->name_sw ?? '') . ' ' . ($svc->name_en ?? ''));
                if (str_contains($nameLower, 'hifadhi') || str_contains($nameLower, 'storage') || $cat === 'stock') {
                    $serviceRevenues[$svc->id] += $unassignedStorage;
                    $unassignedStorage = 0;
                    break;
                }
            }
        }

        if ($registeredServices->count() > 0) {
            foreach ($registeredServices as $svc) {
                $name = $svc->name_sw ?: $svc->name_en;
                if ($svc->crop_type) {
                    $name .= " ({$svc->crop_type})";
                }
                
                $amt = (float) ($serviceRevenues[$svc->id] ?? 0.0);

                $incomeBreakdown[] = [
                    'source_name' => $name,
                    'total_amount' => $amt,
                    'percentage' => $totalRevenue > 0 ? round(($amt / $totalRevenue) * 100, 1) : 0.0
                ];
            }
        } else {
            // Fallback dynamically from settlement deductions if services table is unpopulated
            if ($storageFees > 0) $incomeBreakdown[] = ['source_name' => 'Ada za Hifadhi ya Ghala', 'total_amount' => $storageFees, 'percentage' => $totalRevenue > 0 ? round(($storageFees / $totalRevenue) * 100, 1) : 0.0];
            if ($millingFees > 0) $incomeBreakdown[] = ['source_name' => 'Ada za Ukoboaji', 'total_amount' => $millingFees, 'percentage' => $totalRevenue > 0 ? round(($millingFees / $totalRevenue) * 100, 1) : 0.0];
            if ($dryingFees > 0) $incomeBreakdown[] = ['source_name' => 'Ada za Ukaushaji', 'total_amount' => $dryingFees, 'percentage' => $totalRevenue > 0 ? round(($dryingFees / $totalRevenue) * 100, 1) : 0.0];
            if ($gradingFees > 0) $incomeBreakdown[] = ['source_name' => 'Ada za Upangaji Daraja', 'total_amount' => $gradingFees, 'percentage' => $totalRevenue > 0 ? round(($gradingFees / $totalRevenue) * 100, 1) : 0.0];
        }

        // Add other income sources breakdown dynamically from database
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

        // Remove duplicate sources and sort by amount descending
        $uniqueBreakdown = [];
        foreach ($incomeBreakdown as $ib) {
            $key = $ib['source_name'];
            if (!isset($uniqueBreakdown[$key])) {
                $uniqueBreakdown[$key] = $ib;
            } else {
                $uniqueBreakdown[$key]['total_amount'] += $ib['total_amount'];
                $uniqueBreakdown[$key]['percentage'] = $totalRevenue > 0 ? round(($uniqueBreakdown[$key]['total_amount'] / $totalRevenue) * 100, 1) : 0.0;
            }
        }
        $incomeBreakdown = array_values($uniqueBreakdown);

        usort($incomeBreakdown, function ($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });

        // 6. Executive Insights Generation from Database
        $topRevenueDriver = !empty($incomeBreakdown[0]) && $incomeBreakdown[0]['total_amount'] > 0 
            ? $incomeBreakdown[0] 
            : null;

        $topCostCenter = count($expensesByCategory) > 0 
            ? $expensesByCategory[0] 
            : null;

        if ($profitMargin >= 30) {
            $healthStatus = 'Healthy';
            $healthBadge = '🟢 Afya ya Kifedha ni Imara Sana (Healthy Profitability)';
            $recommendation = 'Ghalani linaendesha shughuli zake kwa faida kubwa ya margin ya ' . $profitMargin . '%. Endelea kuimarisha huduma za kibiashara zilizosajiliwa.';
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
