<template>
  <div class="space-y-6 text-slate-800 dark:text-slate-100">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <router-link to="/" class="hover:text-emerald-600">Dashboard</router-link>
          <span>/</span>
          <span class="text-slate-700 dark:text-slate-200 font-bold">Daftari Kuu & Hesabu</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
          <span>📊 Daftari Kuu: Mapato na Matumizi</span>
          <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-extrabold">Executive Financial Ledger</span>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Uchambuzi wa kina wa faida na hasara, vyanzo vya mapato, na gharama za uendeshaji ghalani kulingana na data za database.</p>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <button 
          v-if="activeTab === 'incomes'"
          @click="openNewIncomeModal"
          class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center gap-2"
        >
          <span>➕</span>
          <span>Sajili Mapato Mpya</span>
        </button>

        <button 
          v-if="activeTab === 'expenses'"
          @click="openNewExpenseModal"
          class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-rose-400/30 transition cursor-pointer flex items-center gap-2"
        >
          <span>➕</span>
          <span>Sajili Matumizi Mapya</span>
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-1">
      <button 
        @click="activeTab = 'analysis'"
        :class="activeTab === 'analysis' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
      >
        <span>📈 Ripoti & Grafu za Faida na Hasara (P&L Charts)</span>
      </button>

      <button 
        @click="activeTab = 'incomes'"
        :class="activeTab === 'incomes' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
      >
        <span>💰 Daftari la Mapato Mengineyo (Incomes)</span>
        <span class="px-1.5 py-0.2 rounded-md bg-emerald-700 text-[10px] text-white">{{ incomesList.length }}</span>
      </button>

      <button 
        @click="activeTab = 'expenses'"
        :class="activeTab === 'expenses' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
      >
        <span>📉 Daftari la Matumizi (Expenses OPEX)</span>
        <span class="px-1.5 py-0.2 rounded-md bg-emerald-700 text-[10px] text-white">{{ expensesList.length }}</span>
      </button>
    </div>

    <!-- TAB 1: Executive Financial Analysis & Interactive P&L Charts -->
    <div v-if="activeTab === 'analysis'" class="space-y-6">
      
      <!-- Executive KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <!-- Total Revenue -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl text-xl font-bold">
            💰
          </div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Jumla ya Mapato Ghafi</p>
            <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">TZS {{ formatCurrency(financialSummary.total_revenue) }}</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Ada za huduma + Mapato mengineyo</p>
          </div>
        </div>

        <!-- Total Expenses (OPEX) -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-xl text-xl font-bold">
            💸
          </div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Jumla ya Matumizi (OPEX)</p>
            <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 mt-0.5">TZS {{ formatCurrency(financialSummary.total_expenses) }}</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Cost-to-Income Ratio: {{ financialSummary.cost_to_income_ratio_pct }}%</p>
          </div>
        </div>

        <!-- Net Operating Profit -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div 
            :class="financialSummary.net_profit >= 0 ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400'"
            class="p-3 rounded-xl text-xl font-bold"
          >
            📈
          </div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Faida Halisi (Net Profit)</p>
            <h3 
              :class="financialSummary.net_profit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600 dark:text-rose-400'"
              class="text-xl font-black mt-0.5"
            >
              TZS {{ formatCurrency(financialSummary.net_profit) }}
            </h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Mapato minus Matumizi</p>
          </div>
        </div>

        <!-- Profit Margin % -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl text-xl font-bold">
            🎯
          </div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Wastani wa Faida (%)</p>
            <h3 class="text-xl font-black text-purple-600 dark:text-purple-400 mt-0.5">{{ financialSummary.profit_margin_pct }}%</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Net Profit Margin</p>
          </div>
        </div>
      </div>

      <!-- Professional Insights Card -->
      <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
            <span>🧠 Professional Executive Financial Insights</span>
            <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold">Real-time Database Analysis</span>
          </h2>

          <div class="text-xs font-black">
            {{ executiveInsights.health_badge }}
          </div>
        </div>

        <!-- Highlights Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Top Revenue Driver -->
          <div class="p-4 bg-emerald-50/60 dark:bg-emerald-950/30 rounded-xl border border-emerald-200/60 dark:border-emerald-800/40 space-y-1">
            <p class="text-[11px] font-extrabold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">🏆 Chanzo Kikuu cha Mapato (Top Revenue Driver)</p>
            <h4 class="text-sm font-black text-slate-900 dark:text-white">
              {{ executiveInsights.top_revenue_driver ? executiveInsights.top_revenue_driver.source_name : 'Bado Hakuna Mapato' }}
            </h4>
            <p v-if="executiveInsights.top_revenue_driver" class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
              TZS {{ formatCurrency(executiveInsights.top_revenue_driver.total_amount) }} ({{ executiveInsights.top_revenue_driver.percentage }}% ya mapato yote)
            </p>
          </div>

          <!-- Top Cost Center -->
          <div class="p-4 bg-rose-50/60 dark:bg-rose-950/30 rounded-xl border border-rose-200/60 dark:border-rose-800/40 space-y-1">
            <p class="text-[11px] font-extrabold text-rose-700 dark:text-rose-400 uppercase tracking-wider">⚠️ Eneo Linalotumia Gharama Kubwa (Highest Cost Center)</p>
            <h4 class="text-sm font-black text-slate-900 dark:text-white">
              {{ executiveInsights.top_cost_center ? executiveInsights.top_cost_center.category_name : 'Bado Hakuna Matumizi' }}
            </h4>
            <p v-if="executiveInsights.top_cost_center" class="text-xs font-bold text-rose-600 dark:text-rose-400">
              TZS {{ formatCurrency(executiveInsights.top_cost_center.total_amount) }} ({{ executiveInsights.top_cost_center.percentage }}% ya matumizi yote)
            </p>
          </div>
        </div>

        <!-- Recommendation -->
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1">
          <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">💡 Mapendekezo ya Kiushauri (Executive Recommendations)</p>
          <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-relaxed">
            {{ executiveInsights.recommendation }}
          </p>
        </div>
      </div>

      <!-- Interactive Visual Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Interactive Chart 1: Income Breakdown (Doughnut Chart) -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="font-black text-sm text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <span>🍩 Mchanganuo wa Grafu: Vyanzo vya Mapato</span>
            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">TZS {{ formatCurrency(financialSummary.total_revenue) }}</span>
          </div>

          <div class="h-64 flex items-center justify-center relative">
            <Doughnut 
              v-if="incomeChartData.labels.length > 0" 
              :data="incomeChartData" 
              :options="doughnutOptions" 
            />
            <div v-else class="text-center py-10 text-slate-400 font-semibold text-xs">
              📊 Bado hakuna mapato ya kuonyesha kwenye grafu.
            </div>
          </div>

          <!-- Income Table Details -->
          <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <div v-for="(inc, idx) in incomeBreakdownList" :key="idx" class="flex items-center justify-between text-xs">
              <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full inline-block" :style="{ backgroundColor: incomeChartColors[idx % incomeChartColors.length] }"></span>
                <span>{{ inc.source_name }}</span>
              </span>
              <span class="font-extrabold text-emerald-600 dark:text-emerald-400">
                TZS {{ formatCurrency(inc.total_amount) }} ({{ inc.percentage }}%)
              </span>
            </div>
          </div>
        </div>

        <!-- Interactive Chart 2: Expenses OPEX Breakdown (Bar Chart) -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="font-black text-sm text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <span>📊 Mchanganuo wa Grafu: Makundi ya Matumizi (OPEX)</span>
            <span class="text-xs text-rose-600 dark:text-rose-400 font-bold">TZS {{ formatCurrency(financialSummary.total_expenses) }}</span>
          </div>

          <div class="h-64 flex items-center justify-center relative">
            <Bar 
              v-if="expensesChartData.labels.length > 0" 
              :data="expensesChartData" 
              :options="barOptions" 
            />
            <div v-else class="text-center py-10 text-slate-400 font-semibold text-xs">
              📊 Bado hakuna matumizi ya kuonyesha kwenye grafu.
            </div>
          </div>

          <!-- Expenses Table Details -->
          <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <div v-for="(exp, idx) in expensesBreakdownList" :key="idx" class="flex items-center justify-between text-xs">
              <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                <span>{{ exp.category_name }}</span>
              </span>
              <span class="font-extrabold text-rose-600 dark:text-rose-400">
                TZS {{ formatCurrency(exp.total_amount) }} ({{ exp.percentage }}%)
              </span>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- TAB 2: Other Incomes Ledger Table & Management -->
    <div v-if="activeTab === 'incomes'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
      <!-- Toolbar -->
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
          <span>💰 Daftari la Mapato Mengineyo (Other Incomes Ledger)</span>
          <span class="px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-[11px] text-slate-600 dark:text-slate-400 font-bold">
            {{ filteredIncomes.length }}
          </span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button 
            @click="openIncomeSourcesModal"
            class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
          >
            ⚙️ Vyanzo vya Mapato
          </button>

          <div class="relative w-full sm:w-60">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">🔍</span>
            <input 
              v-model="searchIncomeQuery" 
              type="text" 
              placeholder="Tafuta mapato..." 
              class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
            />
          </div>
        </div>
      </div>

      <!-- Incomes Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider text-[10.5px]">
              <th class="py-3.5 px-4">Tarehe iliyopokewa</th>
              <th class="py-3.5 px-4">Chanzo cha Mapato</th>
              <th class="py-3.5 px-4">Maelezo</th>
              <th class="py-3.5 px-4">Kiasi (TZS)</th>
              <th class="py-3.5 px-4 text-right">Vitendo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
            <tr v-if="loading" class="text-center py-8">
              <td colspan="5" class="py-10 text-slate-400 font-bold text-xs">
                ⏳ Inapakia mapato kutoka kwenye database...
              </td>
            </tr>
            <tr v-else-if="filteredIncomes.length === 0" class="text-center py-8">
              <td colspan="5" class="py-10 text-slate-400 font-bold text-xs">
                🔍 Hakuna kumbukumbu ya mapato iliyopatikana.
              </td>
            </tr>
            <tr 
              v-else
              v-for="inc in filteredIncomes" 
              :key="inc.id"
              class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
            >
              <td class="py-3.5 px-4 font-bold text-slate-700 dark:text-slate-300">
                {{ inc.date_received }}
              </td>

              <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-white">
                <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px]">
                  💰 {{ inc.source_name }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">
                {{ inc.description || '-' }}
              </td>

              <td class="py-3.5 px-4 font-black text-emerald-600 dark:text-emerald-400">
                TZS {{ formatCurrency(inc.amount) }}
              </td>

              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button 
                    @click="openEditIncomeModal(inc)"
                    title="Badili"
                    class="px-2 py-1 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 text-blue-600 dark:text-blue-400 font-bold text-[11px] rounded-lg border border-blue-200 dark:border-blue-800 cursor-pointer"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="deleteIncomeRecord(inc)"
                    title="Futa"
                    class="px-2 py-1 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 text-rose-600 dark:text-rose-400 font-bold text-[11px] rounded-lg border border-rose-200 dark:border-rose-800 cursor-pointer"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 3: Operating Expenses Ledger Table & Management -->
    <div v-if="activeTab === 'expenses'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
      <!-- Toolbar -->
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
          <span>📉 Daftari la Matumizi ya Uendeshaji (Operating Expenses Ledger)</span>
          <span class="px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-[11px] text-slate-600 dark:text-slate-400 font-bold">
            {{ filteredExpenses.length }}
          </span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button 
            @click="openExpenseCategoriesModal"
            class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
          >
            ⚙️ Makundi ya Matumizi
          </button>

          <div class="relative w-full sm:w-60">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">🔍</span>
            <input 
              v-model="searchExpenseQuery" 
              type="text" 
              placeholder="Tafuta matumizi..." 
              class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/40"
            />
          </div>
        </div>
      </div>

      <!-- Expenses Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider text-[10.5px]">
              <th class="py-3.5 px-4">Tarehe</th>
              <th class="py-3.5 px-4">Kundi la Matumizi</th>
              <th class="py-3.5 px-4">Maelezo</th>
              <th class="py-3.5 px-4">Kiasi (TZS)</th>
              <th class="py-3.5 px-4 text-right">Vitendo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
            <tr v-if="loading" class="text-center py-8">
              <td colspan="5" class="py-10 text-slate-400 font-bold text-xs">
                ⏳ Inapakia matumizi kutoka kwenye database...
              </td>
            </tr>
            <tr v-else-if="filteredExpenses.length === 0" class="text-center py-8">
              <td colspan="5" class="py-10 text-slate-400 font-bold text-xs">
                🔍 Hakuna kumbukumbu ya matumizi iliyopatikana.
              </td>
            </tr>
            <tr 
              v-else
              v-for="exp in filteredExpenses" 
              :key="exp.id"
              class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
            >
              <td class="py-3.5 px-4 font-bold text-slate-700 dark:text-slate-300">
                {{ exp.date_incurred }}
              </td>

              <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-white">
                <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[11px]">
                  💸 {{ exp.category_name }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">
                {{ exp.description || '-' }}
              </td>

              <td class="py-3.5 px-4 font-black text-rose-600 dark:text-rose-400">
                TZS {{ formatCurrency(exp.amount) }}
              </td>

              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button 
                    @click="openEditExpenseModal(exp)"
                    title="Badili"
                    class="px-2 py-1 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 text-blue-600 dark:text-blue-400 font-bold text-[11px] rounded-lg border border-blue-200 dark:border-blue-800 cursor-pointer"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="deleteExpenseRecord(exp)"
                    title="Futa"
                    class="px-2 py-1 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 text-rose-600 dark:text-rose-400 font-bold text-[11px] rounded-lg border border-rose-200 dark:border-rose-800 cursor-pointer"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: Add / Edit Income Entry -->
    <transition name="fade">
      <div v-if="showIncomeModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden animate-fadeIn">
          
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="font-black text-base text-slate-900 dark:text-white flex items-center gap-2">
              <span>💰</span>
              <span>{{ isEditIncome ? 'Badili Mapato' : 'Sajili Mapato Mpya' }}</span>
            </h3>
            <button @click="showIncomeModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitIncomeForm" class="p-6 space-y-4 text-left">
            
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Chanzo cha Mapato *</label>
              <select 
                v-model="incomeForm.source_name" 
                required
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
              >
                <option value="">-- Chagua Chanzo --</option>
                <option v-for="src in incomeSourcesOptions" :key="src.id" :value="src.name">{{ src.name }}</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Kiasi cha Fedha (TZS) *</label>
                <input 
                  type="number" 
                  v-model.number="incomeForm.amount" 
                  required
                  min="1"
                  placeholder="e.g. 150000"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
                />
              </div>

              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Tarehe ya Kupokea *</label>
                <input 
                  type="date" 
                  v-model="incomeForm.date_received" 
                  required
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Maelezo (Description)</label>
              <textarea 
                v-model="incomeForm.description" 
                rows="2"
                placeholder="Maelezo ya ziada ya mapato..."
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
              <button 
                type="button"
                @click="showIncomeModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Ghairi
              </button>
              <button 
                type="submit" 
                :disabled="submitting || !incomeForm.source_name || !incomeForm.amount"
                class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50"
              >
                <span>{{ submitting ? 'Inasave...' : 'Hifadhi Mapato →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

    <!-- MODAL: Add / Edit Expense Entry -->
    <transition name="fade">
      <div v-if="showExpenseModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden animate-fadeIn">
          
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="font-black text-base text-slate-900 dark:text-white flex items-center gap-2">
              <span>💸</span>
              <span>{{ isEditExpense ? 'Badili Matumizi' : 'Sajili Matumizi Mapya' }}</span>
            </h3>
            <button @click="showExpenseModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitExpenseForm" class="p-6 space-y-4 text-left">
            
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Kundi la Matumizi *</label>
              <select 
                v-model="expenseForm.category_name" 
                required
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
              >
                <option value="">-- Chagua Kundi --</option>
                <option v-for="cat in expenseCategoriesOptions" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Kiasi cha Fedha (TZS) *</label>
                <input 
                  type="number" 
                  v-model.number="expenseForm.amount" 
                  required
                  min="1"
                  placeholder="e.g. 200000"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
                />
              </div>

              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Tarehe ya Matumizi *</label>
                <input 
                  type="date" 
                  v-model="expenseForm.date_incurred" 
                  required
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Maelezo (Description)</label>
              <textarea 
                v-model="expenseForm.description" 
                rows="2"
                placeholder="Maelezo ya matumizi haya..."
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
              <button 
                type="button"
                @click="showExpenseModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Ghairi
              </button>
              <button 
                type="submit" 
                :disabled="submitting || !expenseForm.category_name || !expenseForm.amount"
                class="py-2.5 px-4 bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-rose-400/30 transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50"
              >
                <span>{{ submitting ? 'Inasave...' : 'Hifadhi Matumizi →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

    <!-- Toast Notification -->
    <transition name="fade">
      <div 
        v-if="toastMessage"
        :class="toastType === 'error' ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white'"
        class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-2xl shadow-xl font-extrabold text-xs flex items-center gap-2 border border-white/20 animate-slideUp"
      >
        <span>{{ toastType === 'error' ? '⚠️' : '✅' }}</span>
        <span>{{ toastMessage }}</span>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import { Doughnut, Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend);

const activeTab = ref('analysis');

const financialSummary = ref({
  total_revenue: 0,
  total_service_fee_revenue: 0,
  total_other_income: 0,
  total_expenses: 0,
  net_profit: 0,
  profit_margin_pct: 0,
  cost_to_income_ratio_pct: 0
});

const executiveInsights = ref({
  top_revenue_driver: null,
  top_cost_center: null,
  health_status: 'Healthy',
  health_badge: '',
  recommendation: ''
});

const incomeBreakdownList = ref([]);
const expensesBreakdownList = ref([]);

const incomesList = ref([]);
const expensesList = ref([]);

const incomeSourcesOptions = ref([]);
const expenseCategoriesOptions = ref([]);

const loading = ref(false);
const submitting = ref(false);

const searchIncomeQuery = ref('');
const searchExpenseQuery = ref('');

const showIncomeModal = ref(false);
const isEditIncome = ref(false);
const incomeForm = ref({
  id: null,
  source_name: '',
  amount: null,
  date_received: new Date().toISOString().split('T')[0],
  description: ''
});

const showExpenseModal = ref(false);
const isEditExpense = ref(false);
const expenseForm = ref({
  id: null,
  category_name: '',
  amount: null,
  date_incurred: new Date().toISOString().split('T')[0],
  description: ''
});

const toastMessage = ref('');
const toastType = ref('success');

const triggerToast = (msg, type = 'success') => {
  toastMessage.value = msg;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = '';
  }, 4000);
};

const formatCurrency = (val) => {
  return Number(val || 0).toLocaleString('en-US');
};

const incomeChartColors = ['#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#64748b'];

// Income Doughnut Chart Setup
const incomeChartData = computed(() => {
  const labels = incomeBreakdownList.value.map(i => i.source_name);
  const data = incomeBreakdownList.value.map(i => i.total_amount);

  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor: incomeChartColors.slice(0, labels.length),
        borderWidth: 2,
        borderColor: '#ffffff'
      }
    ]
  };
});

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        font: { family: 'Inter', size: 11, weight: '700' },
        padding: 12
      }
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          const val = context.raw || 0;
          return ` TZS ${val.toLocaleString('en-US')}`;
        }
      }
    }
  }
};

