<template>
  <div class="space-y-5 pb-12">
    
    <!-- Top Header Bar for Professional Financial Governance Center -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
      <div>
        <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
          Financial Control Center 📊
        </h1>
        <p class="text-xs text-slate-500 font-medium mt-0.5">
          Financial Overview — <strong class="text-slate-800 font-mono">{{ todayFormatted }}</strong>
        </p>
      </div>

      <!-- 📅 SUBTLE & ELEGANT QUICK DATE FILTERS -->
      <div class="flex flex-wrap items-center gap-1.5 bg-slate-50 p-1.5 rounded-xl border border-slate-200/80 w-full md:w-auto">
        <button 
          v-for="opt in filterOptions" 
          :key="opt.id"
          @click="selectFilter(opt.id)"
          :class="[
            'px-2.5 py-1.5 text-[11px] font-bold rounded-lg transition-all cursor-pointer',
            activeFilter === opt.id 
              ? 'bg-slate-900 text-white shadow-xs' 
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'
          ]"
        >
          {{ opt.label }}
        </button>

        <!-- Custom Date Range Pickers (Visible when 'custom' is selected) -->
        <div v-if="activeFilter === 'custom'" class="flex items-center gap-1.5 pl-2 border-l border-slate-200">
          <input 
            type="date" 
            v-model="customStartDate"
            @change="applyCustomDateFilter"
            class="px-2 py-1 text-[11px] bg-white border border-slate-300 rounded-md font-mono text-slate-800 focus:outline-emerald-600"
          />
          <span class="text-slate-400 text-xs">-</span>
          <input 
            type="date" 
            v-model="customEndDate"
            @change="applyCustomDateFilter"
            class="px-2 py-1 text-[11px] bg-white border border-slate-300 rounded-md font-mono text-slate-800 focus:outline-emerald-600"
          />
        </div>
      </div>
    </div>

    <!-- 🌟 THE 6 EXECUTIVE FINANCIAL CARDS (2 COLS ON MOBILE, 3 TABLET, 6 DESKTOP) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
      
      <!-- CARD 1: GROSS OFFICE REVENUE -->
      <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-emerald-500/50 transition-all space-y-1.5">
        <div class="flex items-center justify-between">
          <span class="text-[9.5px] font-black text-slate-400 uppercase tracking-wider">1. Gross Revenue</span>
          <div class="w-6.5 h-6.5 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs shadow-2xs">💰</div>
        </div>
        <div class="text-lg font-black text-slate-900 font-mono">
          Tsh {{ (finances.grossAllInflows || 0).toLocaleString() }}
        </div>
        <div class="text-[9px] text-slate-500 font-semibold pt-1 border-t border-slate-100 flex items-center justify-between">
          <span>Fees + Logistics + Loans</span>
          <span class="font-mono text-[8px] bg-slate-100 text-slate-600 px-1 rounded font-extrabold uppercase">GROSS</span>
        </div>
      </div>

      <!-- CARD 2: OPERATING EXPENSES / COSTS -->
      <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-red-400 transition-all space-y-1.5">
        <div class="flex items-center justify-between">
          <span class="text-[9.5px] font-black text-slate-400 uppercase tracking-wider">2. Operating Costs</span>
          <div class="w-6.5 h-6.5 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-xs shadow-2xs">🧾</div>
        </div>
        <div class="text-lg font-black text-red-600 font-mono">
          Tsh {{ (finances.totalExpenses || 0).toLocaleString() }}
        </div>
        <div class="text-[9px] text-red-600 font-bold pt-1 border-t border-slate-100 flex items-center justify-between">
          <span>Office & Machine Costs</span>
          <span class="text-[8px] bg-red-50 text-red-700 px-1 rounded font-extrabold uppercase border border-red-200">EXPENSES</span>
        </div>
      </div>

      <!-- CARD 3: TOTAL LOANS DISBURSED -->
      <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-amber-500/50 transition-all space-y-1.5">
        <div class="flex items-center justify-between">
          <span class="text-[9.5px] font-black text-slate-400 uppercase tracking-wider">3. Loans Disbursed</span>
          <div class="w-6.5 h-6.5 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs shadow-2xs">📤</div>
        </div>
        <div class="text-lg font-black text-amber-700 font-mono">
          Tsh {{ (finances.totalLoansDisbursed || 0).toLocaleString() }}
        </div>
        <div class="text-[9px] text-slate-500 font-semibold pt-1 border-t border-slate-100 flex items-center justify-between">
          <span>Lifetime Principal</span>
          <span class="text-[8px] bg-amber-50 text-amber-800 px-1 rounded font-extrabold uppercase border border-amber-200">DISBURSED</span>
        </div>
      </div>

      <!-- CARD 4: LOAN PRINCIPAL RECOVERED -->
      <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-emerald-500/50 transition-all space-y-1.5">
        <div class="flex items-center justify-between">
          <span class="text-[9.5px] font-black text-slate-400 uppercase tracking-wider">4. Principal Recovered</span>
          <div class="w-6.5 h-6.5 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs shadow-2xs">📥</div>
        </div>
        <div class="text-lg font-black text-emerald-700 font-mono">
          Tsh {{ (finances.totalLoansRecovered || 0).toLocaleString() }}
        </div>
        <div class="text-[9px] text-emerald-700 font-bold pt-1 border-t border-slate-100 flex items-center justify-between">
          <span>Recovered via Sales</span>
          <span class="text-[8px] bg-emerald-50 text-emerald-800 px-1 rounded font-extrabold uppercase border border-emerald-200">RECOVERED</span>
        </div>
      </div>

      <!-- CARD 5: OUTSTANDING LOAN DEBT -->
      <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-orange-400 transition-all space-y-1.5">
        <div class="flex items-center justify-between">
          <span class="text-[9.5px] font-black text-slate-400 uppercase tracking-wider">5. Outstanding Debt</span>
          <div class="w-6.5 h-6.5 rounded-xl bg-orange-50 text-orange-700 flex items-center justify-center font-bold text-xs shadow-2xs">💳</div>
        </div>
        <div class="text-lg font-black text-orange-700 font-mono">
          Tsh {{ (finances.loanPortfolio || 0).toLocaleString() }}
        </div>
        <div class="text-[9px] text-orange-800 font-bold pt-1 border-t border-slate-100 flex items-center justify-between">
          <span>Uncollected Debt</span>
          <span v-if="finances.overdueLoansCount > 0" class="text-[8px] bg-red-100 text-red-700 font-extrabold px-1 rounded">
            {{ finances.overdueLoansCount }} Overdue
          </span>
          <span v-else class="text-[8px] bg-orange-50 text-orange-800 font-extrabold px-1 rounded border border-orange-200">
            0% Interest
          </span>
        </div>
      </div>

      <!-- CARD 6: NET OPERATING PROFIT -->
      <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-teal-500/50 transition-all space-y-1.5">
        <div class="flex items-center justify-between">
          <span class="text-[9.5px] font-black text-slate-400 uppercase tracking-wider">6. Net Profit</span>
          <div class="w-6.5 h-6.5 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs shadow-2xs">📈</div>
        </div>
        <div class="text-lg font-black text-teal-700 font-mono">
          Tsh {{ (finances.netProfit || 0).toLocaleString() }}
        </div>
        <div class="text-[9px] text-slate-500 font-semibold pt-1 border-t border-slate-100 flex items-center justify-between">
          <span>Revenues - Expenses</span>
          <span class="text-[8px] bg-teal-50 text-teal-800 font-black px-1 rounded uppercase border border-teal-200">NET PROFIT</span>
        </div>
      </div>

    </div>

    <!-- 2. Financial Trends Chart & Service Revenue Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      
      <!-- Financial Cashflow & Revenue Line Chart (2 cols) -->
      <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
              <span>📈 Financial Performance Trend (Revenues vs Operating Expenses)</span>
            </h2>
            <p class="text-[11px] text-slate-500 font-medium">Monthly performance comparison over the last 6 months.</p>
          </div>
          <div class="flex items-center gap-3 text-xs font-extrabold">
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span><span class="text-slate-600">Revenue</span></div>
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span><span class="text-slate-600">Expenses</span></div>
          </div>
        </div>
        <div class="h-60 relative w-full">
          <Line :data="financialTrendData" :options="financialTrendOptions" />
        </div>
      </div>

      <!-- Service Revenue Sources Donut Chart (1 col) -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between space-y-3">
        <div>
          <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-extrabold text-slate-900">🍩 Grain Service Revenues</h2>
            <span class="text-[10px] font-bold text-slate-400">Total Collected</span>
          </div>
          <div class="h-44 flex items-center justify-center relative my-1">
            <Doughnut :data="revenueSourcesData" :options="revenueSourcesOptions" />
          </div>
        </div>

        <div class="space-y-1.5 pt-2.5 border-t border-slate-100 text-xs font-semibold">
          <div v-if="Object.keys(finances.serviceBreakdown).length === 0" class="text-slate-400 italic text-[11px] py-2 text-center">
            No service revenues recorded.
          </div>
          <div 
            v-for="(amount, serviceName, idx) in finances.serviceBreakdown" 
            :key="serviceName"
            class="flex justify-between items-center text-xs font-bold"
          >
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: getServiceColor(idx) }"></span>
              <span class="text-slate-800 font-bold text-[11.5px]">{{ serviceName }}:</span>
            </div>
            <span class="font-mono font-black text-slate-900">Tsh {{ parseFloat(amount || 0).toLocaleString() }}</span>
          </div>
        </div>
      </div>

    </div>

    <!-- 3. OTHER OPERATIONAL INCOMES & EXPENSES & LOANS RECONCILIATION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      
      <!-- OTHER OPERATIONAL INCOMES CARD -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
          <div>
            <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-1.5">
              <span>🚚 Other Operational Incomes</span>
            </h2>
            <p class="text-[11px] text-slate-500 font-medium">Logistics, Trucking, Rentals & Misc Revenue</p>
          </div>
          <span class="text-xs font-black text-emerald-700 font-mono">Tsh {{ (finances.totalOtherIncome || 0).toLocaleString() }}</span>
        </div>

        <div class="space-y-2">
          <div v-if="Object.keys(finances.otherIncomeBreakdown).length === 0" class="text-slate-400 italic text-xs py-3 text-center">
            No other operational incomes recorded.
          </div>
          <div 
            v-for="(amount, srcName) in finances.otherIncomeBreakdown" 
            :key="srcName"
            class="p-2.5 bg-emerald-50/50 rounded-xl border border-emerald-200/60 flex items-center justify-between text-xs"
          >
            <div class="flex items-center gap-2">
              <span class="text-emerald-600 font-black">•</span>
              <span class="font-bold text-slate-800 uppercase text-[11px]">{{ srcName }}</span>
            </div>
            <span class="font-mono font-black text-emerald-800">Tsh {{ parseFloat(amount || 0).toLocaleString() }}</span>
          </div>
        </div>
      </div>

      <!-- Expenses Breakdown Table -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
          <div>
            <h2 class="text-sm font-extrabold text-slate-900">🧾 Operating Expenses</h2>
            <p class="text-[11px] text-slate-500 font-medium">Categorized Operational Costs</p>
          </div>
          <span class="text-xs font-black text-red-600 font-mono">Tsh {{ (finances.totalExpenses || 0).toLocaleString() }}</span>
        </div>

        <div class="space-y-2">
          <div v-if="Object.keys(finances.expensesBreakdown).length === 0" class="text-slate-400 italic text-xs py-3 text-center">
            No operational expenses recorded.
          </div>
          <div 
            v-for="(amount, catName) in finances.expensesBreakdown" 
            :key="catName"
            class="p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/70 flex items-center justify-between text-xs"
          >
            <div class="flex items-center gap-2">
              <span class="text-red-500 font-black">•</span>
              <span class="font-bold text-slate-800">{{ catName }}</span>
            </div>
            <span class="font-mono font-black text-red-600">Tsh {{ parseFloat(amount || 0).toLocaleString() }}</span>
          </div>
        </div>
      </div>

      <!-- Loan Portfolio & Reconciliation Audit -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-3 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
            <div>
              <h2 class="text-sm font-extrabold text-slate-900">⚖️ Loan Portfolio Reconciliation</h2>
              <p class="text-[11px] text-slate-500 font-medium">Disbursed - Recovered = Outstanding</p>
            </div>
            <span class="px-2 py-0.5 rounded-lg text-[9.5px] font-black bg-slate-100 text-slate-700 border border-slate-200">
              AUDITED
            </span>
          </div>

          <div class="space-y-2 text-xs font-semibold text-slate-700">
            <div class="flex justify-between items-center p-2 bg-slate-50 rounded-xl border border-slate-200">
              <span>(+) Total Principal Disbursed:</span>
              <span class="font-mono font-bold text-amber-900">+ Tsh {{ (finances.totalLoansDisbursed || 0).toLocaleString() }}</span>
            </div>
            <div class="flex justify-between items-center p-2 bg-emerald-50/50 rounded-xl border border-emerald-200 text-emerald-900">
              <span>(-) Principal Recovered:</span>
              <span class="font-mono font-bold">- Tsh {{ (finances.totalLoansRecovered || 0).toLocaleString() }}</span>
            </div>
            <div class="flex justify-between items-center p-2 bg-orange-50/60 rounded-xl border border-orange-200 text-orange-950 font-black">
              <span>(=) Outstanding Balance:</span>
              <span class="font-mono font-black text-orange-900 text-xs">= Tsh {{ (finances.loanPortfolio || 0).toLocaleString() }}</span>
            </div>
          </div>
        </div>

        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-[10.5px] text-slate-700 space-y-0.5">
          <div class="font-black flex items-center gap-1.5 text-slate-900">
            <span>🔒 Financial Governance Policy:</span>
          </div>
          <p class="text-slate-600">
            All farmer loans are 0% Interest, capped at 50% of collateral crop value stored in warehouse.
          </p>
        </div>
      </div>

    </div>

    <!-- Executive Business Analytics & Strategic Insights Panel (Matching White Card Theme) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
              Live Database Intelligence
            </span>
            <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-1.5">
              🧠 Executive Analytics & Top Performance Drivers
            </h2>
          </div>
          <p class="text-[11px] text-slate-500 font-medium mt-0.5">Top 3 revenue generators, primary expense cost centers & strategic advisory</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-mono font-black bg-emerald-50 text-emerald-800 px-3 py-1.5 rounded-xl border border-emerald-200">
            Net Profit: Tsh {{ (finances.netProfit || 0).toLocaleString() }}
          </span>
        </div>
      </div>

      <!-- Top 3 Performance Tables / Mini Grid (3 Columns) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Column 1: Top 3 Core Services (Kinara cha Mapato) -->
        <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-200/80 space-y-3">
          <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
            <h3 class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
              <span>🌾 Top 3 Core Services</span>
            </h3>
            <span class="text-[10px] font-mono font-bold text-emerald-700 bg-emerald-100/70 px-1.5 py-0.5 rounded">
              Tsh {{ (finances.totalRevenue || 0).toLocaleString() }}
            </span>
          </div>

          <div class="space-y-2">
            <div v-for="(item, idx) in top3Services" :key="idx" class="p-2.5 bg-white rounded-lg border border-slate-200/70 shadow-2xs space-y-1">
              <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800 flex items-center gap-1">
                  <span>{{ item.rank }}</span>
                  <span class="capitalize truncate max-w-[120px]">{{ item.name }}</span>
                </span>
                <span class="font-mono font-extrabold text-emerald-700">+ Tsh {{ item.amount.toLocaleString() }}</span>
              </div>
              <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden flex">
                <div class="bg-emerald-500 h-full rounded-full transition-all" :style="{ width: item.pct + '%' }"></div>
              </div>
              <div class="flex justify-between text-[9.5px] text-slate-400 font-medium">
                <span>Rank #{{ idx + 1 }}</span>
                <span>{{ item.pct }}% of revenue</span>
              </div>
            </div>
            <div v-if="top3Services.length === 0" class="text-xs text-slate-400 py-3 text-center">No core services recorded</div>
          </div>
        </div>

        <!-- Column 2: Top 3 Cost Centers -->
        <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-200/80 space-y-3">
          <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
            <h3 class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
              <span>🔥 Top 3 Expense Costs</span>
            </h3>
            <span class="text-[10px] font-mono font-bold text-red-700 bg-red-100/70 px-1.5 py-0.5 rounded">
              Tsh {{ (finances.totalExpenses || 0).toLocaleString() }}
            </span>
          </div>

          <div class="space-y-2">
            <div v-for="(item, idx) in top3Expenses" :key="idx" class="p-2.5 bg-white rounded-lg border border-slate-200/70 shadow-2xs space-y-1">
              <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800 flex items-center gap-1">
                  <span>{{ item.rank }}</span>
                  <span class="capitalize truncate max-w-[120px]">{{ item.name }}</span>
                </span>
                <span class="font-mono font-extrabold text-red-700">- Tsh {{ item.amount.toLocaleString() }}</span>
              </div>
              <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden flex">
                <div class="bg-red-500 h-full rounded-full transition-all" :style="{ width: item.pct + '%' }"></div>
              </div>
              <div class="flex justify-between text-[9.5px] text-slate-400 font-medium">
                <span>Cost Center #{{ idx + 1 }}</span>
                <span>{{ item.pct }}% of expenses</span>
              </div>
            </div>
            <div v-if="top3Expenses.length === 0" class="text-xs text-slate-400 py-3 text-center">No expenses recorded</div>
          </div>
        </div>

        <!-- Column 3: Top 3 Other Incomes -->
        <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-200/80 space-y-3">
          <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
            <h3 class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
              <span>🚛 Top Other Incomes</span>
            </h3>
            <span class="text-[10px] font-mono font-bold text-blue-700 bg-blue-100/70 px-1.5 py-0.5 rounded">
              Tsh {{ (finances.totalOtherIncome || 0).toLocaleString() }}
            </span>
          </div>

          <div class="space-y-2">
            <div v-for="(item, idx) in top3OtherIncomes" :key="idx" class="p-2.5 bg-white rounded-lg border border-slate-200/70 shadow-2xs space-y-1">
              <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800 flex items-center gap-1">
                  <span>{{ item.rank }}</span>
                  <span class="capitalize truncate max-w-[120px]">{{ item.name }}</span>
                </span>
                <span class="font-mono font-extrabold text-blue-700">+ Tsh {{ item.amount.toLocaleString() }}</span>
              </div>
              <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden flex">
                <div class="bg-blue-500 h-full rounded-full transition-all" :style="{ width: item.pct + '%' }"></div>
              </div>
              <div class="flex justify-between text-[9.5px] text-slate-400 font-medium">
                <span>Inflow #{{ idx + 1 }}</span>
                <span>{{ item.pct }}% of other income</span>
              </div>
            </div>
            <div v-if="top3OtherIncomes.length === 0" class="text-xs text-slate-400 py-3 text-center">No other incomes recorded</div>
          </div>
        </div>

      </div>

      <!-- Actionable Executive Advisory Bullet Points (Matching White Card Theme) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
        <div class="bg-amber-50/60 p-3 rounded-xl border border-amber-200/80 space-y-1 text-xs text-amber-900">
          <div class="flex items-center gap-1.5 font-extrabold text-amber-950">
            <span>⛽ Cost Control Advisory:</span>
          </div>
          <p class="leading-relaxed text-[11.5px] font-medium text-amber-900/90">
            Operational costs are dominated by <strong class="font-black text-amber-950">{{ topExpense.name }}</strong> (Tsh {{ topExpense.amount.toLocaleString() }}). Enforce fuel logbook tracking for trucks & machines to optimize mileage.
          </p>
        </div>

        <div class="bg-emerald-50/60 p-3 rounded-xl border border-emerald-200/80 space-y-1 text-xs text-emerald-900">
          <div class="flex items-center gap-1.5 font-extrabold text-emerald-950">
            <span>🌾 Service Revenue Strategy:</span>
          </div>
          <p class="leading-relaxed text-[11.5px] font-medium text-emerald-900/90">
            <strong class="font-black text-emerald-950">{{ topService.name }}</strong> generates the highest core revenue (Tsh {{ topService.amount.toLocaleString() }}). Keep equipment well-serviced to eliminate downtime during intake.
          </p>
        </div>
      </div>
    </div>

    <!-- 4. Live Recent Financial Transactions Ledger Table -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div>
          <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
            📑 Financial Ledger & Audit Trail
          </h2>
          <p class="text-[11px] text-slate-500 font-medium mt-0.5">Real-time ledger of settlements, deductions, and operating expenses</p>
        </div>
        <router-link to="/cashbook" class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200/80 px-3 py-1.5 rounded-xl transition border border-slate-200">
          <span>Full Cashbook</span>
          <span>→</span>
        </router-link>
      </div>

      <div class="overflow-x-auto rounded-xl border border-slate-200/60">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/70 uppercase text-[10px] tracking-wider">
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Transaction Type</th>
              <th class="py-3 px-4">Description</th>
              <th class="py-3 px-4 text-right">Amount (TZS)</th>
              <th class="py-3 px-4 text-center">Payment Method</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr v-if="recentTransactions.length === 0" class="text-center text-slate-400">
              <td colspan="5" class="py-8 text-xs font-normal">No recent transactions recorded.</td>
            </tr>
            <tr v-for="t in recentTransactions" :key="t.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-4 text-slate-500 font-mono text-[11px] whitespace-nowrap">{{ t.date }}</td>
              <td class="py-3 px-4 whitespace-nowrap">
                <span :class="t.isExpense ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-emerald-50 text-emerald-800 border-emerald-200'" class="px-2.5 py-0.5 rounded-md text-[9.5px] font-bold uppercase border">
                  {{ t.type }}
                </span>
              </td>
              <td class="py-3 px-4 font-bold text-slate-900 max-w-[280px] truncate">{{ t.details }}</td>
              <td class="py-3 px-4 text-right font-black font-mono whitespace-nowrap" :class="t.isExpense ? 'text-slate-800' : 'text-emerald-800'">
                {{ t.isExpense ? '-' : '+' }} Tsh {{ t.amount.toLocaleString() }}
              </td>
              <td class="py-3 px-4 text-center font-mono text-slate-500 text-[10.5px] uppercase whitespace-nowrap">
                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-600 font-semibold border border-slate-200/60">
                  {{ t.method || 'CASH' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 5. Executive Enterprise Footer (Clean, Sleek, Corporate Neutral) -->
    <footer class="mt-8 pt-6 border-t border-slate-200/80 text-slate-500 text-xs flex flex-col md:flex-row items-center justify-between gap-4 pb-4">
      <div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
        <span class="font-bold text-slate-700">GARANOKI ERP</span>
        <span class="text-slate-300">|</span>
        <span class="font-medium text-slate-500">Financial Governance & Analytics Dashboard</span>
      </div>

      <div class="flex items-center gap-4 text-[11px] font-medium text-slate-500">
        <span class="font-mono">v2.4.0 (Enterprise Build)</span>
        <span class="text-slate-300">•</span>
        <span>Audit Trail Active</span>
        <span class="text-slate-300">•</span>
        <span>GARANOKI Management System</span>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import { Line, Doughnut } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend);

const loading = ref(false);
const activeFilter = ref('all_time');
const customStartDate = ref('');
const customEndDate = ref('');

const filterOptions = [
  { id: 'all_time', label: 'All Time' },
  { id: 'today', label: 'Today' },
  { id: 'this_week', label: 'This Week' },
  { id: 'this_month', label: 'This Month' },
  { id: 'last_3_months', label: '3 Months' },
  { id: 'last_6_months', label: '6 Months' },
  { id: 'this_year', label: 'This Year' },
  { id: 'custom', label: 'Custom' }
];

const finances = ref({
  grossAllInflows: 0,
  totalLoansDisbursed: 0,
  totalLoansRecovered: 0,
  loanPortfolio: 0,
  netProfit: 0,
  totalExpenses: 0,
  totalOtherIncome: 0,
  activeLoansCount: 0,
  overdueLoansCount: 0,
  serviceBreakdown: {},
  otherIncomeBreakdown: {},
  expensesBreakdown: {}
});

const trendsData = ref({ months: [], revenue: [], expenses: [] });
const recentTransactions = ref([]);

const todayFormatted = computed(() => {
  return new Date().toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
});

const selectFilter = (filterId) => {
  activeFilter.value = filterId;
  if (filterId !== 'custom') {
    fetchFinancialData();
  }
};

const applyCustomDateFilter = () => {
  if (customStartDate.value && customEndDate.value) {
    fetchFinancialData();
  }
};

// Compute start_date and end_date strings based on activeFilter
const getDateRangeParams = () => {
  if (activeFilter.value === 'all_time') return '';

  const now = new Date();
  const formatIsoDate = (d) => d.toISOString().split('T')[0];
  let start, end = formatIsoDate(now);

  if (activeFilter.value === 'today') {
    start = end;
  } else if (activeFilter.value === 'this_week') {
    const day = now.getDay();
    const diff = now.getDate() - day + (day === 0 ? -6 : 1); // Monday
    start = formatIsoDate(new Date(now.setDate(diff)));
  } else if (activeFilter.value === 'this_month') {
    start = formatIsoDate(new Date(now.getFullYear(), now.getMonth(), 1));
  } else if (activeFilter.value === 'last_3_months') {
    start = formatIsoDate(new Date(now.getFullYear(), now.getMonth() - 3, now.getDate()));
  } else if (activeFilter.value === 'last_6_months') {
    start = formatIsoDate(new Date(now.getFullYear(), now.getMonth() - 6, now.getDate()));
  } else if (activeFilter.value === 'this_year') {
    start = formatIsoDate(new Date(now.getFullYear(), 0, 1));
  } else if (activeFilter.value === 'custom') {
    if (customStartDate.value && customEndDate.value) {
      return `?start_date=${customStartDate.value}&end_date=${customEndDate.value}`;
    }
    return '';
  }

  return start && end ? `?start_date=${start}&end_date=${end}` : '';
};

// Financial Trend Line Chart Config (Revenue vs Expenses)
const financialTrendData = computed(() => ({
  labels: trendsData.value.months.length > 0 ? trendsData.value.months : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    {
      label: 'Revenue (TZS)',
      data: trendsData.value.revenue.length > 0 ? trendsData.value.revenue : [0, 0, 0, 0, 0, 0],
      borderColor: '#059669',
      backgroundColor: 'rgba(5, 150, 105, 0.06)',
      borderWidth: 2.5,
      fill: true,
      tension: 0.4
    },
    {
      label: 'Expenses (TZS)',
      data: trendsData.value.expenses && trendsData.value.expenses.length > 0 ? trendsData.value.expenses : [0, 0, 0, 0, 0, 0],
      borderColor: '#ef4444',
      backgroundColor: 'rgba(239, 68, 68, 0.06)',
      borderWidth: 2.5,
      fill: true,
      tension: 0.4
    }
  ]
}));

