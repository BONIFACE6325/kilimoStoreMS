<template>
  <div class="space-y-6 animate-fadeIn pb-12">
    
    <!-- Top Header Banner -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition-colors">
      <div>
        <div class="flex items-center gap-2.5">
          <span class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xl shadow-inner">
            🏬
          </span>
          <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
              Warehouse Inventory & Grain Storage System
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
              Real-time grain stock control, bags/kg sold & received breakdown, 2D bin capacity monitoring.
            </p>
          </div>
        </div>
      </div>

      <!-- Action Toolbar -->
      <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
        <button 
          @click="fetchInventoryData" 
          :disabled="loading"
          class="p-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition border border-slate-200/70 dark:border-slate-700 text-xs font-bold flex items-center gap-1.5 cursor-pointer"
          title="Refresh Data"
        >
          <svg :class="{'animate-spin': loading}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          <span>Refresh Data</span>
        </button>

        <button 
          @click="openIntakeModal" 
          class="py-2.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-extrabold rounded-xl shadow-lg shadow-emerald-900/20 border border-emerald-400/30 transition transform active:scale-98 flex items-center gap-2 cursor-pointer ml-auto md:ml-0"
        >
          <span>📥 Intake New Grain Batch</span>
        </button>
      </div>
    </div>

    <!-- Notification Toast -->
    <transition name="fade">
      <div v-if="toastMessage" :class="toastType === 'success' ? 'bg-emerald-900/90 border-emerald-500 text-emerald-100' : 'bg-red-900/90 border-red-500 text-red-100'" class="p-4 rounded-2xl border backdrop-blur-md shadow-xl flex items-center justify-between text-xs font-bold">
        <div class="flex items-center gap-2">
          <span>{{ toastType === 'success' ? '✅' : '⚠️' }}</span>
          <span>{{ toastMessage }}</span>
        </div>
        <button @click="toastMessage = ''" class="text-xs opacity-80 hover:opacity-100">✕</button>
      </div>
    </transition>

    <!-- Executive Inventory KPI Summary Grid (Real Database Analytics) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      
      <!-- KPI 1: Currently Stored Stock -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Current Stored Stock</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shadow-inner">
            📦
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ formatNum(summaryData.stored_stock_mt) }} <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">MT</span>
          </div>
          <p class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-semibold">
            {{ formatNum(summaryData.stored_stock_kg) }} Kg (~{{ formatNum(summaryData.stored_stock_bags) }} Gunia)
          </p>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Status:</span>
          <span class="font-extrabold text-emerald-600 dark:text-emerald-400">Active Storage</span>
        </div>
      </div>

      <!-- KPI 2: Total Sold Stock -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Stock Sold</span>
          <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm shadow-inner">
            🛍️
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ formatNum(summaryData.sold_stock_mt) }} <span class="text-xs font-bold text-blue-600 dark:text-blue-400">MT</span>
          </div>
          <p class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-semibold">
            {{ formatNum(summaryData.sold_stock_kg) }} Kg (~{{ formatNum(summaryData.sold_stock_bags) }} Gunia Sold)
          </p>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Sales Dispatched:</span>
          <span class="font-extrabold text-blue-600 dark:text-blue-400">Verified DB</span>
        </div>
      </div>

      <!-- KPI 3: Total Grain Intake Received -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Intake Received</span>
          <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm shadow-inner">
            📥
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ formatNum(summaryData.total_intake_mt) }} <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">MT</span>
          </div>
          <p class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-semibold">
            {{ formatNum(summaryData.total_intake_kg) }} Kg (~{{ formatNum(summaryData.total_intake_bags) }} Gunia)
          </p>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Lifetime Intake:</span>
          <span class="font-extrabold text-indigo-600 dark:text-indigo-400">100% Tracked</span>
        </div>
      </div>

      <!-- KPI 4: Silo Bin Utilization -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Silo Utilization</span>
          <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm shadow-inner">
            🏬
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ summaryData.utilization_pct }}%
          </div>
          <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full mt-1 overflow-hidden">
            <div 
              class="h-full rounded-full transition-all duration-500"
              :class="summaryData.utilization_pct > 85 ? 'bg-rose-500' : summaryData.utilization_pct > 60 ? 'bg-amber-500' : 'bg-emerald-500'"
              :style="{ width: Math.min(100, summaryData.utilization_pct) + '%' }"
            ></div>
          </div>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Capacity:</span>
          <span class="font-extrabold text-slate-700 dark:text-slate-300">{{ formatNum(summaryData.warehouse_occupancy_mt) }} / {{ formatNum(summaryData.warehouse_capacity_mt) }} MT</span>
        </div>
      </div>

      <!-- KPI 5: Consignment Batches -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Batches</span>
          <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shadow-inner">
            📜
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ summaryData.total_batches }} <span class="text-xs font-bold text-slate-400">Batches</span>
          </div>
          <p class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-semibold">
            In store & processing queue
          </p>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Active Crops:</span>
          <span class="font-extrabold text-amber-600 dark:text-amber-400">{{ (summaryData.crop_breakdown || []).length }} Types</span>
        </div>
      </div>

      <!-- KPI 6: Moisture Quality Alert -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Quality Alert</span>
          <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm shadow-inner">
            ⚠️
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ highMoistureCount }} <span class="text-xs font-bold text-slate-400">> 13.5% Moist</span>
          </div>
          <p class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5 font-semibold">
            {{ highMoistureCount > 0 ? 'Drying process required' : 'Optimal grain quality' }}
          </p>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Status:</span>
          <span :class="highMoistureCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'" class="font-extrabold">
            {{ highMoistureCount > 0 ? 'Action Needed' : 'Safe Storage' }}
          </span>
        </div>
      </div>

    </div>

    <!-- Commodity Breakdown & Volume Distribution Card (Comprehensive Analysis) -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4 transition-colors">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            <span>🌾 Comprehensive Commodity Breakdown & Distribution</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-black bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
              Live Database Breakdown
            </span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
            Analysis of grain quantities received, currently stored, and converted bags/kg per crop.
          </p>
        </div>
      </div>

      <!-- Commodity Breakdown Grid -->
      <div v-if="!summaryData.crop_breakdown || summaryData.crop_breakdown.length === 0" class="py-8 text-center text-xs text-slate-400 font-medium">
        🌾 No grain crop records registered in database yet. Intake a new batch to see live commodity breakdown analytics.
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="item in summaryData.crop_breakdown" 
          :key="item.crop_type"
          class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-3"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="p-1.5 rounded-lg bg-white dark:bg-slate-700 text-sm shadow-xs">
                {{ getCropEmoji(item.crop_type) }}
              </span>
              <span class="text-sm font-black text-slate-900 dark:text-white">{{ item.crop_type }}</span>
            </div>
            <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-[11px] font-extrabold border border-indigo-200 dark:border-indigo-800">
              {{ item.percentage }}% of Total
            </span>
          </div>

          <!-- Volume Breakdown Numbers -->
          <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
            <div class="p-2.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
              <span class="text-[10.5px] font-bold text-slate-400 block uppercase">Total Received:</span>
              <span class="font-black text-slate-900 dark:text-white text-sm">{{ formatNum(item.received_mt) }} MT</span>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-0.5">
                {{ formatNum(item.received_kg) }} Kg (~{{ formatNum(item.received_bags) }} Gunia)
              </span>
            </div>

            <div class="p-2.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
              <span class="text-[10.5px] font-bold text-slate-400 block uppercase">In Active Store:</span>
              <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">{{ formatNum(item.stored_mt) }} MT</span>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-0.5">
                {{ formatNum(item.stored_kg) }} Kg (~{{ formatNum(item.stored_bags) }} Gunia)
              </span>
            </div>
          </div>

          <!-- Volume Meter -->
          <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
            <div 
              class="h-full rounded-full bg-indigo-600 dark:bg-indigo-500 transition-all duration-500"
              :style="{ width: Math.min(100, item.percentage) + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Grain Processing (Transformation) & Sales Velocity Analytics -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-5 transition-colors">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            <span>⚡ Transformation & Sales Velocity Analytics</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-black bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
              Warehouse Operations
            </span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
            Operational summary of grains milled/hulled into finished products vs total sales volume.
          </p>
        </div>

        <!-- Period Selector -->
        <div class="flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 self-start sm:self-auto">
          <button 
            @click="analyticsTimeframe = 'this_week'"
            :class="analyticsTimeframe === 'this_week' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 font-bold'"
            class="px-3 py-1 rounded-xl text-xs transition-all cursor-pointer"
          >
            This Week
          </button>
          <button 
            @click="analyticsTimeframe = 'this_month'"
            :class="analyticsTimeframe === 'this_month' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 font-bold'"
            class="px-3 py-1 rounded-xl text-xs transition-all cursor-pointer"
          >
            This Month
          </button>
          <button 
            @click="analyticsTimeframe = 'all_time'"
            :class="analyticsTimeframe === 'all_time' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 font-bold'"
            class="px-3 py-1 rounded-xl text-xs transition-all cursor-pointer"
          >
            All Time
          </button>
        </div>
      </div>

      <!-- Analytics Dual Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        
        <!-- Box 1: Grain Transformation & Milling Outputs -->
        <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xs font-black">
                ⚙️
              </div>
              <div>
                <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Processed / Transformed Yields</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Total output produced from milling & hulling</p>
              </div>
            </div>
            <div class="text-right">
              <span class="text-sm font-black text-slate-900 dark:text-white">{{ formatNum(activeAnalytics.total_transformed_mt) }} MT</span>
              <span class="block text-[10.5px] text-amber-600 dark:text-amber-400 font-bold">~{{ formatNum(activeAnalytics.total_transformed_bags) }} Bags</span>
            </div>
          </div>

          <!-- Product Yield Breakdown Items -->
          <div v-if="!activeAnalytics.transform_outputs || activeAnalytics.transform_outputs.length === 0" class="py-4 text-center text-xs text-slate-400 font-medium">
            ⚙️ No milling/transformation records for this timeframe.
          </div>
          <div v-else class="space-y-2">
            <div 
              v-for="prod in activeAnalytics.transform_outputs" 
              :key="prod.crop_type"
              class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs"
            >
              <div class="flex items-center gap-2">
                <span>{{ getCropEmoji(prod.crop_type) }}</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ prod.crop_type }}</span>
              </div>
              <div class="font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span>{{ formatNum(prod.mt) }} MT</span>
                <span class="text-[11px] text-slate-400 font-normal">({{ formatNum(prod.kg) }} Kg / ~{{ formatNum(prod.bags) }} Gunia)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Box 2: Grain Sales & Dispatch Performance -->
        <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-xs font-black">
                🛍️
              </div>
              <div>
                <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Grain Sales & Dispatch</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Total volume sold and dispatched</p>
              </div>
            </div>
            <div class="text-right">
              <span class="text-sm font-black text-slate-900 dark:text-white">{{ formatNum(activeAnalytics.total_sold_mt) }} MT</span>
              <span class="block text-[10.5px] text-emerald-600 dark:text-emerald-400 font-bold">~{{ formatNum(activeAnalytics.total_sold_bags) }} Bags</span>
            </div>
          </div>

          <!-- Sales Breakdown Items -->
          <div v-if="!activeAnalytics.crop_sales || activeAnalytics.crop_sales.length === 0" class="py-4 text-center text-xs text-slate-400 font-medium">
            🛍️ No sales records for this timeframe.
          </div>
          <div v-else class="space-y-2">
            <div 
              v-for="sItem in activeAnalytics.crop_sales" 
              :key="sItem.crop_type"
              class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs"
            >
              <div class="flex items-center gap-2">
                <span>{{ getCropEmoji(sItem.crop_type) }}</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ sItem.crop_type }}</span>
              </div>
              <div class="font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <span>{{ formatNum(sItem.mt) }} MT</span>
                <span class="text-[11px] text-slate-400 font-normal">({{ formatNum(sItem.kg) }} Kg / ~{{ formatNum(sItem.bags) }} Gunia)</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Master Grain Batches Data Table Section -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden space-y-4 transition-colors">
      
      <!-- Table Header & Controls Bar -->
      <div class="p-6 pb-2 border-b border-slate-100 dark:border-slate-800 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
              <span>📜 Grain Inventory Batches Ledger</span>
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                {{ filteredBatches.length }} Records
              </span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
              Comprehensive list of grain intake consignments, moisture readings, and bin allocations.
            </p>
          </div>

          <!-- Quick Filters -->
          <div class="flex flex-wrap items-center gap-2">
            <button 
              @click="cropFilter = ''" 
              :class="cropFilter === '' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
              class="py-1.5 px-3 rounded-xl text-xs font-extrabold transition cursor-pointer"
            >
              All Crops
            </button>

            <button 
              v-for="crop in cropTypesList" 
              :key="crop"
              @click="cropFilter = crop"
              :class="cropFilter === crop ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
              class="py-1.5 px-3 rounded-xl text-xs font-extrabold transition cursor-pointer"
            >
              {{ crop }}
            </button>
          </div>
        </div>

        <!-- Search Bar & Status Filter Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          
          <!-- Search Input -->
          <div class="relative sm:col-span-2">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              🔍
            </div>
            <input 
              type="text" 
              v-model="searchQuery"
              placeholder="Search by Batch Code (e.g. BCH-1141), Farmer Name, or Bin Location..."
              class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <select 
              v-model="statusFilter"
              class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
            >
              <option value="">All Storage Statuses</option>
              <option value="stored">Stored</option>
              <option value="received">Received (New)</option>
              <option value="transformed">Transformed (Processed)</option>
              <option value="sold">Sold / Dispatched</option>
            </select>
          </div>

        </div>
      </div>

      <!-- Data Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 text-[11px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">
              <th class="py-3.5 px-4">Batch Code & Date</th>
              <th class="py-3.5 px-4">Farmer / Owner</th>
              <th class="py-3.5 px-4">Commodity & Variety</th>
              <th class="py-3.5 px-4">Current Stock Weight</th>
              <th class="py-3.5 px-4">Moisture Level</th>
              <th class="py-3.5 px-4">Bin Storage</th>
              <th class="py-3.5 px-4">Age Stored</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs font-medium text-slate-700 dark:text-slate-300">
            
            <tr v-if="loading" class="text-center py-8">
              <td colspan="9" class="py-12 text-slate-400">
                <div class="flex flex-col items-center justify-center gap-2">
                  <svg class="animate-spin h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Loading grain inventory batches...</span>
                </div>
              </td>
            </tr>

            <tr v-else-if="filteredBatches.length === 0" class="text-center py-8">
              <td colspan="9" class="py-12 text-slate-400">
                <div class="flex flex-col items-center justify-center gap-2">
                  <span class="text-3xl">📦</span>
                  <span class="font-bold text-slate-600 dark:text-slate-300">No grain inventory batches found.</span>
                  <span class="text-xs text-slate-400">Try adjusting your filters or click "Intake New Grain Batch".</span>
                </div>
              </td>
            </tr>

            <tr 
              v-for="batch in filteredBatches" 
              :key="batch.id"
              class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors"
            >
              <!-- Batch Code & Date -->
              <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                <div class="flex flex-col">
                  <span class="text-emerald-600 dark:text-emerald-400 font-mono font-black">{{ batch.batch_code }}</span>
                  <span class="text-[10.5px] text-slate-400 font-normal">{{ batch.created_at }}</span>
                </div>
              </td>

              <!-- Farmer / Owner -->
              <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-white">
                {{ batch.farmer_name || 'N/A' }}
              </td>

              <!-- Commodity & Variety -->
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-1.5">
                  <span class="font-extrabold text-slate-900 dark:text-white">{{ batch.crop_type }}</span>
                  <span v-if="batch.variety" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                    {{ batch.variety }}
                  </span>
                </div>
              </td>

              <!-- Current Stock Weight -->
              <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white">
                <div>
                  <span>{{ formatNum(batch.current_weight) }} MT</span>
                  <span class="text-[10.5px] font-normal text-slate-400 block">({{ (parseFloat(batch.current_weight || 0) * 1000).toLocaleString() }} Kg)</span>
                </div>
              </td>

              <!-- Moisture Level -->
              <td class="py-3.5 px-4">
                <span 
                  :class="batch.moisture > 13.5 ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'"
                  class="px-2.5 py-1 rounded-lg text-[11px] font-black border inline-flex items-center gap-1"
                >
                  <span>{{ batch.moisture ? batch.moisture + '%' : '12.0%' }}</span>
                  <span v-if="batch.moisture > 13.5">⚠️</span>
                </span>
              </td>

              <!-- Storage Bin -->
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold text-[11px] border border-slate-200/80 dark:border-slate-700 inline-flex items-center gap-1">
                  <span>🏢</span>
                  <span>{{ batch.bin_name || 'Unassigned' }}</span>
                </span>
              </td>

              <!-- Storage Age -->
              <td class="py-3.5 px-4 font-bold text-slate-600 dark:text-slate-400 text-xs">
                {{ batch.days_stored }} Days
              </td>

              <!-- Status -->
              <td class="py-3.5 px-4">
                <span 
                  :class="getBatchStatusBadgeClass(batch.status)"
                  class="px-2.5 py-1 rounded-lg text-[10.5px] font-black uppercase tracking-wider border"
                >
                  {{ batch.status }}
                </span>
              </td>

              <!-- Actions -->
              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button 
                    @click="openMoveModal(batch)" 
                    class="py-1 px-2.5 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 font-extrabold text-[11px] rounded-lg border border-blue-200 dark:border-blue-800 transition cursor-pointer"
                    title="Transfer Bin"
                  >
                    🚚 Move
                  </button>
                  <button 
                    @click="deleteBatch(batch.id)" 
                    class="py-1 px-2 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 font-extrabold text-[11px] rounded-lg border border-rose-200 dark:border-rose-800 transition cursor-pointer"
                    title="Delete Batch"
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

    <!-- MODAL 1: Intake New Grain Batch Form -->
    <transition name="fade">
      <div v-if="showIntakeModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 max-w-lg w-full space-y-5 transform transition-all">
          
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
              <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-base">📥</span>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Intake New Grain Batch</h3>
            </div>
            <button @click="showIntakeModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
          </div>

          <form @submit.prevent="submitIntakeBatch" class="space-y-4 text-left">
            
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Select Farmer / Owner</label>
              <select 
                v-model="intakeForm.farmer_id" 
                required
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              >
                <option value="" disabled>-- Select Farmer --</option>
                <option v-for="farmer in farmersList" :key="farmer.id" :value="farmer.id">
                  {{ farmer.name }} ({{ farmer.farmer_code || farmer.phone }})
                </option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Crop Type</label>
                <input 
                  type="text" 
                  v-model="intakeForm.crop_type" 
                  required
                  placeholder="e.g. Rice, Paddy, Maize"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                />
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Variety (Optional)</label>
                <input 
                  type="text" 
                  v-model="intakeForm.variety" 
                  placeholder="e.g. Super Kyela"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                />
              </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Intake Quantity</label>
                <input 
                  type="number" 
                  step="0.01"
                  v-model.number="intakeForm.intake_quantity" 
                  required
                  placeholder="e.g. 500 or 50"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                />
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Unit of Measure</label>
                <select
                  v-model="intakeForm.intake_unit"
                  required
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                >
                  <option value="Kilo">Kilo (Kg)</option>
                  <option value="Gunia">Gunia (Bags)</option>
                  <option value="MT">MT (Metric Tons)</option>
                </select>
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Initial Moisture (%)</label>
                <input 
                  type="number" 
                  step="0.1"
                  v-model.number="intakeForm.initial_moisture" 
                  required
                  placeholder="e.g. 13.5"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Assign Target Warehouse Bin</label>
              <select 
                v-model="intakeForm.bin_id" 
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              >
                <option value="">-- Unassigned (Hold in Yard) --</option>
                <option v-for="bin in summaryData.bins || binsList" :key="bin.id" :value="bin.id">
                  Bin {{ bin.name }} (Capacity: {{ bin.capacity_mt }} MT - Occupied: {{ bin.current_occupancy_mt }} MT)
                </option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-3">
              <button 
                type="button"
                @click="showIntakeModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="submitting"
                class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center justify-center gap-1.5"
              >
                <span>{{ submitting ? 'Saving...' : 'Confirm Intake →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

    <!-- MODAL 2: Transfer / Move Batch to Bin -->
    <transition name="fade">
      <div v-if="showMoveModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 max-w-md w-full space-y-4 transform transition-all">
          
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
              <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-base">🚚</span>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Transfer Batch Location</h3>
            </div>
            <button @click="showMoveModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
          </div>

          <div v-if="activeMoveBatch" class="p-3 bg-slate-50 dark:bg-slate-800/70 rounded-2xl space-y-1 text-xs text-left border border-slate-200/60 dark:border-slate-700">
            <div class="font-extrabold text-slate-900 dark:text-white">Batch: {{ activeMoveBatch.batch_code }} ({{ activeMoveBatch.crop_type }})</div>
            <div class="text-slate-500 dark:text-slate-400 font-medium">Owner: {{ activeMoveBatch.farmer_name }} | Weight: {{ activeMoveBatch.current_weight }} MT</div>
            <div class="text-slate-500 dark:text-slate-400 font-medium">Current Bin: <strong class="text-emerald-600 dark:text-emerald-400">{{ activeMoveBatch.bin_name || 'Unassigned' }}</strong></div>
          </div>

          <form @submit.prevent="submitMoveBatch" class="space-y-4 text-left">
            
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Select Destination Bin</label>
              <select 
                v-model="moveForm.destination_bin_id" 
                required
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40"
              >
                <option value="" disabled>-- Select Destination Bin --</option>
                <option v-for="bin in summaryData.bins || binsList" :key="bin.id" :value="bin.id">
                  Bin {{ bin.name }} (Current Occupancy: {{ bin.current_occupancy_mt }} / {{ bin.capacity_mt }} MT)
                </option>
              </select>
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Transfer Reason / Audit Note</label>
              <input 
                type="text" 
                v-model="moveForm.reason" 
                placeholder="e.g. Space reallocation for drying, Quality segregation"
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40"
              />
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
              <button 
                type="button"
                @click="showMoveModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="submitting"
                class="py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-blue-400/30 transition cursor-pointer flex items-center justify-center gap-1.5"
              >
                <span>{{ submitting ? 'Moving...' : 'Execute Move →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const loading = ref(false);
const submitting = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const batchesList = ref([]);
const binsList = ref([]);
const farmersList = ref([]);

const summaryData = ref({
  total_batches: 0,
  total_intake_mt: 0,
  total_intake_kg: 0,
  total_intake_bags: 0,
  stored_stock_mt: 0,
  stored_stock_kg: 0,
  stored_stock_bags: 0,
  sold_stock_mt: 0,
  sold_stock_kg: 0,
  sold_stock_bags: 0,
  warehouse_capacity_mt: 0,
  warehouse_occupancy_mt: 0,
  utilization_pct: 0,
  crop_breakdown: [],
  bins: []
});

const searchQuery = ref('');
const cropFilter = ref('');
const statusFilter = ref('');
const selectedBinFilter = ref('');

const showIntakeModal = ref(false);
const showMoveModal = ref(false);
const activeMoveBatch = ref(null);
const analyticsTimeframe = ref('this_week');

const activeAnalytics = computed(() => {
  const all = summaryData.value?.analytics || {};
  return all[analyticsTimeframe.value] || {
    total_transformed_mt: 0,
    total_transformed_kg: 0,
    total_transformed_bags: 0,
    transform_outputs: [],
    total_sold_mt: 0,
    total_sold_kg: 0,
    total_sold_bags: 0,
    crop_sales: []
  };
});

const intakeForm = ref({
  farmer_id: '',
  crop_type: 'Rice',
  variety: '',
  intake_quantity: null,
  intake_unit: 'Kilo',
  initial_weight_mt: null,
  initial_moisture: 13.0,
  bin_id: ''
});

const moveForm = ref({
  destination_bin_id: '',
  reason: ''
});

const triggerToast = (msg, type = 'success') => {
  toastMessage.value = msg;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = '';
  }, 4000);
};

const formatNum = (val) => {
  const num = parseFloat(val || 0);
  return num.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 1 });
};

const getCropEmoji = (cropName) => {
  const name = (cropName || '').toLowerCase();
  if (name.includes('maize') || name.includes('mahindi')) return '🌽';
  if (name.includes('rice') || name.includes('mchele') || name.includes('paddy') || name.includes('mpunga')) return '🌾';
  if (name.includes('bean') || name.includes('maharage') || name.includes('legume')) return '🫘';
  if (name.includes('pumba') || name.includes('bran') || name.includes('husk')) return '🍂';
  return '📦';
};

// Fetch Real Inventory Summary & Batches from Backend API
const fetchInventoryData = async () => {
  loading.value = true;
  try {
    const [summaryRes, bRes, binRes, fRes] = await Promise.all([
      fetch('/api/v1/inventory/summary'),
      fetch('/api/v1/batches'),
      fetch('/api/v1/bins/map'),
      fetch('/api/v1/farmers')
    ]);

    if (summaryRes.ok) {
      summaryData.value = await summaryRes.json();
    }
    if (bRes.ok) {
      batchesList.value = await bRes.json();
    }
    if (binRes.ok) {
      binsList.value = await binRes.json();
    }
    if (fRes.ok) {
      const fData = await fRes.json();
      farmersList.value = Array.isArray(fData) ? fData : (fData.data || []);
    }
  } catch (err) {
    console.error('Error fetching inventory summary data:', err);
    triggerToast('Failed to connect to backend server', 'error');
  } finally {
    loading.value = false;
  }
};

const highMoistureCount = computed(() => {
  return batchesList.value.filter(b => parseFloat(b.moisture || 0) > 13.5 && b.status !== 'sold').length;
});

const cropTypesList = computed(() => {
  const set = new Set();
  if (summaryData.value.crop_breakdown && summaryData.value.crop_breakdown.length > 0) {
    summaryData.value.crop_breakdown.forEach(item => set.add(item.crop_type));
  } else {
    batchesList.value.forEach(b => {
      if (b.crop_type) set.add(b.crop_type);
    });
  }
  return Array.from(set);
});

// Bin Formatting Helpers
const getBinStatusBadgeClass = (bin) => {
  const occ = parseFloat(bin.current_occupancy_mt || 0);
  const cap = parseFloat(bin.capacity_mt || 1);
  const pct = (occ / cap) * 100;
  if (pct >= 90) return 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800';
  if (pct >= 60) return 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
  if (pct > 0) return 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
  return 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700';
};

const getBinStatusLabel = (bin) => {
  const occ = parseFloat(bin.current_occupancy_mt || 0);
  const cap = parseFloat(bin.capacity_mt || 1);
  const pct = (occ / cap) * 100;
  if (pct >= 90) return 'FULL';
  if (pct >= 60) return 'HIGH';
  if (pct > 0) return 'ACTIVE';
  return 'EMPTY';
};

const getBinProgressClass = (bin) => {
  const occ = parseFloat(bin.current_occupancy_mt || 0);
  const cap = parseFloat(bin.capacity_mt || 1);
  const pct = (occ / cap) * 100;
  if (pct >= 90) return 'bg-rose-500';
  if (pct >= 60) return 'bg-amber-500';
  return 'bg-emerald-500';
};

const toggleBinFilter = (binName) => {
  if (selectedBinFilter.value === binName) {
    selectedBinFilter.value = '';
  } else {
    selectedBinFilter.value = binName;
  }
};

const getBatchStatusBadgeClass = (status) => {
  switch ((status || '').toLowerCase()) {
    case 'stored':
      return 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
    case 'received':
      return 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800';
    case 'transformed':
      return 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800';
    case 'sold':
      return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
    default:
      return 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
  }
};

// Filtered Batches List
const filteredBatches = computed(() => {
  return batchesList.value.filter(batch => {
    // Search Query
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const codeMatch = (batch.batch_code || '').toLowerCase().includes(q);
      const farmerMatch = (batch.farmer_name || '').toLowerCase().includes(q);
      const cropMatch = (batch.crop_type || '').toLowerCase().includes(q);
      const binMatch = (batch.bin_name || '').toLowerCase().includes(q);
      if (!codeMatch && !farmerMatch && !cropMatch && !binMatch) return false;
    }

    // Crop Filter
    if (cropFilter.value) {
      const f = cropFilter.value.toLowerCase();
      const bCrop = (batch.crop_type || '').toLowerCase();
      if (f.includes('rice') || f.includes('mchele')) {
        if (!['rice', 'mchele', 'paddy', 'mpunga'].includes(bCrop)) return false;
      } else if (f.includes('maize') || f.includes('mahindi')) {
        if (!['maize', 'mahindi'].includes(bCrop)) return false;
      } else if (f.includes('pumba') || f.includes('bran')) {
        if (!['pumba', 'bran', 'husk'].includes(bCrop)) return false;
      } else if (bCrop !== f) {
        return false;
      }
    }

    // Status Filter
    if (statusFilter.value && (batch.status || '').toLowerCase() !== statusFilter.value.toLowerCase()) {
      return false;
    }

    // Bin Filter
    if (selectedBinFilter.value && (batch.bin_name || '').toLowerCase() !== selectedBinFilter.value.toLowerCase()) {
      return false;
    }

    return true;
  });
});