// Expenses Bar Chart Setup
const expensesChartData = computed(() => {
  const labels = expensesBreakdownList.value.map(e => e.category_name);
  const data = expensesBreakdownList.value.map(e => e.total_amount);

  return {
    labels,
    datasets: [
      {
        label: 'Gharama (TZS)',
        data,
        backgroundColor: '#f43f5e',
        borderRadius: 8
      }
    ]
  };
});

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: function(context) {
          const val = context.raw || 0;
          return ` TZS ${val.toLocaleString('en-US')}`;
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: 'rgba(226, 232, 240, 0.4)' },
      ticks: {
        font: { size: 10, weight: '600' },
        callback: function(val) {
          return val >= 1000000 ? (val / 1000000) + 'M' : val >= 1000 ? (val / 1000) + 'k' : val;
        }
      }
    },
    x: {
      grid: { display: false },
      ticks: { font: { size: 10, weight: '700' } }
    }
  }
};

const filteredIncomes = computed(() => {
  if (!searchIncomeQuery.value.trim()) return incomesList.value;
  const q = searchIncomeQuery.value.toLowerCase();
  return incomesList.value.filter(inc => 
    (inc.source_name && inc.source_name.toLowerCase().includes(q)) ||
    (inc.description && inc.description.toLowerCase().includes(q))
  );
});