const financialTrendOptions = computed(() => {
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
  const textColor = isDark ? '#cbd5e1' : '#475569';
  const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : '#f1f5f9';

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { 
      legend: { display: false },
      tooltip: {
        backgroundColor: isDark ? '#1e293b' : '#0f172a',
        titleColor: '#ffffff',
        bodyColor: '#cbd5e1'
      }
    },
    scales: {
      y: { 
        grid: { color: gridColor }, 
        ticks: { 
          color: textColor,
          font: { size: 10, weight: '600' },
          callback: (value) => {
            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
            if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
            return value;
          }
        } 
      },
      x: { 
        grid: { display: false }, 
        ticks: { 
          color: textColor,
          font: { size: 10, weight: '600' } 
        } 
      }
    }
  };
});

// Color palette helper for dynamic service donut chart
const servicePalette = ['#4f46e5', '#d97706', '#059669', '#0d9488', '#8b5cf6', '#ec4899', '#3b82f6', '#f59e0b', '#10b981', '#6366f1'];
const getServiceColor = (idx) => servicePalette[idx % servicePalette.length];

// Revenue Sources Donut Chart Config (100% Dynamic from DB)
const revenueSourcesData = computed(() => {
  const breakdown = finances.value.serviceBreakdown || {};
  const keys = Object.keys(breakdown);
  const values = Object.values(breakdown).map(v => parseFloat(v || 0));

  return {
    labels: keys.length > 0 ? keys : ['Milling Services', 'Drying Services', 'Storage Services', 'Grading Services'],
    datasets: [{
      data: values.length > 0 ? values : [0, 0, 0, 0],
      backgroundColor: keys.length > 0 ? keys.map((_, i) => getServiceColor(i)) : ['#4f46e5', '#d97706', '#059669', '#0d9488'],
      borderWidth: 0
    }]
  };
});

const revenueSourcesOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } }
};

// Executive Insights Computed Properties
const topService = computed(() => {
  const breakdown = finances.value.serviceBreakdown || {};
  let topName = 'N/A';
  let maxVal = 0;
  let totalSrv = 0;
  for (const [name, amt] of Object.entries(breakdown)) {
    const val = parseFloat(amt || 0);
    totalSrv += val;
    if (val > maxVal) {
      maxVal = val;
      topName = name;
    }
  }
  const pct = totalSrv > 0 ? ((maxVal / totalSrv) * 100).toFixed(1) : '0';
  return { name: topName, amount: maxVal, pct };
});

const topExpense = computed(() => {
  const breakdown = finances.value.expensesBreakdown || {};
  let topName = 'N/A';
  let maxVal = 0;
  let totalExp = 0;
  for (const [name, amt] of Object.entries(breakdown)) {
    const val = parseFloat(amt || 0);
    totalExp += val;
    if (val > maxVal) {
      maxVal = val;
      topName = name;
    }
  }
  const pct = totalExp > 0 ? ((maxVal / totalExp) * 100).toFixed(1) : '0';
  return { name: topName, amount: maxVal, pct };
});

const loanRecoveryRate = computed(() => {
  const disbursed = finances.value.totalLoansDisbursed || 0;
  const recovered = finances.value.totalLoansRecovered || 0;
  if (disbursed === 0) return '0.0';
  return ((recovered / disbursed) * 100).toFixed(1);
});