// Modal Actions
const openIntakeModal = () => {
  intakeForm.value = {
    farmer_id: farmersList.value.length > 0 ? farmersList.value[0].id : '',
    crop_type: 'Rice',
    variety: '',
    initial_weight_mt: null,
    initial_moisture: 13.0,
    bin_id: (summaryData.value.bins || binsList.value).length > 0 ? (summaryData.value.bins || binsList.value)[0].id : ''
  };
  showIntakeModal.value = true;
};

const submitIntakeBatch = async () => {
  submitting.value = true;
  try {
    const res = await fetch('/api/v1/batches', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(intakeForm.value)
    });

    if (res.ok) {
      triggerToast('New grain batch intake recorded successfully!', 'success');
      showIntakeModal.value = false;
      await fetchInventoryData();
    } else {
      const err = await res.json();
      triggerToast(err.message || 'Failed to record batch intake', 'error');
    }
  } catch (err) {
    console.error('Error recording batch intake:', err);
    triggerToast('Network error while recording intake', 'error');
  } finally {
    submitting.value = false;
  }
};

const openMoveModal = (batch) => {
  activeMoveBatch.value = batch;
  moveForm.value = {
    destination_bin_id: (summaryData.value.bins || binsList.value).length > 0 ? (summaryData.value.bins || binsList.value)[0].id : '',
    reason: 'Space reallocation & storage optimization'
  };
  showMoveModal.value = true;
};

const submitMoveBatch = async () => {
  if (!activeMoveBatch.value) return;
  submitting.value = true;
  try {
    const res = await fetch(`/api/v1/batches/${activeMoveBatch.value.id}/move`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(moveForm.value)
    });

    if (res.ok) {
      triggerToast(`Batch ${activeMoveBatch.value.batch_code} moved successfully!`, 'success');
      showMoveModal.value = false;
      await fetchInventoryData();
    } else {
      const err = await res.json();
      triggerToast(err.message || 'Failed to transfer batch', 'error');
    }
  } catch (err) {
    console.error('Error moving batch:', err);
    triggerToast('Network error while moving batch', 'error');
  } finally {
    submitting.value = false;
  }
};

const deleteBatch = async (id) => {
  if (!confirm('Are you sure you want to delete this grain batch record?')) return;
  try {
    const res = await fetch(`/api/v1/batches/${id}`, { method: 'DELETE' });
    if (res.ok) {
      triggerToast('Batch deleted successfully', 'success');
      await fetchInventoryData();
    } else {
      triggerToast('Failed to delete batch', 'error');
    }
  } catch (err) {
    console.error('Error deleting batch:', err);
  }
};

onMounted(() => {
  fetchInventoryData();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