const filteredExpenses = computed(() => {
  if (!searchExpenseQuery.value.trim()) return expensesList.value;
  const q = searchExpenseQuery.value.toLowerCase();
  return expensesList.value.filter(exp => 
    (exp.category_name && exp.category_name.toLowerCase().includes(q)) ||
    (exp.description && exp.description.toLowerCase().includes(q))
  );
});

const fetchAccountingData = async () => {
  loading.value = true;
  try {
    const [summaryRes, incRes, expRes, incSourcesRes, expCatsRes] = await Promise.all([
      fetch('/api/v1/accounting/financial-summary'),
      fetch('/api/v1/incomes'),
      fetch('/api/v1/expenses'),
      fetch('/api/v1/incomes/sources'),
      fetch('/api/v1/expenses/categories')
    ]);

    if (summaryRes.ok) {
      const sData = await summaryRes.json();
      financialSummary.value = sData.summary || {};
      executiveInsights.value = sData.executive_insights || {};
      incomeBreakdownList.value = sData.income_breakdown || [];
      expensesBreakdownList.value = sData.expenses_breakdown || [];
    }

    if (incRes.ok) incomesList.value = await incRes.json();
    if (expRes.ok) expensesList.value = await expRes.json();
    if (incSourcesRes.ok) incomeSourcesOptions.value = await incSourcesRes.json();
    if (expCatsRes.ok) expenseCategoriesOptions.value = await expCatsRes.json();

  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kupakua taarifa za hesabu', 'error');
  } finally {
    loading.value = false;
  }
};