const profitMarginPct = computed(() => {
  const rev = (finances.value.grossAllInflows || 0) - (finances.value.totalLoansRecovered || 0);
  const exp = finances.value.totalExpenses || 0;
  if (rev === 0) return '0.0';
  const net = rev - exp;
  return ((net / rev) * 100).toFixed(1);
});

// Top 3 Structured Mini Table Computed Properties
const top3Services = computed(() => {
  const breakdown = finances.value.serviceBreakdown || {};
  const list = Object.entries(breakdown).map(([name, amt]) => ({
    name,
    amount: parseFloat(amt || 0)
  })).sort((a, b) => b.amount - a.amount);

  const total = list.reduce((acc, curr) => acc + curr.amount, 0);
  const ranks = ['🥇', '🥈', '🥉', '🏅'];

  return list.slice(0, 3).map((item, idx) => ({
    rank: ranks[idx] || '•',
    name: item.name,
    amount: item.amount,
    pct: total > 0 ? ((item.amount / total) * 100).toFixed(1) : '0'
  }));
});

const top3Expenses = computed(() => {
  const breakdown = finances.value.expensesBreakdown || {};
  const list = Object.entries(breakdown).map(([name, amt]) => ({
    name,
    amount: parseFloat(amt || 0)
  })).sort((a, b) => b.amount - a.amount);

  const total = list.reduce((acc, curr) => acc + curr.amount, 0);
  const ranks = ['🚨', '🔧', '🍲', '📌'];

  return list.slice(0, 3).map((item, idx) => ({
    rank: ranks[idx] || '•',
    name: item.name,
    amount: item.amount,
    pct: total > 0 ? ((item.amount / total) * 100).toFixed(1) : '0'
  }));
});

