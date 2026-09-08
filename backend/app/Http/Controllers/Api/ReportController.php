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
use Illuminate\Support\Facades\DB;
use App\Traits\HasTenantScope;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use HasTenantScope;

    public function getDashboardStats(Request $request)
    {
        try {
            $tenantId = $this->getTenantId($request);
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date') ? $request->query('end_date') . ' 23:59:59' : null;

            $activeWeight = (float) Batch::where('tenant_id', $tenantId)->whereIn('status', ['stored', 'received', 'processing'])
                ->sum(DB::raw('COALESCE(NULLIF(intake_quantity, 0), current_weight_mt)'));
            $totalIntakeWeight = (float) Batch::where('tenant_id', $tenantId)->sum(DB::raw('COALESCE(NULLIF(intake_quantity, 0), initial_weight_mt)'));
            $farmersCount = Farmer::where('tenant_id', $tenantId)->where('status', 'active')->count();
            $outstandingLoans = (float) Loan::where('tenant_id', $tenantId)->whereIn('status', ['active', 'overdue'])->sum('current_balance');
            $activeLoansCount = Loan::where('tenant_id', $tenantId)->where('status', 'active')->count();
            $overdueLoansCount = Loan::where('tenant_id', $tenantId)->where('status', 'overdue')->count();

            $serviceMetrics = $this->calculateServiceMetrics($startDate, $endDate, $tenantId);
            $totalServiceFeeRevenue = $serviceMetrics['total_revenue'];
            $dynamicServiceBreakdown = $serviceMetrics['breakdown'];

            $totalLoansRecovered = $this->getSumByDateRange(SettlementDeduction::query()->where('deduction_type', 'loan_principal'), 'created_at', $startDate, $endDate);
            $otherIncomeTotal = $this->getSumByDateRange(OtherIncome::where('tenant_id', $tenantId), 'date_received', $request->query('start_date'), $request->query('end_date'));
            $totalLoansDisbursed = $this->getSumByDateRange(Loan::where('tenant_id', $tenantId), 'created_at', $startDate, $endDate, 'principal_amount');
            $totalExpenses = $this->getSumByDateRange(Expense::where('tenant_id', $tenantId), 'date_incurred', $request->query('start_date'), $request->query('end_date'));

            $grossStoreInflows = $totalServiceFeeRevenue + $totalLoansRecovered + $otherIncomeTotal;
            $totalNetServiceProfit = ($totalServiceFeeRevenue + $otherIncomeTotal) - $totalExpenses;

            $settlementSalesQuery = Settlement::where('tenant_id', $tenantId);
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
                $invoiceSalesQuery = Invoice::where('tenant_id', $tenantId);
                if ($startDate && $endDate) {
                    $invoiceSalesQuery->whereBetween('created_at', [$startDate, $endDate]);
                }
                $totalCropSales = (float) $invoiceSalesQuery->sum('total_amount');
            }

            $branchIds = \App\Models\Branch::where('tenant_id', $tenantId)->pluck('id');
            $rawBins = Bin::whereIn('branch_id', $branchIds)->get();
            $totalCapacity = floatval($rawBins->sum('capacity_mt')) ?: 1;
            $totalOccupied = floatval(Batch::where('tenant_id', $tenantId)->whereNotIn('status', ['transformed', 'sold'])->sum('current_weight_mt'));
            $occupancyPercentage = round(($totalOccupied / $totalCapacity) * 100, 1);

            $otherIncomeMap = $this->getGroupedMap(OtherIncome::where('tenant_id', $tenantId), 'source_name', 'date_received', $request->query('start_date'), $request->query('end_date'));
            $expensesMap = $this->getGroupedMap(Expense::where('tenant_id', $tenantId), 'category_name', 'date_incurred', $request->query('start_date'), $request->query('end_date'));

            $trends = $this->getMonthlyTrends($tenantId);

            // Calculate registered crop distribution in original units (no forced MT conversion)
            $cropDistribution = [];
            $batches = Batch::where('tenant_id', $tenantId)->whereIn('status', ['stored', 'received', 'processing'])->get();
            foreach ($batches as $b) {
                $cropName = $b->crop_type ?: 'General';
                $unit = $b->intake_unit ?: 'Gunia';
                $qty = (float) ($b->intake_quantity > 0 ? $b->intake_quantity : $b->current_weight_mt);
                $key = "{$cropName} ({$unit})";
                $cropDistribution[$key] = ($cropDistribution[$key] ?? 0) + $qty;
            }

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
                    'stock_valuation_tzs' => $this->getStockValuation($tenantId),
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
                    'drying_jobs' => DryingJob::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->count(),
                    'drying_active' => DryingJob::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->whereIn('status', ['queued', 'processing'])->count(),
                    'drying_completed' => DryingJob::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->where('status', 'completed')->count(),
                    'drying_qty' => DryingJob::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->sum('weight_before_mt'),
                    'milling_jobs' => MillingJob::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->count(),
                    'milling_active' => MillingJob::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->whereIn('status', ['queued', 'processing'])->count(),
                    'milling_completed' => MillingJob::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->where('status', 'completed')->count(),
                    'milling_qty' => MillingJob::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->sum('input_weight_mt'),
                    'grading_jobs' => GradingRecord::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->count(),
                    'grading_qty' => GradingRecord::whereHas('batch', function($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })->count(),
                ],
                'trends' => $trends,
                'crop_distribution' => $cropDistribution
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

    private function calculateServiceMetrics(?string $start, ?string $end, ?string $tenantId = null): array
    {
        $dynamicServiceBreakdown = [];

        // 1. Drying Jobs
        $dQuery = DryingJob::with('service');
        if ($tenantId) {
            $dQuery->whereHas('batch', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        }
        if ($start && $end) {
            $dQuery->whereBetween('created_at', [$start, $end]);
        }
        foreach ($dQuery->where('fee_amount', '>', 0)->get() as $dj) {
            $name = $dj->service ? ($dj->service->name_sw ?: $dj->service->name_en) : ($dj->machine_id ?: 'Huduma ya Kuanika (Drying)');
            $dynamicServiceBreakdown[$name] = ($dynamicServiceBreakdown[$name] ?? 0.0) + (float)$dj->fee_amount;
        }

        // 2. Milling Jobs
        $mQuery = MillingJob::with('service');
        if ($tenantId) {
            $mQuery->whereHas('batch', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        }
        if ($start && $end) {
            $mQuery->whereBetween('created_at', [$start, $end]);
        }
        foreach ($mQuery->where('fee_amount', '>', 0)->get() as $mj) {
            $name = $mj->service ? ($mj->service->name_sw ?: $mj->service->name_en) : ($mj->machine_id ?: 'Huduma ya Kukoboa (Milling)');
            $dynamicServiceBreakdown[$name] = ($dynamicServiceBreakdown[$name] ?? 0.0) + (float)$mj->fee_amount;
        }

        // 3. Grading Records
        $gQuery = GradingRecord::with('service');
        if ($tenantId) {
            $gQuery->whereHas('batch', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        }
        if ($start && $end) {
            $gQuery->whereBetween('created_at', [$start, $end]);
        }
        foreach ($gQuery->where('fee_amount', '>', 0)->get() as $gr) {
            $name = $gr->service ? ($gr->service->name_sw ?: $gr->service->name_en) : 'Huduma ya Kupanga Madaraja (Grading)';
            $dynamicServiceBreakdown[$name] = ($dynamicServiceBreakdown[$name] ?? 0.0) + (float)$gr->fee_amount;
        }

        // 4. Storage Fee Deductions
        $deductionQuery = SettlementDeduction::query();
        if ($tenantId) {
            $deductionQuery->whereHas('settlement', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        }
        if ($start && $end) {
            $deductionQuery->whereBetween('created_at', [$start, $end]);
        }

        $storageRev = (float) (clone $deductionQuery)->where('deduction_type', 'storage_fee')->sum('amount');
        if ($storageRev > 0) {
            $dynamicServiceBreakdown['Ada ya Hifadhi (Storage)'] = ($dynamicServiceBreakdown['Ada ya Hifadhi (Storage)'] ?? 0.0) + $storageRev;
        }

        $totalMappedSrv = array_sum($dynamicServiceBreakdown);
        $totalDeductionsSrv = (float) (clone $deductionQuery)->whereIn('deduction_type', ['drying_fee', 'milling_fee', 'grading_fee', 'storage_fee'])->sum('amount');
        if ($totalDeductionsSrv > $totalMappedSrv) {
            $unmappedDiff = $totalDeductionsSrv - $totalMappedSrv;
            if ($unmappedDiff > 0) {
                $dynamicServiceBreakdown['Huduma Nyingine za Mauzo'] = $unmappedDiff;
            }
        }

        return [
            'total_revenue' => max(array_sum($dynamicServiceBreakdown), $totalDeductionsSrv),
            'breakdown' => $dynamicServiceBreakdown,
        ];
    }

    private function getStockValuation(?string $tenantId = null): float
    {
        $bQuery = Batch::query();
        if ($tenantId) $bQuery->where('tenant_id', $tenantId);
        $maizeWeight = (clone $bQuery)->where('crop_type', 'Maize')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        $riceWeight = (clone $bQuery)->where('crop_type', 'Rice')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        $beansWeight = (clone $bQuery)->where('crop_type', 'Beans')->where('status', 'stored')->sum('current_weight_mt') * 1000;
        return ($maizeWeight * 800) + ($riceWeight * 1500) + ($beansWeight * 2000);
    }

    private function getMonthlyTrends(?string $tenantId = null): array
    {
        $monthlyRevenue = [];
        $monthlyExpenses = [];
        $monthlyIntake = [];
        $monthlyDispatch = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            
            $sQuery = Settlement::whereYear('settled_at', $date->year)->whereMonth('settled_at', $date->month);
            $oiQuery = OtherIncome::whereYear('date_received', $date->year)->whereMonth('date_received', $date->month);
            $eQuery = Expense::whereYear('date_incurred', $date->year)->whereMonth('date_incurred', $date->month);
            $bIntakeQuery = Batch::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
            $bDispatchQuery = Batch::whereYear('updated_at', $date->year)->whereMonth('updated_at', $date->month)->where('status', 'sold');

            if ($tenantId) {
                $sQuery->where('tenant_id', $tenantId);
                $oiQuery->where('tenant_id', $tenantId);
                $eQuery->where('tenant_id', $tenantId);
                $bIntakeQuery->where('tenant_id', $tenantId);
                $bDispatchQuery->where('tenant_id', $tenantId);
            }

            $revSum = $sQuery->sum('total_deductions') + $oiQuery->sum('amount');
            $monthlyRevenue[$monthName] = (float)$revSum;

            $expQuery = Expense::whereYear('date_incurred', $date->year)->whereMonth('date_incurred', $date->month);
            if ($tenantId) $expQuery->where('tenant_id', $tenantId);
            $expSum = $expQuery->sum('amount');
            $monthlyExpenses[$monthName] = (float)$expSum;
            
            $intakeSum = $bIntakeQuery->sum(DB::raw('COALESCE(NULLIF(intake_quantity, 0), initial_weight_mt)'));
            $monthlyIntake[$monthName] = (float)$intakeSum;

            $dispatchSum = $bDispatchQuery->sum(DB::raw('COALESCE(NULLIF(intake_quantity, 0), current_weight_mt)'));
            $monthlyDispatch[$monthName] = (float)$dispatchSum;
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
        $tenantId = $this->getTenantId($request);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $deductionsQuery = SettlementDeduction::whereHas('settlement', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });
        $otherIncomeQuery = OtherIncome::where('tenant_id', $tenantId);
        $expensesQuery = Expense::where('tenant_id', $tenantId);

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

    public function getInventoryAnalytics(Request $request)
    {
        try {
            $tenantId = $this->getTenantId($request);
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date') ? $request->query('end_date') . ' 23:59:59' : null;

            $batchQuery = Batch::where('tenant_id', $tenantId)->with(['bin', 'farmer', 'dryingJobs.service', 'millingJobs.service', 'gradingRecords.service']);
            if ($startDate && $endDate) {
                $batchQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
            $batches = $batchQuery->get();

            $cropBreakdown = [];
            foreach ($batches as $b) {
                $crop = $b->crop_type ?: 'General';
                $unit = $b->intake_unit ?: 'Gunia';
                
                if (!isset($cropBreakdown[$crop])) {
                    $cropBreakdown[$crop] = [
                        'crop_type' => $crop,
                        'unit' => $unit,
                        'total_received_qty' => 0.0,
                        'serviced_qty' => 0.0,
                        'pending_raw_qty' => 0.0,
                        'sold_dispatched_qty' => 0.0,
                        'current_bin_qty' => 0.0,
                        'services_applied' => [],
                        'batch_count' => 0
                    ];
                }

                $qty = (float) ($b->intake_quantity > 0 ? $b->intake_quantity : $b->current_weight_mt);
                $cropBreakdown[$crop]['total_received_qty'] += $qty;
                $cropBreakdown[$crop]['batch_count']++;

                $hasService = false;

                foreach ($b->dryingJobs as $dj) {
                    $hasService = true;
                    $sName = $dj->service ? ($dj->service->name_sw ?: $dj->service->name_en) : 'Kuanika Mazao (Drying)';
                    $cropBreakdown[$crop]['services_applied'][$sName] = ($cropBreakdown[$crop]['services_applied'][$sName] ?? 0) + 1;
                }

                foreach ($b->millingJobs as $mj) {
                    $hasService = true;
                    $sName = $mj->service ? ($mj->service->name_sw ?: $mj->service->name_en) : 'Kukoboa / Kusaga (Milling)';
                    $cropBreakdown[$crop]['services_applied'][$sName] = ($cropBreakdown[$crop]['services_applied'][$sName] ?? 0) + 1;
                }

                foreach ($b->gradingRecords as $gr) {
                    $hasService = true;
                    $sName = $gr->service ? ($gr->service->name_sw ?: $gr->service->name_en) : 'Sorting & Grading';
                    $cropBreakdown[$crop]['services_applied'][$sName] = ($cropBreakdown[$crop]['services_applied'][$sName] ?? 0) + 1;
                }

                if ($hasService || in_array($b->status, ['processing', 'processed'])) {
                    $cropBreakdown[$crop]['serviced_qty'] += $qty;
                } else if (in_array($b->status, ['stored', 'received'])) {
                    $cropBreakdown[$crop]['pending_raw_qty'] += $qty;
                }

                if ($b->status === 'sold') {
                    $cropBreakdown[$crop]['sold_dispatched_qty'] += $qty;
                } else if (in_array($b->status, ['stored', 'received', 'processing'])) {
                    $cropBreakdown[$crop]['current_bin_qty'] += $qty;
                }
            }

            foreach ($cropBreakdown as &$cData) {
                $formattedServices = [];
                foreach ($cData['services_applied'] as $sName => $count) {
                    $formattedServices[] = [
                        'name' => $sName,
                        'count' => $count
                    ];
                }
                $cData['services_applied'] = $formattedServices;
            }
            unset($cData);

            $serviceMetrics = $this->calculateServiceMetrics($startDate, $endDate, $tenantId);
            $serviceBreakdownMap = $serviceMetrics['breakdown'];

            $topRevenueService = null;
            $maxRev = -1;
            foreach ($serviceBreakdownMap as $sName => $rev) {
                if ($rev > $maxRev) {
                    $maxRev = $rev;
                    $topRevenueService = [
                        'name' => $sName,
                        'amount' => $rev
                    ];
                }
            }

            $serviceCountsMap = [];
            $dQuery = DryingJob::whereHas('batch', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->with('service');
            $mQuery = MillingJob::whereHas('batch', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->with('service');
            $gQuery = GradingRecord::whereHas('batch', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->with('service');

            if ($startDate && $endDate) {
                $dQuery->whereBetween('created_at', [$startDate, $endDate]);
                $mQuery->whereBetween('created_at', [$startDate, $endDate]);
                $gQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            foreach ($dQuery->get() as $dj) {
                $name = $dj->service ? ($dj->service->name_sw ?: $dj->service->name_en) : 'Kuanika Mazao (Drying)';
                $serviceCountsMap[$name] = ($serviceCountsMap[$name] ?? 0) + 1;
            }
            foreach ($mQuery->get() as $mj) {
                $name = $mj->service ? ($mj->service->name_sw ?: $mj->service->name_en) : 'Kukoboa / Kusaga (Milling)';
                $serviceCountsMap[$name] = ($serviceCountsMap[$name] ?? 0) + 1;
            }
            foreach ($gQuery->get() as $gr) {
                $name = $gr->service ? ($gr->service->name_sw ?: $gr->service->name_en) : 'Sorting & Grading';
                $serviceCountsMap[$name] = ($serviceCountsMap[$name] ?? 0) + 1;
            }

            $topUsageService = null;
            $maxCount = -1;
            foreach ($serviceCountsMap as $sName => $count) {
                if ($count > $maxCount) {
                    $maxCount = $count;
                    $topUsageService = [
                        'name' => $sName,
                        'count' => $count
                    ];
                }
            }

            return response()->json([
                'crop_analytics' => array_values($cropBreakdown),
                'top_revenue_service' => $topRevenueService,
                'top_usage_service' => $topUsageService,
                'service_breakdown' => $serviceBreakdownMap,
                'service_counts' => $serviceCountsMap,
                'summary' => [
                    'total_batches_count' => count($batches),
                    'active_crops_count' => count($cropBreakdown)
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('getInventoryAnalytics failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

            $tenant = \App\Models\Tenant::first();
            $tenantId = $tenant ? $tenant->id : null;

            Farmer::create([
                'tenant_id' => $tenantId,
                'farmer_code' => 'FRM-001',
                'name' => 'boniface gwakila',
                'phone' => '07645367365',
                'region' => 'Kigoma',
                'district' => 'Kigoma',
                'ward' => 'Mahembe',
                'village' => 'Nkungwe',
                'street' => 'Kabuta b',
                'status' => 'inactive',
            ]);

            Schema::enableForeignKeyConstraints();

            return response()->json([
                'success' => true,
                'message' => 'Operational data wiped successfully. Default farmer boniface gwakila preserved clean.'
            ]);
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $type = $request->query('type', 'executive');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $statsResponse = $this->getDashboardStats($request);
            $statsData = json_decode($statsResponse->getContent(), true);

            $inventoryResponse = $this->getInventoryAnalytics($request);
            $inventoryData = json_decode($inventoryResponse->getContent(), true);

            $stats = $statsData['stats'] ?? [];
            $warehouse = $statsData['warehouse'] ?? [];
            $serviceBreakdown = $statsData['service_breakdown'] ?? [];
            $expensesBreakdown = $statsData['expenses_breakdown'] ?? [];
            $cropAnalytics = $inventoryData['crop_analytics'] ?? [];
            $serviceCounts = $inventoryData['service_counts'] ?? [];

            $refNumber = 'KSM-PDF-' . strtoupper(substr(md5(time() . rand(100, 999)), 0, 6));
            $periodLabel = ($startDate && $endDate) ? "Kipindi: {$startDate} hadi {$endDate}" : 'Kipindi: Muda Wote (Lifetime Report)';

            $html = '
            <!DOCTYPE html>
            <html lang="sw">
            <head>
                <meta charset="UTF-8">
                <title>KilimoStore Executive Report</title>
                <style>
                    @page { margin: 25px 30px; }
                    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; margin: 0; padding: 0; }
                    .header-table { width: 100%; border-bottom: 3px solid #059669; padding-bottom: 12px; margin-bottom: 20px; }
                    .company-title { font-size: 18px; font-weight: 900; color: #047857; text-transform: uppercase; margin: 0; }
                    .company-sub { font-size: 10px; font-weight: bold; color: #64748b; margin-top: 2px; }
                    .doc-badge { background-color: #047857; color: #ffffff; padding: 4px 8px; font-weight: 900; font-size: 9px; text-transform: uppercase; border-radius: 4px; display: inline-block; }
                    .ref-text { font-size: 10px; font-weight: bold; color: #334155; margin-top: 4px; }
                    
                    .section-title { font-size: 12px; font-weight: 800; color: #0f172a; margin-top: 15px; margin-bottom: 8px; border-left: 4px solid #059669; padding-left: 8px; text-transform: uppercase; }
                    
                    .cards-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 15px; }
                    .card-cell { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; text-align: left; }
                    .card-label { font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; }
                    .card-val { font-size: 13px; font-weight: 900; color: #047857; margin-top: 2px; }
                    
                    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
                    .data-table th { background-color: #f1f5f9; color: #334155; font-size: 9.5px; font-weight: 800; text-transform: uppercase; border: 1px solid #cbd5e1; padding: 7px; text-align: left; }
                    .data-table td { border: 1px solid #e2e8f0; padding: 7px; font-size: 10px; }
                    .data-table tr:nth-child(even) { background-color: #f8fafc; }

                    .footer-table { width: 100%; margin-top: 40px; border-top: 1px solid #cbd5e1; padding-top: 15px; page-break-inside: avoid; }
                    .sig-title { font-size: 9.5px; font-weight: bold; color: #475569; text-transform: uppercase; }
                    .sig-line { border-bottom: 1px solid #0f172a; height: 35px; width: 85%; }
                    .stamp-box { border: 2px dashed #94a3b8; width: 85px; height: 85px; border-radius: 50%; text-align: center; line-height: 85px; color: #94a3b8; font-size: 8.5px; font-weight: bold; margin: auto; }
                </style>
            </head>
            <body>
                <table class="header-table">
                    <tr>
                        <td>
                            <div class="company-title">KILIMO STORE MANAGEMENT SYSTEM</div>
                            <div class="company-sub">S.L.P 100, Kigoma, Tanzania | Simu: +255 764 536 736 | Email: info@kilimostore.co.tz</div>
                            <div class="company-sub">HATI RASMI YA ANKARA NA RIPOTI ZA GHALA (OFFICIAL INVOICE VOUCHER)</div>
                        </td>
                        <td style="text-align: right;">
                            <div class="doc-badge">OFFICIAL EXECUTIVE PDF</div>
                            <div class="ref-text">Kumb: ' . $refNumber . '</div>
                            <div class="ref-text">' . $periodLabel . '</div>
                            <div class="ref-text">Tarehe: ' . date('d/m/Y H:i') . '</div>
                        </td>
                    </tr>
                </table>
            ';

            if ($type === 'inventory') {
                $html .= '<div class="section-title">🌾 Uchambuzi wa Mazao na Hifadhi Ghalani (Crop Inventory Analytics)</div>';
                $html .= '<table class="data-table">
                    <thead>
                        <tr>
                            <th>Aina ya Zao</th>
                            <th>Kipimo</th>
                            <th>Jumla Iliyopokelewa</th>
                            <th>Iliyopata Huduma</th>
                            <th>Bado Hazijachakatwa</th>
                            <th>Iliyouzwa / Kuondoka</th>
                            <th>Iliyopo Ghalani sasa</th>
                        </tr>
                    </thead>
                    <tbody>';
                foreach ($cropAnalytics as $c) {
                    $html .= '<tr>
                        <td><strong>' . htmlspecialchars($c['crop_type']) . '</strong></td>
                        <td>' . htmlspecialchars($c['unit']) . '</td>
                        <td>' . number_format($c['total_received_qty']) . ' ' . htmlspecialchars($c['unit']) . '</td>
                        <td style="color: #047857;">' . number_format($c['serviced_qty']) . ' ' . htmlspecialchars($c['unit']) . '</td>
                        <td style="color: #d97706;">' . number_format($c['pending_raw_qty']) . ' ' . htmlspecialchars($c['unit']) . '</td>
                        <td style="color: #9333ea;">' . number_format($c['sold_dispatched_qty']) . ' ' . htmlspecialchars($c['unit']) . '</td>
                        <td style="color: #2563eb; font-weight: bold;">' . number_format($c['current_bin_qty']) . ' ' . htmlspecialchars($c['unit']) . '</td>
                    </tr>';
                }
                $html .= '</tbody></table>';

            } else if ($type === 'services') {
                $html .= '<div class="section-title">⚙️ Uchambuzi wa Huduma Zote Zilizosajiliwa (Service Charges Breakdown)</div>';
                $html .= '<table class="data-table">
                    <thead>
                        <tr>
                            <th>Jina la Huduma</th>
                            <th>Mara Zilizotolewa (Usage)</th>
                            <th>Mapato Yaliyopatikana (TZS)</th>
                            <th>Asilimia (%)</th>
                        </tr>
                    </thead>
                    <tbody>';
                $totRev = $stats['total_revenue_tzs'] ?? 1;
                foreach ($serviceBreakdown as $name => $rev) {
                    $cnt = $serviceCounts[$name] ?? 0;
                    $pct = $totRev > 0 ? round(($rev / $totRev) * 100, 1) : 0;
                    $html .= '<tr>
                        <td><strong>' . htmlspecialchars($name) . '</strong></td>
                        <td>' . number_format($cnt) . ' mara</td>
                        <td style="color: #047857; font-weight: bold;">TZS ' . number_format($rev) . '</td>
                        <td>' . $pct . '%</td>
                    </tr>';
                }
                $html .= '</tbody></table>';

            } else if ($type === 'financial') {
                $html .= '<div class="section-title">💰 Mchanganuo wa Mapato na Matumizi (Financial Ledger Statement)</div>';
                $html .= '<table class="cards-table">
                    <tr>
                        <td class="card-cell">
                            <div class="card-label">Jumla ya Mapato Ghafi</div>
                            <div class="card-val">TZS ' . number_format($stats['total_revenue_tzs'] ?? 0) . '</div>
                        </td>
                        <td class="card-cell">
                            <div class="card-label">Jumla ya Matumizi (OPEX)</div>
                            <div class="card-val" style="color: #e11d48;">TZS ' . number_format($stats['total_expenses_tzs'] ?? 0) . '</div>
                        </td>
                        <td class="card-cell">
                            <div class="card-label">Faida Halisi (Net Profit)</div>
                            <div class="card-val" style="color: #2563eb;">TZS ' . number_format($stats['total_net_service_profit_tzs'] ?? 0) . '</div>
                        </td>
                    </tr>
                </table>';

                $html .= '<div class="section-title">📉 Matumizi ya Uendeshaji (OPEX Expenses)</div>';
                $html .= '<table class="data-table">
                    <thead><tr><th>Kundi la Matumizi</th><th>Kiasi (TZS)</th></tr></thead>
                    <tbody>';
                foreach ($expensesBreakdown as $cat => $amt) {
                    $html .= '<tr><td>' . htmlspecialchars($cat) . '</td><td style="color: #e11d48;">TZS ' . number_format($amt) . '</td></tr>';
                }
                $html .= '</tbody></table>';

            } else {
                $html .= '<div class="section-title">📊 Muhtasari Mkuu wa Uendeshaji (Executive Operational Overview)</div>';
                $html .= '<table class="cards-table">
                    <tr>
                        <td class="card-cell">
                            <div class="card-label">Mapato Ghafi</div>
                            <div class="card-val">TZS ' . number_format($stats['total_revenue_tzs'] ?? 0) . '</div>
                        </td>
                        <td class="card-cell">
                            <div class="card-label">Mzigo Uliopo Ghalani</div>
                            <div class="card-val">' . number_format($stats['total_weight_stored_mt'] ?? 0) . ' MT</div>
                        </td>
                        <td class="card-cell">
                            <div class="card-label">Jumla ya Mauzo</div>
                            <div class="card-val" style="color: #9333ea;">TZS ' . number_format($stats['total_crop_sales_tzs'] ?? 0) . '</div>
                        </td>
                        <td class="card-cell">
                            <div class="card-label">Deni la Mikopo</div>
                            <div class="card-val" style="color: #d97706;">TZS ' . number_format($stats['loan_portfolio_value'] ?? 0) . '</div>
                        </td>
                    </tr>
                </table>';
            }

            $html .= '
                <table class="footer-table">
                    <tr>
                        <td style="width: 38%;">
                            <div class="sig-title">Imeandaliwa Na:</div>
                            <div class="sig-line"></div>
                            <div style="font-size: 9px; font-weight: bold; margin-top: 3px;">Mhasibu wa Ghala / Store Accountant</div>
                        </td>
                        <td style="width: 38%;">
                            <div class="sig-title">Imeidhinishwa Na:</div>
                            <div class="sig-line"></div>
                            <div style="font-size: 9px; font-weight: bold; margin-top: 3px;">Meneja wa Ghala / Warehouse Lead</div>
                        </td>
                        <td style="width: 24%; text-align: center;">
                            <div class="stamp-box">OFFICIAL STAMP</div>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';

            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="KilimoStore_Executive_Report_' . $type . '.pdf"'
            ]);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
    }
}
