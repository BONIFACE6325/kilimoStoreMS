<template>
  <div class="space-y-6 text-slate-800 dark:text-slate-100">
    
    <!-- Top Executive Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <router-link to="/" class="hover:text-emerald-600">Dashboard</router-link>
          <span>/</span>
          <span class="text-slate-700 dark:text-slate-200 font-bold">Ripoti & Uchambuzi</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
          <span>📊 Kituo Kikuu cha Ripoti na Uchambuzi (Executive Reports Hub)</span>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Uchambuzi wa kina wa uendeshaji ghalani, mazao, huduma, fedha, mikopo, na mauzo kutoka kwenye database.</p>
      </div>

      <div class="flex items-center gap-2 print:hidden">
        <button 
          @click="downloadCSVReport"
          class="px-4 py-2.5 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 font-extrabold text-xs rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-xs transition cursor-pointer flex items-center gap-2"
        >
          <span>📥</span>
          <span>Pakua CSV / Excel</span>
        </button>

        <button 
          @click="printReport"
          class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center gap-2"
        >
          <span>🖨️</span>
          <span>Chapa / PDF</span>
        </button>
      </div>
    </div>

    <!-- Period Filter Controls Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
      <div class="flex items-center gap-2">
        <span class="text-xs font-black text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
          <span>🗓️</span>
          <span>Kipindi cha Ripoti:</span>
        </span>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button 
          @click="applyPeriodFilter('all')"
          :class="selectedPeriod === 'all' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          Muda Wote
        </button>
        <button 
          @click="applyPeriodFilter('today')"
          :class="selectedPeriod === 'today' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          Leo
        </button>
        <button 
          @click="applyPeriodFilter('this_week')"
          :class="selectedPeriod === 'this_week' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          Wiki Hii
        </button>
        <button 
          @click="applyPeriodFilter('this_month')"
          :class="selectedPeriod === 'this_month' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          Mwezi Huu
        </button>
        <button 
          @click="applyPeriodFilter('this_year')"
          :class="selectedPeriod === 'this_year' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          Mwaka Huu
        </button>
        <button 
          @click="applyPeriodFilter('custom')"
          :class="selectedPeriod === 'custom' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
          class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
        >
          ⚙️ Tarehe Zako
        </button>
      </div>

      <!-- Custom Date Inputs -->
      <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-slate-800">
        <input 
          type="date" 
          v-model="startDate" 
          class="px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
        />
        <span class="text-xs text-slate-400 font-bold">hadi</span>
        <input 
          type="date" 
          v-model="endDate" 
          class="px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white"
        />
        <button 
          @click="fetchAllReportData" 
          class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-xs cursor-pointer"
        >
          🔍 Onyesha
        </button>
      </div>
    </div>

    <!-- Navigation Tabs for All Report Categories -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-1 overflow-x-auto print:hidden">
      <button 
        @click="activeTab = 'executive'"
        :class="activeTab === 'executive' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 whitespace-nowrap"
      >
        <span>📊 1. Ripoti Kuu ya Uendeshaji & Trends</span>
      </button>

      <button 
        @click="activeTab = 'inventory'"
        :class="activeTab === 'inventory' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 whitespace-nowrap"
      >
        <span>🌾 2. Ripoti ya Mazao & Hifadhi</span>
      </button>

      <button 
        @click="activeTab = 'services'"
        :class="activeTab === 'services' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 whitespace-nowrap"
      >
        <span>⚙️ 3. Ripoti ya Utoaji Huduma</span>
      </button>

      <button 
        @click="activeTab = 'financial'"
        :class="activeTab === 'financial' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 whitespace-nowrap"
      >
        <span>💰 4. Ripoti ya Kifedha & OPEX</span>
      </button>

      <button 
        @click="activeTab = 'loans'"
        :class="activeTab === 'loans' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 whitespace-nowrap"
      >
        <span>👨‍🌾 5. Ripoti ya Wakulima & Mikopo</span>
      </button>

      <button 
        @click="activeTab = 'sales'"
        :class="activeTab === 'sales' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
        class="px-4 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 whitespace-nowrap"
      >
        <span>🛒 6. Ripoti ya Wanunuzi & Mauzo</span>
      </button>
    </div>

    <!-- TAB 1: Executive Operational Summary & Trends -->
    <div v-if="activeTab === 'executive'" class="space-y-6">
      
      <!-- Strategic Executive Metric Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl text-xl font-bold">💰</div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Mapato Ghafi (Gross Revenue)</p>
            <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">TZS {{ formatCurrency(stats.total_revenue_tzs) }}</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">+ Mapato mengineyo TZS {{ formatCurrency(stats.total_other_income_tzs) }}</p>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-xl text-xl font-bold">📦</div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Mzigo Uliopo Ghalani</p>
            <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ formatCurrency(stats.total_weight_stored_mt) }} MT</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Ujazo wa Ghala: {{ warehouse.occupancy_pct }}%</p>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl text-xl font-bold">🛒</div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Jumla ya Mauzo ya Mazao</p>
            <h3 class="text-xl font-black text-purple-600 dark:text-purple-400 mt-0.5">TZS {{ formatCurrency(stats.total_crop_sales_tzs) }}</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Invoices & Settlements</p>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl text-xl font-bold">👨‍🌾</div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Wakulima & Mikopo</p>
            <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ stats.registered_farmers }} Wakulima</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Deni la Mikopo: TZS {{ formatCurrency(stats.loan_portfolio_value) }}</p>
          </div>
        </div>
      </div>

      <!-- Trend Chart: Revenue vs Expenses & Intake vs Dispatch -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Chart 1: Financial Performance Trends -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="font-black text-sm text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <span>📈 Mwelekeo wa Mapato na Matumizi (Financial Trend)</span>
            <span class="text-xs text-emerald-600 font-bold">Miezi 6 Iliyopita</span>
          </div>

          <div class="h-64 flex items-center justify-center relative">
            <Bar v-if="financialTrendChartData.labels.length > 0" :data="financialTrendChartData" :options="trendChartOptions" />
            <div v-else class="text-center py-10 text-slate-400 font-semibold text-xs">📊 Bado hakuna data za kutosha za miezi.</div>
          </div>
        </div>

        <!-- Chart 2: Inventory Flow Trends -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="font-black text-sm text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <span>🌾 Mwelekeo wa Mzigo Kuingia na Kutoka (Stock Intake vs Dispatch)</span>
            <span class="text-xs text-blue-600 font-bold">Miezi 6 Iliyopita</span>
          </div>

          <div class="h-64 flex items-center justify-center relative">
            <Bar v-if="inventoryTrendChartData.labels.length > 0" :data="inventoryTrendChartData" :options="trendChartOptions" />
            <div v-else class="text-center py-10 text-slate-400 font-semibold text-xs">📊 Bado hakuna data za kutosha za mizigo.</div>
          </div>
        </div>

      </div>

    </div>

    <!-- TAB 2: Inventory & Crop Analytics Report -->
    <div v-if="activeTab === 'inventory'" class="space-y-6">
      
      <!-- Warehouse Capacity Gauge Card -->
      <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
          <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
            <span>🏢 Ujazo wa Ghala (Warehouse Capacity Occupancy)</span>
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Jumla ya uwezo: <span class="font-bold text-slate-900 dark:text-white">{{ formatCurrency(warehouse.capacity_mt) }} MT</span> | 
            Uliotumika: <span class="font-bold text-emerald-600">{{ formatCurrency(warehouse.occupied_mt) }} MT</span>
          </p>
        </div>

        <div class="w-full sm:w-72 space-y-1.5">
          <div class="flex items-center justify-between text-xs font-black">
            <span>Ujazo Uliopo:</span>
            <span class="text-emerald-600 dark:text-emerald-400">{{ warehouse.occupancy_pct }}%</span>
          </div>
          <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
            <div 
              class="h-full bg-emerald-500 transition-all duration-500 rounded-full" 
              :style="{ width: `${Math.min(100, warehouse.occupancy_pct)}%` }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Comprehensive Crop-by-Crop Operations Table -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
            <span>🌾 Uchambuzi wa Mazao Yote Yaliyosajiliwa (Registered Crops Analytics)</span>
          </h3>
          <span class="text-xs font-bold text-slate-400">Aina za Mazao: {{ cropAnalyticsList.length }}</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-extrabold uppercase border-b border-slate-200/80 dark:border-slate-800">
              <tr>
                <th class="py-3.5 px-4">Aina ya Zao</th>
                <th class="py-3.5 px-4">Kipimo</th>
                <th class="py-3.5 px-4">Jumla Iliyopokelewa</th>
                <th class="py-3.5 px-4">Iliyopata Huduma</th>
                <th class="py-3.5 px-4">Bado Hazijachakatwa</th>
                <th class="py-3.5 px-4">Iliyouzwa / Kuondoka</th>
                <th class="py-3.5 px-4">Iliyopo Ghalani sasa</th>
                <th class="py-3.5 px-4">Huduma Zilizofanyika</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
              <tr v-if="cropAnalyticsList.length === 0">
                <td colspan="8" class="py-10 text-center text-slate-400 font-bold text-xs">
                  🔍 Hakuna data ya mazao iliyopatikana.
                </td>
              </tr>
              <tr v-for="c in cropAnalyticsList" :key="c.crop_type" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white text-sm">
                  {{ c.crop_type }}
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-500">
                  <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold">
                    {{ c.unit }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-white">
                  {{ formatCurrency(c.total_received_qty) }} {{ c.unit }}
                </td>
                <td class="py-3.5 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                  {{ formatCurrency(c.serviced_qty) }} {{ c.unit }}
                </td>
                <td class="py-3.5 px-4 font-bold text-amber-600 dark:text-amber-400">
                  {{ formatCurrency(c.pending_raw_qty) }} {{ c.unit }}
                </td>
                <td class="py-3.5 px-4 font-bold text-purple-600 dark:text-purple-400">
                  {{ formatCurrency(c.sold_dispatched_qty) }} {{ c.unit }}
                </td>
                <td class="py-3.5 px-4 font-black text-blue-600 dark:text-blue-400">
                  {{ formatCurrency(c.current_bin_qty) }} {{ c.unit }}
                </td>
                <td class="py-3.5 px-4">
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="s in c.services_applied" 
                      :key="s.name"
                      class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10.5px] font-bold"
                    >
                      {{ s.name }} ({{ s.count }})
                    </span>
                    <span v-if="!c.services_applied || c.services_applied.length === 0" class="text-slate-400 italic">Bado</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- TAB 3: Service Processing Analytics Report -->
    <div v-if="activeTab === 'services'" class="space-y-6">
      
      <!-- Top Services Highlights Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Top Usage Service -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
          <p class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wider">🌟 Huduma Inayopatikana Sana Na Wateja</p>
          <h3 class="text-base font-black text-slate-900 dark:text-white">
            {{ topUsageService ? topUsageService.name : 'Bado Hakuna' }}
          </h3>
          <p v-if="topUsageService" class="text-xs font-bold text-emerald-600">Mara {{ topUsageService.count }} zimetolewa</p>
        </div>

        <!-- Top Revenue Service -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
          <p class="text-[11px] font-extrabold text-blue-600 uppercase tracking-wider">🏆 Huduma Inayoingiza Pesa Nyingi</p>
          <h3 class="text-base font-black text-slate-900 dark:text-white">
            {{ topRevenueService ? topRevenueService.name : 'Bado Hakuna' }}
          </h3>
          <p v-if="topRevenueService" class="text-xs font-bold text-blue-600">TZS {{ formatCurrency(topRevenueService.amount) }}</p>
        </div>

        <!-- Total Service Fees Collected -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
          <p class="text-[11px] font-extrabold text-purple-600 uppercase tracking-wider">💰 Jumla ya Ada za Huduma Zote</p>
          <h3 class="text-xl font-black text-purple-600 dark:text-purple-400">
            TZS {{ formatCurrency(stats.total_revenue_tzs) }}
          </h3>
          <p class="text-xs text-slate-400">Zilizokusanywa kupitia mauzo</p>
        </div>
      </div>

      <!-- Services Performance Table -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800">
          <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
            <span>⚙️ Uchambuzi wa Huduma Zote Zilizosajiliwa (Registered Services Analysis)</span>
          </h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-extrabold uppercase border-b border-slate-200/80 dark:border-slate-800">
              <tr>
                <th class="py-3.5 px-4">Jina la Huduma</th>
                <th class="py-3.5 px-4">Mara Zilizotolewa (Usage)</th>
                <th class="py-3.5 px-4">Mapato Yaliyopatikana (TZS)</th>
                <th class="py-3.5 px-4">Asilimia ya Mapato (%)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
              <tr v-for="(rev, name) in serviceBreakdownMap" :key="name" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white text-sm">
                  {{ name }}
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-700 dark:text-slate-300">
                  {{ serviceCountsMap[name] || 0 }} mara
                </td>
                <td class="py-3.5 px-4 font-black text-emerald-600 dark:text-emerald-400">
                  TZS {{ formatCurrency(rev) }}
                </td>
                <td class="py-3.5 px-4 font-extrabold text-blue-600 dark:text-blue-400">
                  {{ stats.total_revenue_tzs > 0 ? ((rev / stats.total_revenue_tzs) * 100).toFixed(1) : 0 }}%
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- TAB 4: Financial Ledger & OPEX Report -->
    <div v-if="activeTab === 'financial'" class="space-y-6">
      
      <!-- Financial Breakdown Doughnut & Bar Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Doughnut: Incomes -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="font-black text-sm text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
            <span>🍩 Mgawanyo wa Mapato (Income Breakdown)</span>
            <span class="text-emerald-600 font-bold text-xs">TZS {{ formatCurrency(stats.total_revenue_tzs + stats.total_other_income_tzs) }}</span>
          </div>

          <div class="h-64 flex items-center justify-center relative">
            <Doughnut v-if="incomeChartData.labels.length > 0" :data="incomeChartData" :options="doughnutOptions" />
            <div v-else class="text-center py-10 text-slate-400 font-semibold text-xs">📊 Bado hakuna mapato.</div>
          </div>
        </div>

        <!-- Bar: OPEX Expenses -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="font-black text-sm text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
            <span>📊 Mgawanyo wa Matumizi (OPEX Breakdown)</span>
            <span class="text-rose-600 font-bold text-xs">TZS {{ formatCurrency(stats.total_expenses_tzs) }}</span>
          </div>

          <div class="h-64 flex items-center justify-center relative">
            <Bar v-if="expensesChartData.labels.length > 0" :data="expensesChartData" :options="barOptions" />
            <div v-else class="text-center py-10 text-slate-400 font-semibold text-xs">📊 Bado hakuna matumizi.</div>
          </div>
        </div>

      </div>

    </div>

    <!-- TAB 5: Farmers & Loan Portfolio Report -->
    <div v-if="activeTab === 'loans'" class="space-y-6">
      
      <!-- Loan Portfolio Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
          <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Wakulima Waliosajiliwa</p>
          <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ stats.registered_farmers }}</h3>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
          <p class="text-[11px] font-extrabold text-blue-600 uppercase tracking-wider">Mikopo Yote Yaliyotolewa</p>
          <h3 class="text-xl font-black text-blue-600 dark:text-blue-400">TZS {{ formatCurrency(stats.total_loans_disbursed_tzs) }}</h3>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
          <p class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wider">Mikopo Yaliyorejeshwa</p>
          <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400">TZS {{ formatCurrency(stats.total_loans_recovered_tzs) }}</h3>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
          <p class="text-[11px] font-extrabold text-rose-600 uppercase tracking-wider">Deni Linalodaiwa Sasa</p>
          <h3 class="text-xl font-black text-rose-600 dark:text-rose-400">TZS {{ formatCurrency(stats.loan_portfolio_value) }}</h3>
        </div>
      </div>

      <!-- Farmers List & Credit Table -->
      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800">
          <h3 class="font-black text-sm text-slate-900 dark:text-white">👨‍🌾 Daftari la Wakulima na Hali ya Mikopo (Farmers & Credit Ledger)</h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-extrabold uppercase border-b border-slate-200/80 dark:border-slate-800">
              <tr>
                <th class="py-3.5 px-4">Kodi ya Mkulima</th>
                <th class="py-3.5 px-4">Jina la Mkulima</th>
                <th class="py-3.5 px-4">Namba ya Simu</th>
                <th class="py-3.5 px-4">Kijiji / Mtaa</th>
                <th class="py-3.5 px-4">Hali (Status)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
              <tr v-if="farmersList.length === 0">
                <td colspan="5" class="py-8 text-center text-slate-400 font-bold">Hakuna wakulima waliosajiliwa.</td>
              </tr>
              <tr v-for="f in farmersList" :key="f.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                <td class="py-3.5 px-4 font-black text-emerald-600">{{ f.farmer_code }}</td>
                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white capitalize">{{ f.name }}</td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">{{ f.phone || '-' }}</td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">{{ f.village || f.street || '-' }}</td>
                <td class="py-3.5 px-4">
                  <span 
                    :class="f.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                    class="px-2 py-0.5 rounded text-[10.5px] font-bold"
                  >
                    {{ f.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- TAB 6: Buyers & Sales Report -->
    <div v-if="activeTab === 'sales'" class="space-y-6">
      
      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <h3 class="font-black text-sm text-slate-900 dark:text-white">🛒 Orodha ya Wanunuzi na Jumla ya Mauzo (Buyers Purchases Report)</h3>
          <span class="text-xs font-bold text-emerald-600">Jumla ya Mauzo: TZS {{ formatCurrency(stats.total_crop_sales_tzs) }}</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-extrabold uppercase border-b border-slate-200/80 dark:border-slate-800">
              <tr>
                <th class="py-3.5 px-4">Jina la Mnunuzi</th>
                <th class="py-3.5 px-4">Simu</th>
                <th class="py-3.5 px-4">Jumla ya Invoices</th>
                <th class="py-3.5 px-4">Jumla ya Pesa Aliyonunua (TZS)</th>
                <th class="py-3.5 px-4">Hali</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
              <tr v-if="buyersList.length === 0">
                <td colspan="5" class="py-8 text-center text-slate-400 font-bold">Hakuna wanunuzi waliosajiliwa.</td>
              </tr>
              <tr v-for="b in buyersList" :key="b.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white text-sm capitalize">{{ b.name }}</td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">{{ b.phone || '-' }}</td>
                <td class="py-3.5 px-4 font-bold text-blue-600">{{ b.invoices_count || 0 }} Invoices</td>
                <td class="py-3.5 px-4 font-black text-emerald-600 dark:text-emerald-400">TZS {{ formatCurrency(b.total_spent || 0) }}</td>
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10.5px] font-bold">Active Buyer</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import { Doughnut, Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend);

const activeTab = ref('executive');
const selectedPeriod = ref('all');
const startDate = ref('');
const endDate = ref('');

const loading = ref(false);

const stats = ref({});
const warehouse = ref({});
const serviceBreakdownMap = ref({});
const serviceCountsMap = ref({});
const otherIncomeBreakdown = ref({});
const expensesBreakdown = ref({});
const machineStats = ref({});
const trends = ref({ months: [], revenue: [], expenses: [], intake: [], dispatch: [] });

const cropAnalyticsList = ref([]);
const topRevenueService = ref(null);
const topUsageService = ref(null);

const farmersList = ref([]);
const buyersList = ref([]);

const formatCurrency = (val) => {
  return Number(val || 0).toLocaleString('en-US');
};

const applyPeriodFilter = (period) => {
  selectedPeriod.value = period;
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const todayStr = `${year}-${month}-${day}`;

  if (period === 'all') {
    startDate.value = '';
    endDate.value = '';
  } else if (period === 'today') {
    startDate.value = todayStr;
    endDate.value = todayStr;
  } else if (period === 'this_week') {
    const monday = new Date(now);
    const dayOfWeek = now.getDay() || 7;
    monday.setDate(now.getDate() - (dayOfWeek - 1));
    const mYear = monday.getFullYear();
    const mMonth = String(monday.getMonth() + 1).padStart(2, '0');
    const mDay = String(monday.getDate()).padStart(2, '0');
    startDate.value = `${mYear}-${mMonth}-${mDay}`;
    endDate.value = todayStr;
  } else if (period === 'this_month') {
    startDate.value = `${year}-${month}-01`;
    endDate.value = todayStr;
  } else if (period === 'this_year') {
    startDate.value = `${year}-01-01`;
    endDate.value = todayStr;
  }

  if (period !== 'custom') {
    fetchAllReportData();
  }
};

const fetchAllReportData = async () => {
  loading.value = true;
  try {
    let qParams = '';
    if (startDate.value && endDate.value) {
      qParams = `?start_date=${startDate.value}&end_date=${endDate.value}`;
    }

    const [dashRes, invRes, farmersRes, buyersRes] = await Promise.all([
      fetch(`/api/v1/dashboard/stats${qParams}`),
      fetch(`/api/v1/inventory/analytics${qParams}`),
      fetch('/api/v1/farmers'),
      fetch('/api/v1/buyers')
    ]);

    if (dashRes.ok) {
      const dData = await dashRes.json();
      stats.value = dData.stats || {};
      warehouse.value = dData.warehouse || {};
      serviceBreakdownMap.value = dData.service_breakdown || {};
      otherIncomeBreakdown.value = dData.other_income_breakdown || {};
      expensesBreakdown.value = dData.expenses_breakdown || {};
      machineStats.value = dData.machine_stats || {};
      trends.value = dData.trends || { months: [], revenue: [], expenses: [], intake: [], dispatch: [] };
    }

    if (invRes.ok) {
      const iData = await invRes.json();
      cropAnalyticsList.value = iData.crop_analytics || [];
      topRevenueService.value = iData.top_revenue_service || null;
      topUsageService.value = iData.top_usage_service || null;
      serviceCountsMap.value = iData.service_counts || {};
    }

    if (farmersRes.ok) farmersList.value = await farmersRes.json();
    if (buyersRes.ok) buyersList.value = await buyersRes.json();

  } catch (err) {
    console.error('Error fetching report data:', err);
  } finally {
    loading.value = false;
  }
};

// Charts
const trendChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top', labels: { font: { family: 'Inter', size: 11, weight: '700' } } }
  },
  scales: {
    y: { beginAtZero: true, grid: { color: 'rgba(226, 232, 240, 0.4)' } },
    x: { grid: { display: false } }
  }
};