const top3OtherIncomes = computed(() => {
  const breakdown = finances.value.otherIncomeBreakdown || {};
  const list = Object.entries(breakdown).map(([name, amt]) => ({
    name,
    amount: parseFloat(amt || 0)
  })).sort((a, b) => b.amount - a.amount);

  const total = list.reduce((acc, curr) => acc + curr.amount, 0);
  const ranks = ['🚛', '📦', '🏢', '🔹'];

  return list.slice(0, 3).map((item, idx) => ({
    rank: ranks[idx] || '•',
    name: item.name,
    amount: item.amount,
    pct: total > 0 ? ((item.amount / total) * 100).toFixed(1) : '0'
  }));
});

const fetchFinancialData = async () => {
  loading.value = true;
  const dateQueryParams = getDateRangeParams();

  try {
    const [dashRes, plRes, settRes, expRes] = await Promise.all([
      fetch('/api/v1/dashboard/stats' + dateQueryParams),
      fetch('/api/v1/reports/profit-loss' + dateQueryParams),
      fetch('/api/v1/sales/settlements'),
      fetch('/api/v1/expenses')
    ]);

    if (dashRes.ok) {
      const dashData = await dashRes.json();
      if (dashData.stats) {
        finances.value.grossAllInflows = dashData.stats.gross_all_inflows_tzs || 0;
        finances.value.totalLoansDisbursed = dashData.stats.total_loans_disbursed_tzs || 0;
        finances.value.totalLoansRecovered = dashData.stats.total_loans_recovered_tzs || 0;
        finances.value.loanPortfolio = dashData.stats.loan_portfolio_value || 0;
        finances.value.totalOtherIncome = dashData.stats.total_other_income_tzs || 0;
        finances.value.netProfit = dashData.stats.total_net_service_profit_tzs || 0;
        finances.value.activeLoansCount = dashData.stats.active_loans_count || 0;
        finances.value.overdueLoansCount = dashData.stats.overdue_loans_count || 0;
      }
      if (dashData.service_breakdown) {
        finances.value.serviceBreakdown = dashData.service_breakdown;
      }
      if (dashData.other_income_breakdown) {
        finances.value.otherIncomeBreakdown = dashData.other_income_breakdown;
      }
      if (dashData.trends) {
        trendsData.value = dashData.trends;
      }
    }

    if (plRes.ok) {
      const plData = await plRes.json();
      if (plData.expenses) {
        finances.value.totalExpenses = plData.expenses.total || 0;
        finances.value.expensesBreakdown = plData.expenses.breakdown || {};
      }
    }

    // Build Recent Financial Ledger
    let settlements = [];
    let expensesList = [];
    if (settRes.ok) settlements = await settRes.json();
    if (expRes.ok) expensesList = await expRes.json();

    const txList = [];
    settlements.slice(0, 4).forEach(s => {
      const dateStr = new Date(s.settled_at || s.created_at).toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
      txList.push({
        id: 'sett-' + s.id,
        date: dateStr,
        type: 'Settlement & Deductions',
        typeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        details: 'Farmer Settlement: ' + (s.farmer?.name || 'Farmer'),
        amount: parseFloat(s.gross_amount || 0),
        isExpense: false,
        method: s.payment_method || 'mobile_money'
      });
    });

    expensesList.slice(0, 3).forEach(e => {
      const dateStr = new Date(e.date_incurred || e.created_at).toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
      txList.push({
        id: 'exp-' + e.id,
        date: dateStr,
        type: 'Operating Expense',
        typeClass: 'bg-red-50 text-red-700 border-red-200',
        details: e.category_name + ' (' + (e.description || 'Office Expense') + ')',
        amount: parseFloat(e.amount || 0),
        isExpense: true,
        method: 'CASH'
      });
    });

    recentTransactions.value = txList;

  } catch (err) {
    console.error('Error fetching financial dashboard data:', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchFinancialData();
});
</script>
