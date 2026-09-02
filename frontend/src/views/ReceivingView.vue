<template>
  <div class="space-y-6">
    <!-- Header & Action Bar (Clean Single Line Layout) -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center justify-center font-black text-lg shadow-2xs">
          📊
        </div>
        <div>
          <h1 class="text-lg md:text-xl font-black text-slate-900 dark:text-slate-50 tracking-tight">Miamala ya Kila Siku & Mfuko wa Siku</h1>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Usimamizi wa mapokezi, maingizo ya mauzo, malipo ya wakulima, na gharama za uendeshaji.</p>
        </div>
      </div>

      <!-- Action Bar Controls in Single Neat Line -->
      <div class="flex items-center gap-2 flex-wrap md:flex-nowrap w-full md:w-auto justify-end">
        <!-- Date Selector -->
        <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-xl shadow-2xs hover:border-emerald-400 transition">
          <span class="text-slate-500 dark:text-slate-400 text-xs">📅</span>
          <input 
            v-model="selectedDate" 
            type="date" 
            @change="fetchData"
            class="bg-transparent text-slate-800 dark:text-slate-100 font-black text-xs focus:outline-none cursor-pointer"
          />
        </div>

        <!-- Quick Filter Preset Buttons -->
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-[11px] font-bold text-slate-600 dark:text-slate-300">
          <button 
            @click="setToday" 
            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
            :class="selectedDate === todayDateStr ? 'bg-emerald-600 text-white font-black shadow-2xs' : 'hover:bg-slate-200 dark:bg-slate-700'"
          >
            Leo
          </button>
          <button 
            @click="setYesterday" 
            class="px-2.5 py-1 rounded-lg transition cursor-pointer"
            :class="selectedDate === yesterdayDateStr ? 'bg-emerald-600 text-white font-black shadow-2xs' : 'hover:bg-slate-200 dark:bg-slate-700'"
          >
            Jana
          </button>
        </div>

        <!-- Action Button 1: Sajili Matumizi (Clean Neutral Style) -->
        <button 
          @click="openExpenseModal" 
          class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
        >
          <span>💸</span>
          <span>Sajili Matumizi</span>
        </button>

        <!-- Action Button 2: Report (PDF) -->
        <button 
          @click="openCashbookPDF" 
          class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
        >
          <span>📄</span>
          <span>Report (PDF)</span>
        </button>
      </div>
    </div>

    <!-- 6 UNIFORM FINANCIAL KPI METRIC CARDS (All Consistent White & Distinct Net Profit Card) -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3.5">
      <!-- Card 1: Chanzo cha Mfuko wa Leo (Opening Cash Balance) -->
      <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs relative overflow-hidden group hover:border-slate-300 dark:border-slate-600 transition-all">
        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Chanzo cha Mfuko (Opening)</div>
        <div class="text-base md:text-lg font-black text-slate-900 dark:text-slate-50 mt-1 font-mono">
          Tsh {{ openingCashBalance.toLocaleString() }}
        </div>
        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 font-semibold flex items-center gap-1">
          <span>🔄 Salio lililotoka Jana</span>
        </div>
      </div>

      <!-- Card 2: Jumla ya Maingizo ya Mauzo (Gross Inflows) -->
      <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs relative overflow-hidden group hover:border-emerald-300 dark:border-emerald-500/30 transition-all">
        <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-500">Maingizo ya Mauzo {{ isSelectedToday ? '(Leo)' : '' }}</div>
        <div class="text-base md:text-lg font-black text-emerald-700 dark:text-emerald-400 mt-1 font-mono">
          Tsh {{ todayInflows.toLocaleString() }}
        </div>
        <div class="text-[10px] text-emerald-600 dark:text-emerald-500 mt-1 font-semibold">
          Hela za Mauzo kutoka Wanunuzi
        </div>
      </div>

      <!-- Card 3: Malipo ya Wakulima (Farmer Payouts) -->
      <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs relative overflow-hidden group hover:border-blue-300 transition-all">
        <div class="text-[10px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Malipo ya Wakulima</div>
        <div class="text-base md:text-lg font-black text-blue-700 dark:text-blue-400 mt-1 font-mono">
          Tsh {{ todayFarmerPayouts.toLocaleString() }}
        </div>
        <div class="text-[10px] text-blue-600 dark:text-blue-400 mt-1 font-semibold">
          Hela za Wakulima (Baada ya Makato)
        </div>
      </div>

      <!-- Card 4: Matumizi ya Ofisi / Uendeshaji (Operational Expenses Only) -->
      <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs relative overflow-hidden group hover:border-red-300 transition-all">
        <div class="text-[10px] font-black uppercase tracking-wider text-red-500">Matumizi ya Ofisi</div>
        <div class="text-base md:text-lg font-black text-red-600 dark:text-red-400 mt-1 font-mono">
          Tsh {{ todayExpenses.toLocaleString() }}
        </div>
        <div class="text-[10px] text-red-500 mt-1 font-semibold">
          Chakula, Umeme, Mafuta, Mishahara
        </div>
      </div>

      <!-- Card 5: FAIDA SAFI YA LEO (PROMINENT DEDICATED NET PROFIT CARD) -->
      <div 
        @click="openProfitBreakdownModal"
        class="bg-emerald-50/90 dark:bg-emerald-900/40 p-4 rounded-2xl border border-emerald-300 dark:border-emerald-500/30 shadow-xs relative overflow-hidden group hover:border-emerald-500 transition-all cursor-pointer"
      >
        <div class="flex items-center justify-between">
          <div class="text-[10px] font-black uppercase tracking-wider text-emerald-900 dark:text-emerald-400">Faida Safi ya Leo</div>
          <span class="text-[8.5px] bg-emerald-700 text-white font-extrabold px-1.5 py-0.5 rounded-md shadow-2xs group-hover:bg-emerald-800 transition">🔍 Mchanganuo</span>
        </div>
        <div class="text-base md:text-lg font-black font-mono mt-1" :class="todayStandaloneProfit >= 0 ? 'text-emerald-800 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
          Tsh {{ todayStandaloneProfit.toLocaleString() }}
        </div>
        <div class="text-[9.5px] text-emerald-700 dark:text-emerald-400 mt-1 font-bold">
          Ada za Huduma minus Matumizi
        </div>
      </div>

      <!-- Card 6: Salio la Kufunga Siku (CASHBOX BALANCE) -->
      <div 
        class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs relative overflow-hidden group hover:border-teal-400 transition-all"
      >
        <div class="text-[10px] font-black uppercase tracking-wider text-teal-700 dark:text-teal-400">Salio la Kufunga (Cashbox)</div>
        <div class="text-base md:text-lg font-black text-slate-900 dark:text-slate-50 mt-1 font-mono">
          Tsh {{ closingCashBalance.toLocaleString() }}
        </div>
        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 font-semibold">
          Fedha Taslimu ya Siku Mfukoni
        </div>
      </div>
    </div>

    <!-- FINANCIAL ANALYTICS & VISUAL GRAPH SECTION (STANDALONE DAILY PROFIT SCALE) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Left Chart (2 cols): 7-Day Trend Line Chart Scaled Purely to Standalone Daily Net Profit -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-3 animate-fadeIn">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-50 flex items-center gap-2">
              <span>📈 Mwenendo wa Faida Safi ya Siku (Standalone Profit Trend)</span>
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Grafu inapima faida iliyopatikana siku hiyo tu (bila ya jana). Gusa mstari kuona mchanganuo wa mapato na matumizi.</p>
          </div>
          <span class="text-[10px] bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 font-black px-2.5 py-1 rounded-lg">Standalone Daily Profit</span>
        </div>
        <div class="h-60 relative w-full cursor-pointer">
          <canvas ref="trendChartCanvas"></canvas>
        </div>
      </div>

      <!-- Right Chart (1 col): DYNAMIC TOGGLE DOUGHNUT CHART (Mapato ya Huduma vs Matumizi ya Ofisi) -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-3 flex flex-col justify-between animate-fadeIn">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-50 flex items-center gap-1.5">
              <span>📊 {{ rightChartMode === 'income' ? 'Vyanzo vya Mapato' : 'Matumizi ya Ofisi' }}</span>
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
              {{ rightChartMode === 'income' ? 'Aina za huduma zilizopata faida' : 'Gharama za uendeshaji za siku' }}
            </p>
          </div>

          <!-- Dynamic Toggle Pill Buttons (Income vs Expenses) -->
          <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-[10px] font-extrabold">
            <button 
              @click="setRightChartMode('income')" 
              class="px-2.5 py-1 rounded-lg transition cursor-pointer"
              :class="rightChartMode === 'income' ? 'bg-emerald-700 dark:bg-emerald-600 text-white font-black shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50'"
            >
              🟢 Mapato
            </button>
            <button 
              @click="setRightChartMode('expense')" 
              class="px-2.5 py-1 rounded-lg transition cursor-pointer"
              :class="rightChartMode === 'expense' ? 'bg-red-700 dark:bg-red-600 text-white font-black shadow-2xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50'"
            >
              🔴 Matumizi
            </button>
          </div>
        </div>

        <div class="h-60 relative w-full flex items-center justify-center">
          <canvas ref="breakdownChartCanvas"></canvas>
        </div>
      </div>
    </div>

    <!-- LEDGER TABS & TABLE CONTROLS -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-950/50">
        <!-- Tabs Navigation -->
        <div class="flex items-center gap-2 bg-slate-200/70 dark:bg-slate-700/70 p-1 rounded-2xl">
          <button 
            @click="activeTab = 'ledger'" 
            class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer"
            :class="activeTab === 'ledger' ? 'bg-white dark:bg-slate-900 text-emerald-900 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50'"
          >
            📋 Miamala Yote ya Siku (Combined Ledger)
          </button>
          <button 
            @click="activeTab = 'expenses'" 
            class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer"
            :class="activeTab === 'expenses' ? 'bg-white dark:bg-slate-900 text-emerald-900 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50'"
          >
            💸 Matumizi ya Ofisi (Expenses)
          </button>
          <button 
            @click="activeTab = 'intake'" 
            class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer"
            :class="activeTab === 'intake' ? 'bg-white dark:bg-slate-900 text-emerald-900 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50'"
          >
            📦 Mapokezi ya Mazao (Grain Intakes)
          </button>
        </div>

        <!-- Quick Search -->
        <div class="relative w-full md:w-72">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">🔍</span>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Tafuta miamala, gharama, maelezo..." 
            class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-emerald-500 transition"
          />
        </div>
      </div>

      <!-- TAB 1: COMBINED DAILY TRANSACTIONS LEDGER -->
      <div v-if="activeTab === 'ledger'" class="space-y-0">
        <!-- Sub-Filter Bar for Combined Ledger Table -->
        <div class="px-6 py-2.5 bg-slate-100/60 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-600 dark:text-slate-300">
            <span class="mr-1 text-slate-500 dark:text-slate-400 font-semibold">🔍 Chuja Aina ya Muamala:</span>
            <button 
              @click="ledgerTypeFilter = 'ALL'" 
              class="px-3 py-1 rounded-lg transition cursor-pointer"
              :class="ledgerTypeFilter === 'ALL' ? 'bg-slate-900 text-white font-black shadow-2xs' : 'bg-white dark:bg-slate-900 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700'"
            >
              Wote (Combined)
            </button>
            <button 
              @click="ledgerTypeFilter = 'INFLOW'" 
              class="px-3 py-1 rounded-lg transition cursor-pointer"
              :class="ledgerTypeFilter === 'INFLOW' ? 'bg-emerald-700 dark:bg-emerald-600 text-white font-black shadow-2xs' : 'bg-white dark:bg-slate-900 hover:bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20'"
            >
              🟢 Mauzo Tu
            </button>
            <button 
              @click="ledgerTypeFilter = 'FARMER_PAYOUT'" 
              class="px-3 py-1 rounded-lg transition cursor-pointer"
              :class="ledgerTypeFilter === 'FARMER_PAYOUT' ? 'bg-blue-700 text-white font-black shadow-2xs' : 'bg-white dark:bg-slate-900 hover:bg-blue-50 dark:bg-blue-500/10 text-blue-800 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20'"
            >
              🔵 Wakulima Tu
            </button>
            <button 
              @click="ledgerTypeFilter = 'EXPENSE'" 
              class="px-3 py-1 rounded-lg transition cursor-pointer"
              :class="ledgerTypeFilter === 'EXPENSE' ? 'bg-red-700 dark:bg-red-600 text-white font-black shadow-2xs' : 'bg-white dark:bg-slate-900 hover:bg-red-50 dark:bg-red-500/10 text-red-800 dark:text-red-400 border border-red-200 dark:border-red-500/20'"
            >
              🔴 Matumizi ya Ofisi Tu
            </button>
          </div>

          <div class="text-[11px] text-slate-500 dark:text-slate-400 font-bold">
            Miamala Inayoonekana: <strong class="font-mono text-slate-900 dark:text-slate-50">{{ filteredCombinedLedger.length }}</strong>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100/70 dark:bg-slate-800/70 text-slate-700 dark:text-slate-200 font-extrabold border-b border-slate-200/80 dark:border-slate-700/80 uppercase text-[10px] tracking-wider">
                <th class="py-3 px-4">Tarehe / Muda</th>
                <th class="py-3 px-4">Aina ya Muamala</th>
                <th class="py-3 px-4">Kipengele / Ref #</th>
                <th class="py-3 px-4">Maelezo</th>
                <th class="py-3 px-4 text-right">Maingizo (Inflow)</th>
                <th class="py-3 px-4 text-right">Matumizi / Malipo</th>
                <th class="py-3 px-4 text-center">Njia ya Malipo</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700 dark:text-slate-200">
              <tr v-if="loading" class="text-center py-10">
                <td colspan="7" class="py-8 text-slate-400 font-bold">
                  <span class="animate-spin inline-block mr-2">⚙️</span> Inapakia miamala ya tarehe {{ selectedDate }}...
                </td>
              </tr>
              <tr v-else-if="filteredCombinedLedger.length === 0" class="text-center py-10">
                <td colspan="7" class="py-8 text-slate-400 font-bold">
                  Hakuna muamala wowote uliorekodiwa tarehe {{ selectedDate }}.
                </td>
              </tr>
              <tr 
                v-for="(item, idx) in filteredCombinedLedger" 
                :key="idx"
                class="hover:bg-slate-50 dark:bg-slate-950 transition-colors"
              >
                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 font-mono text-[11px] whitespace-nowrap">
                  {{ formatDate(item.date) }}
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <span 
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1"
                    :class="{
                      'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30': item.type === 'INFLOW',
                      'bg-blue-100 dark:bg-blue-500/20 text-blue-800 dark:text-blue-400 border border-blue-300': item.type === 'FARMER_PAYOUT',
                      'bg-red-100 dark:bg-red-500/20 text-red-800 dark:text-red-400 border border-red-300': item.type === 'EXPENSE'
                    }"
                  >
                    <template v-if="item.type === 'INFLOW'">🟢 MAINGIZO YA MAUZO</template>
                    <template v-else-if="item.type === 'FARMER_PAYOUT'">🔵 MALIPO YA MKULIMA</template>
                    <template v-else>🔴 MATUMIZI YA OFISI</template>
                  </span>
                </td>
                <td class="py-3 px-4">
                  <div class="font-extrabold text-slate-900 dark:text-slate-50">{{ item.category }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">{{ item.reference || 'N/A' }}</div>
                </td>
                <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-medium">
                  {{ item.description }}
                </td>
                <td class="py-3 px-4 text-right font-black font-mono text-emerald-700 dark:text-emerald-400">
                  {{ item.inflow > 0 ? ('Tsh ' + item.inflow.toLocaleString()) : '-' }}
                </td>
                <td class="py-3 px-4 text-right font-black font-mono" :class="item.type === 'FARMER_PAYOUT' ? 'text-blue-700 dark:text-blue-400' : 'text-red-600 dark:text-red-400'">
                  {{ item.outflow > 0 ? ('Tsh ' + item.outflow.toLocaleString()) : '-' }}
                </td>
                <td class="py-3 px-4 text-center text-[11px] font-bold text-slate-600 dark:text-slate-300">
                  {{ item.payment_method || 'Cash' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 2: OPERATIONAL EXPENSES LEDGER -->
      <div v-if="activeTab === 'expenses'" class="space-y-4 p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-50">Orodha ya Matumizi ya Ofisi Tarehe {{ selectedDate }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Chakula cha wafanyakazi, umeme, mafuta ya generator, mishahara ya vibarua na matengenezo.</p>
          </div>
          <button @click="openExpenseModal" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
            <span>+ Sajili Matumizi</span>
          </button>
        </div>

        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-2xs">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100/70 dark:bg-slate-800/70 text-slate-700 dark:text-slate-200 font-extrabold border-b uppercase text-[10px]">
                <th class="py-3 px-4">Kipengele (Category)</th>
                <th class="py-3 px-4">Maelezo (Description)</th>
                <th class="py-3 px-4 text-right">Kiasi (Amount)</th>
                <th class="py-3 px-4 text-center">Tarehe</th>
                <th class="py-3 px-4 text-center">Vitendo</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-semibold text-slate-700 dark:text-slate-200">
              <tr v-if="filteredExpenses.length === 0" class="text-center text-slate-400">
                <td colspan="5" class="py-8">Hakuna matumizi ya ofisi yaliyorekodiwa tarehe {{ selectedDate }}.</td>
              </tr>
              <tr v-for="exp in filteredExpenses" :key="exp.id" class="hover:bg-slate-50 dark:bg-slate-950 transition">
                <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                  <span class="text-base">{{ getCategoryEmoji(exp.category_name) }}</span>
                  <span>{{ exp.category_name }}</span>
                </td>
                <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ exp.description || 'Matumizi ya kawaida' }}</td>
                <td class="py-3 px-4 text-right font-black text-red-600 dark:text-red-400 font-mono">Tsh {{ parseFloat(exp.amount || 0).toLocaleString() }}</td>
                <td class="py-3 px-4 text-center text-slate-500 dark:text-slate-400 font-mono">{{ formatDate(exp.date_incurred) }}</td>
                <td class="py-3 px-4 text-center">
                  <button @click="deleteExpense(exp.id)" class="text-red-500 hover:text-red-700 dark:text-red-400 font-bold p-1 cursor-pointer">🗑️ Futakazi</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 3: GRAIN INBOUND DELIVERIES -->
      <div v-if="activeTab === 'intake'" class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-100/70 dark:bg-slate-800/70 text-slate-700 dark:text-slate-200 font-extrabold border-b border-slate-200/80 dark:border-slate-700/80 uppercase text-[10px] tracking-wider">
              <th class="py-3.5 px-4">#</th>
              <th class="py-3.5 px-4">Batch Code</th>
              <th class="py-3.5 px-4">Mkulima</th>
              <th class="py-3.5 px-4">Zao</th>
              <th class="py-3.5 px-4 text-right">Uzito (Kg)</th>
              <th class="py-3.5 px-4 text-center">Unyevu (%)</th>
              <th class="py-3.5 px-4 text-center">Silo / Bin</th>
              <th class="py-3.5 px-4 text-center">Tarehe</th>
              <th class="py-3.5 px-4 text-center">Vitendo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-semibold text-slate-700 dark:text-slate-200">
            <tr v-if="filteredBatches.length === 0" class="text-center py-10">
              <td colspan="9" class="py-8 text-slate-400 font-bold">Hakuna mzigo uliopokelewa tarehe {{ selectedDate }}.</td>
            </tr>
            <tr v-for="(b, idx) in filteredBatches" :key="b.id" class="hover:bg-slate-50 dark:bg-slate-950">
              <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">{{ idx + 1 }}</td>
              <td class="py-3 px-4 font-mono font-black text-emerald-800 dark:text-emerald-400">{{ b.batch_code }}</td>
              <td class="py-3 px-4 font-extrabold text-slate-900 dark:text-slate-50">{{ b.farmer_name }}</td>
              <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-100">{{ b.crop_type }}</td>
              <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-slate-50 font-mono">{{ ((b.initial_weight || b.current_weight || 0) * 1000).toLocaleString() }} Kg</td>
              <td class="py-3 px-4 text-center font-bold" :class="parseFloat(b.moisture_percentage||0) <= 13 ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-600'">{{ b.moisture_percentage || '12.5' }}%</td>
              <td class="py-3 px-4 text-center font-extrabold text-slate-700 dark:text-slate-200">{{ b.storage_location || 'Silo A1' }}</td>
              <td class="py-3 px-4 text-center text-slate-500 dark:text-slate-400 font-mono">{{ formatDate(b.created_at) }}</td>
              <td class="py-3 px-4 text-center">
                <button @click="printGRN(b)" class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 rounded-lg font-extrabold hover:bg-emerald-200 transition cursor-pointer">🖨️ GRN PDF</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL 1: SAJILI MATUMIZI YA OFISI -->
    <div v-if="modals.expense" class="fixed inset-0 z-[90] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-900 text-white">
          <div class="flex items-center gap-2">
            <span class="text-lg">💸</span>
            <h3 class="text-base font-extrabold">Sajili Matumizi ya Ofisi / Uendeshaji</h3>
          </div>
          <button @click="modals.expense = false" class="text-slate-300 hover:text-white p-1 cursor-pointer">✕</button>
        </div>

        <div class="p-6 space-y-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <div>
            <label class="block mb-1 font-bold">Kipengele cha Matumizi (Category) *</label>
            <select v-model="expenseForm.category_name" class="w-full p-2.5 border rounded-xl font-bold bg-slate-50 dark:bg-slate-950 focus:bg-white dark:bg-slate-900">
              <option value="Chakula cha Wafanyakazi">🍲 Chakula cha Wafanyakazi / Vibarua</option>
              <option value="Umeme wa Mashine">⚡ Umeme wa Mashine / Kinu</option>
              <option value="Mafuta ya Generator">⛽ Mafuta ya Generator / Usafiri</option>
              <option value="Mishahara ya Vibarua">👷 Mishahara ya Vibarua (Casual Wages)</option>
              <option value="Ukarabati na Maintenance">🛠️ Ukarabati na Maintenance</option>
              <option value="Matumizi Mengineyo">📝 Matumizi Mengineyo (Office Expenses)</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 font-bold">Kiasi cha Matumizi (TZS) *</label>
            <input v-model.number="expenseForm.amount" type="number" placeholder="e.g. 35000" class="w-full p-2.5 border rounded-xl font-bold text-sm text-slate-900 dark:text-slate-50 font-mono"/>
          </div>

          <div>
            <label class="block mb-1 font-bold">Tarehe ya Matumizi *</label>
            <input v-model="expenseForm.date_incurred" type="date" class="w-full p-2.5 border rounded-xl font-bold bg-slate-50 dark:bg-slate-950"/>
          </div>

          <div>
            <label class="block mb-1 font-bold">Maelezo (Notes / Receipt Ref) *</label>
            <textarea v-model="expenseForm.description" rows="2" placeholder="e.g. Chakula cha mchana vibarua 12 waliopakia mzigo..." class="w-full p-2.5 border rounded-xl font-medium"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button @click="modals.expense = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl cursor-pointer">Ghairi</button>
            <button @click="submitExpense" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-black rounded-xl shadow-xs cursor-pointer">Hifadhi Matumizi</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: 100% DYNAMIC MCHANGANUO WA FAIDA YA SIKU HUSIKA (DYNAMIC PROFIT AUDIT MODAL) -->
    <div v-if="modals.profitBreakdown" class="fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-teal-800 flex items-center justify-between bg-gradient-to-r from-teal-900 to-emerald-950 text-white">
          <div class="flex items-center gap-2">
            <span class="text-xl">📊</span>
            <div>
              <h3 class="text-sm font-extrabold">Mchanganuo wa Faida ya Siku Husika (Dynamic Daily Audit)</h3>
              <p class="text-[10px] text-teal-200">Tarehe: {{ selectedDate }} (Bila kujumlisha salio la jana)</p>
            </div>
          </div>
          <button @click="modals.profitBreakdown = false" class="text-slate-300 hover:text-white p-1 cursor-pointer">✕</button>
        </div>

        <div class="p-6 space-y-5 text-xs text-slate-800 dark:text-slate-100 font-sans max-h-[80vh] overflow-y-auto">
          <!-- Step 1: DYNAMIC Real Revenue (Services & Interest Earned Today) -->
          <div class="bg-teal-50 dark:bg-teal-900/40/70 dark:bg-teal-900/40 border border-teal-200 dark:border-teal-700/50 p-4 rounded-2xl space-y-2">
            <div class="font-black text-teal-900 dark:text-teal-400 uppercase tracking-wider text-[11px] flex items-center justify-between">
              <span>1. Ada za Huduma & Riba za Mikopo (Mapato ya Faida)</span>
              <span class="font-mono text-xs text-teal-800 dark:text-teal-400 font-black">+ Tsh {{ todaySystemFeeIncome.toLocaleString() }}</span>
            </div>

            <!-- Dynamic Services & Deductions Breakdown -->
            <div class="space-y-1.5 text-slate-600 dark:text-slate-300 font-semibold border-t border-teal-200 dark:border-teal-700/50/80 dark:border-teal-700/50 pt-2">
              <div v-if="todayDynamicDeductions.length === 0" class="text-slate-400 italic py-1">
                Hakuna makato au ada ya huduma iliyorekodiwa siku hii.
              </div>
              <div 
                v-for="(item, idx) in todayDynamicDeductions" 
                :key="idx" 
                class="flex justify-between items-center py-1 border-b border-teal-100/60 last:border-0"
              >
                <span class="flex items-center gap-1.5">
                  <span class="text-teal-600 dark:text-teal-400 font-bold text-sm">•</span>
                  <span class="text-slate-800 dark:text-slate-100 font-bold">{{ item.label }}:</span>
                  <span v-if="item.type === 'loan_principal'" class="text-[9.5px] bg-amber-100 dark:bg-amber-500/20 text-amber-900 dark:text-amber-400 px-1.5 py-0.5 rounded font-extrabold uppercase">Rejesho la Cashbox (Sio Faida)</span>
                </span>
                <span class="font-mono font-black" :class="item.type === 'loan_principal' ? 'text-amber-800 dark:text-amber-400' : 'text-teal-950 dark:text-teal-400'">Tsh {{ item.amount.toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- Step 2: DYNAMIC Operational Expenses Incurred Today (Matumizi ya Ofisi Peakee) -->
          <div class="bg-red-50/70 dark:bg-red-900/40 border border-red-200 dark:border-red-500/20 p-4 rounded-2xl space-y-2">
            <div class="font-black text-red-900 dark:text-red-400 uppercase tracking-wider text-[11px] flex items-center justify-between">
              <span>2. Matumizi ya Ofisi Peakee (Office Operational Expenses)</span>
              <span class="font-mono text-xs text-red-700 dark:text-red-400 font-black">- Tsh {{ todayExpenses.toLocaleString() }}</span>
            </div>

            <!-- Dynamic Expenses Breakdown -->
            <div class="space-y-1.5 text-slate-600 dark:text-slate-300 font-semibold border-t border-red-200/80 dark:border-red-700/50 pt-2">
              <div v-if="filteredExpenses.length === 0" class="text-slate-400 italic py-1">
                Hakuna matumizi ya ofisi yaliyorekodiwa siku hii.
              </div>
              <div 
                v-for="exp in filteredExpenses" 
                :key="exp.id" 
                class="flex justify-between items-center py-1 border-b border-red-100/60 last:border-0"
              >
                <span class="flex items-center gap-1.5">
                  <span class="text-red-500 font-bold">•</span>
                  <span>{{ getCategoryEmoji(exp.category_name) }} <strong class="text-slate-900 dark:text-slate-50">{{ exp.category_name }}</strong> <span class="text-slate-500 dark:text-slate-400">({{ exp.description || 'Matumizi' }})</span>:</span>
                </span>
                <span class="font-mono font-black text-red-600 dark:text-red-400">Tsh {{ parseFloat(exp.amount||0).toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- Step 3: Standalone Daily Net Operating Profit -->
          <div class="p-4 rounded-2xl border-2 flex items-center justify-between" :class="todayStandaloneProfit >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/40 border-emerald-400 dark:border-emerald-500/50 text-emerald-950 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-900/40 border-red-400 dark:border-red-500/50 text-red-950 dark:text-red-400'">
            <div>
              <div class="text-[11px] font-black uppercase tracking-wider">3. FAIDA SAFI YA SIKU HIYO TU (Standalone Net Profit)</div>
              <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Ada za Huduma + Riba minus Matumizi ya Ofisi (bila Mkopo Mama).</p>
            </div>
            <div class="text-xl font-black font-mono" :class="todayStandaloneProfit >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
              Tsh {{ todayStandaloneProfit.toLocaleString() }}
            </div>
          </div>

          <!-- Step 4: Full Cash Movement Audit -->
          <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 p-4 rounded-2xl space-y-2 text-[11px] font-semibold text-slate-700 dark:text-slate-200">
            <div class="font-black text-slate-900 dark:text-slate-50 uppercase">4. Mzunguko wa Mfuko wa Dratiba (Office Cashbox Audit):</div>
            <div class="flex justify-between">
              <span>Salio la Mwanzo wa Siku (Chanzo cha Jana):</span>
              <span class="font-mono font-bold">Tsh {{ openingCashBalance.toLocaleString() }}</span>
            </div>
            <div class="flex justify-between text-emerald-700 dark:text-emerald-400">
              <span>(+) Ada za Huduma & Riba (Faida ya Leo):</span>
              <span class="font-mono font-bold">+ Tsh {{ todaySystemFeeIncome.toLocaleString() }}</span>
            </div>
            <div v-if="todayLoanPrincipalRecovery > 0" class="flex justify-between text-amber-800 dark:text-amber-400 font-bold">
              <span>(+) Rejesho la Mtaji wa Mkopo (Hela ya Ofisi Iliyorudi):</span>
              <span class="font-mono font-bold">+ Tsh {{ todayLoanPrincipalRecovery.toLocaleString() }}</span>
            </div>
            <div class="flex justify-between text-red-600 dark:text-red-400">
              <span>(-) Matumizi ya Ofisi (Chakula, Umeme, Mafuta):</span>
              <span class="font-mono font-bold">- Tsh {{ todayExpenses.toLocaleString() }}</span>
            </div>
            <div class="flex justify-between text-slate-900 dark:text-slate-50 font-black border-t border-slate-300 dark:border-slate-600 pt-1.5 text-xs">
              <span>(=) SALIO LA KUFUNGA SIKU (Office Cashbox):</span>
              <span class="font-mono font-black text-teal-800 dark:text-teal-400">Tsh {{ closingCashBalance.toLocaleString() }}</span>
            </div>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 italic pt-1 border-t border-slate-200 dark:border-slate-700">
              💡 Mahesabu ya Calculator: Tsh {{ openingCashBalance.toLocaleString() }} (Jana) + Tsh {{ todaySystemFeeIncome.toLocaleString() }} (Faida) + Tsh {{ todayLoanPrincipalRecovery.toLocaleString() }} (Mkopo Uliorudi) - Tsh {{ todayExpenses.toLocaleString() }} (Matumizi) = Tsh {{ closingCashBalance.toLocaleString() }}.
            </p>
          </div>
        </div>

        <div class="px-6 py-3 bg-slate-100 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 flex justify-end">
          <button @click="modals.profitBreakdown = false" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl cursor-pointer">
            Funga (Close)
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL 3: STUNNING ENTERPRISE-GRADE DAILY CASHBOOK PDF STATEMENT -->
    <div v-if="modals.cashbookPDF" class="fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-950 text-white">
          <div class="flex items-center gap-2">
            <span class="text-xl">📄</span>
            <div>
              <h3 class="text-sm font-extrabold">Taarifa ya Mfuko & Miamala (Official Daily Cashbook Statement)</h3>
              <p class="text-[10px] text-slate-300">Data Halisi za Tarehe: {{ selectedDate }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button @click="triggerPrint" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-xs">
              <span>📄 Print / Save PDF</span>
            </button>
            <button @click="modals.cashbookPDF = false" class="text-slate-400 hover:text-white p-1 cursor-pointer">✕</button>
          </div>
        </div>

        <div id="printableArea" class="p-8 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-50 font-sans space-y-6 max-h-[80vh] overflow-y-auto print:p-0 print:max-h-none">
          <!-- Corporate Letterhead Header -->
          <div class="flex items-start justify-between border-b-2 border-slate-900 pb-5">
            <div class="flex items-center gap-3.5">
              <div class="w-12 h-12 rounded-2xl bg-emerald-800 text-white flex items-center justify-center font-black text-2xl shadow-xs">
                🌾
              </div>
              <div>
                <div class="text-xl font-black tracking-tight text-slate-900 dark:text-slate-50 uppercase">GALANOKI MILLING & WAREHOUSE</div>
                <div class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mt-0.5">
                  Industrial Complex Block A & B, Arusha, Tanzania<br/>
                  Simu: +255 700 000 100 | Barua pepe: info@galanoki.co.tz
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-base font-black text-emerald-900 dark:text-emerald-400 tracking-tight uppercase">DAILY CASHBOOK STATEMENT</div>
              <div class="text-[11px] text-slate-600 dark:text-slate-300 font-mono mt-1 space-y-0.5">
                <div><strong>Ref #:</strong> STMT-{{ selectedDate.replace(/-/g, '') }}</div>
                <div><strong>Tarehe ya Ripoti:</strong> {{ formatDate(selectedDate) }}</div>
                <div><strong>Muda wa Print:</strong> {{ new Date().toLocaleTimeString('sw-TZ') }}</div>
              </div>
            </div>
          </div>

          <!-- 5 Financial Metric Cards Grid -->
          <div class="grid grid-cols-5 gap-2 p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-bold">
            <div class="bg-white dark:bg-slate-900 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700">
              <div class="text-[9px] text-slate-500 dark:text-slate-400 uppercase tracking-wider font-extrabold">1. Opening Float</div>
              <div class="text-xs font-black text-slate-900 dark:text-slate-50 font-mono mt-0.5">Tsh {{ openingCashBalance.toLocaleString() }}</div>
              <div class="text-[8px] text-slate-400 mt-0.5">Salio la Jana</div>
            </div>
            <div class="bg-emerald-50/70 dark:bg-emerald-900/40 p-2.5 rounded-xl border border-emerald-200 dark:border-emerald-500/20">
              <div class="text-[9px] text-emerald-700 dark:text-emerald-400 uppercase tracking-wider font-extrabold">2. Gross Inflows</div>
              <div class="text-xs font-black text-emerald-800 dark:text-emerald-400 font-mono mt-0.5">Tsh {{ todayInflows.toLocaleString() }}</div>
              <div class="text-[8px] text-emerald-600 dark:text-emerald-500 mt-0.5">Mauzo ya Wanunuzi</div>
            </div>
            <div class="bg-blue-50/70 dark:bg-blue-900/40 p-2.5 rounded-xl border border-blue-200 dark:border-blue-500/20">
              <div class="text-[9px] text-blue-700 dark:text-blue-400 uppercase tracking-wider font-extrabold">3. Farmer Payouts</div>
              <div class="text-xs font-black text-blue-800 dark:text-blue-400 font-mono mt-0.5">Tsh {{ todayFarmerPayouts.toLocaleString() }}</div>
              <div class="text-[8px] text-blue-600 dark:text-blue-400 mt-0.5">Malipo Wakulima</div>
            </div>
            <div class="bg-red-50/70 dark:bg-red-900/40 p-2.5 rounded-xl border border-red-200 dark:border-red-500/20">
              <div class="text-[9px] text-red-700 dark:text-red-400 uppercase tracking-wider font-extrabold">4. Office Expenses</div>
              <div class="text-xs font-black text-red-800 dark:text-red-400 font-mono mt-0.5">Tsh {{ todayExpenses.toLocaleString() }}</div>
              <div class="text-[8px] text-red-600 dark:text-red-400 mt-0.5">Matumizi Ofisi</div>
            </div>
            <div class="bg-teal-900 text-white p-2.5 rounded-xl border border-teal-950">
              <div class="text-[9px] text-teal-200 uppercase tracking-wider font-extrabold">5. Closing Balance</div>
              <div class="text-xs font-black text-white font-mono mt-0.5">Tsh {{ closingCashBalance.toLocaleString() }}</div>
              <div class="text-[8px] text-teal-200 mt-0.5">Salio la Mfukoni</div>
            </div>
          </div>

          <!-- Standalone Profit Banner -->
          <div class="p-3 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-teal-900/40 dark:to-emerald-900/40 border border-teal-200 dark:border-teal-700/50 rounded-2xl text-xs flex justify-between items-center font-bold text-teal-950 dark:text-teal-400 dark:text-teal-400">
            <div class="flex items-center gap-2">
              <span class="text-base">💡</span>
              <div>
                <span>Faida Safi ya Huduma & Mikopo (Standalone Operating Profit):</span>
                <p class="text-[10px] text-teal-700 dark:text-teal-400 font-medium">Ada za huduma zilizokusanywa minus Matumizi ya Ofisi tarehe {{ formatDate(selectedDate) }}.</p>
              </div>
            </div>
            <span class="font-mono text-sm font-black text-emerald-900 dark:text-emerald-400 bg-white dark:bg-slate-900 px-3 py-1 rounded-xl border border-emerald-300 dark:border-emerald-500/30">
              Tsh {{ todayStandaloneProfit.toLocaleString() }}
            </span>
          </div>

          <!-- Table 1: Dynamic Service Incomes Breakdown -->
          <div class="space-y-2">
            <div class="text-xs font-black text-slate-900 dark:text-slate-50 uppercase tracking-wider flex items-center justify-between border-b border-slate-300 dark:border-slate-600 pb-1">
              <span>1. Mapato ya Huduma & Rejesho la Mikopo (Service Income Collected):</span>
              <span class="font-mono text-emerald-800 dark:text-emerald-400 font-extrabold">Total: Tsh {{ todaySystemFeeIncome.toLocaleString() }}</span>
            </div>
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-100 dark:bg-slate-800 font-extrabold text-slate-800 dark:text-slate-100 text-[10px] uppercase">
                  <th class="py-2 px-3 border border-slate-200 dark:border-slate-700">#</th>
                  <th class="py-2 px-3 border border-slate-200 dark:border-slate-700">Aina ya Huduma / Makato</th>
                  <th class="py-2 px-3 border border-slate-200 dark:border-slate-700 text-right">Kiasi Kilichokusanywa (TZS)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 font-semibold text-slate-700 dark:text-slate-200">
                <tr v-if="todayDynamicDeductions.length === 0">
                  <td colspan="3" class="py-3 px-3 text-slate-400 italic text-center">Hakuna ada za huduma zilizokusanywa tarehe hii.</td>
                </tr>
                <tr v-for="(item, idx) in todayDynamicDeductions" :key="idx" class="hover:bg-slate-50 dark:bg-slate-950">
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 font-mono text-[11px] text-slate-400">{{ idx + 1 }}</td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 font-bold text-slate-900 dark:text-slate-50">• {{ item.label }}</td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 text-right font-black text-emerald-800 dark:text-emerald-400 font-mono">Tsh {{ item.amount.toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Table 2: Office Expenses Breakdown -->
          <div class="space-y-2">
            <div class="text-xs font-black text-slate-900 dark:text-slate-50 uppercase tracking-wider flex items-center justify-between border-b border-slate-300 dark:border-slate-600 pb-1">
              <span>2. Matumizi ya Ofisi (Office Operational Expenses):</span>
              <span class="font-mono text-red-700 dark:text-red-400 font-extrabold">Total: Tsh {{ todayExpenses.toLocaleString() }}</span>
            </div>
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-100 dark:bg-slate-800 font-extrabold text-slate-800 dark:text-slate-100 text-[10px] uppercase">
                  <th class="py-2 px-3 border border-slate-200 dark:border-slate-700">Kipengele</th>
                  <th class="py-2 px-3 border border-slate-200 dark:border-slate-700">Maelezo</th>
                  <th class="py-2 px-3 border border-slate-200 dark:border-slate-700 text-right">Kiasi (TZS)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 font-semibold text-slate-700 dark:text-slate-200">
                <tr v-if="filteredExpenses.length === 0">
                  <td colspan="3" class="py-3 px-3 text-slate-400 italic text-center">Hakuna matumizi ya ofisi yaliyorekodiwa tarehe hii.</td>
                </tr>
                <tr v-for="exp in filteredExpenses" :key="exp.id" class="hover:bg-slate-50 dark:bg-slate-950">
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 font-bold text-slate-900 dark:text-slate-50">{{ exp.category_name }}</td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">{{ exp.description || '-' }}</td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 text-right font-black text-red-600 dark:text-red-400 font-mono">Tsh {{ parseFloat(exp.amount || 0).toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Table 3: Complete Combined Transaction Ledger -->
          <div class="space-y-2">
            <div class="text-xs font-black text-slate-900 dark:text-slate-50 uppercase tracking-wider border-b border-slate-300 dark:border-slate-600 pb-1">
              3. Orodha ya Miamala Yote ya Siku (Combined Transaction Ledger):
            </div>
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-900 text-white font-extrabold text-[10px] uppercase">
                  <th class="py-2 px-3 border border-slate-800">Muda</th>
                  <th class="py-2 px-3 border border-slate-800">Aina ya Muamala</th>
                  <th class="py-2 px-3 border border-slate-800">Ref #</th>
                  <th class="py-2 px-3 border border-slate-800">Maelezo</th>
                  <th class="py-2 px-3 border border-slate-800 text-right">Inflow</th>
                  <th class="py-2 px-3 border border-slate-800 text-right">Outflow</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 font-semibold text-slate-700 dark:text-slate-200">
                <tr v-if="combinedLedger.length === 0">
                  <td colspan="6" class="py-3 px-3 text-slate-400 italic text-center">Hakuna miamala yoyote iliyorekodiwa tarehe {{ selectedDate }}.</td>
                </tr>
                <tr v-for="(item, idx) in combinedLedger" :key="idx" class="hover:bg-slate-50 dark:bg-slate-950">
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-500 dark:text-slate-400">{{ formatDate(item.date) }}</td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 font-bold">
                    <span v-if="item.type === 'INFLOW'" class="text-emerald-800 dark:text-emerald-400 font-black">Mauzo ya Zao</span>
                    <span v-else-if="item.type === 'FARMER_PAYOUT'" class="text-blue-800 dark:text-blue-400 font-black">Malipo Mkulima</span>
                    <span v-else class="text-red-700 dark:text-red-400 font-black">Matumizi Ofisi</span>
                  </td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 font-mono text-[10px] text-slate-600 dark:text-slate-300">{{ item.reference }}</td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-[11px]">{{ item.description }}</td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 text-right font-black font-mono text-emerald-800 dark:text-emerald-400">
                    {{ item.inflow > 0 ? ('Tsh ' + item.inflow.toLocaleString()) : '-' }}
                  </td>
                  <td class="py-2 px-3 border border-slate-200 dark:border-slate-700 text-right font-black font-mono" :class="item.type === 'FARMER_PAYOUT' ? 'text-blue-800 dark:text-blue-400' : 'text-red-700 dark:text-red-400'">
                    {{ item.outflow > 0 ? ('Tsh ' + item.outflow.toLocaleString()) : '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Official Authorization Signatures -->
          <div class="pt-8 border-t-2 border-slate-300 dark:border-slate-600 grid grid-cols-2 gap-12 text-xs text-slate-700 dark:text-slate-200">
            <div>
              <div class="font-black text-slate-900 dark:text-slate-50 mb-8 uppercase text-[11px]">Sahihi ya Mweka Hazina (Cashier):</div>
              <div class="border-b border-slate-400 w-3/4 mb-1"></div>
              <div class="text-slate-500 dark:text-slate-400 font-semibold">Mweka Hazina (Galonoki MS)</div>
            </div>

            <div>
              <div class="font-black text-slate-900 dark:text-slate-50 mb-8 uppercase text-[11px]">Sahihi ya Meneja wa Ghala (Warehouse Manager):</div>
              <div class="border-b border-slate-400 w-3/4 mb-1"></div>
              <div class="text-slate-500 dark:text-slate-400 font-semibold">Warehouse Manager Approval</div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import Chart from 'chart.js/auto';

// Today and Yesterday Date Strings
const todayDateStr = new Date().toISOString().split('T')[0];
const yesterdayDate = new Date();
yesterdayDate.setDate(yesterdayDate.getDate() - 1);
const yesterdayDateStr = yesterdayDate.toISOString().split('T')[0];

// Reactive State
const selectedDate = ref(todayDateStr);
const activeTab = ref('ledger');
const searchQuery = ref('');
const ledgerTypeFilter = ref('ALL');
const loading = ref(false);
const rightChartMode = ref('income'); // 'income' or 'expense'

const trendChartCanvas = ref(null);
const breakdownChartCanvas = ref(null);
let trendChartInstance = null;
let breakdownChartInstance = null;

const settlements = ref([]);
const expenses = ref([]);
const batches = ref([]);
const farmers = ref([]);
const services = ref([]);

const modals = ref({
  expense: false,
  profitBreakdown: false,
  cashbookPDF: false
});

const expenseForm = ref({
  category_name: 'Chakula cha Wafanyakazi',
  amount: 0,
  date_incurred: selectedDate.value,
  description: ''
});

// Toggle Right Chart Mode (Income vs Expense)
const setRightChartMode = (mode) => {
  rightChartMode.value = mode;
  renderCharts();
};

// Quick Date Helpers
const isSelectedToday = computed(() => selectedDate.value === todayDateStr);

const setToday = () => {
  selectedDate.value = todayDateStr;
  fetchData();
};

const setYesterday = () => {
  selectedDate.value = yesterdayDateStr;
  fetchData();
};

// Date Formatter
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('sw-TZ', { day: '2-digit', month: 'short', year: 'numeric' });
  } catch (e) {
    return dateStr;
  }
};

const getDateStr = (dt) => {
  if (!dt) return '';
  return dt.split('T')[0].split(' ')[0];
};

// Helper: Calculate exact Net Revenue / Profit (Services + Interest only) for a settlement record
const getSettlementFeeIncome = (s) => {
  if (s.deductions && Array.isArray(s.deductions) && s.deductions.length > 0) {
    return s.deductions.reduce((sum, d) => {
      const type = d.deduction_type || '';
      if (type === 'loan_principal') return sum; // Exclude Loan Principal from Net Profit!
      return sum + parseFloat(d.amount || 0);
    }, 0);
  }
  const totalDed = parseFloat(s.total_deductions || s.deductions_amount || 0);
  const loanPrincipal = parseFloat(s.loan_principal_amount || s.loan_principal || 0);
  return Math.max(0, totalDed - loanPrincipal);
};

// Helper: Calculate Loan Principal Recovery (Asset Cash recovery, NOT profit)
const getSettlementLoanPrincipalRecovery = (s) => {
  if (s.deductions && Array.isArray(s.deductions) && s.deductions.length > 0) {
    return s.deductions.reduce((sum, d) => {
      const type = d.deduction_type || '';
      if (type === 'loan_principal') return sum + parseFloat(d.amount || 0);
      return sum;
    }, 0);
  }
  return parseFloat(s.loan_principal_amount || s.loan_principal || 0);
};

// Helper: Calculate Total Cash Retained from Settlement into Cashbox (Service Fee + Interest + Principal Recovery)
const getSettlementTotalCashRetained = (s) => {
  if (s.deductions && Array.isArray(s.deductions) && s.deductions.length > 0) {
    return s.deductions.reduce((sum, d) => sum + parseFloat(d.amount || 0), 0);
  }
  return parseFloat(s.total_deductions || s.deductions_amount || 0);
};

// Fetch Real Data from Backend APIs
const fetchData = async () => {
  loading.value = true;
  try {
    const [bRes, eRes, sRes, fRes, srvRes] = await Promise.all([
      fetch('/api/v1/batches'),
      fetch('/api/v1/expenses'),
      fetch('/api/v1/sales/settlements').catch(() => ({ ok: false })),
      fetch('/api/v1/farmers'),
      fetch('/api/v1/services').catch(() => ({ ok: false }))
    ]);

    if (bRes.ok) batches.value = await bRes.json();
    if (eRes.ok) expenses.value = await eRes.json();
    if (sRes.ok && typeof sRes.json === 'function') settlements.value = await sRes.json();
    if (fRes.ok) farmers.value = await fRes.json();
    if (srvRes.ok && typeof srvRes.json === 'function') services.value = await srvRes.json();

    renderCharts();
  } catch (err) {
    console.error('Error loading real daily cashbook data:', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});

watch(selectedDate, () => {
  renderCharts();
});

// Category Emojis Helper
const getCategoryEmoji = (cat) => {
  if (!cat) return '📝';
  if (cat.includes('Chakula')) return '🍲';
  if (cat.includes('Umeme')) return '⚡';
  if (cat.includes('Mafuta')) return '⛽';
  if (cat.includes('Vibarua')) return '👷';
  if (cat.includes('Ukarabati')) return '🛠️';
  return '📝';
};

// REAL FINANCIAL ENGINE (SINGLE DAY AUDIT & CARRY OVER)

// 1. Opening Cash Balance (Salio la Mwanzo wa Siku)
const openingCashBalance = computed(() => {
  let netCash = 0;

  settlements.value.forEach(s => {
    const dateStr = getDateStr(s.settled_at || s.created_at);
    if (dateStr && dateStr < selectedDate.value) {
      netCash += getSettlementTotalCashRetained(s);
    }
  });

  expenses.value.forEach(exp => {
    const dateStr = getDateStr(exp.date_incurred);
    if (dateStr && dateStr < selectedDate.value) {
      netCash -= parseFloat(exp.amount || 0);
    }
  });

  return Math.max(0, netCash);
});

// 2. Today's Gross Crop Sales Inflows
const todayInflows = computed(() => {
  let total = 0;
  settlements.value.forEach(s => {
    const dateStr = getDateStr(s.settled_at || s.created_at);
    if (dateStr === selectedDate.value) {
      total += parseFloat(s.gross_amount || 0);
    }
  });
  return total;
});

// 3. Today's Operational Office Expenses Only (Chakula, Umeme, Mafuta, Mishahara)
const todayExpenses = computed(() => {
  return expenses.value
    .filter(exp => getDateStr(exp.date_incurred) === selectedDate.value)
    .reduce((sum, exp) => sum + parseFloat(exp.amount || 0), 0);
});

// 4. Today's Farmer Payouts (Hela Zilizolipwa kwa Wakulima)
const todayFarmerPayouts = computed(() => {
  let total = 0;
  settlements.value.forEach(s => {
    const dateStr = getDateStr(s.settled_at || s.created_at);
    if (dateStr === selectedDate.value) {
      total += parseFloat(s.net_payout || 0);
    }
  });
  return total;
});

// 5. Today's Total Cash Outflows (Farmer Payouts + Office Expenses)
const todayOutflows = computed(() => {
  return todayFarmerPayouts.value + todayExpenses.value;
});

// 6. 100% DYNAMIC Service Fees & Loan Recoveries Breakdown for selectedDate
const todayDynamicDeductions = computed(() => {
  const map = {};

  settlements.value.forEach(s => {
    const dateStr = getDateStr(s.settled_at || s.created_at);
    if (dateStr === selectedDate.value) {
      if (s.deductions && Array.isArray(s.deductions) && s.deductions.length > 0) {
        s.deductions.forEach(d => {
          const rawType = d.deduction_type || 'Ada ya Huduma';
          let label = rawType;

          if (rawType === 'storage_fee') label = 'Ada ya Hifadhi Ghalani';
          else if (rawType === 'drying_fee') label = 'Ada ya Kukausha Mazao';
          else if (rawType === 'milling_fee') label = 'Ada ya Kukoboa / Kusaga';
          else if (rawType === 'grading_fee') label = 'Ada ya Sorting & Packaging';
          else if (rawType === 'loan_principal') label = 'Rejesho la Mtaji wa Mkopo (Principal Recovery)';
          else if (rawType === 'loan_interest') label = 'Riba ya Mkopo (Interest Revenue)';
          else {
            label = rawType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
          }

          if (!map[label]) {
            map[label] = { label, amount: 0, type: rawType };
          }
          map[label].amount += parseFloat(d.amount || 0);
        });
      } else {
        const fee = parseFloat(s.total_deductions || 0);
        if (fee > 0) {
          const label = 'Ada za Huduma & Urejesho';
          if (!map[label]) map[label] = { label, amount: 0, type: 'general' };
          map[label].amount += fee;
        }
      }
    }
  });

  return Object.values(map);
});

// Total Retained Office Cash Inflow Today (Service Fees + Interest + Loan Principal Recoveries)
const todayTotalOfficeRetainedCash = computed(() => {
  return todayDynamicDeductions.value.reduce((sum, item) => sum + item.amount, 0);
});

// Total System Real Fee Revenue (Service fees + Interest) earned on selectedDate (FOR PROFIT)
const todaySystemFeeIncome = computed(() => {
  return todayDynamicDeductions.value
    .filter(item => item.type !== 'loan_principal')
    .reduce((sum, item) => sum + item.amount, 0);
});

// Total Loan Principal Recovered on selectedDate (FOR CASHFLOW)
const todayLoanPrincipalRecovery = computed(() => {
  return todayDynamicDeductions.value
    .filter(item => item.type === 'loan_principal')
    .reduce((sum, item) => sum + item.amount, 0);
});

// 7. Standalone Single-Day Net Profit (Faida Safi ya Huduma = Mapato ya Huduma - Matumizi ya Ofisi)
const todayStandaloneProfit = computed(() => {
  return todaySystemFeeIncome.value - todayExpenses.value;
});

// 8. Closing Cash Balance Today (100% Mathematically Aligned: Opening + Today Retained - Expenses)
const closingCashBalance = computed(() => {
  return Math.max(0, openingCashBalance.value + todayTotalOfficeRetainedCash.value - todayExpenses.value);
});

// RENDER CHARTS FUNCTION (SCALED PURELY TO STANDALONE DAILY PROFIT FOR EACH DAY)
const renderCharts = async () => {
  await nextTick();

  if (trendChartInstance) trendChartInstance.destroy();
  if (breakdownChartInstance) breakdownChartInstance.destroy();

  // Generate 7 Days dates ending on selectedDate
  const days = [];
  const curr = new Date(selectedDate.value);
  for (let i = 6; i >= 0; i--) {
    const d = new Date(curr);
    d.setDate(d.getDate() - i);
    days.push(d.toISOString().split('T')[0]);
  }

  // Calculate 7-Day Fee Income per day using getSettlementFeeIncome
  const daysFeeIncomes = days.map(day => {
    let dayIncome = 0;
    settlements.value.forEach(s => {
      const dateStr = getDateStr(s.settled_at || s.created_at);
      if (dateStr === day) {
        dayIncome += getSettlementFeeIncome(s);
      }
    });
    return dayIncome;
  });

  // Calculate 7-Day Expenses per day
  const daysExpensesData = days.map(day => {
    return expenses.value
      .filter(e => getDateStr(e.date_incurred) === day)
      .reduce((sum, e) => sum + parseFloat(e.amount || 0), 0);
  });

  // Calculate Standalone Net Profit for EACH DAY (Fee Income - Office Expenses of THAT DAY ONLY)
  const daysStandaloneProfits = days.map((day, idx) => {
    return daysFeeIncomes[idx] - daysExpensesData[idx];
  });

  // Calculate Opening Balance for EACH DAY (Accumulated prior Fee Incomes - Accumulated prior Office Expenses)
  const daysOpeningBalances = days.map(day => {
    let priorIncome = 0;
    settlements.value.forEach(s => {
      const dateStr = getDateStr(s.settled_at || s.created_at);
      if (dateStr && dateStr < day) {
        priorIncome += getSettlementFeeIncome(s);
      }
    });

    let priorExpense = 0;
    expenses.value.forEach(exp => {
      const dateStr = getDateStr(exp.date_incurred);
      if (dateStr && dateStr < day) {
        priorExpense += parseFloat(exp.amount || 0);
      }
    });

    return Math.max(0, priorIncome - priorExpense);
  });

  // Calculate 7-Day Cumulative Closing Cash Balances for hover audit
  const daysClosingBalances = days.map((day, idx) => {
    return Math.max(0, daysOpeningBalances[idx] + daysStandaloneProfits[idx]);
  });

  const dayLabels = days.map(day => {
    const d = new Date(day);
    return d.toLocaleDateString('sw-TZ', { day: '2-digit', month: 'short' });
  });

  // Render Line Chart scaled 100% ONLY to STANDALONE DAILY NET PROFIT
  if (trendChartCanvas.value) {
    trendChartInstance = new Chart(trendChartCanvas.value, {
      type: 'line',
      data: {
        labels: dayLabels,
        datasets: [
          {
            label: 'Faida Safi ya Siku (Standalone Profit)',
            data: daysStandaloneProfits,
            borderColor: '#059669',
            backgroundColor: 'rgba(5, 150, 105, 0.12)',
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 9,
            pointBackgroundColor: '#059669',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            animations: {
              y: {
                duration: 1200,
                delay: (context) => context.dataIndex * 120,
                easing: 'easeInOutCubic'
              },
              x: {
                duration: 1200,
                delay: (context) => context.dataIndex * 120,
                easing: 'easeInOutCubic'
              }
            }
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 1800,
          easing: 'easeInOutQuart'
        },
        interaction: {
          mode: 'index',
          intersect: false,
        },
        hover: {
          mode: 'index',
          intersect: false
        },
        onClick: (event, elements) => {
          if (elements && elements.length > 0) {
            const index = elements[0].index;
            const clickedDay = days[index];
            if (clickedDay && clickedDay !== selectedDate.value) {
              selectedDate.value = clickedDay;
              fetchData();
            }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            mode: 'index',
            intersect: false,
            padding: 12,
            boxPadding: 6,
            callbacks: {
              title: (items) => {
                if (!items.length) return '';
                const idx = items[0].dataIndex;
                return `📅 Tarehe: ${dayLabels[idx]} (${days[idx]})`;
              },
              label: (ctx) => {
                const idx = ctx.dataIndex;
                const profit = daysStandaloneProfits[idx] || 0;
                return `💡 Faida Safi ya Siku Hiyo Tu: Tsh ${profit.toLocaleString()}`;
              },
              afterBody: (items) => {
                if (!items.length) return '';
                const idx = items[0].dataIndex;
                const income = daysFeeIncomes[idx] || 0;
                const expense = daysExpensesData[idx] || 0;
                const opening = daysOpeningBalances[idx] || 0;
                const closing = daysClosingBalances[idx] || 0;
                return [
                  `--------------------------------`,
                  `🟢 Mapato ya Huduma Siku Hiyo: Tsh ${income.toLocaleString()}`,
                  `🔴 Matumizi ya Ofisi Siku Hiyo: Tsh ${expense.toLocaleString()}`,
                  `🔄 Salio la Jana (Opening Float): Tsh ${opening.toLocaleString()}`,
                  `💰 Salio la Kufunga Siku (Closing): Tsh ${closing.toLocaleString()}`
                ];
              }
            }
          }
        },
        scales: {
          y: {
            ticks: {
              callback: (val) => 'Tsh ' + (val >= 1000000 ? (val/1000000).toFixed(1) + 'M' : (val/1000).toFixed(0) + 'k')
            }
          }
        }
      }
    });
  }

  // Render DYNAMIC DOUGHNUT CHART (Mapato ya Huduma vs Matumizi ya Ofisi)
  if (breakdownChartCanvas.value) {
    let pieLabels = [];
    let pieData = [];
    let pieColors = [];

    if (rightChartMode.value === 'income') {
      // 🟢 SERVICE INCOMES BREAKDOWN (Vyanzo vya Mapato ya Faida PEKEE - Excluding Loan Principal)
      const realIncomeDeductions = todayDynamicDeductions.value.filter(item => item.type !== 'loan_principal');
      if (realIncomeDeductions.length > 0) {
        pieLabels = realIncomeDeductions.map(item => item.label);
        pieData = realIncomeDeductions.map(item => item.amount);
        pieColors = ['#059669', '#0284c7', '#7c3aed', '#d97706', '#0d9488', '#e11d48'];
      } else {
        pieLabels = ['Hakuna Mapato ya Huduma Leo'];
        pieData = [1];
        pieColors = ['#e2e8f0'];
      }
    } else {
      // 🔴 OPERATIONAL EXPENSES BREAKDOWN (Matumizi ya Ofisi)
      const expMap = {};
      filteredExpenses.value.forEach(exp => {
        const cat = exp.category_name || 'Matumizi Mengineyo';
        expMap[cat] = (expMap[cat] || 0) + parseFloat(exp.amount || 0);
      });

      const categories = Object.keys(expMap);
      if (categories.length > 0) {
        pieLabels = categories;
        pieData = categories.map(cat => expMap[cat]);
        pieColors = ['#dc2626', '#ea580c', '#ca8a04', '#9333ea', '#2563eb'];
      } else {
        pieLabels = ['Hakuna Matumizi ya Ofisi Leo'];
        pieData = [1];
        pieColors = ['#e2e8f0'];
      }
    }

    breakdownChartInstance = new Chart(breakdownChartCanvas.value, {
      type: 'doughnut',
      data: {
        labels: pieLabels,
        datasets: [{
          data: pieData,
          backgroundColor: pieColors,
          borderWidth: 2,
          hoverOffset: 8,
          animations: {
            numbers: {
              type: 'number',
              properties: ['circumference', 'endAngle'],
              duration: 1500,
              easing: 'easeInOutQuart'
            }
          }
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 1600,
          easing: 'easeInOutQuart',
          animateRotate: true,
          animateScale: true
        },
        plugins: {
          legend: { 
            position: 'bottom', 
            labels: { 
              font: { weight: 'bold', size: 10 },
              boxWidth: 12,
              padding: 8
            } 
          },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const val = ctx.raw;
                if (val === 1 && (ctx.label.includes('Hakuna'))) return ctx.label;
                return `${ctx.label}: Tsh ${val.toLocaleString()}`;
              }
            }
          }
        }
      }
    });
  }
};

// FILTERED EXPENSES
const filteredExpenses = computed(() => {
  return expenses.value.filter(exp => {
    const matchesDate = getDateStr(exp.date_incurred) === selectedDate.value;
    const q = searchQuery.value.toLowerCase().trim();
    const matchesSearch = !q || (exp.category_name && exp.category_name.toLowerCase().includes(q)) || (exp.description && exp.description.toLowerCase().includes(q));
    return matchesDate && matchesSearch;
  });
});

// FILTERED BATCHES
const filteredBatches = computed(() => {
  return batches.value.filter(b => {
    const matchesDate = getDateStr(b.created_at) === selectedDate.value;
    const q = searchQuery.value.toLowerCase().trim();
    const matchesSearch = !q || (b.batch_code && b.batch_code.toLowerCase().includes(q)) || (b.farmer_name && b.farmer_name.toLowerCase().includes(q));
    return matchesDate && matchesSearch;
  });
});

// COMBINED DAILY LEDGER (With distinct badges for Farmer Payout vs Office Expenses)
const combinedLedger = computed(() => {
  const list = [];

  settlements.value.forEach(s => {
    const dateStr = getDateStr(s.settled_at || s.created_at);
    if (dateStr === selectedDate.value) {
      const farmerName = s.farmer ? s.farmer.name : 'Mkulima';
      const buyerName = s.invoice && s.invoice.buyer ? s.invoice.buyer.name : 'Mnunuzi';

      list.push({
        date: s.settled_at || s.created_at,
        type: 'INFLOW',
        category: 'Mauzo ya Zao (Gross Sales)',
        reference: 'SETT-' + s.id,
        description: `Mauzo ya ${farmerName} kwa Mnunuzi (${buyerName})`,
        inflow: parseFloat(s.gross_amount || 0),
        outflow: 0,
        payment_method: s.payment_method || 'Mobile Money / Bank'
      });

      list.push({
        date: s.settled_at || s.created_at,
        type: 'FARMER_PAYOUT',
        category: 'Malipo ya Mkulima (Net Payout)',
        reference: 'PAY-' + s.id,
        description: `Kumlipa ${farmerName} (Makato ya Huduma & Mkopo: Tsh ${getSettlementFeeIncome(s).toLocaleString()})`,
        inflow: 0,
        outflow: parseFloat(s.net_payout || 0),
        payment_method: s.payment_method || 'Mobile Money'
      });
    }
  });

  expenses.value.forEach(exp => {
    const dateStr = getDateStr(exp.date_incurred);
    if (dateStr === selectedDate.value) {
      list.push({
        date: exp.date_incurred,
        type: 'EXPENSE',
        category: exp.category_name,
        reference: 'EXP-' + exp.id,
        description: exp.description || 'Matumizi ya ofisi',
        inflow: 0,
        outflow: parseFloat(exp.amount || 0),
        payment_method: 'Cash'
      });
    }
  });

  return list.sort((a, b) => new Date(b.date) - new Date(a.date));
});

const filteredCombinedLedger = computed(() => {
  let list = combinedLedger.value;

  if (ledgerTypeFilter.value !== 'ALL') {
    list = list.filter(item => item.type === ledgerTypeFilter.value);
  }

  const q = searchQuery.value.toLowerCase().trim();
  if (!q) return list;
  return list.filter(item => 
    item.category.toLowerCase().includes(q) ||
    item.description.toLowerCase().includes(q) ||
    item.reference.toLowerCase().includes(q)
  );
});

// ACTIONS
const openExpenseModal = () => {
  expenseForm.value = {
    category_name: 'Chakula cha Wafanyakazi',
    amount: 0,
    date_incurred: selectedDate.value,
    description: ''
  };
  modals.value.expense = true;
};

const openProfitBreakdownModal = () => {
  modals.value.profitBreakdown = true;
};

const submitExpense = async () => {
  if (!expenseForm.value.amount || expenseForm.value.amount <= 0) {
    alert('Tafadhali ingiza kiasi sahihi cha gharama.');
    return;
  }

  try {
    const res = await fetch('/api/v1/expenses', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(expenseForm.value)
    });

    if (res.ok) {
      modals.value.expense = false;
      await fetchData();
    } else {
      alert('Imeshindwa kuhifadhi matumizi.');
    }
  } catch (err) {
    console.error('Error saving expense:', err);
  }
};

const deleteExpense = async (id) => {
  if (!confirm('Je, una uhakika unataka kufuta matumizi haya?')) return;
  try {
    const res = await fetch(`/api/v1/expenses/${id}`, { method: 'DELETE' });
    if (res.ok) await fetchData();
  } catch (err) {
    console.error(err);
  }
};

const openCashbookPDF = () => {
  modals.value.cashbookPDF = true;
};

const triggerPrint = () => {
  window.print();
};

const printGRN = (batch) => {
  alert(`Risiti ya Mapokezi (GRN) kwa Batch: ${batch.batch_code}`);
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #printableArea, #printableArea * {
    visibility: visible;
  }
  #printableArea {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