// Incomes Actions
const openNewIncomeModal = () => {
  isEditIncome.value = false;
  incomeForm.value = {
    id: null,
    source_name: '',
    amount: null,
    date_received: new Date().toISOString().split('T')[0],
    description: ''
  };
  showIncomeModal.value = true;
};

const openEditIncomeModal = (inc) => {
  isEditIncome.value = true;
  incomeForm.value = {
    id: inc.id,
    source_name: inc.source_name,
    amount: inc.amount,
    date_received: inc.date_received,
    description: inc.description || ''
  };
  showIncomeModal.value = true;
};

const submitIncomeForm = async () => {
  if (!incomeForm.value.source_name || !incomeForm.value.amount || incomeForm.value.amount <= 0) {
    triggerToast('Tafadhali jaza sehemu zote za mapato kwa usahihi', 'error');
    return;
  }

  submitting.value = true;
  try {
    const url = isEditIncome.value ? `/api/v1/incomes/${incomeForm.value.id}` : '/api/v1/incomes';
    const method = isEditIncome.value ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(incomeForm.value)
    });
    const data = await res.json();
    if (res.ok) {
      triggerToast(data.message || 'Mapato yamesajiliwa kikamilifu!');
      showIncomeModal.value = false;
      await fetchAccountingData();
    } else {
      triggerToast(data.error || data.message || 'Imeshindwa kusajili mapato', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kusajili mapato', 'error');
  } finally {
    submitting.value = false;
  }
};