const financialTrendChartData = computed(() => {
  return {
    labels: trends.value.months || [],
    datasets: [
      { label: 'Mapato (TZS)', data: trends.value.revenue || [], backgroundColor: '#10b981', borderRadius: 6 },
      { label: 'Matumizi (TZS)', data: trends.value.expenses || [], backgroundColor: '#f43f5e', borderRadius: 6 }
    ]
  };
});

const inventoryTrendChartData = computed(() => {
  return {
    labels: trends.value.months || [],
    datasets: [
      { label: 'Mzigo Ulioingia (MT)', data: trends.value.intake || [], backgroundColor: '#3b82f6', borderRadius: 6 },
      { label: 'Mzigo Uliotoka (MT)', data: trends.value.dispatch || [], backgroundColor: '#a855f7', borderRadius: 6 }
    ]
  };
});

const incomeChartColors = ['#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#64748b'];

const incomeChartData = computed(() => {
  const labels = Object.keys(serviceBreakdownMap.value).concat(Object.keys(otherIncomeBreakdown.value));
  const data = Object.values(serviceBreakdownMap.value).concat(Object.values(otherIncomeBreakdown.value));

  return {
    labels,
    datasets: [{ data, backgroundColor: incomeChartColors.slice(0, labels.length), borderWidth: 2, borderColor: '#ffffff' }]
  };
});

