<template>
  <div class="space-y-6 pb-12">
    
    <!-- Page Header Bar (Clean Dashboard Style) -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-50 tracking-tight flex items-center gap-2">
          Usimamizi wa Wakulima 👨‍🌾
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
          Orodha ya wakulima, mizigo ghalani, huduma za kinu, mikopo na taarifa za mauzo
        </p>
      </div>
      <div class="flex items-center gap-2.5 w-full sm:w-auto">
        <button @click="openAddFarmerModal" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2 w-full sm:w-auto">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>Sajili Mkulima Mpya</span>
        </button>
      </div>
    </div>

    <!-- Toast Notification Popup Card (Enterprise Modern Card) -->
    <div v-if="toast.show" 
         class="fixed top-6 right-6 z-100 max-w-md w-full p-4 rounded-2xl shadow-2xl backdrop-blur-xl border flex items-start gap-3.5 text-white transition-all transform animate-fadeIn duration-200"
         :class="toast.type === 'error' 
           ? 'bg-gradient-to-r from-rose-950/95 via-red-950/95 to-slate-900/95 border-rose-500/80 shadow-rose-950/50' 
           : 'bg-gradient-to-r from-emerald-950/95 via-teal-950/95 to-slate-900/95 border-emerald-500/80 shadow-emerald-950/50'">
      
      <div class="p-2 rounded-xl flex-shrink-0 flex items-center justify-center text-lg leading-none"
           :class="toast.type === 'error' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'">
        <span>{{ toast.type === 'error' ? '⚠️' : '✅' }}</span>
      </div>

      <div class="flex-1 min-w-0 pr-1 space-y-0.5">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-black uppercase tracking-wider" :class="toast.type === 'error' ? 'text-rose-400' : 'text-emerald-400'">
            {{ toast.type === 'error' ? 'Onyo la Mfumo' : 'Taarifa ya Mfumo' }}
          </span>
          <button @click="toast.show = false" class="text-white/60 hover:text-white text-xs font-bold p-0.5 rounded-lg hover:bg-white/10 dark:bg-slate-900/10 transition cursor-pointer">✕</button>
        </div>
        <div class="font-extrabold text-xs text-white/95 leading-relaxed break-words">
          {{ toast.message }}
        </div>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-emerald-100/80 shadow-2xs space-y-3">
      <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="relative flex-1 w-full">
          <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input v-model="searchQuery" type="text" placeholder="Tafuta mkulima kwa Jina, Code, au Namba ya Simu..." class="w-full pl-10 pr-4 py-2.5 bg-emerald-50/30 dark:bg-emerald-900/40 border border-emerald-200/70 dark:border-emerald-700/50 rounded-xl text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"/>
        </div>
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
          <select v-model="regionFilter" class="bg-emerald-50/30 dark:bg-emerald-900/40 border border-emerald-200/70 dark:border-emerald-700/50 rounded-xl text-xs sm:text-sm font-semibold py-2.5 px-3 focus:outline-none">
            <option value="">Mikoa Yote</option>
            <option v-for="r in availableRegions" :key="r" :value="r">{{ r }}</option>
          </select>
          <button @click="resetFilters" class="px-3.5 py-2.5 text-xs font-bold text-emerald-800 dark:text-emerald-400 hover:text-emerald-950 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:bg-emerald-500/20 transition">
            Safisha
          </button>
        </div>
      </div>

      <!-- STATUS TABS (Active / Inactive / Wote) -->
      <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 flex-wrap">
        <button 
          @click="statusFilter = ''"
          :class="!statusFilter ? 'bg-slate-900 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-200/60 dark:bg-slate-700/60'"
          class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5"
        >
          <span>👥 Wakulima Wote ({{ farmers.length }})</span>
        </button>

        <button 
          @click="statusFilter = 'active'"
          :class="statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-xs font-black' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-400 font-bold hover:bg-emerald-100/80 dark:bg-emerald-900/50'"
          class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5"
        >
          <span>🟢 Active Farmers (Wenye Mzigo Ghalani)</span>
        </button>

        <button 
          @click="statusFilter = 'inactive'"
          :class="statusFilter === 'inactive' ? 'bg-slate-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-200/60 dark:bg-slate-700/60'"
          class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5"
        >
          <span>⚪ Inactive Farmers (Wasiokuwa na Mzigo)</span>
        </button>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-emerald-100/80 dark:border-slate-700/80 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-emerald-50/70 dark:bg-slate-950 text-emerald-950 dark:text-emerald-400 font-bold border-b border-emerald-100 dark:border-slate-800 capitalize text-[11px] tracking-wide">
            <tr>
              <th class="py-3 px-4">Mkulima</th>
              <th class="py-3 px-4">Simu</th>
              <th class="py-3 px-4">Mkoa / Wilaya</th>
              <th class="py-3 px-4">Mazao Ghalani</th>
              <th class="py-3 px-4">Deni la Mkopo</th>
              <th class="py-3 px-4">Hali (Status)</th>
              <th class="py-3 px-4 text-right">Vitendo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 font-medium">
            <tr v-if="loading" class="text-center text-slate-400">
              <td colspan="7" class="py-6">
                <div class="flex items-center justify-center gap-2">
                  <span class="w-4 h-4 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin"></span>
                  <span>Inapakia wakulima kutoka database...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="filteredFarmers.length === 0" class="text-center text-slate-400">
              <td colspan="7" class="py-6">Hakuna mkulima aliyepatikana kwenye database.</td>
            </tr>
            <tr v-for="f in paginatedFarmers" :key="f.id" class="hover:bg-emerald-50/40 dark:hover:bg-slate-800/50 transition-colors">
              <td class="py-2.5 px-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black flex items-center justify-center text-xs shadow-2xs shrink-0">
                    {{ (f.name || 'M').charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 dark:text-slate-50 text-xs leading-tight capitalize">{{ (f.name || '').toLowerCase() }}</div>
                    <div class="text-[10px] text-emerald-700 dark:text-emerald-400 font-mono font-bold mt-0.5">{{ f.farmer_code }}</div>
                  </div>
                </div>
              </td>
              <td class="py-2 px-4 text-slate-600 dark:text-slate-300 font-mono font-semibold text-xs">{{ f.phone || 'N/A' }}</td>
              <td class="py-2 px-4 text-slate-600 dark:text-slate-300 font-semibold text-xs">{{ f.region || 'N/A' }} {{ f.district ? '(' + f.district + ')' : '' }}</td>
              <td class="py-2 px-4 font-black text-emerald-700 dark:text-emerald-400 text-xs">
                {{ (parseFloat(f.active_stock || f.total_deposited || 0) * 1000).toLocaleString() }} Kg
              </td>
              <td class="py-2 px-4">
                <span :class="parseFloat(f.loan_balance || 0) > 0 ? 'text-red-600 dark:text-red-400 font-black text-xs' : 'text-slate-400 font-semibold text-xs'">
                  Tsh {{ parseFloat(f.loan_balance || 0).toLocaleString() }}
                </span>
              </td>
              <td class="py-2 px-4">
                <span :class="f.status === 'active' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-900 dark:text-emerald-400 border-emerald-300 dark:border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700'" class="px-2.5 py-0.5 rounded-lg text-[10px] font-black border flex items-center gap-1 w-fit shadow-2xs">
                  <span>{{ f.status === 'active' ? '🟢 Active' : '⚪ Inactive' }}</span>
                </span>
              </td>
              <td class="py-2 px-4 text-right">
                <button 
                  @click="openFarmerProfile(f.id)" 
                  title="Fungua Profile ya Mkulima"
                  class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-600 text-emerald-700 dark:text-emerald-400 hover:text-white border border-emerald-200/80 dark:border-emerald-700/50 hover:border-emerald-600 rounded-lg text-xs font-bold transition-all duration-150 inline-flex items-center gap-1 cursor-pointer"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  <span>Profile</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="px-6 py-4 bg-emerald-50/40 dark:bg-emerald-900/40 border-t border-emerald-100 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300 font-semibold">
        <div>Inaonyesha {{ (currentPage - 1) * pageSize + 1 }} hadi {{ Math.min(currentPage * pageSize, filteredFarmers.length) }} kati ya {{ filteredFarmers.length }}</div>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 disabled:opacity-40 font-bold shadow-2xs">Iliyopita</button>
          <button @click="currentPage++" :disabled="currentPage * pageSize >= filteredFarmers.length" class="px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-200 disabled:opacity-40 font-bold shadow-2xs">Ifuatayo</button>
        </div>
      </div>
    </div>

    <!-- MAIN FARMER PROFILE MODAL (With Strict Transformed Batch Locking & Product Tree Services) -->
    <div v-if="modals.profile" class="fixed inset-0 z-[80] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-7xl h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-emerald-200 dark:border-emerald-500/20">
        
        <!-- Profile Modal Header -->
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 via-teal-900 to-emerald-950 text-white shrink-0">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white font-black flex items-center justify-center text-xl shadow-md shadow-emerald-900/50 ring-2 ring-emerald-400/40">
              {{ (selectedFarmer.name || 'M').charAt(0).toUpperCase() }}
            </div>
            <div>
              <h2 class="text-lg sm:text-xl font-black text-white flex items-center gap-2">
                {{ selectedFarmer.name }}
                <span class="text-xs font-mono font-black text-emerald-300 bg-emerald-950 px-2.5 py-0.5 rounded-lg border border-emerald-700/80">{{ selectedFarmer.farmer_code }}</span>
              </h2>
              <p class="text-xs text-emerald-200 font-semibold mt-0.5">Farmer Profile & Services — {{ selectedFarmer.phone }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button @click="openFarmerPDFReceipt" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black rounded-xl transition shadow-xs flex items-center gap-1.5">
              <span>🖨️ Risiti ya PDF</span>
            </button>
            <button @click="closeProfileModal" title="Funga Profile Modal" class="text-emerald-200 hover:text-white p-2 rounded-full hover:bg-emerald-800/60 transition cursor-pointer">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <!-- Profile Loading State -->
        <div v-if="loadingProfile" class="flex-1 flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400">
          <div class="w-8 h-8 rounded-full border-3 border-emerald-600 border-t-transparent animate-spin mb-3"></div>
          <div class="font-extrabold text-sm text-slate-700 dark:text-slate-200">Inapakia taarifa za mkulima kutoka database...</div>
        </div>

        <!-- Profile Modal Body (2 Columns) -->
        <div v-else class="flex-1 overflow-y-auto p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 bg-slate-50 dark:bg-slate-950">
          
          <!-- LEFT COLUMN: Profile Info & Address (4 cols) -->
          <div class="lg:col-span-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-emerald-100 shadow-2xs space-y-5">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-xl flex items-center justify-center shadow-md">
                  {{ (selectedFarmer.name || 'A').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="text-base font-black text-slate-900 dark:text-slate-50 leading-tight">{{ selectedFarmer.name }}</div>
                  <div class="text-xs font-mono font-bold text-emerald-700 dark:text-emerald-400 mt-0.5">{{ selectedFarmer.farmer_code }}</div>
                </div>
              </div>
              <button 
                @click="isEditingFarmer = !isEditingFarmer" 
                class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 rounded-xl text-xs font-extrabold flex items-center gap-1.5 transition"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>{{ isEditingFarmer ? 'Funga' : 'Hariri' }}</span>
              </button>
            </div>

            <!-- Inline Edit Farmer Form -->
            <div v-if="isEditingFarmer" class="p-4 bg-emerald-50/50 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl space-y-3">
              <div class="text-xs font-extrabold text-emerald-950 dark:text-emerald-400 pb-1 border-b border-emerald-200 dark:border-emerald-500/20">✏️ Hariri Taarifa za Mkulima</div>
              <div class="space-y-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200">
                <div>
                  <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Jina Kamili *</label>
                  <input v-model="editFarmerForm.name" type="text" class="w-full p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-medium"/>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Simu *</label>
                    <input v-model="editFarmerForm.phone" type="text" class="w-full p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-medium"/>
                  </div>
                  <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">NIDA</label>
                    <input v-model="editFarmerForm.national_id" type="text" class="w-full p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-medium"/>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Mkoa</label>
                    <input v-model="editFarmerForm.region" type="text" class="w-full p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-medium"/>
                  </div>
                  <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Wilaya</label>
                    <input v-model="editFarmerForm.district" type="text" class="w-full p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-medium"/>
                  </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                  <button @click="isEditingFarmer = false" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl">Ghairi</button>
                  <button @click="saveEditFarmer" class="px-3.5 py-1.5 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Hifadhi</button>
                </div>
              </div>
            </div>

            <!-- Details List -->
            <div v-else class="space-y-3 text-xs sm:text-sm font-medium">
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold">Simu:</span>
                <strong class="text-slate-900 dark:text-slate-50 font-bold font-mono">{{ selectedFarmer.phone || 'N/A' }}</strong>
              </div>
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold">Mkoa:</span>
                <strong class="text-slate-900 dark:text-slate-50 font-bold">{{ selectedFarmer.region || 'Arusha' }}</strong>
              </div>
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold">Wilaya:</span>
                <strong class="text-slate-900 dark:text-slate-50 font-bold">{{ selectedFarmer.district || 'Arumeru' }}</strong>
              </div>
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold">Kata:</span>
                <strong class="text-slate-900 dark:text-slate-50 font-bold">{{ selectedFarmer.ward || 'Usa River' }}</strong>
              </div>
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                <span class="text-slate-400 font-semibold">National ID:</span>
                <strong class="text-slate-900 dark:text-slate-50 font-bold font-mono">{{ selectedFarmer.national_id || 'N/A' }}</strong>
              </div>
            </div>

            <!-- KPI Highlight Cards (Clean Enterprise Theme) -->
            <div class="space-y-3 pt-2">
              <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex items-center justify-between">
                <div>
                  <div class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider">Jumla ya Mzigo Ghalani</div>
                  <div class="text-xl font-black text-slate-900 dark:text-slate-50 mt-0.5 font-mono">{{ totalFarmerStockKg.toLocaleString() }} Kg</div>
                  <div class="text-[9.5px] text-slate-400 font-semibold mt-0.5">Physical Stock in Storage</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-black flex items-center justify-center text-sm border border-slate-200 dark:border-slate-700">📦</div>
              </div>

              <!-- PATO SAFI LA MKULIMA LOTE (CLEAN ENTERPRISE CARD) -->
              <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-emerald-300 dark:border-emerald-500/30 shadow-xs flex items-center justify-between">
                <div>
                  <div class="text-[10px] font-black uppercase text-emerald-800 dark:text-emerald-400 tracking-wider">Pato Safi Aliyolipwa (Net Payout)</div>
                  <div class="text-xl font-black text-emerald-700 dark:text-emerald-400 font-mono mt-0.5">Tsh {{ totalFarmerNetPayout.toLocaleString() }}</div>
                  <div class="text-[9.5px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5">Jumla ya malipo halisi baada ya makato</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-black flex items-center justify-center text-sm border border-emerald-200 dark:border-emerald-500/20">💵</div>
              </div>

              <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-red-200 dark:border-red-500/20 shadow-xs flex items-center justify-between">
                <div>
                  <div class="text-[10px] font-black uppercase text-red-600 dark:text-red-400 tracking-wider">Mkopo Bado (Deni)</div>
                  <div class="text-xl font-black text-red-600 dark:text-red-400 font-mono mt-0.5">Tsh {{ totalFarmerLoanBalance.toLocaleString() }}</div>
                  <div class="text-[9.5px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5">Salio la deni la mikopo</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-black flex items-center justify-center text-sm border border-red-200 dark:border-red-500/20">💳</div>
              </div>
            </div>

          </div>

          <!-- RIGHT COLUMN: Interactive Tabs & Processing Tree (8 cols) -->
          <div class="lg:col-span-8 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-emerald-100 shadow-2xs flex flex-col space-y-5">
            
            <!-- Tabs Navigation Bar -->
            <div class="flex items-center gap-2 border-b border-emerald-100 pb-3">
              <button 
                @click="profileTab = 'batches'" 
                :class="profileTab === 'batches' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-400 hover:bg-emerald-100 dark:bg-emerald-500/20'" 
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center gap-2"
              >
                <span>📦 Mzigo & Processing Tree</span>
              </button>
              <button 
                @click="profileTab = 'loans'" 
                :class="profileTab === 'loans' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-400 hover:bg-emerald-100 dark:bg-emerald-500/20'" 
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center gap-2"
              >
                <span>💰 Mikopo</span>
              </button>
              <button 
                @click="profileTab = 'sales'" 
                :class="profileTab === 'sales' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-400 hover:bg-emerald-100 dark:bg-emerald-500/20'" 
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center gap-2"
              >
                <span>🏷️ Mauzo & Settlement</span>
              </button>
            </div>

            <!-- TAB 1: BATCHES & SERVICE PROCESSING TREE -->
            <div v-if="profileTab === 'batches'" class="space-y-5">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-50">Orodha ya Mizigo na Huduma Za Kinu</h3>
                <div class="flex gap-2">
                  <button @click="openApplyServiceModal" class="px-3.5 py-1.5 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 text-xs font-bold rounded-xl transition flex items-center gap-1">
                    <span>+ Huduma Mpya</span>
                  </button>
                  <button @click="openIntakeModal" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1">
                    <span>+ Pokea Mpya</span>
                  </button>
                </div>
              </div>

              <!-- Batches List with Transformed Locking and Active Output Product Buttons -->
              <div class="space-y-4">
                <div v-if="topLevelFarmerBatches.length === 0" class="text-center text-slate-400 py-12 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                  Hakuna shehena yoyote iliyopokelewa kwenye database.
                </div>

                <div v-for="b in topLevelFarmerBatches" :key="b.id" class="p-4 bg-emerald-50/40 dark:bg-emerald-900/40 border border-emerald-200/80 dark:border-emerald-700/50 rounded-2xl space-y-3.5 shadow-2xs hover:border-emerald-300 dark:border-emerald-500/30 transition">
                  <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-extrabold text-sm flex items-center justify-center shadow-xs flex-shrink-0">
                        📦
                      </div>
                      <div>
                        <div class="flex items-center gap-2 flex-wrap">
                          <span class="font-black text-slate-900 dark:text-slate-50 font-mono text-sm">{{ b.batch_code }}</span>
                          
                          <!-- SINGLE SOLID STATUS BADGE ("Neno moja solid tu") -->
                          <span v-if="b.status === 'transformed'" class="px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-purple-600 text-white uppercase shadow-2xs">
                            🔒 TRANSFORMED
                          </span>
                          <span v-else-if="b.status === 'sold' || (parseFloat(b.current_weight_mt || b.current_weight || 0) <= 0)" class="px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-rose-600 text-white uppercase shadow-2xs">
                            🏷️ IMEUZWA
                          </span>
                          <span v-else class="px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-emerald-600 text-white uppercase shadow-2xs">
                            🟢 GHALANI
                          </span>

                          <span v-if="b.is_collateral" class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-amber-500 text-white shadow-2xs">
                            🔒 Dhamana
                          </span>
                        </div>

                        <!-- SOLID SINGLE-LINE SUMMARY -->
                        <div class="text-xs text-slate-600 dark:text-slate-300 font-medium mt-1 flex flex-wrap items-center gap-2">
                          <span>Zao: <strong class="text-slate-900 dark:text-slate-50 font-bold">{{ b.crop_type }}</strong> {{ b.variety ? '(' + b.variety + ')' : '' }}</span>
                          <span class="text-slate-300">•</span>
                          <span>Intake: <strong class="text-slate-900 dark:text-slate-50 font-extrabold">{{ ((parseFloat(b.initial_weight_mt || b.current_weight_mt || 0)) * 1000).toLocaleString() }} Kg</strong></span>
                          <span class="text-slate-300">•</span>
                          <span class="px-2 py-0.5 rounded-md bg-amber-100/90 dark:bg-amber-900/50 text-amber-950 dark:text-amber-400 text-[10.5px] font-black border border-amber-300/80 flex items-center gap-1 shadow-2xs">
                            {{ formatStorageDaysBadge(b.created_at, b.status, b.updated_at) }}
                            <span class="text-amber-800 dark:text-amber-400 font-bold">({{ formatDate(b.created_at) }})</span>
                          </span>
                          <span v-if="(parseFloat(b.initial_weight_mt || 0)) > (parseFloat(b.current_weight_mt || 0))" class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 text-[10px] font-black">
                            Umeuzwa: {{ Math.max(0, ((parseFloat(b.initial_weight_mt || 0)) - (parseFloat(b.current_weight_mt || 0))) * 1000).toLocaleString() }} Kg
                          </span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                      <div class="text-right">
                        <div class="text-sm font-black" :class="(parseFloat(b.current_weight_mt || b.current_weight || 0) <= 0) ? 'text-slate-400 line-through' : 'text-emerald-700 dark:text-emerald-400'">
                          {{ (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000).toLocaleString() }} Kg
                        </div>
                        <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Ghalani</div>
                      </div>

                      <!-- ACTION BUTTONS -->
                      <button 
                        v-if="b.status !== 'transformed' && b.status !== 'sold' && (parseFloat(b.current_weight_mt || b.current_weight || 0) > 0)"
                        @click="openApplyServiceForBatch(b)" 
                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center gap-1 cursor-pointer"
                        title="Bonyeza hapa kupanga huduma kwenye mzigo huu"
                      >
                        <span>+ Panga Huduma</span>
                      </button>

                      <!-- Icon-Only Toggle Accordion Button -->
                      <button 
                        @click="toggleBatchAccordion(b.id)" 
                        :title="isBatchExpanded(b.id) ? 'Funga Maelezo' : 'Fungua Maelezo'"
                        class="p-2 text-emerald-800 dark:text-emerald-400 hover:text-emerald-950 dark:text-emerald-400 bg-emerald-100/70 dark:bg-emerald-900/50 hover:bg-emerald-200/90 rounded-xl transition flex items-center justify-center w-8 h-8 shadow-2xs cursor-pointer"
                      >
                        <svg class="w-4 h-4 transform transition-transform duration-200" :class="isBatchExpanded(b.id) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                      </button>
                    </div>
                  </div>

                  <!-- COLLAPSIBLE TREE SECTION (KUFUNGUA/KUFUNGA WORKFLOW TREE) -->
                  <div v-if="isBatchExpanded(b.id)" class="space-y-4 pt-3 border-t border-emerald-100 animate-fadeIn">
                    
                    <div class="pl-6 relative space-y-3">
                      <div class="absolute left-3 top-2 bottom-3 w-0.5 bg-emerald-400"></div>

                      <!-- 1. SERVICES PIPELINE SECTION -->
                      <div v-if="getBatchServices(b).length > 0" class="space-y-2.5">
                        <div class="text-[10.5px] font-black text-emerald-900 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1">
                          ⚙️ Huduma Zilizopangwa Ghalani (Services Pipeline):
                        </div>

                        <div v-for="(s, sIdx) in getBatchServices(b)" :key="s.id" class="relative pl-4">
                          <div class="absolute -left-3 top-4 w-3 h-0.5 bg-emerald-400"></div>

                          <!-- SERVICE ITEM CARD -->
                          <div class="p-3 bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-500/20 rounded-xl text-xs shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                              <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 font-extrabold text-[11px] flex items-center justify-center shadow-2xs flex-shrink-0">{{ sIdx + 1 }}</span>
                              <div>
                                <span class="font-black text-slate-900 dark:text-slate-50 text-xs block">{{ s.service_name || s.type || 'Huduma ya Kinu' }}</span>
                                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                  <span v-if="s.rate || s.unit_price">Bei: Tsh {{ parseFloat(s.rate || s.unit_price || 0).toLocaleString() }} / {{ s.unit || 'Kg' }}</span>
                                  <span v-if="s.quantity">| Kiwango: {{ s.quantity }} {{ s.unit || 'Kg' }}</span>
                                </div>
                              </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-auto">
                              <div class="text-right mr-1">
                                <div class="text-xs font-black text-emerald-700 dark:text-emerald-400">Tsh {{ parseFloat(s.fee_amount || s.cost || 0).toLocaleString() }}</div>
                                <div class="text-[9.5px] text-slate-400 font-bold uppercase">Jumla ya Ada</div>
                              </div>
                              <button v-if="s.status !== 'completed' && b.status !== 'transformed'" @click="openCompleteServiceModal(b, s)" class="px-2.5 py-1 bg-amber-100 dark:bg-amber-500/20 hover:bg-amber-200 text-amber-900 dark:text-amber-400 border border-amber-300 rounded-lg text-[10px] font-black uppercase tracking-wider cursor-pointer flex items-center gap-1 transition shadow-2xs" title="Bonyeza hapa kukamilisha huduma hii">
                                <span>⚙️ INAENDELEA</span>
                                <span class="text-[9px] font-bold text-amber-700 dark:text-amber-400">(Kamilisha)</span>
                              </button>
                              <span v-else-if="s.status === 'completed'" class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30 rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1 shadow-2xs">
                                <span>✅ IMEKAMILIKA</span>
                              </span>
                              <span v-else class="px-2.5 py-1 bg-amber-100 dark:bg-amber-500/20 text-amber-900 dark:text-amber-400 border border-amber-300 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                ⚙️ INAENDELEA
                              </span>
                              <button v-if="s.status !== 'completed'" @click="deleteAssignedService(b, s)" class="px-2 py-1 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20 rounded-lg text-[10px] font-bold transition shadow-2xs cursor-pointer" title="Futa Huduma & Rejelea Mzigo">
                                🗑️ Futa
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- 2. MATOKEO YA TRANSFORMATION (PRODUCTS ZILIZOZALISHWA — RENDERED ONCE) -->
                      <div v-if="getBatchChildren(b).length > 0" class="pt-3 border-t border-dashed border-emerald-200 dark:border-emerald-500/20 space-y-2.5">
                        <div class="text-[10.5px] font-black text-teal-900 dark:text-teal-400 uppercase tracking-wider flex items-center gap-1.5">
                          <span>🌿 Processing Tree Outcome (Product Zilizozalishwa Kutokana na Transformation):</span>
                        </div>

                        <div v-for="child in getBatchChildren(b)" :key="child.id" class="relative pl-4 space-y-2">
                          <div class="absolute -left-3 top-4 w-3 h-0.5 bg-teal-400"></div>

                          <!-- CHILD PRODUCT CARD -->
                          <div class="p-3 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-teal-300 rounded-xl flex items-center justify-between text-xs shadow-2xs">
                            <div class="flex items-center gap-2.5">
                              <span class="text-teal-700 dark:text-teal-400 font-black text-base">🌾</span>
                              <div>
                                <div class="font-black text-slate-900 dark:text-slate-50 text-xs flex items-center gap-1.5 flex-wrap">
                                  <span>{{ child.crop_type }}</span>
                                  <span class="px-1.5 py-0.5 bg-teal-100 text-teal-800 dark:text-teal-400 text-[9.5px] font-black rounded uppercase">Result Product</span>
                                  <span v-if="child.status === 'sold' || parseFloat(child.current_weight_mt || 0) <= 0" class="px-2 py-0.5 bg-rose-600 text-white text-[9.5px] font-black rounded-lg uppercase shadow-2xs">🏷️ IMEUZWA</span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono flex items-center gap-2 mt-0.5">
                                  <span>Code: {{ child.batch_code }}</span>
                                  <span>•</span>
                                  <span class="font-extrabold text-teal-900 dark:text-teal-400 bg-teal-100/90 px-2 py-0.5 rounded-md border border-teal-200 dark:border-teal-700/50">
                                    {{ formatStorageDaysBadge(child.created_at, child.status, child.updated_at) }}
                                  </span>
                                </div>
                              </div>
                            </div>
                            <div class="flex items-center gap-3">
                              <div v-if="child.status !== 'sold' && parseFloat(child.current_weight_mt || 0) > 0" class="font-black text-emerald-800 dark:text-emerald-400 text-sm">
                                {{ (parseFloat(child.current_weight_mt || 0) * 1000).toLocaleString() }} Kg
                              </div>
                              <div v-else class="font-black text-rose-600 text-sm font-mono">
                                0 Kg
                              </div>
                              <button v-if="child.status !== 'sold' && parseFloat(child.current_weight_mt || 0) > 0" @click="openApplyServiceForChild(child)" class="px-2.5 py-1 bg-teal-700 hover:bg-teal-800 text-white text-[11px] font-bold rounded-lg shadow-2xs flex items-center gap-1 cursor-pointer transition">
                                <span>+ Huduma ya {{ child.crop_type }}</span>
                              </button>
                              <button v-if="child.status !== 'sold' && parseFloat(child.current_weight_mt || 0) > 0" @click="revertTransformationFromChild(b, child)" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold rounded-lg shadow-2xs flex items-center gap-1 cursor-pointer transition" title="Futa matokeo haya ya transformation na urudishe mpunga">
                                <span>🗑️ Futa Zao / Revert</span>
                              </button>
                            </div>
                          </div>

                          <!-- SUB-SERVICES ASSIGNED SPECIFICALLY TO THIS CHILD PRODUCT -->
                          <div v-if="getBatchServices(child).length > 0" class="pl-4 space-y-2 border-l-2 border-dashed border-teal-400 ml-4">
                            <div v-for="cs in getBatchServices(child)" :key="cs.id" class="p-2.5 bg-white dark:bg-slate-900 border border-teal-200 dark:border-teal-700/50 rounded-xl text-xs shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                              <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-teal-100 text-teal-800 dark:text-teal-400 font-black text-[10px] flex items-center justify-center flex-shrink-0">⚙️</span>
                                <div>
                                  <span class="font-extrabold text-slate-900 dark:text-slate-50 text-xs block">{{ cs.service_name || cs.type || 'Huduma ya Product' }}</span>
                                  <div class="text-[10.5px] font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span>Bei: Tsh {{ getServiceRate(cs).toLocaleString() }} / {{ cs.unit || 'Kg' }}</span>
                                    <span>| Kiwango: {{ getServiceQuantity(cs, child).toLocaleString() }} {{ cs.unit || 'Kg' }}</span>
                                  </div>
                                </div>
                              </div>
                              <div class="flex items-center gap-2 self-end sm:self-auto">
                                <div class="text-right mr-1">
                                  <div class="text-xs font-black text-teal-800 dark:text-teal-400">
                                    Tsh {{ calculateServiceTotalFee(cs, child).toLocaleString() }}
                                  </div>
                                  <div class="text-[9px] text-slate-400 font-bold uppercase">Jumla ya Ada</div>
                                </div>
                                <button v-if="cs.status !== 'completed'" @click="openCompleteServiceModal(child, cs)" class="px-2 py-0.5 bg-amber-100 dark:bg-amber-500/20 hover:bg-amber-200 text-amber-900 dark:text-amber-400 border border-amber-300 rounded-md text-[9.5px] font-black uppercase cursor-pointer flex items-center gap-1 transition shadow-2xs" title="Bonyeza hapa kukamilisha huduma hii">
                                  <span>⚙️ INAENDELEA</span>
                                  <span class="text-[8.5px] font-bold text-amber-700 dark:text-amber-400">(Kamilisha)</span>
                                </button>
                                <span v-else class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30 rounded-md text-[9.5px] font-black uppercase flex items-center gap-1 shadow-2xs">
                                  <span>✅ IMEKAMILIKA</span>
                                </span>
                                <button v-if="cs.status !== 'completed'" @click="deleteAssignedService(child, cs)" class="px-1.5 py-0.5 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20 rounded-md text-[9.5px] font-bold transition shadow-2xs cursor-pointer">
                                  🗑️ Futa
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- EMPTY STATE -->
                      <div v-if="getBatchServices(b).length === 0 && getBatchChildren(b).length === 0" class="text-xs text-slate-400 italic pl-4 py-1">
                        Hakuna huduma za kinu wala matawi ya ziada kwenye batch hii.
                      </div>

                    </div>

                  </div>

                </div>
              </div>
            </div>

            <!-- TAB 2: LOANS WORKFLOW -->
            <div v-if="profileTab === 'loans'" class="space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-50">Historia ya Mikopo na Dhamana</h3>
                <button @click="modals.newLoan = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  <span>Omba Mkopo Mpya</span>
                </button>
              </div>

              <!-- Loans Table -->
              <div class="border border-emerald-100 rounded-2xl overflow-hidden shadow-2xs">
                <table class="w-full text-left text-xs">
                  <thead class="bg-emerald-50/80 dark:bg-emerald-900/40 text-emerald-950 dark:text-emerald-400 font-extrabold border-b border-emerald-100 uppercase text-[10px]">
                    <tr>
                      <th class="py-2.5 px-3">Tarehe</th>
                      <th class="py-2.5 px-3">Dhamana (Collateral)</th>
                      <th class="py-2.5 px-3">Kiasi cha Mkopo</th>
                      <th class="py-2.5 px-3">Salio la Mkopo</th>
                      <th class="py-2.5 px-3">Hali</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 font-medium">
                    <tr v-if="farmerLoans.length === 0" class="text-center text-slate-400">
                      <td colspan="5" class="py-6">Hakuna historia ya mikopo iliyotolewa.</td>
                    </tr>
                    <tr v-for="l in farmerLoans" :key="l.id" class="hover:bg-slate-50 dark:bg-slate-950 transition">
                      <td class="py-2 px-3 text-slate-600 dark:text-slate-300 font-mono font-semibold">{{ formatDate(l.created_at) }}</td>
                      <td class="py-2 px-3 font-bold text-emerald-800 dark:text-emerald-400 font-mono">{{ l.collateral_batch?.batch_code || l.collateral_batch_id || 'Dhamana ya Mazao' }}</td>
                      <td class="py-2 px-3 font-bold text-slate-900 dark:text-slate-50">Tsh {{ parseFloat(l.principal_amount || 0).toLocaleString() }}</td>
                      <td class="py-2 px-3 font-black" :class="parseFloat(l.current_balance || l.remaining_balance || 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-500'">
                        Tsh {{ parseFloat(l.current_balance || l.remaining_balance || 0).toLocaleString() }}
                      </td>
                      <td class="py-2 px-3 whitespace-nowrap">
                        <span :class="parseFloat(l.current_balance || l.remaining_balance || 0) <= 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/20'" class="px-2 py-0.5 rounded-md text-[10px] font-black border uppercase whitespace-nowrap inline-flex items-center gap-1">
                          {{ parseFloat(l.current_balance || l.remaining_balance || 0) <= 0 ? '✅ IMEKATWA' : '⚙️ IPO HAI' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- TAB 3: SETTLEMENT & SALES DASHBOARD -->
            <div v-if="profileTab === 'sales'" class="space-y-4">
              <!-- Header & Action Button -->
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-50">Historia ya Mauzo & Settlement za Mkulima</h3>
                <button @click="openNewSaleModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  <span>+ Fanya Mauzo Mpya</span>
                </button>
              </div>

              <!-- FINANCIAL SUMMARY CARDS BANNER FOR FARMER SALES -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/80 p-3.5 rounded-2xl shadow-2xs">
                  <div class="text-[10px] font-black text-slate-500 dark:text-slate-500 dark:text-slate-400 uppercase tracking-wider">1. Jumla Thamani ya Mauzo (Gross)</div>
                  <div class="text-base font-black text-slate-900 dark:text-slate-50 font-mono mt-1">Tsh {{ totalFarmerGrossSales.toLocaleString() }}</div>
                  <div class="text-[9.5px] text-slate-500 dark:text-slate-400 font-medium">Thamani yote kabla ya makato</div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/80 p-3.5 rounded-2xl shadow-2xs">
                  <div class="text-[10px] font-black text-slate-500 dark:text-slate-500 dark:text-slate-400 uppercase tracking-wider">2. Makato Yote (Deductions)</div>
                  <div class="text-base font-black text-red-600 dark:text-red-400 font-mono mt-1">Tsh {{ totalFarmerDeductions.toLocaleString() }}</div>
                  <div class="text-[9.5px] text-slate-500 dark:text-slate-400 font-medium">Kinu, Ukaushaji & Mikopo</div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-500/30 p-3.5 rounded-2xl shadow-2xs">
                  <div class="text-[10px] font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">3. Pato Safi la Mkulima (Net Payout)</div>
                  <div class="text-base font-black text-emerald-700 dark:text-emerald-400 font-mono mt-1">Tsh {{ totalFarmerNetPayout.toLocaleString() }}</div>
                  <div class="text-[9.5px] text-emerald-700 dark:text-emerald-400 font-medium">Jumla ya malipo aliyochukua mkononi</div>
                </div>
              </div>

              <!-- Sales History Table -->
              <div class="border border-emerald-100 rounded-2xl overflow-hidden shadow-2xs">
                <table class="w-full text-left text-xs">
                  <thead class="bg-emerald-50/80 dark:bg-emerald-900/40 text-emerald-950 dark:text-emerald-400 font-extrabold border-b border-emerald-100 uppercase text-[10px]">
                    <tr>
                      <th class="py-2.5 px-3">Tarehe</th>
                      <th class="py-2.5 px-3">Invoice / Risiti</th>
                      <th class="py-2.5 px-3">Mnunuzi (Buyer)</th>
                      <th class="py-2.5 px-3">Thamani Ghafi</th>
                      <th class="py-2.5 px-3">Makato</th>
                      <th class="py-2.5 px-3">Malipo (Payout)</th>
                      <th class="py-2.5 px-3">Hali</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 font-medium">
                    <tr v-if="farmerSettlements.length === 0" class="text-center text-slate-400">
                      <td colspan="7" class="py-6">Hakuna historia ya mauzo yaliyokamilishwa.</td>
                    </tr>
                    <tr v-for="st in farmerSettlements" :key="st.id" class="hover:bg-slate-50 dark:bg-slate-950 transition">
                      <td class="py-2 px-3 text-slate-600 dark:text-slate-300 font-mono font-semibold">{{ formatDate(st.created_at || st.settled_at) }}</td>
                      <td class="py-2 px-3 font-bold text-emerald-800 dark:text-emerald-400 font-mono">{{ st.invoice?.invoice_number || ('SETT-' + st.id) }}</td>
                      <td class="py-2 px-3 font-bold text-slate-800 dark:text-slate-100">{{ st.invoice?.buyer?.name || 'Mnunuzi wa Jumla' }}</td>
                      <td class="py-2 px-3 font-bold text-slate-900 dark:text-slate-50">Tsh {{ parseFloat(st.gross_amount || 0).toLocaleString() }}</td>
                      <td class="py-2 px-3 font-bold text-red-600 dark:text-red-400">Tsh {{ parseFloat(st.total_deductions || 0).toLocaleString() }}</td>
                      <td class="py-2 px-3 font-black text-emerald-700 dark:text-emerald-400">Tsh {{ parseFloat(st.net_payout || 0).toLocaleString() }}</td>
                      <td class="py-2 px-3 whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30 uppercase whitespace-nowrap inline-flex items-center gap-1">
                          ✅ {{ st.payment_status || 'SETTLED' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

            </div>

          </div>

        </div>
      </div>
    </div>

    <!-- MODAL 1: SAJILI HUDUMA MPYA (Assign Service - ONLY Active Non-Transformed Batches Allowed!) -->
    <div v-if="modals.applyService" class="fixed inset-0 z-[90] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Sajili Huduma ya Kinu (Assign Service)</h3>
          <button @click="modals.applyService = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-3.5 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <div>
            <label class="block mb-1 font-bold">Chagua Shehena (Batch/Product Active) *</label>
            <select v-model="serviceForm.batch_id" class="w-full p-2.5 border rounded-xl font-bold">
              <option value="">Chagua batch active...</option>
              <option v-for="b in activeNonTransformedBatches" :key="b.id" :value="b.id">{{ b.batch_code }} ({{ b.crop_type }} - {{ (parseFloat(b.current_weight_mt||b.current_weight||0)*1000) }} Kg)</option>
            </select>
            <span class="text-[10.5px] text-amber-700 dark:text-amber-400 mt-1 block">⚠️ Batches zilizo-transformed (zilizotumika kikamilifu) haziruhusiwi kupangiwa huduma mpya.</span>
          </div>
          <div>
            <label class="block mb-1 font-bold">Chagua Huduma Iliyosajiliwa (Huduma za Zao la {{ selectedBatchForService ? selectedBatchForService.crop_type : 'Mzigo Uliochaguliwa' }}) *</label>
            <select v-model="serviceForm.service_id" @change="onServiceCatalogSelect" class="w-full p-2.5 bg-emerald-50/50 dark:bg-emerald-900/40 border border-emerald-300 dark:border-emerald-500/30 rounded-xl font-extrabold text-emerald-950 dark:text-emerald-400">
              <option value="">Chagua huduma inayohusika na zao hili...</option>
              <option v-for="s in filteredCatalogServices" :key="s.id" :value="s.id">
                {{ s.name_sw }} — Tsh {{ parseFloat(s.rate).toLocaleString() }} / {{ s.unit }} ({{ s.crop_type || 'Zote' }})
              </option>
            </select>
            <span v-if="selectedBatchForService && filteredCatalogServices.length === 0" class="text-[10.5px] text-amber-700 dark:text-amber-400 mt-1 block font-bold">
              ⚠️ Hakuna huduma iliyochujwa kwa zao hili. (Tafadhali sajili huduma ya {{ selectedBatchForService.crop_type }} kwenye Orodha ya Huduma).
            </span>
          </div>

          <div v-if="serviceForm.service_id" class="p-3 bg-emerald-50/60 dark:bg-emerald-900/40 rounded-2xl border border-emerald-200 dark:border-emerald-500/20 space-y-1">
            <label class="block font-extrabold text-emerald-950 dark:text-emerald-400 text-xs">Bei ya Kitengo / Rate (Tsh) *</label>
            <div class="flex items-center gap-2">
              <input v-model.number="serviceForm.rate" type="number" placeholder="Ingiza bei ya leo..." class="w-full p-2.5 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-500/30 rounded-xl font-black text-emerald-950 dark:text-emerald-400 text-sm"/>
              <span class="text-xs font-black text-emerald-800 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-500/20 px-3 py-2.5 rounded-xl border border-emerald-200 dark:border-emerald-500/20 uppercase whitespace-nowrap">/ {{ serviceForm.unit || 'Kg' }}</span>
            </div>
            <span class="text-[10.5px] text-slate-500 dark:text-slate-400 block font-semibold pt-0.5">
              💡 Bei hii imetoka kwenye Catalog. Unaweza kuibadilisha hapa ikiwa bei imebadilika leo.
            </span>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="modals.applyService = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl">Ghairi</button>
            <button @click="submitApplyService" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Panga Huduma</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: OMBA MKOPO MPYA (New Loan Request with Strict Collateral & 50% Value Limit) -->
    <div v-if="modals.newLoan" class="fixed inset-0 z-[90] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <div>
            <h3 class="text-base font-extrabold">Omba Mkopo Mpya wa Mkulima</h3>
            <p class="text-[10.5px] text-emerald-200 font-medium">Bila Riba (0% Interest) — Dhamana ya Mazao Ghalani</p>
          </div>
          <button @click="modals.newLoan = false" class="text-emerald-200 hover:text-white p-1 cursor-pointer">✕</button>
        </div>
        <div class="p-6 space-y-3.5 text-xs font-semibold text-slate-700 dark:text-slate-200">
          
          <div v-if="activeNonTransformedBatches.length === 0" class="p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-300 text-amber-950 dark:text-amber-400 rounded-2xl space-y-1">
            <div class="font-extrabold text-xs flex items-center gap-1.5 text-amber-900 dark:text-amber-400">
              <span>⚠️ Hawezi Kukopeshwa!</span>
            </div>
            <p class="text-[11px] leading-relaxed font-medium text-amber-800 dark:text-amber-400">
              Mkulima huyu hana mzigo wowote ghalani (au mazao yake yote yameuzwa). Mfumo hauruhusu kutoa mkopo bila kuwa na akiba ya mzigo ghalani kama dhamana.
            </p>
          </div>

          <template v-else>
            <div>
              <label class="block mb-1 font-bold">Chagua Mazao ya Dhamana (Collateral Batch) *</label>
              <select v-model="loanForm.collateral_batch_id" class="w-full p-2.5 bg-emerald-50/50 dark:bg-emerald-900/40 border border-emerald-300 dark:border-emerald-500/30 rounded-xl font-extrabold text-emerald-950 dark:text-emerald-400">
                <option value="">Chagua batch ya dhamana ghalani...</option>
                <option v-for="b in activeNonTransformedBatches" :key="b.id" :value="b.id">
                  {{ b.batch_code }} — {{ b.crop_type }} ({{ (parseFloat(b.current_weight_mt||b.current_weight||0)*1000).toLocaleString() }} Kg)
                </option>
              </select>
            </div>

            <!-- Calculated Max Limit Card -->
            <div v-if="selectedCollateralBatch" class="p-3.5 bg-emerald-50/80 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl space-y-1.5">
              <div class="flex justify-between items-center text-xs">
                <span class="text-slate-600 dark:text-slate-300 font-bold">Akiba ya Mzigo Ghalani:</span>
                <span class="font-mono font-black text-emerald-900 dark:text-emerald-400">
                  {{ (parseFloat(selectedCollateralBatch.current_weight_mt||0)*1000).toLocaleString() }} Kg
                </span>
              </div>
              <div class="flex justify-between items-center text-xs border-t border-emerald-200/80 dark:border-emerald-700/50 pt-1.5">
                <span class="text-emerald-950 dark:text-emerald-400 font-extrabold">Kikomo cha Juu cha Mkopo (Max 50% ya Thamani):</span>
                <span class="font-mono font-black text-emerald-700 dark:text-emerald-400 text-sm">
                  Tsh {{ maxLoanLimit.toLocaleString() }}
                </span>
              </div>
              <span class="text-[10px] text-emerald-800 dark:text-emerald-400 font-medium block pt-0.5">
                ℹ️ Riba: <strong>0% (Bila Riba)</strong>. Kiasi kitakatwa kikamilifu kwenye mauzo yajayo.
              </span>
            </div>

            <div>
              <label class="block mb-1 font-bold">Kiasi cha Mkopo Unachoomba (Tsh) *</label>
              <input 
                v-model.number="loanForm.amount" 
                type="number" 
                :max="maxLoanLimit"
                placeholder="Ingiza kiasi cha mkopo..." 
                class="w-full p-2.5 bg-white dark:bg-slate-900 border rounded-xl font-black text-slate-900 dark:text-slate-50 text-sm"
                :class="loanForm.amount > maxLoanLimit ? 'border-red-500 text-red-600 dark:text-red-400 focus:ring-red-500' : 'border-slate-300 dark:border-slate-600'"
              />
              <span v-if="loanForm.amount > maxLoanLimit" class="text-[10.5px] text-red-600 dark:text-red-400 font-bold mt-1 block">
                ⚠️ Kiasi hiki kinazidi kikomo cha 50% cha mzigo ghalani (Tsh {{ maxLoanLimit.toLocaleString() }}).
              </span>
            </div>

            <div>
              <label class="block mb-1 font-bold">Tarehe ya Kulipa (Due Date) *</label>
              <input v-model="loanForm.due_date" type="date" class="w-full p-2.5 border border-slate-300 dark:border-slate-600 rounded-xl font-bold"/>
            </div>
          </template>

          <div class="flex justify-end gap-2 pt-2">
            <button @click="modals.newLoan = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl cursor-pointer">Ghairi</button>
            <button 
              v-if="activeNonTransformedBatches.length > 0"
              @click="submitNewLoan" 
              :disabled="loanForm.amount > maxLoanLimit || !loanForm.collateral_batch_id || loanForm.amount <= 0"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-xs cursor-pointer transition"
            >
              Tuma Ombi
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 3: KAMILISHA HUDUMA (Crop Transformation & Tree Building with Strict Validations) -->
    <div v-if="modals.completeService" class="fixed inset-0 z-[90] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Kamilisha Huduma & Batch Transformation</h3>
          <button @click="modals.completeService = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <div class="p-3 bg-emerald-900 text-white rounded-xl border border-emerald-700">
            <div class="font-extrabold text-sm text-emerald-300">Mzigo Mama: {{ selectedBatchForComplete.batch_code }} ({{ selectedBatchForComplete.crop_type }})</div>
            <div class="text-xs text-emerald-100 mt-0.5">Uzito wa Sasa: {{ currentBatchWeightKg }} Kg</div>
          </div>

          <div>
            <label class="block mb-1 font-bold">Je, Zao limebadilika? (Mf. Mpunga kuwa Mchele)</label>
            <select v-model="completeForm.has_changed" class="w-full p-2.5 border rounded-xl font-bold">
              <option value="yes">Ndiyo, Zao Limebadilika (Crop Transformation)</option>
              <option value="no">Hapana, Zao ni Lilelile</option>
            </select>
          </div>

          <div v-if="completeForm.has_changed === 'yes'" class="space-y-4 p-4 bg-emerald-50/60 dark:bg-emerald-900/40 rounded-2xl border border-emerald-200 dark:border-emerald-500/20">
            <!-- Primary Output Product -->
            <div class="space-y-2">
              <div class="font-extrabold text-emerald-950 dark:text-emerald-400 text-xs flex items-center gap-1.5">
                <span>🌾 1. Zao Kuu Lililopatikana (Primary Output Product)</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <div>
                  <label class="block mb-1 font-bold">Zao Jipya *</label>
                  <select v-model="completeForm.output_crop" class="w-full p-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                    <option v-for="c in cropsList" :key="c" :value="c">{{ c }}</option>
                  </select>
                </div>
                <div>
                  <label class="block mb-1 font-bold">Kiasi *</label>
                  <input v-model.number="completeForm.output_quantity" type="number" placeholder="e.g. 30" class="w-full p-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold"/>
                </div>
                <div>
                  <label class="block mb-1 font-bold">Kipimo / Unit *</label>
                  <select v-model="completeForm.output_unit" class="w-full p-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                    <option v-for="u in unitsList" :key="u.name" :value="u.name">{{ u.name }}</option>
                  </select>
                </div>
              </div>
              <div class="text-[11px] text-emerald-800 dark:text-emerald-400 font-bold text-right">
                Uzito wa Zao Kuu: {{ getUnitKg(completeForm.output_unit, completeForm.output_quantity || 0) }} Kg
              </div>
            </div>

            <!-- Mandatory By-Product Toggle -->
            <div class="pt-3 border-t border-emerald-200 dark:border-emerald-500/20 space-y-2">
              <label class="block mb-1 font-extrabold text-slate-800 dark:text-slate-100 text-xs">
                🌱 2. Je, kuna zao la ziada (By-Product) litapatikana? *
              </label>
              <select v-model="completeForm.has_byproduct" class="w-full p-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-900 dark:text-slate-50">
                <option value="no">Hapana, Hakuna Zao la Ziada</option>
                <option value="yes">Ndiyo, Kuna Zao la Ziada (e.g. Pumba, Manamane, Taka)</option>
              </select>
            </div>

            <!-- Conditional By-Product Fields -->
            <div v-if="completeForm.has_byproduct === 'yes'" class="p-3 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-500/30 rounded-2xl space-y-2 animate-fadeIn">
              <div class="font-extrabold text-teal-900 dark:text-teal-400 text-xs">Maelezo ya Zao la Ziada (By-Product Details)</div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <div>
                  <label class="block mb-1 font-bold">Zao la Ziada *</label>
                  <select v-model="completeForm.byproduct_crop" class="w-full p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                    <option value="Pumba">Pumba ya Mchele</option>
                    <option value="Pumba ya Mahindi">Pumba ya Mahindi</option>
                    <option value="Manamane">Manamane / Broken Rice</option>
                    <option v-for="c in cropsList" :key="c" :value="c">{{ c }}</option>
                  </select>
                </div>
                <div>
                  <label class="block mb-1 font-bold">Kiasi cha Ziada *</label>
                  <input v-model.number="completeForm.byproduct_quantity" type="number" placeholder="e.g. 15" class="w-full p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold"/>
                </div>
                <div>
                  <label class="block mb-1 font-bold">Kipimo / Unit *</label>
                  <select v-model="completeForm.byproduct_unit" class="w-full p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                    <option v-for="u in unitsList" :key="u.name" :value="u.name">{{ u.name }}</option>
                  </select>
                </div>
              </div>
              <div class="text-[11px] text-teal-800 dark:text-teal-400 font-bold text-right">
                Uzito wa Zao la Ziada: {{ getUnitKg(completeForm.byproduct_unit, completeForm.byproduct_quantity || 0) }} Kg
              </div>
            </div>

            <div class="p-2.5 bg-emerald-950 text-emerald-200 rounded-xl text-xs font-mono font-bold flex items-center justify-between">
              <span>Jumla ya Patokazi:</span>
              <span class="text-white text-sm font-black">{{ totalOutputWeightKg }} Kg</span>
            </div>

            <div v-if="totalOutputWeightKg > currentBatchWeightKg" class="p-2 bg-red-100 dark:bg-red-500/20 text-red-800 dark:text-red-400 rounded-lg text-[11px] font-bold border border-red-200 dark:border-red-500/20">
              ⚠️ Tahadhari: Jumla ya uzito uliopatikana ({{ totalOutputWeightKg }} Kg) unazidi uzito wa mzigo mama ({{ currentBatchWeightKg }} Kg)!
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button @click="modals.completeService = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl">Ghairi</button>
            <button @click="submitCompleteService" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Thibitisha (Complete)</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 4: ADD FARMER MODAL -->
    <div v-if="modals.addFarmer" class="fixed inset-0 z-[80] bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Sajili Mkulima Mpya</h3>
          <button @click="modals.addFarmer = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-3 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <div>
            <label class="block mb-1 font-bold">Jina Kamili *</label>
            <input v-model="newFarmerForm.name" type="text" placeholder="e.g. Bakari Juma" class="w-full p-2.5 border rounded-xl"/>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block mb-1 font-bold">Namba ya Simu *</label>
              <input v-model="newFarmerForm.phone" type="text" placeholder="0754123456" class="w-full p-2.5 border rounded-xl"/>
            </div>
            <div>
              <label class="block mb-1 font-bold">NIDA Number</label>
              <input v-model="newFarmerForm.national_id" type="text" class="w-full p-2.5 border rounded-xl"/>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block mb-1 font-bold">Mkoa *</label>
              <input v-model="newFarmerForm.region" type="text" class="w-full p-2.5 border rounded-xl"/>
            </div>
            <div>
              <label class="block mb-1 font-bold">Wilaya</label>
              <input v-model="newFarmerForm.district" type="text" class="w-full p-2.5 border rounded-xl"/>
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button @click="modals.addFarmer = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl">Ghairi</button>
            <button @click="submitAddFarmer" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Sajili Mkulima</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 5: INTAKE MODAL (Pokea Mpya Database) -->
    <div v-if="modals.intake" class="fixed inset-0 z-[90] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Sajili Upokeaji Mpya (Intake)</h3>
          <button @click="modals.intake = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-3 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <div>
            <label class="block mb-1 font-bold">Aina ya Zao *</label>
            <select v-model="intakeForm.crop_type" class="w-full p-2.5 border rounded-xl font-bold">
              <option v-for="c in cropsList" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block mb-1 font-bold">Kiasi *</label>
              <input v-model.number="intakeForm.quantity" type="number" class="w-full p-2.5 border rounded-xl font-bold"/>
            </div>
            <div>
              <label class="block mb-1 font-bold">Vipimo / Units *</label>
              <select v-model="intakeForm.unit" class="w-full p-2.5 border rounded-xl font-bold">
                <option v-for="u in unitsList" :key="u.name" :value="u.name">{{ u.name }}</option>
              </select>
            </div>
          </div>
          <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-400 rounded-xl font-extrabold text-center text-xs space-y-1">
            <div>Uzito wa Jumla: <span class="text-sm font-black text-emerald-950 dark:text-emerald-400">{{ calculatedIntakeWeight.toLocaleString() }} Kg</span> ({{ (calculatedIntakeWeight / 1000).toFixed(2) }} Tani)</div>
            <div class="text-[10.5px] font-bold text-emerald-700 dark:text-emerald-400 bg-white/80 dark:bg-slate-900/80 py-0.5 px-2 rounded-lg border border-emerald-200 dark:border-emerald-500/20 inline-block shadow-2xs">
              ({{ intakeForm.quantity || 0 }} {{ intakeForm.unit }} @ 1 {{ intakeForm.unit }} = {{ getUnitKg ? getUnitKg(intakeForm.unit, 1) : 100 }} Kg)
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button @click="modals.intake = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl">Ghairi</button>
            <button @click="submitIntake" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Sajili (Save)</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 4: PRINTABLE FARMER PROFILE STATEMENT & RECEIPT PDF -->
    <div v-if="modals.farmerReceipt" class="fixed inset-0 z-[100] bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-emerald-950 text-white">
          <div class="flex items-center gap-2">
            <span class="text-lg">🖨️</span>
            <h3 class="text-sm font-extrabold">Taarifa ya Mkulima (Farmer Statement & Storage Voucher)</h3>
          </div>
          <div class="flex items-center gap-2">
            <button @click="triggerFarmerPrint" class="px-3.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold rounded-lg transition flex items-center gap-1">
              <span>🖨️ Print / Save as PDF</span>
            </button>
            <button @click="modals.farmerReceipt = false" class="text-slate-400 hover:text-white p-1">✕</button>
          </div>
        </div>

        <div id="printableFarmerReceiptArea" class="p-8 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-50 font-sans space-y-6 max-h-[75vh] overflow-y-auto print:p-0 print:max-h-none">
          <!-- Letterhead Header -->
          <div class="flex items-start justify-between border-b-2 border-emerald-700 pb-4">
            <div>
              <div class="text-2xl font-black tracking-tight text-emerald-800 dark:text-emerald-400">GALANOKI MILLING & WAREHOUSE</div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold leading-relaxed">
                Agro-Inventory Management System<br/>
                Industrial Complex Block A & B, Tanzania<br/>
                Helpline: +255 700 000 100 | info@galanoki.co.tz
              </div>
            </div>
            <div class="text-right">
              <div class="text-xl font-black text-slate-900 dark:text-slate-50 tracking-tight">FARMER VOUCHER</div>
              <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">
                <strong>Voucher #:</strong> FVR-{{ selectedFarmer.farmer_code }}<br/>
                <strong>Date:</strong> {{ new Date().toLocaleDateString() }}
              </div>
            </div>
          </div>

          <!-- Farmer Bio -->
          <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="p-3 bg-emerald-50/60 dark:bg-emerald-900/40 rounded-2xl border border-emerald-200 dark:border-emerald-500/20 space-y-1">
              <div class="text-[10px] font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">Taarifa za Mkulima:</div>
              <div class="text-sm font-black text-slate-900 dark:text-slate-50">{{ selectedFarmer.name }}</div>
              <div class="text-slate-600 dark:text-slate-300">Simu: {{ selectedFarmer.phone || 'N/A' }} | Code: {{ selectedFarmer.farmer_code }}</div>
              <div class="text-slate-500 dark:text-slate-400">Eneo: {{ selectedFarmer.ward || 'Usa River' }}, {{ selectedFarmer.district || 'Arumeru' }}, {{ selectedFarmer.region || 'Arusha' }}</div>
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
              <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Jumla ya Mzigo Ghalani:</span>
                <span class="font-black text-emerald-800 dark:text-emerald-400">{{ totalFarmerStockKg.toLocaleString() }} Kg</span>
              </div>
              <div class="flex justify-between border-t border-slate-200 dark:border-slate-700 pt-1 mt-1">
                <span class="text-slate-500 dark:text-slate-400">Jumla ya Deni la Mikopo:</span>
                <span class="font-black text-red-600 dark:text-red-400">Tsh {{ totalActiveLoansBalance.toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- Table of Batches -->
          <div>
            <div class="text-xs font-black text-slate-900 dark:text-slate-50 uppercase tracking-wider mb-2">Orodha ya Shehena (Batches):</div>
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="border-b-2 border-slate-900 font-extrabold text-slate-900 dark:text-slate-50">
                  <th class="py-2">Batch Code</th>
                  <th class="py-2">Zao</th>
                  <th class="py-2 text-right">Uzito wa Sasa</th>
                  <th class="py-2 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 font-semibold text-slate-700 dark:text-slate-200">
                <tr v-for="b in farmerBatches" :key="b.id">
                  <td class="py-2.5 font-mono font-bold text-emerald-800 dark:text-emerald-400">{{ b.batch_code }}</td>
                  <td class="py-2.5">{{ b.crop_type }}</td>
                  <td class="py-2.5 text-right font-mono font-bold">{{ (parseFloat(b.current_weight_mt||b.current_weight||0)*1000).toLocaleString() }} Kg</td>
                  <td class="py-2.5 text-center font-extrabold text-[11px] uppercase">{{ b.status }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Official Signatures -->
          <div class="pt-8 border-t border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-8 text-[11px] text-slate-500 dark:text-slate-400">
            <div>
              <div class="font-bold text-slate-800 dark:text-slate-100 mb-6">Sahihi ya Mkulima:</div>
              <div class="border-b border-slate-300 dark:border-slate-600 w-3/4 mb-1"></div>
              <div>{{ selectedFarmer.name }}</div>
            </div>

            <div>
              <div class="font-bold text-slate-800 dark:text-slate-100 mb-6">Sahihi & Muhuri wa Kinu:</div>
              <div class="border-b border-slate-300 dark:border-slate-600 w-3/4 mb-1"></div>
              <div>Galanoki Warehouse Manager</div>
            </div>
          </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
          <button @click="modals.farmerReceipt = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs">Funga</button>
          <button @click="triggerFarmerPrint" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5">
            <span>🖨️ Chapisha / Hifadhi PDF</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: FANYA MAUZO MPYA (RECORD NEW SALE & SETTLEMENT) -->
    <div v-if="modals.newSale" class="fixed inset-0 z-[90] bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <div class="flex items-center gap-2">
            <span class="text-xl">🏷️</span>
            <h3 class="text-base font-extrabold">Fanya Mauzo & Settlement za Mkulima</h3>
          </div>
          <button @click="modals.newSale = false" class="text-emerald-200 hover:text-white p-1 cursor-pointer">✕</button>
        </div>

        <div class="p-6 space-y-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
          
          <!-- Uchambuzi wa Makato Card -->
          <div class="bg-emerald-50/70 dark:bg-emerald-900/40 p-3.5 rounded-2xl border border-emerald-200 dark:border-emerald-500/20 space-y-2">
            <div class="font-extrabold text-emerald-950 dark:text-emerald-400 text-xs flex items-center justify-between border-b border-emerald-200 dark:border-emerald-500/20 pb-1.5">
              <span>Uchambuzi wa Makato ya Mkulima</span>
              <span class="text-emerald-700 dark:text-emerald-400 font-mono text-[11px]">{{ selectedFarmer.name }}</span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-[11px] pt-1">
              <div>
                <span class="text-slate-500 dark:text-slate-400 block">Ada Hifadhi:</span>
                <strong class="text-slate-900 dark:text-slate-50 font-bold">Tsh {{ (settlementForm.storage_fee || 45000).toLocaleString() }}</strong>
              </div>
              <div>
                <span class="text-slate-500 dark:text-slate-400 block">Ada Kinu:</span>
                <strong class="text-slate-900 dark:text-slate-50 font-bold">Tsh {{ (settlementForm.milling_fee || 120000).toLocaleString() }}</strong>
              </div>
              <div>
                <span class="text-slate-500 dark:text-slate-400 block">Den la Mkopo:</span>
                <strong class="text-red-600 dark:text-red-400 font-bold">Tsh {{ totalFarmerLoanBalance.toLocaleString() }}</strong>
              </div>
            </div>
          </div>

          <!-- Input Fields -->
          <div class="space-y-3">
            <div>
              <label class="block font-extrabold text-slate-900 dark:text-slate-50 mb-1">Chagua Batch ya Kuuza *</label>
              <select v-model="settlementForm.batch_id" @change="onSettlementBatchChange" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-600 rounded-xl font-bold text-slate-900 dark:text-slate-50">
                <option value="">Chagua batch ya kuuza...</option>
                <option v-for="b in activeNonTransformedBatches" :key="b.id" :value="b.id">{{ b.batch_code }} - {{ b.crop_type }} ({{ (parseFloat(b.current_weight_mt||b.current_weight||0)*1000).toLocaleString() }} Kg Ghalani)</option>
              </select>
            </div>

            <!-- Aina ya Mauzo Toggle -->
            <div>
              <label class="block font-extrabold text-slate-900 dark:text-slate-50 mb-1">Aina ya Mauzo *</label>
              <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <button 
                  type="button"
                  @click="settlementForm.sale_type = 'full'; onSaleTypeChange();"
                  :class="settlementForm.sale_type === 'full' ? 'bg-emerald-600 text-white shadow-xs font-black' : 'text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-200/60 dark:bg-slate-700/60'"
                  class="py-2 px-3 rounded-lg text-xs transition cursor-pointer flex items-center justify-center gap-1"
                >
                  <span>📦 Kuuza Mzigo Wote (100%)</span>
                </button>
                <button 
                  type="button"
                  @click="settlementForm.sale_type = 'partial'"
                  :class="settlementForm.sale_type === 'partial' ? 'bg-emerald-600 text-white shadow-xs font-black' : 'text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-200/60 dark:bg-slate-700/60'"
                  class="py-2 px-3 rounded-lg text-xs transition cursor-pointer flex items-center justify-center gap-1"
                >
                  <span>⚖️ Kuuza Kidogo Kidogo</span>
                </button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-extrabold text-slate-900 dark:text-slate-50 mb-1">Uzito wa Kuuza (Kg) *</label>
                <input 
                  v-model.number="settlementForm.sold_weight_kg" 
                  :disabled="settlementForm.sale_type === 'full'"
                  type="number" 
                  placeholder="Uzito kwa Kg..." 
                  class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-600 rounded-xl font-bold text-slate-900 dark:text-slate-50 disabled:bg-slate-100 dark:bg-slate-800 disabled:text-slate-500 dark:text-slate-400"
                />
                <div v-if="selectedSettlementBatch" class="text-[10.5px] text-slate-500 dark:text-slate-400 font-semibold mt-1 space-y-0.5">
                  <div>Uliopo: <strong class="text-slate-900 dark:text-slate-50 font-bold">{{ availableBatchKg.toLocaleString() }} Kg</strong> | Utakaobaki: <strong class="text-emerald-700 dark:text-emerald-400 font-extrabold">{{ remainingBatchKgAfterSale.toLocaleString() }} Kg</strong></div>
                  <div class="text-amber-900 dark:text-amber-400 font-bold flex items-center gap-1 bg-amber-50 dark:bg-amber-500/10 p-1 rounded border border-amber-200 dark:border-amber-500/20">
                    <span>⏱️ Muda wa Utunzaji: <strong class="text-amber-950 dark:text-amber-400 font-black">Siku {{ calculateStorageDays(selectedSettlementBatch.created_at, selectedSettlementBatch.status, selectedSettlementBatch.updated_at) }}</strong> (Mapokezi: {{ formatDate(selectedSettlementBatch.created_at) }})</span>
                  </div>
                </div>
              </div>
              <div>
                <label class="block font-extrabold text-slate-900 dark:text-slate-50 mb-1">Bei ya Mauzo (Tsh/Kg) *</label>
                <input v-model.number="settlementForm.price_per_kg" type="number" placeholder="e.g. 1800" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-600 rounded-xl font-bold text-slate-900 dark:text-slate-50"/>
              </div>
            </div>

            <div>
              <label class="block font-extrabold text-slate-900 dark:text-slate-50 mb-1">Jina la Mnunuzi (Buyer) *</label>
              <input v-model="settlementForm.buyer_name" type="text" placeholder="Ingiza jina la mnunuzi..." class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-600 rounded-xl font-bold text-slate-900 dark:text-slate-50"/>
            </div>
          </div>

          <!-- Summary Badge -->
          <div class="p-3 bg-slate-900 text-white rounded-2xl flex items-center justify-between text-xs font-bold">
            <div>
              <span class="text-slate-400 block text-[10px]">Malipo Halisi kwa Mkulima (Payout)</span>
              <span class="text-emerald-400 font-black text-sm">Tsh {{ settleNetPayout.toLocaleString() }}</span>
            </div>
            <span class="text-[10.5px] bg-slate-800 text-slate-300 px-2.5 py-1 rounded-lg">Ghafi: Tsh {{ settleGrossSales.toLocaleString() }}</span>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <button @click="modals.newSale = false" class="flex-1 py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-800 dark:text-slate-100 font-bold rounded-xl transition cursor-pointer">
              Ghairi
            </button>
            <button @click="submitSettlement" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl shadow-md transition cursor-pointer">
              Kamilisha Makato & Mauzo
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- CUSTOM ENTERPRISE CONFIRMATION MODAL (No Native Browser Confirm) -->
    <div v-if="confirmModal.show" class="fixed inset-0 z-70 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-800 space-y-0 transform transition-all scale-100">
        <!-- Header -->
        <div class="px-6 py-4 flex items-center justify-between" :class="confirmModal.isDanger ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white' : 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white'">
          <div class="flex items-center gap-2 font-black text-sm sm:text-base">
            <span>{{ confirmModal.title }}</span>
          </div>
          <button @click="confirmModal.show = false" class="text-white/80 hover:text-white p-1 rounded-full hover:bg-white/20 dark:bg-slate-900/20 transition cursor-pointer">✕</button>
        </div>

        <!-- Body Content -->
        <div class="p-6 space-y-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <p class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-relaxed">
            {{ confirmModal.message }}
          </p>

          <div v-if="confirmModal.warningNote" class="p-3.5 rounded-2xl text-xs font-bold leading-relaxed border flex items-start gap-2.5" :class="confirmModal.isDanger ? 'bg-red-50 dark:bg-red-500/10 text-red-900 border-red-200 dark:border-red-500/20' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-900 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20'">
            <span class="text-base leading-none">⚠️</span>
            <div>{{ confirmModal.warningNote }}</div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
          <button 
            @click="confirmModal.show = false" 
            class="px-4 py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200 font-extrabold rounded-xl text-xs transition cursor-pointer"
          >
            {{ confirmModal.cancelText || 'Ghairi' }}
          </button>
          <button 
            @click="executeConfirmAction" 
            :class="confirmModal.isDanger ? 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-md shadow-red-600/20' : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-md shadow-emerald-600/20'"
            class="px-5 py-2.5 font-black rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer"
          >
            <span>{{ confirmModal.confirmText || 'Thibitisha' }}</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAgroMaster } from '../composables/useAgroMaster.js';

const { cropsList, unitsList } = useAgroMaster();

const loading = ref(false);
const loadingProfile = ref(false);
const farmers = ref([]);
const catalogServices = ref([]);
const searchQuery = ref('');
const regionFilter = ref('');
const statusFilter = ref('');
const currentPage = ref(1);
const pageSize = ref(15);

const selectedFarmer = ref({});
const farmerBatches = ref([]);
const farmerLoans = ref([]);
const farmerServices = ref([]);
const farmerSettlements = ref([]);
const isEditingFarmer = ref(false);
const profileTab = ref('batches');

const expandedBatches = ref({});
const selectedBatchForComplete = ref({});

const toast = ref({ show: false, message: '', type: 'success' });

const triggerToast = (msg, type = 'success') => {
  toast.value = { show: true, message: msg, type };
  setTimeout(() => { toast.value.show = false; }, 3500);
};

const modals = ref({
  addFarmer: false,
  profile: false,
  intake: false,
  applyService: false,
  newLoan: false,
  newSale: false,
  completeService: false,
  farmerReceipt: false
});

const confirmModal = ref({
  show: false,
  title: '',
  message: '',
  warningNote: '',
  confirmText: 'Ndiyo, Futa',
  cancelText: 'Ghairi',
  isDanger: true,
  action: null
});

const triggerConfirmModal = (options) => {
  confirmModal.value = {
    show: true,
    title: options.title || 'Uthibitisho',
    message: options.message || '',
    warningNote: options.warningNote || '',
    confirmText: options.confirmText || 'Ndiyo, Futa',
    cancelText: options.cancelText || 'Ghairi',
    isDanger: options.isDanger !== undefined ? options.isDanger : true,
    action: options.onConfirm || null
  };
};

const executeConfirmAction = async () => {
  if (confirmModal.value.action) {
    const act = confirmModal.value.action;
    confirmModal.value.show = false;
    await act();
  } else {
    confirmModal.value.show = false;
  }
};

const triggerFarmerPrint = () => {
  openFarmerPDFReceipt();
};

const openFarmerPDFReceipt = () => {
  if (!selectedFarmer.value || !selectedFarmer.value.name) {
    triggerToast('Chagua mkulima kwanza ili kuchapisha risiti.', 'error');
    return;
  }

  const printWin = window.open('', '_blank');
  if (!printWin) {
    triggerToast('Tafadhali ruhusu Popups kwenye Kivinjari chako ili kuchapisha PDF.', 'error');
    return;
  }

  const f = selectedFarmer.value;
  const batches = farmerBatches.value || [];
  const services = farmerServices.value || [];
  const loans = farmerLoans.value || [];
  const settlements = farmerSettlements.value || [];

  // Totals calculations
  let totalIntakeKg = 0;
  batches.filter(b => !b.parent_batch_id).forEach(b => {
    totalIntakeKg += parseFloat(b.initial_weight_mt || b.current_weight_mt || 0) * 1000;
  });

  const activeStockKg = totalFarmerStockKg.value || 0;

  let totalServiceFees = 0;
  services.forEach(s => {
    let fee = parseFloat(s.fee_amount || s.cost || 0);
    const rate = parseFloat(s.rate || 0);
    const qty = (parseFloat(s.output_weight_mt || s.quantity || 0) * 1000);
    if (fee > 0 && fee < 500 && rate > 0) {
      fee = rate * (qty > 0 ? qty : 1000);
    }
    totalServiceFees += fee;
  });

  let totalLoansIssued = 0;
  let totalLoanBalance = 0;
  loans.forEach(l => {
    totalLoansIssued += parseFloat(l.amount || 0);
    totalLoanBalance += parseFloat(l.current_balance || 0);
  });

  let totalGrossSales = 0;
  let totalDeductions = 0;
  let totalNetPayout = 0;
  settlements.forEach(st => {
    totalGrossSales += parseFloat(st.gross_amount || 0);
    totalDeductions += parseFloat(st.total_deductions || 0);
    totalNetPayout += parseFloat(st.net_payout || 0);
  });

  // 1. Batch Rows
  let batchRows = '';
  batches.forEach((b, idx) => {
    const isChild = !!b.parent_batch_id;
    const isSold = b.status === 'sold' || parseFloat(b.current_weight_mt || 0) <= 0;
    const isTransformed = b.status === 'transformed';
    const initKg = ((b.initial_weight_mt || b.current_weight_mt || 0) * 1000).toLocaleString();
    const currKg = isSold ? '0' : ((b.current_weight_mt || 0) * 1000).toLocaleString();

    let statusBadge = '<span style="color:#047857; font-weight:bold; padding:2px 6px; background:#d1fae5; border-radius:4px; font-size:10px;">🟢 GHALANI</span>';
    if (isSold) {
      statusBadge = '<span style="color:#b91c1c; font-weight:bold; padding:2px 6px; background:#fee2e2; border-radius:4px; font-size:10px;">🏷️ IMEUZWA</span>';
    } else if (isTransformed) {
      statusBadge = '<span style="color:#6d28d9; font-weight:bold; padding:2px 6px; background:#f3e8ff; border-radius:4px; font-size:10px;">🔒 TRANSFORMED</span>';
    }

    batchRows += `
      <tr style="background:${idx % 2 === 0 ? '#ffffff' : '#f8fafc'};">
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">${idx + 1}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; font-family:monospace; font-weight:bold; color:#0f172a;">
          ${b.batch_code || 'BCH-' + b.id} ${isChild ? '<span style="font-size:9px; color:#0d9488; font-weight:normal;">(Tawi)</span>' : ''}
        </td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; font-weight:600;">${b.crop_type}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right;">${initKg} Kg</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right; font-weight:bold; color:${isSold ? '#b91c1c' : '#047857'};">${currKg} Kg</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">${statusBadge}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center; color:#64748b;">${formatDate(b.created_at)}</td>
      </tr>
    `;
  });

  // 2. Service Rows
  let serviceRows = '';
  services.forEach((s, idx) => {
    let fee = parseFloat(s.fee_amount || s.cost || 0);
    const rate = parseFloat(s.rate || 0);
    const qty = (parseFloat(s.output_weight_mt || s.quantity || 0) * 1000);
    if (fee > 0 && fee < 500 && rate > 0) {
      fee = rate * (qty > 0 ? qty : 1000);
    }
    const isDone = s.status === 'completed';

    serviceRows += `
      <tr style="background:${idx % 2 === 0 ? '#ffffff' : '#f8fafc'};">
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">${idx + 1}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; font-family:monospace;">${s.batch_code || '-'}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; font-weight:bold; color:#0f172a;">${s.service_name || s.type}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right;">Tsh ${(parseFloat(s.rate || 0)).toLocaleString()}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right; font-weight:bold; color:#047857;">Tsh ${fee.toLocaleString()}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">
          ${isDone ? '<span style="color:#047857; font-weight:bold; padding:2px 6px; background:#d1fae5; border-radius:4px; font-size:10px;">✅ IMEKAMILIKA</span>' : '<span style="color:#d97706; font-weight:bold; padding:2px 6px; background:#fef3c7; border-radius:4px; font-size:10px;">⚙️ INAENDELEA</span>'}
        </td>
      </tr>
    `;
  });

  // 3. Loan Rows
  let loanRows = '';
  loans.forEach((l, idx) => {
    const amt = parseFloat(l.amount || 0);
    const bal = parseFloat(l.current_balance || 0);
    const isPaid = l.status === 'completed' || bal <= 0;

    loanRows += `
      <tr style="background:${idx % 2 === 0 ? '#ffffff' : '#f8fafc'};">
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">${idx + 1}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">${formatDate(l.created_at)}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right; font-weight:bold;">Tsh ${amt.toLocaleString()}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; font-family:monospace; text-align:center;">${l.collateral_batch?.batch_code || 'BCH-DHAMANA'}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right; font-weight:bold; color:${isPaid ? '#047857' : '#b91c1c'};">Tsh ${bal.toLocaleString()}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">
          ${isPaid ? '<span style="color:#047857; font-weight:bold; padding:2px 6px; background:#d1fae5; border-radius:4px; font-size:10px;">✅ COMPLETED</span>' : '<span style="color:#b91c1c; font-weight:bold; padding:2px 6px; background:#fee2e2; border-radius:4px; font-size:10px;">🔴 INADAIWA</span>'}
        </td>
      </tr>
    `;
  });

  // 4. Settlement Rows
  let settlementRows = '';
  settlements.forEach((st, idx) => {
    const gross = parseFloat(st.gross_amount || 0);
    const ded = parseFloat(st.total_deductions || 0);
    const net = parseFloat(st.net_payout || 0);
    const invNo = st.invoice?.invoice_number || ('INV-' + (st.id ? st.id.toString().substring(0,8) : '001'));
    const buyer = st.invoice?.buyer?.name || 'Mnunuzi wa Jumla';

    settlementRows += `
      <tr style="background:${idx % 2 === 0 ? '#ffffff' : '#f8fafc'};">
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">${idx + 1}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; font-family:monospace; font-weight:bold; color:#0d9488;">${invNo}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">${formatDate(st.created_at)}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; font-weight:600;">${buyer}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right; font-weight:bold;">Tsh ${gross.toLocaleString()}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right; font-weight:bold; color:#b91c1c;">- Tsh ${ded.toLocaleString()}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:right; font-weight:900; color:#047857;">Tsh ${net.toLocaleString()}</td>
        <td style="padding:6px 8px; border:1px solid #e2e8f0; text-align:center;">
          <span style="color:#047857; font-weight:bold; padding:2px 6px; background:#d1fae5; border-radius:4px; font-size:10px;">✅ SETTLED</span>
        </td>
      </tr>
    `;
  });

  const htmlContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Taarifa ya Mkulima - ${f.name}</title>
      <style>
        @page { size: A4 portrait; margin: 10mm; }
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; color: #0f172a; padding: 10px; line-height: 1.4; background: #ffffff; }
        .header-table { width: 100%; border-bottom: 3px solid #047857; padding-bottom: 10px; margin-bottom: 14px; }
        .logo-title { font-size: 20px; font-weight: 900; color: #047857; letter-spacing: -0.5px; margin: 0; }
        .logo-sub { font-size: 10.5px; color: #475569; font-weight: 600; margin-top: 2px; }
        .doc-type { text-align: right; }
        .doc-type-title { font-size: 13px; font-weight: 900; color: #0f172a; text-transform: uppercase; }
        .doc-type-sub { font-size: 9.5px; color: #64748b; font-family: monospace; }
        
        .profile-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; margin-bottom: 14px; display: table; width: 100%; box-sizing: border-box; }
        .profile-col { display: table-cell; width: 50%; vertical-align: top; font-size: 10.5px; }
        .profile-col strong { color: #334155; }

        .summary-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 16px; }
        .summary-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; text-align: center; }
        .summary-title { font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 3px; }
        .summary-val { font-size: 12px; font-weight: 900; color: #0f172a; }

        .section-title { font-size: 11px; font-weight: 800; color: #047857; text-transform: uppercase; border-bottom: 2px solid #10b981; padding-bottom: 3px; margin-top: 16px; margin-bottom: 8px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; }
        table.data-table th { background: #047857; color: #ffffff; padding: 6px 8px; border: 1px solid #047857; text-align: left; font-weight: 800; text-transform: uppercase; }
        table.data-table td { padding: 6px 8px; border: 1px solid #e2e8f0; }

        .sign-section { margin-top: 25px; width: 100%; display: table; }
        .sign-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 20px; }
        .sign-line { border-bottom: 1px solid #94a3b8; margin-top: 35px; margin-bottom: 4px; width: 80%; }
        
        .footer-note { text-align: center; margin-top: 20px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px; }
      </style>
    </head>
    <body>
      <table class="header-table">
        <tr>
          <td>
            <div class="logo-title">🌾 AGROVAULT / KILIMO STORE</div>
            <div class="logo-sub">Ghalanoki Milling, Storage & Agricultural ERP Platform</div>
          </td>
          <td class="doc-type">
            <div class="doc-type-title">TAARIFA YA RIPOTI YA MKULIMA</div>
            <div class="doc-type-sub">Ref: STMT-${f.farmer_code} | Tarehe: ${new Date().toLocaleDateString('sw-TZ')}</div>
          </td>
        </tr>
      </table>

      <div class="profile-card">
        <div class="profile-col">
          <div><strong>Mkulima:</strong> ${f.name}</div>
          <div><strong>Namba ya Mkulima:</strong> ${f.farmer_code || 'N/A'}</div>
          <div><strong>Simu:</strong> ${f.phone || 'N/A'}</div>
          <div><strong>NIDA:</strong> ${f.national_id || 'N/A'}</div>
        </div>
        <div class="profile-col">
          <div><strong>Mkoa / Wilaya:</strong> ${f.region || 'Arusha'} / ${f.district || 'Arumeru'}</div>
          <div><strong>Kata / Kijiji:</strong> ${f.ward || 'USA River'} / ${f.village || 'Kijijini'}</div>
          <div><strong>Hali ya Akaunti:</strong> ${f.status === 'active' ? '🟢 ACTIVE (Ana Mzigo Ghalani)' : '⚪ INACTIVE (Hana Mzigo Ghalani)'}</div>
          <div><strong>Tarehe ya Kutoa PDF:</strong> ${new Date().toLocaleString('sw-TZ')}</div>
        </div>
      </div>

      <!-- SUMMARY CARDS -->
      <table class="summary-table">
        <tr>
          <td class="summary-card" style="border-top:3px solid #047857;">
            <div class="summary-title">Jumla ya Intake</div>
            <div class="summary-val" style="color:#047857;">${totalIntakeKg.toLocaleString()} Kg</div>
          </td>
          <td class="summary-card" style="border-top:3px solid #0d9488;">
            <div class="summary-title">Mzigo Ghalani</div>
            <div class="summary-val" style="color:#0d9488;">${activeStockKg.toLocaleString()} Kg</div>
          </td>
          <td class="summary-card" style="border-top:3px solid #2563eb;">
            <div class="summary-title">Jumla ya Mauzo</div>
            <div class="summary-val" style="color:#2563eb;">Tsh ${totalGrossSales.toLocaleString()}</div>
          </td>
          <td class="summary-card" style="border-top:3px solid #b91c1c;">
            <div class="summary-title">Jumla ya Makato</div>
            <div class="summary-val" style="color:#b91c1c;">Tsh ${totalDeductions.toLocaleString()}</div>
          </td>
          <td class="summary-card" style="border-top:3px solid #059669;">
            <div class="summary-title">Malipo kwa Mkulima</div>
            <div class="summary-val" style="color:#059669;">Tsh ${totalNetPayout.toLocaleString()}</div>
          </td>
          <td class="summary-card" style="border-top:3px solid #d97706;">
            <div class="summary-title">Deni la Mkopo</div>
            <div class="summary-val" style="color:#d97706;">Tsh ${totalLoanBalance.toLocaleString()}</div>
          </td>
        </tr>
      </table>

      <!-- SECTION 1 -->
      <div class="section-title">1. UPOKEAJI WA MAZIGO GHALANI (INTAKE & BATCHES HISTORY)</div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:5%;">#</th>
            <th style="width:20%;">Batch Code</th>
            <th style="width:18%;">Aina ya Zao</th>
            <th style="width:15%; text-align:right;">Uzito wa Intake</th>
            <th style="width:14%; text-align:right;">Uzito wa Sasa</th>
            <th style="width:14%; text-align:center;">Hali ya Mzigo</th>
            <th style="width:14%; text-align:center;">Tarehe</th>
          </tr>
        </thead>
        <tbody>
          ${batchRows || '<tr><td colspan="7" style="text-align:center; padding:10px; color:#94a3b8;">Hakuna mizigo.</td></tr>'}
        </tbody>
      </table>

      <!-- SECTION 2 -->
      <div class="section-title">2. HISTORIA YA ADA ZA HUDUMA ZA KINU & MAKAUSHO</div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:5%;">#</th>
            <th style="width:18%;">Batch Code</th>
            <th style="width:25%;">Jina la Huduma</th>
            <th style="width:18%; text-align:right;">Bei ya Kitengo (Rate)</th>
            <th style="width:18%; text-align:right;">Jumla ya Ada (Tsh)</th>
            <th style="width:16%; text-align:center;">Hali</th>
          </tr>
        </thead>
        <tbody>
          ${serviceRows || '<tr><td colspan="6" style="text-align:center; padding:10px; color:#94a3b8;">Hakuna huduma zilizopangwa.</td></tr>'}
        </tbody>
      </table>

      <!-- SECTION 3 -->
      <div class="section-title">3. HISTORIA YA MIKOPO NA DHAMANA (LOANS STATEMENT)</div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:5%;">#</th>
            <th style="width:15%; text-align:center;">Tarehe</th>
            <th style="width:22%; text-align:right;">Kiasi Kilichopewa</th>
            <th style="width:20%; text-align:center;">Dhamana (Batch)</th>
            <th style="width:22%; text-align:right;">Salio Bado (Deni)</th>
            <th style="width:16%; text-align:center;">Hali</th>
          </tr>
        </thead>
        <tbody>
          ${loanRows || '<tr><td colspan="6" style="text-align:center; padding:10px; color:#94a3b8;">Hakuna mikopo iliyochukuliwa.</td></tr>'}
        </tbody>
      </table>

      <!-- SECTION 4 -->
      <div class="section-title">4. HISTORIA YA MAUZO NA SETTLEMENT (SALES & PAYOUT STATEMENT)</div>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:5%;">#</th>
            <th style="width:15%;">Invoice #</th>
            <th style="width:12%; text-align:center;">Tarehe</th>
            <th style="width:20%;">Mnunuzi</th>
            <th style="width:16%; text-align:right;">Thamani Ghafi</th>
            <th style="width:14%; text-align:right;">Makato Yote</th>
            <th style="width:18%; text-align:right;">Malipo Halisi (Net)</th>
          </tr>
        </thead>
        <tbody>
          ${settlementRows || '<tr><td colspan="7" style="text-align:center; padding:10px; color:#94a3b8;">Hakuna mauzo yaliyofanyika bado.</td></tr>'}
        </tbody>
      </table>

      <!-- SIGNATURES -->
      <div class="sign-section">
        <div class="sign-col">
          <div style="font-weight:bold; color:#334155;">Sahihi ya Mkulima / Mpokeaji:</div>
          <div class="sign-line"></div>
          <div style="font-size:9.5px; color:#64748b;">${f.name} | Simu: ${f.phone || 'N/A'}</div>
        </div>
        <div class="sign-col">
          <div style="font-weight:bold; color:#334155;">Sahihi & Muhuri wa Meneja wa Ghalani:</div>
          <div class="sign-line"></div>
          <div style="font-size:9.5px; color:#64748b;">AgroVault / KilimoStore Warehouse Management</div>
        </div>
      </div>

      <div class="footer-note">
        *** Mfumo wa KilimoStoreMS / AgroVault ERP — Taarifa Hii ni Halisi na Imetolewa Kiotomatiki ***
      </div>

      <script>
        window.onload = function() { window.print(); }
      <\/script>
    </body>
    </html>
  `;

  printWin.document.write(htmlContent);
  printWin.document.close();
};

const editFarmerForm = ref({ name: '', phone: '', national_id: '', region: '', district: '', ward: '', village: '', street: '' });
const newFarmerForm = ref({ name: '', phone: '', national_id: '', region: 'Arusha', district: 'Arumeru' });
const intakeForm = ref({ crop_type: 'Mpunga', quantity: 45, unit: 'Gunia' });
const serviceForm = ref({ batch_id: '', service_id: '' });
const loanForm = ref({ amount: 1500000, due_date: '2026-12-31', collateral_batch_id: '' });
const completeForm = ref({
  type: 'milling',
  has_changed: 'yes',
  output_crop: 'Mchele Grade A',
  output_quantity: 30,
  output_unit: 'Gunia',
  has_byproduct: 'yes',
  byproduct_crop: 'Pumba',
  byproduct_quantity: 12,
  byproduct_unit: 'Kiloba / Roba'
});
const settlementForm = ref({ 
  batch_id: '', 
  sale_type: 'full', 
  buyer_name: 'Mnunuzi wa Jumla / Kiwanda', 
  price_per_kg: 1800, 
  sold_weight_kg: 0, 
  storage_fee: 45000, 
  milling_fee: 120000 
});

const onSettlementBatchChange = () => {
  const b = activeNonTransformedBatches.value.find(item => item.id === settlementForm.value.batch_id);
  if (b) {
    const availKg = parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000;
    settlementForm.value.sold_weight_kg = availKg;
    settlementForm.value.sale_type = 'full';
  }
};

const onSaleTypeChange = () => {
  const b = activeNonTransformedBatches.value.find(item => item.id === settlementForm.value.batch_id);
  if (b && settlementForm.value.sale_type === 'full') {
    settlementForm.value.sold_weight_kg = parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000;
  }
};

const selectedSettlementBatch = computed(() => {
  if (!settlementForm.value.batch_id) return null;
  return activeNonTransformedBatches.value.find(b => String(b.id) === String(settlementForm.value.batch_id)) || null;
});

const availableBatchKg = computed(() => {
  if (!selectedSettlementBatch.value) return 0;
  return parseFloat(selectedSettlementBatch.value.current_weight_mt || selectedSettlementBatch.value.current_weight || 0) * 1000;
});

const remainingBatchKgAfterSale = computed(() => {
  return Math.max(0, availableBatchKg.value - (Number(settlementForm.value.sold_weight_kg) || 0));
});

// Top-level parent batches (without parent_batch_id) to avoid visual card duplication
const topLevelFarmerBatches = computed(() => {
  return farmerBatches.value.filter(b => !b.parent_batch_id);
});

// Active non-transformed & unsold batches filter (Excludes fully sold & 0kg batches)
const activeNonTransformedBatches = computed(() => {
  const map = new Map();
  farmerBatches.value.forEach(b => {
    const bChildren = getBatchChildren(b);
    const availKg = parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000;
    if (b.status !== 'transformed' && b.status !== 'sold' && availKg > 0.01 && bChildren.length === 0) {
      map.set(b.id || b.batch_code, b);
    }
    bChildren.forEach(c => {
      const cChildren = getBatchChildren(c);
      const cAvailKg = parseFloat(c.current_weight_mt || c.current_weight || 0) * 1000;
      if (c.status !== 'transformed' && c.status !== 'sold' && cAvailKg > 0.01 && cChildren.length === 0) {
        map.set(c.id || c.batch_code, c);
      }
    });
  });
  return Array.from(map.values());
});

const selectedBatchForService = computed(() => {
  if (!serviceForm.value.batch_id) return null;
  return activeNonTransformedBatches.value.find(
    b => String(b.id) === String(serviceForm.value.batch_id)
  ) || null;
});

const filteredCatalogServices = computed(() => {
  const batch = selectedBatchForService.value;
  if (!batch) {
    return catalogServices.value;
  }

  // Prevent assigning duplicate services to the same batch
  const existingServices = getBatchServices(batch);
  const existingServiceNames = new Set(
    existingServices.map(s => String(s.service_name || s.type || '').toLowerCase().trim())
  );
  const existingServiceIds = new Set(
    existingServices.map(s => String(s.service_id || s.id))
  );

  const crop = String(batch.crop_type || '').toLowerCase();
  
  return catalogServices.value.filter(s => {
    // 1. DUPLICATE CHECK
    const sNameLower = String(s.name_sw || s.name || '').toLowerCase().trim();
    if (existingServiceIds.has(String(s.id)) || existingServiceNames.has(sNameLower)) {
      return false; // Already assigned to this batch!
    }

    // 2. CROP FILTER
    if (!s.crop_type || s.crop_type === 'Zote' || String(s.crop_type).toLowerCase() === 'all' || s.crop_type === '') {
      return true;
    }
    
    const sCrop = String(s.crop_type).toLowerCase();
    
    if (crop.includes(sCrop) || sCrop.includes(crop)) return true;
    if (crop.includes('mchele') && sCrop.includes('mchele')) return true;
    if (crop.includes('pumba') && sCrop.includes('pumba')) return true;
    if (crop.includes('mpunga') && sCrop.includes('mpunga')) return true;
    if (crop.includes('mahindi') && sCrop.includes('mahindi')) return true;
    if (crop.includes('alizeti') && sCrop.includes('alizeti')) return true;
    
    return false;
  });
});

const getServiceRate = (cs) => {
  if (!cs) return 0;

  // 1. Check registered catalogServices to get true catalog rate
  const foundCatalog = catalogServices.value.find(cat => 
    (cs.service_id && String(cat.id) === String(cs.service_id)) ||
    (cat.name_sw && String(cat.name_sw).toLowerCase().trim() === String(cs.service_name || cs.type || '').toLowerCase().trim())
  );
  if (foundCatalog && foundCatalog.rate !== undefined && foundCatalog.rate !== null && parseFloat(foundCatalog.rate) > 0) {
    return parseFloat(foundCatalog.rate);
  }

  // 2. Direct rate or unit_price properties
  if (cs.rate !== undefined && cs.rate !== null && parseFloat(cs.rate) > 0) {
    return parseFloat(cs.rate);
  }
  if (cs.unit_price !== undefined && cs.unit_price !== null && parseFloat(cs.unit_price) > 0) {
    return parseFloat(cs.unit_price);
  }

  // 3. Fallback
  return parseFloat(cs.fee_amount || cs.fee || cs.cost || 0);
};

const getBatchWeightKg = (batch) => {
  if (!batch) return 0;
  let wMt = parseFloat(batch.current_weight_mt || 0);
  let initMt = parseFloat(batch.initial_weight_mt || 0);

  if (wMt <= 0 || (wMt < 0.05 && initMt >= 0.05)) {
    wMt = initMt;
  }

  if (wMt > 0) {
    return wMt * 1000;
  }

  if (batch.current_weight) {
    const cw = parseFloat(batch.current_weight);
    return cw < 10 && initMt >= 0.05 ? initMt * 1000 : cw;
  }

  if (batch.intake_quantity) {
    return getUnitKg(batch.intake_unit, batch.intake_quantity);
  }
  return 0;
};

const getServiceQuantity = (cs, batch) => {
  const bKg = getBatchWeightKg(batch);
  const unit = String(cs?.unit || 'kg').toLowerCase();

  let q = cs && cs.quantity !== undefined && cs.quantity !== null ? parseFloat(cs.quantity) : 0;
  
  if (q > 0) {
    if (unit.includes('kg') || unit.includes('kilo')) {
      if (q < 10 && bKg >= 100) {
        return bKg;
      }
    }
    return q;
  }

  if (unit.includes('gunia') || unit.includes('bag')) return bKg / 100;
  if (unit.includes('tani') || unit.includes('ton')) return bKg / 1000;
  if (unit.includes('roba') || unit.includes('kiloba')) return bKg / 25;
  return bKg > 0 ? bKg : 1;
};

const calculateServiceTotalFee = (cs, batch) => {
  if (!cs) return 0;
  const rate = getServiceRate(cs);
  const qty = getServiceQuantity(cs, batch);

  if (rate > 0 && qty > 0) {
    return rate * qty;
  }

  const baseFee = parseFloat(cs.fee_amount || cs.fee || cs.cost || 0);
  if (baseFee < 1000 && rate > 0 && qty > 0) {
    return rate * qty;
  }
  return baseFee;
};

const editAssignedService = async (b, s) => {
  if (s.status === 'completed') {
    triggerToast('Huduma iliyokamilika (COMPLETED) haiwezi kuhaririwa kwa usalama wa data!', 'error');
    return;
  }
  const currentRate = getServiceRate(s);
  const newRateStr = prompt(`Hariri Bei ya Unit ya "${s.service_name || s.type}" (Tsh):`, currentRate);
  if (newRateStr === null) return;
  const newRate = parseFloat(newRateStr);
  if (isNaN(newRate) || newRate < 0) {
    triggerToast('Tafadhali ingiza namba sahihi.', 'error');
    return;
  }

  const qty = getServiceQuantity(s, b);
  const newFee = newRate * qty;

  try {
    const res = await fetch(`/api/v1/services/${s.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        fee_amount: newFee,
        fee: newFee,
        rate: newRate
      })
    });
    if (!res.ok) {
      s.fee_amount = newFee;
      s.rate = newRate;
    }
    await openFarmerProfile(selectedFarmer.value.id);
    triggerToast('Bei ya Huduma Imerekebishwa Kikamilifu! ✏️');
  } catch (e) {
    s.fee_amount = newFee;
    s.rate = newRate;
    triggerToast('Bei ya Huduma Imerekebishwa! ✏️');
  }
};

const revertTransformationFromChild = (parentBatch, targetChild) => {
  if (!parentBatch) return;

  const children = getBatchChildren(parentBatch);

  // MASHARTI: Check if ANY sub-service on Mchele or Pumba has ALREADY been completed!
  let completedSubService = null;
  for (const child of children) {
    const services = getBatchServices(child);
    const completed = services.find(s => s.status === 'completed');
    if (completed) {
      completedSubService = { service: completed, child };
      break;
    }
  }

  if (completedSubService) {
    triggerToast(
      `Huwezi kurudisha nyuma: Huduma ya ${completedSubService.child.crop_type} tayari imekamilika! ⚠️`,
      'error'
    );
    return;
  }

  const origQty = parentBatch.intake_quantity || Math.round((parentBatch.initial_weight_mt || 0) * 10);
  const origUnit = parentBatch.intake_unit || 'Gunia';
  const origKg = ((parentBatch.initial_weight_mt || 0) * 1000).toLocaleString();
  const childrenNames = children.map(c => c.crop_type).join(' na ');

  triggerConfirmModal({
    title: '⚠️ Uthibitisho wa Revert ya Transformation',
    message: `Je, una uhakika unataka kufuta matokeo ya transformation na kurudisha mchakato mzima nyuma?`,
    warningNote: `Tendo hili litarudisha zao mama (${parentBatch.crop_type}) kuwa kiasi cha asili cha ${origQty} ${origUnit} (${origKg} Kg) na kufuta mazao yote yaliyozalishwa (${childrenNames}).`,
    confirmText: 'Ndiyo, Futa na Revert',
    cancelText: 'Ghairi',
    isDanger: true,
    onConfirm: async () => {
      try {
        // 1. Delete all sub-services on children
        for (const child of children) {
          const childServices = getBatchServices(child);
          for (const cs of childServices) {
            try {
              const jType = String(cs.type || 'milling').toLowerCase();
              await fetch(`/api/v1/processing/${jType}/${cs.id}`, { method: 'DELETE' });
              await fetch(`/api/v1/services/${cs.id}`, { method: 'DELETE' });
            } catch (e) {}
          }
        }

        // 2. Delete all parent transformation services
        const parentServices = getBatchServices(parentBatch);
        for (const ps of parentServices) {
          try {
            const jType = String(ps.type || 'milling').toLowerCase();
            await fetch(`/api/v1/processing/${jType}/${ps.id}`, { method: 'DELETE' });
            await fetch(`/api/v1/services/${ps.id}`, { method: 'DELETE' });
          } catch (e) {}
        }

        // 3. Delete all child outcome batches
        for (const child of children) {
          try {
            await fetch(`/api/v1/batches/${child.id}`, { method: 'DELETE' });
          } catch (e) {}
        }

        // 4. Revert parent batch weight & status
        const restoredWeight = parentBatch.initial_weight_mt || parentBatch.current_weight_mt || 0;
        parentBatch.status = 'received';
        parentBatch.current_weight_mt = restoredWeight;

        await fetch(`/api/v1/batches/${parentBatch.id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            status: 'received',
            current_weight_mt: restoredWeight,
            current_weight: restoredWeight
          })
        });

        await openFarmerProfile(selectedFarmer.value.id);
        await fetchFarmers();
        triggerToast(`Transformation Imefutwa: ${parentBatch.crop_type} umerudi (${origQty} ${origUnit})! 🔄`);
      } catch (e) {
        triggerToast('Imefeli kurudisha nyuma transformation.', 'error');
      }
    }
  });
};

const deleteAssignedService = (b, s) => {
  if (s.status === 'completed') {
    triggerToast('Huduma iliyokamilika (COMPLETED) haiwezi kufutwa kwa usalama wa mahesabu!', 'error');
    return;
  }

  // If this is a top-level transformation service, delegate to revertTransformationFromChild with strict checks
  const isTransformation = b && !b.parent_batch_id && (b.status === 'transformed' || getBatchChildren(b).length > 0) && (s.type === 'milling' || String(s.service_name || '').toLowerCase().includes('kukoboa'));

  if (isTransformation) {
    revertTransformationFromChild(b, null);
    return;
  }

  // Otherwise, it is a sub-service on a child product (or regular batch service). Delete ONLY this sub-service.
  triggerConfirmModal({
    title: '🗑️ Uthibitisho wa Kufuta Huduma',
    message: `Je, una uhakika unataka kufuta huduma ya "${s.service_name || s.type}"?`,
    warningNote: `Inafuta ada ya huduma hii pekee. Zao la ${b ? b.crop_type : 'ghalani'} litalobaki salama ili uweze kupanga huduma nyingine.`,
    confirmText: 'Ndiyo, Futa Huduma',
    cancelText: 'Ghairi',
    isDanger: true,
    onConfirm: async () => {
      try {
        const jobType = String(s.type || 'milling').toLowerCase();
        await fetch(`/api/v1/processing/${jobType}/${s.id}`, { method: 'DELETE' });
        try { await fetch(`/api/v1/services/${s.id}`, { method: 'DELETE' }); } catch(e) {}

        if (b && b.services) {
          b.services = b.services.filter(srv => srv.id !== s.id);
        }
        farmerServices.value = farmerServices.value.filter(srv => srv.id !== s.id);

        await openFarmerProfile(selectedFarmer.value.id);
        await fetchFarmers();
        triggerToast('Huduma Imefutwa na Unaweza Kupanga Huduma Nyingine! 🔄');
      } catch (e) {
        triggerToast('Imefeli kufuta huduma.', 'error');
      }
    }
  });
};

const fetchFarmers = async () => {
  loading.value = true;
  try {
    const res = await fetch('/api/v1/farmers');
    if (res.ok) {
      const data = await res.json();
      farmers.value = Array.isArray(data) ? data : (data.data || []);
    }
  } catch (e) {
    console.error('Error fetching farmers:', e);
  } finally {
    loading.value = false;
  }
};

const defaultRegisteredServices = [
  { id: 1, name_sw: 'Kukoboa (Sembe/Mpunga)', category: 'milling', rate: 70.00, unit: 'kg' },
  { id: 2, name_sw: 'Kusogeza kwenye kinu', category: 'milling', rate: 300.00, unit: 'gunia' },
  { id: 3, name_sw: 'Kuanika mpunga (Drying)', category: 'drying', rate: 1000.00, unit: 'gunia' },
  { id: 4, name_sw: 'Kugiredi (Grading)', category: 'grading', rate: 8.00, unit: 'kg' },
  { id: 5, name_sw: 'Kudoloti (Color sorting)', category: 'milling', rate: 22.00, unit: 'kg' },
  { id: 6, name_sw: 'Kuanika + Kuchanganya', category: 'drying', rate: 1500.00, unit: 'gunia' },
  { id: 7, name_sw: 'Kuchanganya Mchele na Mafuta', category: 'milling', rate: 2.50, unit: 'kg' },
  { id: 8, name_sw: 'Kupanga stoko (Warehouse)', category: 'storage', rate: 700.00, unit: 'gunia' },
  { id: 9, name_sw: 'Wafanyakazi (Labor)', category: 'milling', rate: 1000.00, unit: 'gunia' }
];

const fetchServicesCatalog = async () => {
  try {
    const res = await fetch('/api/v1/services');
    if (res.ok) {
      const data = await res.json();
      if (Array.isArray(data) && data.length > 0) {
        catalogServices.value = data;
      } else {
        catalogServices.value = defaultRegisteredServices;
      }
    } else {
      catalogServices.value = defaultRegisteredServices;
    }
  } catch (e) {
    catalogServices.value = defaultRegisteredServices;
  }
};

const availableRegions = computed(() => {
  const set = new Set();
  farmers.value.forEach(f => { if (f.region) set.add(f.region); });
  return Array.from(set);
});

const filteredFarmers = computed(() => {
  return farmers.value.filter(f => {
    const q = searchQuery.value.toLowerCase();
    const matchQ = !q || (f.name && f.name.toLowerCase().includes(q)) || (f.farmer_code && f.farmer_code.toLowerCase().includes(q)) || (f.phone && f.phone.includes(q));
    const matchR = !regionFilter.value || f.region === regionFilter.value;
    const matchS = !statusFilter.value || (f.status || 'active').toLowerCase() === statusFilter.value.toLowerCase();
    return matchQ && matchR && matchS;
  });
});

const paginatedFarmers = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  return filteredFarmers.value.slice(start, start + pageSize.value);
});

const expandedBatchIds = ref(new Set());

const isBatchExpanded = (batchId) => {
  if (batchId === undefined || batchId === null) return false;
  return expandedBatchIds.value.has(String(batchId));
};

const toggleBatchAccordion = (batchId) => {
  if (batchId === undefined || batchId === null) return;
  const strId = String(batchId);
  const newSet = new Set(expandedBatchIds.value);
  if (newSet.has(strId)) {
    newSet.delete(strId);
  } else {
    newSet.add(strId);
  }
  expandedBatchIds.value = newSet;
};

const closeAllSubModals = () => {
  modals.value.applyService = false;
  modals.value.completeService = false;
  modals.value.intake = false;
  modals.value.newLoan = false;
  modals.value.newSale = false;
  modals.value.farmerReceipt = false;
};

const getBatchServices = (b) => {
  if (b.services && b.services.length > 0) return b.services;
  return farmerServices.value.filter(s => s.batch_code === b.batch_code);
};

const getBatchChildren = (b) => {
  if (b.children && b.children.length > 0) return b.children;
  return farmerBatches.value.filter(child => child.parent_batch_id === b.id);
};

const closeProfileModal = () => {
  modals.value = {
    addFarmer: false,
    profile: false,
    intake: false,
    applyService: false,
    newLoan: false,
    newSale: false,
    completeService: false,
    farmerReceipt: false
  };
};

const openFarmerProfile = async (id) => {
  modals.value.profile = true;
  loadingProfile.value = true;
  isEditingFarmer.value = false;
  profileTab.value = 'batches';

  try {
    const res = await fetch(`/api/v1/farmers/${id}`);
    if (res.ok) {
      const data = await res.json();
      selectedFarmer.value = data.farmer || {};
      editFarmerForm.value = { ...selectedFarmer.value };
      
      const rawBatches = data.batches || [];
      farmerBatches.value = rawBatches;
      farmerLoans.value = data.loans || [];
      farmerServices.value = data.services || [];
      farmerSettlements.value = data.settlements || [];

      // Closed (collapsed) by default
      expandedBatchIds.value = new Set();
    }
  } catch (e) {
    console.error('Error fetching farmer profile:', e);
    triggerToast('Kosa la mtandao wakati wa kupakia profile.', 'error');
  } finally {
    loadingProfile.value = false;
  }
};

const openNewSaleModal = () => {
  closeAllSubModals();
  settlementForm.value.sale_type = 'full';
  if (activeNonTransformedBatches.value.length > 0) {
    const firstB = activeNonTransformedBatches.value[0];
    settlementForm.value.batch_id = firstB.id;
    settlementForm.value.sold_weight_kg = (parseFloat(firstB.current_weight_mt || firstB.current_weight || 0) * 1000);
  } else {
    settlementForm.value.batch_id = '';
    settlementForm.value.sold_weight_kg = 0;
  }
  modals.value.newSale = true;
};

const totalFarmerStockKg = computed(() => {
  let totalKg = 0;
  farmerBatches.value.forEach(b => {
    if (b.status === 'received' || b.status === 'stored') {
      totalKg += getBatchWeightKg(b);
    }
  });
  return totalKg;
});

const totalFarmerLoanBalance = computed(() => {
  let bal = 0;
  farmerLoans.value.forEach(l => {
    bal += parseFloat(l.current_balance || l.remaining_balance || 0);
  });
  return bal;
});

const openApplyServiceModal = () => {
  closeAllSubModals();
  if (activeNonTransformedBatches.value.length > 0) {
    serviceForm.value.batch_id = activeNonTransformedBatches.value[0].id;
  } else {
    serviceForm.value.batch_id = '';
  }
  serviceForm.value.service_id = '';
  modals.value.applyService = true;
};

const openIntakeModal = () => {
  closeAllSubModals();
  intakeForm.value = { crop_type: 'Mpunga', quantity: 45, unit: 'Gunia' };
  modals.value.intake = true;
};

const openApplyServiceForBatch = (b) => {
  closeAllSubModals();
  if (b.status === 'transformed') {
    triggerToast('Mzigo huu umeshabadilishwa (transformed) kuwa zao lingine.', 'error');
    return;
  }
  if (b.status === 'sold') {
    triggerToast('Mzigo huu tayari umeuzwa wote! Huwezi kupanga huduma tena.', 'error');
    return;
  }
  serviceForm.value.batch_id = b.id;
  serviceForm.value.service_id = '';
  modals.value.applyService = true;
};

const openApplyServiceForChild = (child) => {
  closeAllSubModals();
  if (child.status === 'sold' || parseFloat(child.current_weight_mt || 0) <= 0) {
    triggerToast('Mzigo huu tayari umeshauzwa wote! Huwezi kupanga huduma tena.', 'error');
    return;
  }
  const childChildren = getBatchChildren(child);
  if (childChildren.length > 0) {
    triggerToast('Mzigo huu umeshabadilishwa (transformed) kuwa zao lingine la ziada.', 'error');
    return;
  }
  serviceForm.value.batch_id = child.id;
  serviceForm.value.service_id = '';
  modals.value.applyService = true;
};

const currentBatchWeightKg = computed(() => {
  const b = selectedBatchForComplete.value;
  if (!b) return 0;
  return (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000);
});

const getUnitKg = (unitName, qty = 1) => {
  if (!unitName) return (qty || 0) * 1;
  const nameLower = String(unitName).toLowerCase();
  const list = unitsList.value || [];
  const unitObj = list.find(u => 
    u && u.name && (u.name.toLowerCase() === nameLower || nameLower.includes(u.name.toLowerCase()) || u.name.toLowerCase().includes(nameLower))
  );
  if (unitObj && unitObj.kg) {
    return (qty || 0) * unitObj.kg;
  }
  if (nameLower.includes('tani') || nameLower.includes('ton')) return (qty || 0) * 1000;
  if (nameLower.includes('gunia') || nameLower.includes('bag')) return (qty || 0) * 100;
  if (nameLower.includes('roba') || nameLower.includes('kiloba') || nameLower.includes('sack')) return (qty || 0) * 25;
  return (qty || 0) * 1;
};

const totalOutputWeightKg = computed(() => {
  const outKg = getUnitKg(completeForm.value.output_unit, completeForm.value.output_quantity || 0);
  let byKg = 0;
  if (completeForm.value.has_byproduct === 'yes') {
    byKg = getUnitKg(completeForm.value.byproduct_unit, completeForm.value.byproduct_quantity || 0);
  }
  return outKg + byKg;
});

const suggestedOutputCrops = computed(() => {
  const crop = (selectedBatchForComplete.value.crop_type || '').toLowerCase();
  if (crop.includes('mpunga')) {
    return ['Mchele Grade A', 'Mchele Grade B', 'Mchele Safi (Kyela)'];
  }
  if (crop.includes('mahindi')) {
    return ['Unga wa Mahindi (Sembe)', 'Unga wa Mahindi (Dona)', 'Mahindi Yaliyokobolewa'];
  }
  return ['Mchele Grade A', 'Mchele Grade B', 'Unga wa Mahindi', 'Maharagwe Safi'];
});

const saveEditFarmer = async () => {
  try {
    const res = await fetch(`/api/v1/farmers/${selectedFarmer.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(editFarmerForm.value)
    });
    if (res.ok) {
      Object.assign(selectedFarmer.value, editFarmerForm.value);
      await fetchFarmers();
      isEditingFarmer.value = false;
      triggerToast('Taarifa za Mkulima Zimehifadhiwa kwenye Database! ✓');
    }
  } catch (e) {
    triggerToast('Imefeli kuhifadhi taarifa.', 'error');
  }
};

const calculatedIntakeWeight = computed(() => {
  const qty = intakeForm.value.quantity || 0;
  const selectedUnit = (intakeForm.value.unit || '').toLowerCase();
  const unitObj = unitsList.value.find(u => u.name.toLowerCase().includes(selectedUnit) || selectedUnit.includes(u.name.toLowerCase()));
  const ratio = unitObj ? (unitObj.kg || 1) : (selectedUnit.includes('tani') ? 1000 : (selectedUnit.includes('gunia') ? 100 : 1));
  return qty * ratio;
});

const submitIntake = async () => {
  if (!intakeForm.value.quantity || intakeForm.value.quantity <= 0) {
    triggerToast('Weka kiasi sahihi zaidi ya 0!', 'error');
    return;
  }
  const weightMt = calculatedIntakeWeight.value / 1000;
  try {
    const res = await fetch('/api/v1/batches', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        farmer_id: selectedFarmer.value.id,
        crop_type: intakeForm.value.crop_type,
        initial_weight_mt: weightMt,
        intake_quantity: intakeForm.value.quantity,
        intake_unit: intakeForm.value.unit
      })
    });
    if (res.ok) {
      modals.value.intake = false;
      await openFarmerProfile(selectedFarmer.value.id);
      await fetchFarmers();
      triggerToast('Upokeaji Mpya Umesajiliwa kwenye Database! 📦');
    }
  } catch (e) {
    triggerToast('Imefeli kusajili upokeaji.', 'error');
  }
};

const onServiceCatalogSelect = () => {
  const selected = catalogServices.value.find(s => s.id === serviceForm.value.service_id);
  if (selected) {
    serviceForm.value.service_type = (selected.category || 'milling').toLowerCase();
    serviceForm.value.service_name = selected.name_sw;
    serviceForm.value.rate = parseFloat(selected.rate || 0);
    serviceForm.value.unit = selected.unit || 'kg';
  }
};

const submitApplyService = async () => {
  if (!serviceForm.value.batch_id || !serviceForm.value.service_id) {
    triggerToast('Tafadhali chagua batch na huduma iliyosajiliwa.', 'error');
    return;
  }

  // VALIDATION: Ensure target batch is NOT transformed into sub-child batches
  const targetB = activeNonTransformedBatches.value.find(b => String(b.id) === String(serviceForm.value.batch_id)) || farmerBatches.value.find(b => String(b.id) === String(serviceForm.value.batch_id));
  if (targetB) {
    const targetChildren = getBatchChildren(targetB);
    if (targetChildren.length > 0 && targetB.status === 'transformed') {
      triggerToast('Huwezi kuweka huduma kwenye mzigo ulioshatumiwa kikamilifu kuwa zao lingine!', 'error');
      return;
    }
  }

  const selectedCatalogService = catalogServices.value.find(s => s.id === serviceForm.value.service_id);
  const serviceName = selectedCatalogService ? selectedCatalogService.name_sw : 'Huduma ya Kinu';

  // DUPLICATE GUARD: Prevent duplicate service assignment to same batch
  if (targetB) {
    const existing = getBatchServices(targetB);
    const sNameLower = String(serviceName).toLowerCase().trim();
    const isDuplicate = existing.some(s => 
      String(s.service_id) === String(serviceForm.value.service_id) ||
      String(s.service_name || s.type).toLowerCase().trim() === sNameLower
    );

    if (isDuplicate) {
      triggerToast(`⚠️ Huduma ya "${serviceName}" tayari imeshapangwa kwenye mzigo huu. Huwezi kuipanga mara mbili!`, 'error');
      return;
    }
  }

  const rate = serviceForm.value.rate !== undefined && serviceForm.value.rate !== null && serviceForm.value.rate >= 0
    ? parseFloat(serviceForm.value.rate)
    : (selectedCatalogService ? parseFloat(selectedCatalogService.rate || 0) : 0);
  const unit = serviceForm.value.unit || (selectedCatalogService ? (selectedCatalogService.unit || 'kg').toLowerCase() : 'kg');
  const sType = selectedCatalogService ? (selectedCatalogService.category || 'milling').toLowerCase() : 'milling';

  let batchWeightKg = 0;
  if (targetB) {
    batchWeightKg = (parseFloat(targetB.current_weight_mt || targetB.initial_weight_mt || targetB.current_weight || 0) * 1000);
    if (batchWeightKg <= 0 && targetB.intake_quantity) {
      batchWeightKg = getUnitKg(targetB.intake_unit, targetB.intake_quantity);
    }
  }

  let qty = batchWeightKg;
  if (unit.includes('gunia') || unit.includes('bag')) {
    qty = batchWeightKg / 100;
  } else if (unit.includes('tani') || unit.includes('ton')) {
    qty = batchWeightKg / 1000;
  } else if (unit.includes('roba') || unit.includes('kiloba')) {
    qty = batchWeightKg / 25;
  }

  const feeAmount = (rate > 0 && qty > 0) ? (rate * qty) : (selectedCatalogService ? parseFloat(selectedCatalogService.rate || 0) : 120000);

  try {
    const res = await fetch(`/api/v1/batches/${serviceForm.value.batch_id}/processing`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        type: sType,
        status: 'in_progress',
        service_id: serviceForm.value.service_id,
        service_name: serviceName,
        fee: feeAmount,
        fee_amount: feeAmount,
        rate: rate,
        quantity: qty,
        unit: unit
      })
    });
    if (res.ok) {
      modals.value.applyService = false;
      await openFarmerProfile(selectedFarmer.value.id);
      triggerToast(`Huduma ya "${serviceName}" Imepangwa Kikamilifu! ⚙️`);
    }
  } catch (e) {
    triggerToast('Imefeli kupanga huduma.', 'error');
  }
};

const selectedCollateralBatch = computed(() => {
  if (!loanForm.value.collateral_batch_id) return null;
  return farmerBatches.value.find(b => b.id === loanForm.value.collateral_batch_id) || null;
});

const maxLoanLimit = computed(() => {
  if (!selectedCollateralBatch.value) return 0;
  const b = selectedCollateralBatch.value;
  const weightKg = (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000);
  // Estimated crop baseline price = TZS 1,000/Kg. Max loan cap = 50% of crop value (TZS 500 per Kg).
  return weightKg * 500;
});

const submitNewLoan = async () => {
  if (!loanForm.value.amount || loanForm.value.amount <= 0 || !loanForm.value.collateral_batch_id) {
    triggerToast('Tafadhali jaza sehemu zote za mkopo na dhamana.', 'error');
    return;
  }

  if (maxLoanLimit.value > 0 && loanForm.value.amount > maxLoanLimit.value) {
    triggerToast(`Kiasi cha mkopo (Tsh ${loanForm.value.amount.toLocaleString()}) kinazidi kikomo cha 50% cha mzigo ghalani (Tsh ${maxLoanLimit.value.toLocaleString()})!`, 'error');
    return;
  }

  try {
    const res = await fetch('/api/v1/loans', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        farmer_id: selectedFarmer.value.id,
        collateral_batch_id: loanForm.value.collateral_batch_id,
        principal_amount: loanForm.value.amount,
        due_date: loanForm.value.due_date
      })
    });
    const data = await res.json();
    if (res.ok) {
      modals.value.newLoan = false;
      await openFarmerProfile(selectedFarmer.value.id);
      await fetchFarmers();
      triggerToast('Ombi la Mkopo (0% Riba) na Dhamana Limewasilishwa kikamilifu! 💰');
    } else {
      triggerToast(data.error || 'Imefeli kuwasilisha ombi la mkopo.', 'error');
    }
  } catch (e) {
    triggerToast('Imefeli kuwasilisha ombi la mkopo.', 'error');
  }
};

const selectedServiceForComplete = ref(null);

const openCompleteServiceModal = (b, s = null) => {
  if (!b) return;
  closeAllSubModals();
  selectedBatchForComplete.value = b;
  selectedServiceForComplete.value = s;
  const bWeightKg = (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000);
  const estGuniacount = Math.max(1, Math.round((bWeightKg * 0.7) / 100));
  const estPumbacount = Math.max(1, Math.round((bWeightKg * 0.25) / 25));
  
  const currentCrop = (b.crop_type || '').toLowerCase();
  const sName = String(s?.service_name || s?.type || '').toLowerCase();

  // Is this a primary raw intake transformation (e.g. Milling on Mpunga)?
  const isMpungaIntake = !b.parent_batch_id && currentCrop.includes('mpunga') && (sName.includes('kukoboa') || sName.includes('milling') || !s);
  const defaultHasChanged = isMpungaIntake ? 'yes' : 'no';

  let defaultOutputCrop = b.crop_type || 'Mchele';
  let defaultByproductCrop = 'Pumba ya Mchele';
  if (isMpungaIntake) {
    defaultOutputCrop = 'Mchele';
    defaultByproductCrop = 'Pumba ya Mchele';
  } else if (currentCrop.includes('mahindi')) {
    defaultOutputCrop = 'Unga wa Mahindi';
    defaultByproductCrop = 'Pumba ya Mahindi';
  } else if (currentCrop.includes('alizeti')) {
    defaultOutputCrop = 'Mafuta ya Alizeti';
    defaultByproductCrop = 'Mashudu ya Alizeti';
  }

  completeForm.value = {
    type: s ? (s.type || 'milling').toLowerCase() : 'milling',
    job_id: s ? (s.job_id || s.id) : null,
    has_changed: defaultHasChanged,
    output_crop: defaultOutputCrop,
    output_quantity: estGuniacount,
    output_unit: 'Gunia',
    has_byproduct: isMpungaIntake ? 'yes' : 'no',
    byproduct_crop: defaultByproductCrop,
    byproduct_quantity: estPumbacount,
    byproduct_unit: 'Kiloba / Roba'
  };

  modals.value.completeService = true;
};

const submitCompleteService = async () => {
  const parent = selectedBatchForComplete.value;
  const s = selectedServiceForComplete.value;
  if (!parent) return;

  const isChanging = completeForm.value.has_changed === 'yes';

  // VALIDATION: Prevent output weight exceeding parent batch weight ONLY if crop is transforming
  if (isChanging && totalOutputWeightKg.value > currentBatchWeightKg.value) {
    triggerToast(`Kosa: Uzito wa pato (${totalOutputWeightKg.value} Kg) hauwezi kuzidi uzito wa mama (${currentBatchWeightKg.value} Kg)!`, 'error');
    return;
  }

  const outKg = getUnitKg(completeForm.value.output_unit, completeForm.value.output_quantity || 0);
  const byKg = (isChanging && completeForm.value.has_byproduct === 'yes') ? getUnitKg(completeForm.value.byproduct_unit, completeForm.value.byproduct_quantity || 0) : 0;

  try {
    const payload = {
      type: completeForm.value.type || (s ? (s.type || 'milling').toLowerCase() : 'milling'),
      job_id: completeForm.value.job_id || (s ? (s.job_id || s.id) : null),
      service_id: s ? (s.service_id || s.id) : null,
      status: 'completed',
      output_crop: isChanging ? completeForm.value.output_crop : parent.crop_type,
      output_unit: completeForm.value.output_unit,
      output_quantity: completeForm.value.output_quantity,
      final_value: isChanging ? (outKg / 1000) : (parent.current_weight_mt || 0),
      has_byproduct: isChanging ? completeForm.value.has_byproduct : 'no',
      by_product_crop: (isChanging && completeForm.value.has_byproduct === 'yes') ? completeForm.value.byproduct_crop : null,
      by_product_unit: (isChanging && completeForm.value.has_byproduct === 'yes') ? completeForm.value.byproduct_unit : null,
      by_product_quantity: (isChanging && completeForm.value.has_byproduct === 'yes') ? completeForm.value.byproduct_quantity : 0,
      by_product_value: (isChanging && completeForm.value.has_byproduct === 'yes') ? (byKg / 1000) : 0,
      fee: s ? (s.fee_amount || s.fee || s.cost || 0) : 0,
      fee_amount: s ? (s.fee_amount || s.fee || s.cost || 0) : 0
    };

    const res = await fetch(`/api/v1/batches/${parent.id}/processing`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      modals.value.completeService = false;
      await openFarmerProfile(selectedFarmer.value.id);
      await fetchFarmers();
      if (isChanging) {
        triggerToast('Huduma Imekamilika! Mzigo umebadilika na kutoa mazao mapya! 🌳✨');
      } else {
        triggerToast(`Huduma ya "${s ? (s.service_name || s.type) : 'Kinu'}" Imekamilika! Mzigo unabaki salama kupewa huduma zingine! ⚙️✓`);
      }
    }
  } catch (e) {
    triggerToast('Imefeli kukamilisha huduma.', 'error');
  }
};

const openAddFarmerModal = () => {
  newFarmerForm.value = { name: '', phone: '', national_id: '', region: 'Arusha', district: 'Arumeru' };
  modals.value.addFarmer = true;
};

const submitAddFarmer = async () => {
  if (!newFarmerForm.value.name || !newFarmerForm.value.phone) {
    triggerToast('Jaza jina na namba ya simu.', 'error');
    return;
  }

  try {
    const res = await fetch('/api/v1/farmers', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(newFarmerForm.value)
    });

    const data = await res.json().catch(() => ({}));

    if (res.ok) {
      modals.value.addFarmer = false;
      await fetchFarmers();
      triggerToast('Mkulima Mpya Umesajiliwa kwenye Database! 🌾');
      // Reset form inputs
      newFarmerForm.value = { name: '', phone: '', region: '', location: '', national_id: '' };
    } else {
      console.error('Server Error on registering farmer:', data);
      const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Imefeli kusajili mkulima.');
      triggerToast(errMsg, 'error');
    }
  } catch (e) {
    console.error('Network Error on registering farmer:', e);
    triggerToast('Imefeli kusajili mkulima: ' + e.message, 'error');
  }
};

const totalFarmerGrossSales = computed(() => {
  return (farmerSettlements.value || []).reduce((sum, st) => sum + parseFloat(st.gross_amount || 0), 0);
});

const totalFarmerDeductions = computed(() => {
  return (farmerSettlements.value || []).reduce((sum, st) => sum + parseFloat(st.total_deductions || 0), 0);
});

const totalFarmerNetPayout = computed(() => {
  return (farmerSettlements.value || []).reduce((sum, st) => sum + parseFloat(st.net_payout || 0), 0);
});

const settleGrossSales = computed(() => {
  const b = activeNonTransformedBatches.value.find(item => item.id === settlementForm.value.batch_id);
  const defaultKg = b ? (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000) : totalFarmerStockKg.value;
  const soldKg = Number(settlementForm.value.sold_weight_kg) || defaultKg || 0;
  return soldKg * (settlementForm.value.price_per_kg || 0);
});

const settleTotalDeductions = computed(() => {
  let totalFees = 0;
  farmerServices.value.forEach(s => {
    if (s.status !== 'paid') {
      const fee = parseFloat(s.fee_amount || s.fee || s.cost || 0);
      const rate = parseFloat(s.rate || 0);
      const qty = (parseFloat(s.output_weight_mt || s.quantity || 0) * 1000);
      if (fee > 0 && fee < 500 && rate > 0) {
        totalFees += rate * (qty > 0 ? qty : 1000);
      } else {
        totalFees += fee;
      }
    }
  });
  return totalFees + totalFarmerLoanBalance.value;
});

const settleNetPayout = computed(() => {
  return Math.max(0, settleGrossSales.value - settleTotalDeductions.value);
});

const submitSettlement = async () => {
  if (!settlementForm.value.batch_id) {
    triggerToast('Chagua batch ya kuuza!', 'error');
    return;
  }

  const b = activeNonTransformedBatches.value.find(item => item.id === settlementForm.value.batch_id);
  const defaultKg = b ? (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000) : 0;
  const soldKg = Number(settlementForm.value.sold_weight_kg) || defaultKg;

  if (soldKg <= 0) {
    triggerToast('Ingiza uzito wa Kg wa kuuza!', 'error');
    return;
  }

  if (b && soldKg > defaultKg + 0.01) {
    triggerToast(`Kiasi cha kuuza (${soldKg.toLocaleString()} Kg) kinazidi uzito uliopo ghalani (${defaultKg.toLocaleString()} Kg)!`, 'error');
    return;
  }

  if (!settlementForm.value.price_per_kg || settlementForm.value.price_per_kg <= 0) {
    triggerToast('Weka bei ya mauzo kwa kila Kg!', 'error');
    return;
  }

  try {
    const res = await fetch('/api/v1/sales/confirm', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        farmer_id: selectedFarmer.value.id,
        batch_id: settlementForm.value.batch_id,
        buyer_name: settlementForm.value.buyer_name || 'Mnunuzi wa Jumla / Kiwanda',
        price_per_kg: settlementForm.value.price_per_kg,
        sold_weight_kg: soldKg
      })
    });

    const data = await res.json();
    if (res.ok && data.success !== false) {
      triggerToast(`Mauzo na Makato Vimekamilika! Malipo kwa Mkulima: Tsh ${settleNetPayout.value.toLocaleString()} 🏷️`);
      modals.value.newSale = false;
      settlementForm.value = {
        batch_id: '',
        buyer_name: 'Mnunuzi wa Jumla / Kiwanda',
        price_per_kg: 1800,
        sold_weight_kg: 0,
        storage_fee: 45000,
        milling_fee: 120000
      };
      await openFarmerProfile(selectedFarmer.value.id);
      await fetchFarmers();
    } else {
      triggerToast(data.message || 'Hitilafu imetokea wakati wa kukamilisha mauzo.', 'error');
    }
  } catch (e) {
    console.error('Error in submitSettlement:', e);
    triggerToast('Hitilafu ya mtandao wakati wa kukamilisha mauzo.', 'error');
  }
};



const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('sw-TZ', { day: '2-digit', month: 'short', year: 'numeric' });
};

const calculateStorageDays = (dateStr, status = 'active', updatedAtStr = null) => {
  if (!dateStr) return 0;
  const startDate = new Date(dateStr);
  if (isNaN(startDate.getTime())) return 0;

  let endDate = new Date();
  if ((status === 'sold' || status === 'transformed') && updatedAtStr) {
    const updatedDate = new Date(updatedAtStr);
    if (!isNaN(updatedDate.getTime())) {
      endDate = updatedDate;
    }
  }

  const diffMs = endDate.getTime() - startDate.getTime();
  if (diffMs < 0) return 1;

  const days = Math.floor(diffMs / (1000 * 60 * 60 * 24));
  return days === 0 ? 1 : days;
};

const formatStorageDaysBadge = (dateStr, status = 'active', updatedAtStr = null) => {
  const days = calculateStorageDays(dateStr, status, updatedAtStr);
  if (days === 1) {
    return '⏱️ Imepokelewa Leo (Siku 1)';
  }
  return `⏱️ Siku ${days} Ghalani`;
};

const resetFilters = () => {
  searchQuery.value = '';
  regionFilter.value = '';
  statusFilter.value = '';
};

onMounted(() => {
  fetchFarmers();
  fetchServicesCatalog();
});
</script>