const deleteIncomeRecord = async (inc) => {
  if (!confirm(`Je, una uhakika unataka kufuta kumbukumbu ya mapato ya TZS ${formatCurrency(inc.amount)}?`)) {
    return;
  }

  try {
    const res = await fetch(`/api/v1/incomes/${inc.id}`, { method: 'DELETE' });
    const data = await res.json();
    if (res.ok) {
      triggerToast('Mapato yamefutwa kikamilifu!');
      await fetchAccountingData();
    } else {
      triggerToast(data.message || 'Imeshindwa kufuta mapato', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kufuta mapato', 'error');
  }
};

// Expenses Actions
const openNewExpenseModal = () => {
  isEditExpense.value = false;
  expenseForm.value = {
    id: null,
    category_name: '',
    amount: null,
    date_incurred: new Date().toISOString().split('T')[0],
    description: ''
  };
  showExpenseModal.value = true;
};

const openEditExpenseModal = (exp) => {
  isEditExpense.value = true;
  expenseForm.value = {
    id: exp.id,
    category_name: exp.category_name,
    amount: exp.amount,
    date_incurred: exp.date_incurred,
    description: exp.description || ''
  };
  showExpenseModal.value = true;
};

const submitExpenseForm = async () => {
  if (!expenseForm.value.category_name || !expenseForm.value.amount || expenseForm.value.amount <= 0) {
    triggerToast('Tafadhali jaza sehemu zote za matumizi kwa usahihi', 'error');
    return;
  }

  submitting.value = true;
  try {
    const url = isEditExpense.value ? `/api/v1/expenses/${expenseForm.value.id}` : '/api/v1/expenses';
    const method = isEditExpense.value ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(expenseForm.value)
    });
    const data = await res.json();
    if (res.ok) {
      triggerToast(data.message || 'Matumizi yamesajiliwa kikamilifu!');
      showExpenseModal.value = false;
      await fetchAccountingData();
    } else {
      triggerToast(data.error || data.message || 'Imeshindwa kusajili matumizi', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kusajili matumizi', 'error');
  } finally {
    submitting.value = false;
  }
};

const deleteExpenseRecord = async (exp) => {
  if (!confirm(`Je, una uhakika unataka kufuta matumizi ya TZS ${formatCurrency(exp.amount)}?`)) {
    return;
  }

  try {
    const res = await fetch(`/api/v1/expenses/${exp.id}`, { method: 'DELETE' });
    const data = await res.json();
    if (res.ok) {
      triggerToast('Matumizi yamefutwa kikamilifu!');
      await fetchAccountingData();
    } else {
      triggerToast(data.message || 'Imeshindwa kufuta matumizi', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kufuta matumizi', 'error');
  }
};

const openIncomeSourcesModal = () => {
  alert('Ili kuongeza au kubadili vyanzo vya mapato, unaweza kuviongeza moja kwa moja unapochagua chanzo au kupitia Mipangilio.');
};

const openExpenseCategoriesModal = () => {
  alert('Ili kuongeza au kubadili makundi ya matumizi, unaweza kuyapanga au kuyaongeza kupitia Mipangilio.');
};

onMounted(() => {
  fetchAccountingData();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
  animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slideUp {
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