const expensesChartData = computed(() => {
  const labels = Object.keys(expensesBreakdown.value);
  const data = Object.values(expensesBreakdown.value);

  return {
    labels,
    datasets: [{ label: 'Matumizi (TZS)', data, backgroundColor: '#f43f5e', borderRadius: 8 }]
  };
});

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'right', labels: { font: { family: 'Inter', size: 11, weight: '700' } } } }
};

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
};

const printReport = () => {
  window.print();
};

const downloadCSVReport = () => {
  let filename = `Ripoti_KilimoStore_${activeTab.value}_${new Date().toISOString().split('T')[0]}.csv`;
  let dataArray = [];
  let headers = [];

  if (activeTab.value === 'inventory') {
    headers = ['crop_type', 'unit', 'total_received_qty', 'serviced_qty', 'pending_raw_qty', 'sold_dispatched_qty', 'current_bin_qty'];
    dataArray = cropAnalyticsList.value;
  } else if (activeTab.value === 'services') {
    headers = ['service_name', 'usage_count', 'total_revenue_tzs'];
    dataArray = Object.keys(serviceBreakdownMap.value).map(name => ({
      service_name: name,
      usage_count: serviceCountsMap.value[name] || 0,
      total_revenue_tzs: serviceBreakdownMap.value[name]
    }));
  } else if (activeTab.value === 'loans') {
    headers = ['farmer_code', 'name', 'phone', 'village', 'status'];
    dataArray = farmersList.value;
  } else if (activeTab.value === 'sales') {
    headers = ['name', 'phone', 'invoices_count', 'total_spent'];
    dataArray = buyersList.value;
  } else {
    headers = ['metric', 'value'];
    dataArray = [
      { metric: 'Jumla ya Mapato Ghafi', value: stats.value.total_revenue_tzs },
      { metric: 'Jumla ya Mzigo Ghalani (MT)', value: stats.value.total_weight_stored_mt },
      { metric: 'Jumla ya Mauzo (TZS)', value: stats.value.total_crop_sales_tzs },
      { metric: 'Deni la Mikopo (TZS)', value: stats.value.loan_portfolio_value }
    ];
  }

  if (!dataArray || !dataArray.length) return;

  let csvContent = 'data:text/csv;charset=utf-8,';
  csvContent += headers.join(',') + '\r\n';
  dataArray.forEach(row => {
    let rowStr = headers.map(h => `"${String(row[h] || '').replace(/"/g, '""')}"`).join(',');
    csvContent += rowStr + '\r\n';
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(() => {
  fetchAllReportData();
});
</script>

<style scoped>
@media print {
  body {
    background: white !important;
    color: black !important;
  }
  .print\:hidden {
    display: none !important;
  }
}
</style>
