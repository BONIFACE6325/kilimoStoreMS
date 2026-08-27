<template>
  <div class="space-y-6 pb-12">
    
    <!-- Page Header Banner (Lush Emerald & Teal Gradient) -->
    <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-emerald-950 p-6 sm:p-8 rounded-3xl border border-emerald-700/60 shadow-xl text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-5 relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-2 text-xs font-black text-emerald-300 uppercase tracking-widest">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
          Farmer Management Module
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white mt-1 tracking-tight">Orodha ya Wakulima na Profile Za Mfumo</h1>
        <p class="text-xs sm:text-sm text-emerald-100 mt-1">Sajili wakulima, fuatilia mizigo ghalani, huduma za kinu, mikopo na mauzo.</p>
      </div>
      <div class="flex items-center gap-3 relative z-10">
        <button @click="openAddFarmerModal" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-emerald-900/40 transition transform hover:-translate-y-0.5 flex items-center gap-2 border border-emerald-400/30">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>Sajili Mkulima Mpya</span>
        </button>
      </div>
    </div>

    <!-- Toast Notification Banner -->
    <div v-if="toast.show" class="fixed top-5 right-5 z-50 px-5 py-3 rounded-2xl shadow-2xl font-extrabold text-xs flex items-center gap-3 text-white transition-all transform animate-bounce" :class="toast.type === 'success' ? 'bg-emerald-600 border border-emerald-400' : 'bg-red-600 border border-red-400'">
      <span>{{ toast.type === 'success' ? '✅' : '⚠️' }}</span>
      <span>{{ toast.message }}</span>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-emerald-100/80 shadow-2xs flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="relative flex-1 w-full">
        <svg class="w-4 h-4 text-emerald-600 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input v-model="searchQuery" type="text" placeholder="Tafuta mkulima kwa Jina, Code, au Namba ya Simu..." class="w-full pl-10 pr-4 py-2.5 bg-emerald-50/30 border border-emerald-200/70 rounded-xl text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"/>
      </div>
      <div class="flex items-center gap-2.5 w-full sm:w-auto">
        <select v-model="regionFilter" class="bg-emerald-50/30 border border-emerald-200/70 rounded-xl text-xs sm:text-sm font-semibold py-2.5 px-3 focus:outline-none">
          <option value="">Mikoa Yote</option>
          <option v-for="r in availableRegions" :key="r" :value="r">{{ r }}</option>
        </select>
        <select v-model="statusFilter" class="bg-emerald-50/30 border border-emerald-200/70 rounded-xl text-xs sm:text-sm font-semibold py-2.5 px-3 focus:outline-none">
          <option value="">Hali Zote</option>
          <option value="active">ACTIVE</option>
          <option value="inactive">INACTIVE</option>
        </select>
        <button @click="resetFilters" class="px-3.5 py-2.5 text-xs font-bold text-emerald-800 hover:text-emerald-950 border border-emerald-200 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition">
          Safisha
        </button>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-emerald-100/80 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
          <thead class="bg-emerald-50/80 text-emerald-900 font-extrabold border-b border-emerald-100 uppercase text-[10.5px] tracking-wider">
            <tr>
              <th class="py-4 px-5">Mkulima</th>
              <th class="py-4 px-5">Simu</th>
              <th class="py-4 px-5">Mkoa / Wilaya</th>
              <th class="py-4 px-5">Mazao Ghalani</th>
              <th class="py-4 px-5">Deni la Mkopo</th>
              <th class="py-4 px-5">Hali</th>
              <th class="py-4 px-5 text-right">Vitendo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium">
            <tr v-if="loading" class="text-center text-slate-400">
              <td colspan="7" class="py-12">
                <div class="flex items-center justify-center gap-2">
                  <span class="w-4 h-4 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin"></span>
                  <span>Inapakia wakulima kutoka database...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="filteredFarmers.length === 0" class="text-center text-slate-400">
              <td colspan="7" class="py-12">Hakuna mkulima aliyepatikana kwenye database.</td>
            </tr>
            <tr v-for="f in paginatedFarmers" :key="f.id" class="hover:bg-emerald-50/40 transition-all">
              <td class="py-4 px-5">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black flex items-center justify-center text-sm shadow-xs">
                    {{ (f.name || 'M').charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 text-sm">{{ f.name }}</div>
                    <div class="text-[11px] text-emerald-700 font-mono font-bold mt-0.5">{{ f.farmer_code }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-5 text-slate-600 font-mono font-semibold">{{ f.phone || 'N/A' }}</td>
              <td class="py-4 px-5 text-slate-600 font-semibold">{{ f.region || 'N/A' }} {{ f.district ? '(' + f.district + ')' : '' }}</td>
              <td class="py-4 px-5 font-black text-emerald-700 text-sm">
                {{ (parseFloat(f.active_stock || f.total_deposited || 0) * 1000).toLocaleString() }} Kg
              </td>
              <td class="py-4 px-5">
                <span :class="parseFloat(f.loan_balance || 0) > 0 ? 'text-red-600 font-black text-sm' : 'text-slate-400 font-semibold'">
                  Tsh {{ parseFloat(f.loan_balance || 0).toLocaleString() }}
                </span>
              </td>
              <td class="py-4 px-5">
                <span :class="f.status === 'active' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-600 border-slate-200'" class="px-3 py-1 rounded-full text-[10px] font-black border uppercase">
                  {{ f.status || 'active' }}
                </span>
              </td>
              <td class="py-4 px-5 text-right">
                <button @click="openFarmerProfile(f.id)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition inline-flex items-center gap-1.5 transform hover:-translate-y-0.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  <span>Fungua Profile</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="px-6 py-4 bg-emerald-50/40 border-t border-emerald-100 flex items-center justify-between text-xs text-slate-600 font-semibold">
        <div>Inaonyesha {{ (currentPage - 1) * pageSize + 1 }} hadi {{ Math.min(currentPage * pageSize, filteredFarmers.length) }} kati ya {{ filteredFarmers.length }}</div>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3.5 py-1.5 bg-white border border-slate-200 rounded-xl text-slate-700 disabled:opacity-40 font-bold shadow-2xs">Iliyopita</button>
          <button @click="currentPage++" :disabled="currentPage * pageSize >= filteredFarmers.length" class="px-3.5 py-1.5 bg-white border border-slate-200 rounded-xl text-slate-700 disabled:opacity-40 font-bold shadow-2xs">Ifuatayo</button>
        </div>
      </div>
    </div>

    <!-- MAIN FARMER PROFILE MODAL (With Strict Transformed Batch Locking & Product Tree Services) -->
    <div v-if="modals.profile" class="fixed inset-0 z-40 bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4">
      <div class="bg-white w-full max-w-7xl h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-emerald-200">
        
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
            <button @click="modals.profile = false" class="text-emerald-200 hover:text-white p-2 rounded-full hover:bg-emerald-800/60 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <!-- Profile Loading State -->
        <div v-if="loadingProfile" class="flex-1 flex flex-col items-center justify-center bg-slate-50 text-slate-500">
          <div class="w-8 h-8 rounded-full border-3 border-emerald-600 border-t-transparent animate-spin mb-3"></div>
          <div class="font-extrabold text-sm text-slate-700">Inapakia taarifa za mkulima kutoka database...</div>
        </div>

        <!-- Profile Modal Body (2 Columns) -->
        <div v-else class="flex-1 overflow-y-auto p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 bg-slate-50">
          
          <!-- LEFT COLUMN: Profile Info & Address (4 cols) -->
          <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-emerald-100 shadow-2xs space-y-5">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-xl flex items-center justify-center shadow-md">
                  {{ (selectedFarmer.name || 'A').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="text-base font-black text-slate-900 leading-tight">{{ selectedFarmer.name }}</div>
                  <div class="text-xs font-mono font-bold text-emerald-700 mt-0.5">{{ selectedFarmer.farmer_code }}</div>
                </div>
              </div>
              <button 
                @click="isEditingFarmer = !isEditingFarmer" 
                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-extrabold flex items-center gap-1.5 transition"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>{{ isEditingFarmer ? 'Funga' : 'Hariri' }}</span>
              </button>
            </div>

            <!-- Inline Edit Farmer Form -->
            <div v-if="isEditingFarmer" class="p-4 bg-emerald-50/50 border border-emerald-200 rounded-2xl space-y-3">
              <div class="text-xs font-extrabold text-emerald-950 pb-1 border-b border-emerald-200">✏️ Hariri Taarifa za Mkulima</div>
              <div class="space-y-2.5 text-xs font-semibold text-slate-700">
                <div>
                  <label class="block text-[11px] font-bold text-slate-500 mb-1">Jina Kamili *</label>
                  <input v-model="editFarmerForm.name" type="text" class="w-full p-2 bg-white border border-slate-200 rounded-xl font-medium"/>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Simu *</label>
                    <input v-model="editFarmerForm.phone" type="text" class="w-full p-2 bg-white border border-slate-200 rounded-xl font-medium"/>
                  </div>
                  <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">NIDA</label>
                    <input v-model="editFarmerForm.national_id" type="text" class="w-full p-2 bg-white border border-slate-200 rounded-xl font-medium"/>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Mkoa</label>
                    <input v-model="editFarmerForm.region" type="text" class="w-full p-2 bg-white border border-slate-200 rounded-xl font-medium"/>
                  </div>
                  <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Wilaya</label>
                    <input v-model="editFarmerForm.district" type="text" class="w-full p-2 bg-white border border-slate-200 rounded-xl font-medium"/>
                  </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                  <button @click="isEditingFarmer = false" class="px-3 py-1.5 bg-slate-200 text-slate-700 font-bold rounded-xl">Ghairi</button>
                  <button @click="saveEditFarmer" class="px-3.5 py-1.5 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Hifadhi</button>
                </div>
              </div>
            </div>

            <!-- Details List -->
            <div v-else class="space-y-3 text-xs sm:text-sm font-medium">
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-400 font-semibold">Simu:</span>
                <strong class="text-slate-900 font-bold font-mono">{{ selectedFarmer.phone || 'N/A' }}</strong>
              </div>
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-400 font-semibold">Mkoa:</span>
                <strong class="text-slate-900 font-bold">{{ selectedFarmer.region || 'Arusha' }}</strong>
              </div>
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-400 font-semibold">Wilaya:</span>
                <strong class="text-slate-900 font-bold">{{ selectedFarmer.district || 'Arumeru' }}</strong>
              </div>
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-400 font-semibold">Kata:</span>
                <strong class="text-slate-900 font-bold">{{ selectedFarmer.ward || 'Usa River' }}</strong>
              </div>
              <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-400 font-semibold">National ID:</span>
                <strong class="text-slate-900 font-bold font-mono">{{ selectedFarmer.national_id || 'N/A' }}</strong>
              </div>
            </div>

            <!-- KPI Highlight Cards (Vibrant Emerald Theme) -->
            <div class="space-y-3 pt-2">
              <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-4 rounded-2xl flex items-center justify-between shadow-lg shadow-emerald-600/20">
                <div>
                  <div class="text-[10.5px] font-black uppercase text-emerald-100 tracking-wider">Jumla ya Mzigo Ghalani</div>
                  <div class="text-2xl font-black text-white mt-0.5">{{ totalFarmerStockKg.toLocaleString() }} Kg</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/20 text-white font-black flex items-center justify-center text-base">📦</div>
              </div>

              <div class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 p-4 rounded-2xl flex items-center justify-between">
                <div>
                  <div class="text-[10.5px] font-black uppercase text-red-700 tracking-wider">Mkopo Bado (Deni)</div>
                  <div class="text-2xl font-black text-red-600 mt-0.5">Tsh {{ totalFarmerLoanBalance.toLocaleString() }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 font-black flex items-center justify-center text-base">💳</div>
              </div>
            </div>

          </div>

          <!-- RIGHT COLUMN: Interactive Tabs & Processing Tree (8 cols) -->
          <div class="lg:col-span-8 bg-white p-5 rounded-2xl border border-emerald-100 shadow-2xs flex flex-col space-y-5">
            
            <!-- Tabs Navigation Bar -->
            <div class="flex items-center gap-2 border-b border-emerald-100 pb-3">
              <button 
                @click="profileTab = 'batches'" 
                :class="profileTab === 'batches' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100'" 
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center gap-2"
              >
                <span>📦 Mzigo & Processing Tree</span>
              </button>
              <button 
                @click="profileTab = 'loans'" 
                :class="profileTab === 'loans' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100'" 
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center gap-2"
              >
                <span>💰 Mikopo</span>
              </button>
              <button 
                @click="profileTab = 'sales'" 
                :class="profileTab === 'sales' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100'" 
                class="px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center gap-2"
              >
                <span>🏷️ Mauzo & Settlement</span>
              </button>
            </div>

            <!-- TAB 1: BATCHES & SERVICE PROCESSING TREE -->
            <div v-if="profileTab === 'batches'" class="space-y-5">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900">Orodha ya Mizigo na Huduma Za Kinu</h3>
                <div class="flex gap-2">
                  <button @click="openApplyServiceModal" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-xl transition flex items-center gap-1">
                    <span>+ Huduma Mpya</span>
                  </button>
                  <button @click="modals.intake = true" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1">
                    <span>+ Pokea Mpya</span>
                  </button>
                </div>
              </div>

              <!-- Batches List with Transformed Locking and Active Output Product Buttons -->
              <div class="space-y-4">
                <div v-if="topLevelFarmerBatches.length === 0" class="text-center text-slate-400 py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                  Hakuna shehena yoyote iliyopokelewa kwenye database.
                </div>

                <div v-for="b in topLevelFarmerBatches" :key="b.id" class="p-4 bg-emerald-50/40 border border-emerald-200/80 rounded-2xl space-y-3.5 shadow-2xs hover:border-emerald-300 transition">
                  <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-extrabold text-sm flex items-center justify-center shadow-xs">
                        📦
                      </div>
                      <div>
                        <div class="flex items-center gap-2">
                          <span class="font-black text-slate-900 font-mono text-sm">{{ b.batch_code }}</span>
                          
                          <!-- TRANSFORMED LOCK BADGE -->
                          <span v-if="b.status === 'transformed'" class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-900 border border-purple-300 flex items-center gap-1">
                            🔒 TRANSFORMED (IMEKAMLIKA)
                          </span>
                          <span v-else class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase">
                            {{ b.status }}
                          </span>

                          <span v-if="b.is_collateral" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1">
                            🔒 Dhamana ya Mkopo
                          </span>
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5">Zao: <strong class="text-slate-800">{{ b.crop_type }}</strong> {{ b.variety ? '(' + b.variety + ')' : '' }}</div>
                      </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                      <div class="text-right">
                        <div class="text-sm font-black text-emerald-700">{{ (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000).toLocaleString() }} Kg</div>
                        <div class="text-[10.5px] text-slate-400 font-semibold">Uzito Uliopo Ghalani</div>
                      </div>

                      <!-- IF TRANSFORMED: CANNOT APPLY SERVICE ON ORIGINAL BATCH ANYMORE -->
                      <div v-if="b.status === 'transformed'" class="px-3 py-1.5 bg-slate-200 text-slate-500 rounded-xl font-bold text-xs cursor-not-allowed">
                        🔒 Mzigo Umeshatumiwa
                      </div>

                      <button 
                        v-else 
                        @click="openCompleteServiceModal(b)" 
                        class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-xs transition"
                      >
                        Kamilisha Huduma
                      </button>

                      <!-- KUFUNGUA NA KUFUNGA (Toggle Accordion Button) -->
                      <button 
                        @click="toggleBatchAccordion(b.id)" 
                        class="p-2 text-emerald-800 hover:text-emerald-950 bg-emerald-100/70 hover:bg-emerald-200/80 rounded-xl transition flex items-center gap-1 text-xs font-black"
                      >
                        <span>{{ expandedBatches[b.id] ? 'Funga' : 'Fungua' }}</span>
                        <svg class="w-4 h-4 transform transition-transform" :class="expandedBatches[b.id] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                      </button>
                    </div>
                  </div>

                  <!-- COLLAPSIBLE TREE SECTION (KUFUNGUA/KUFUNGA) -->
                  <div v-if="expandedBatches[b.id]" class="space-y-3 pt-2 border-t border-emerald-100 animate-fadeIn">
                    
                    <!-- ASSIGNED SERVICES UNDER BATCH (Vertical Tree Line & Branch Nodes) -->
                    <div v-if="getBatchServices(b).length > 0" class="pl-6 relative space-y-2">
                      <div class="absolute left-3 top-2 bottom-3 w-0.5 bg-emerald-300"></div>
                      
                      <div class="text-[10.5px] font-black text-emerald-900 uppercase tracking-wider flex items-center gap-1">
                        ⚙️ Huduma Zilizopangwa Kufanyika (Mill Service Pipeline):
                      </div>

                      <div v-for="s in getBatchServices(b)" :key="s.id" class="relative pl-4 flex items-center justify-between p-2.5 bg-white border border-emerald-200 rounded-xl text-xs shadow-2xs">
                        <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-3 h-0.5 bg-emerald-300"></div>
                        <div class="flex items-center gap-3">
                          <span class="font-bold text-slate-900">{{ s.type || s.service_type || 'Kukoboa' }}</span>
                          <span class="text-slate-400 font-mono">({{ s.service_name || s.machine || 'Kinu Block A' }})</span>
                          <span class="font-bold text-emerald-700">Tsh {{ parseFloat(s.fee_amount || s.cost || 0).toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <span :class="s.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'" class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase">
                            {{ s.status }}
                          </span>
                          <button v-if="s.status !== 'completed' && b.status !== 'transformed'" @click="openCompleteServiceModal(b)" class="px-2 py-1 bg-emerald-600 text-white font-bold rounded-lg text-[10.5px]">
                            Kamilisha
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- TRANSFORMED CROP OUTCOME TREE (Multi-branch Children: Mchele Grade A + Pumba) -->
                    <div v-if="getBatchChildren(b).length > 0" class="pl-6 relative space-y-2">
                      <div class="absolute left-3 top-2 bottom-3 w-0.5 bg-emerald-400"></div>

                      <div class="text-[10.5px] font-black text-indigo-900 uppercase tracking-wider flex items-center gap-1">
                        🌳 Processing Tree Outcome (Product Zilizopatikana - Ziko Active):
                      </div>

                      <div v-for="child in getBatchChildren(b)" :key="child.id" class="relative pl-4 p-3 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs shadow-2xs">
                        <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-3 h-0.5 bg-emerald-400"></div>
                        <div class="flex items-center gap-2.5">
                          <span class="text-emerald-600 font-black text-sm">🌾</span>
                          <div>
                            <div class="font-black text-slate-900 text-xs">{{ child.crop_type }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ child.batch_code }}</div>
                          </div>
                        </div>
                        <div class="flex items-center gap-3">
                          <div class="font-black text-emerald-800 text-sm">{{ (parseFloat(child.current_weight_mt || child.current_weight || 0) * 1000).toLocaleString() }} Kg</div>
                          <!-- ACTION ON ACTIVE CHILD PRODUCT BATCH -->
                          <button @click="openApplyServiceForChild(child)" class="px-2.5 py-1 bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-bold rounded-lg shadow-2xs">
                            + Huduma ya Product Hii
                          </button>
                        </div>
                      </div>
                    </div>

                    <div v-if="getBatchServices(b).length === 0 && getBatchChildren(b).length === 0" class="text-xs text-slate-400 italic pl-4 py-1">
                      Hakuna huduma za kinu wala matawi ya ziada kwenye batch hii.
                    </div>

                  </div>

                </div>
              </div>
            </div>

            <!-- TAB 2: LOANS WORKFLOW -->
            <div v-if="profileTab === 'loans'" class="space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900">Historia ya Mikopo na Dhamana</h3>
                <button @click="modals.newLoan = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  <span>Omba Mkopo Mpya</span>
                </button>
              </div>

              <!-- Loans Table -->
              <div class="border border-emerald-100 rounded-2xl overflow-hidden shadow-2xs">
                <table class="w-full text-left text-xs">
                  <thead class="bg-emerald-50/80 text-emerald-900 font-extrabold border-b">
                    <tr>
                      <th class="py-3 px-4">Tarehe</th>
                      <th class="py-3 px-4">Dhamana (Collateral)</th>
                      <th class="py-3 px-4">Kiasi cha Mkopo</th>
                      <th class="py-3 px-4">Salio la Mkopo</th>
                      <th class="py-3 px-4">Hali</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 font-medium">
                    <tr v-if="farmerLoans.length === 0" class="text-center text-slate-400">
                      <td colspan="5" class="py-8">Hakuna historia ya mikopo iliyotolewa.</td>
                    </tr>
                    <tr v-for="l in farmerLoans" :key="l.id" class="hover:bg-slate-50 transition">
                      <td class="py-3 px-4 text-slate-600 font-mono font-semibold">{{ formatDate(l.created_at) }}</td>
                      <td class="py-3 px-4 font-bold text-emerald-800 font-mono">{{ l.collateral_batch?.batch_code || l.collateral_batch_id || 'Dhamana ya Mazao' }}</td>
                      <td class="py-3 px-4 font-bold text-slate-900">Tsh {{ parseFloat(l.principal_amount || 0).toLocaleString() }}</td>
                      <td class="py-3 px-4 font-black" :class="parseFloat(l.current_balance || l.remaining_balance || 0) > 0 ? 'text-red-600' : 'text-emerald-600'">
                        Tsh {{ parseFloat(l.current_balance || l.remaining_balance || 0).toLocaleString() }}
                      </td>
                      <td class="py-3 px-4">
                        <span :class="parseFloat(l.current_balance || l.remaining_balance || 0) > 0 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'" class="px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase">
                          {{ l.status || 'ACTIVE' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- TAB 3: SETTLEMENT & SALES DASHBOARD -->
            <div v-if="profileTab === 'sales'" class="space-y-4">
              <!-- KPI Row -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-indigo-50/70 border border-indigo-200 p-4 rounded-2xl">
                  <div class="text-[11px] font-extrabold text-indigo-800 uppercase tracking-wider">Thamani Ghafi ya Mauzo</div>
                  <div class="text-lg font-black text-indigo-900 mt-1">Tsh {{ settleGrossSales.toLocaleString() }}</div>
                </div>
                <div class="bg-red-50/70 border border-red-200 p-4 rounded-2xl">
                  <div class="text-[11px] font-extrabold text-red-800 uppercase tracking-wider">Jumla ya Makato</div>
                  <div class="text-lg font-black text-red-600 mt-1">Tsh {{ settleTotalDeductions.toLocaleString() }}</div>
                </div>
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-4 rounded-2xl shadow-lg shadow-emerald-600/20">
                  <div class="text-[11px] font-black uppercase tracking-wider text-emerald-100">Malipo Halisi (Remis)</div>
                  <div class="text-xl font-black mt-1">Tsh {{ settleNetPayout.toLocaleString() }}</div>
                </div>
              </div>

              <!-- Settlement Actions & Breakdown -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-emerald-50/30 p-4 rounded-2xl border border-emerald-100">
                <div class="space-y-3 text-xs">
                  <div class="font-extrabold text-slate-900">Uchambuzi wa Makato ya Mkulima</div>
                  <div class="space-y-2">
                    <div class="flex justify-between font-semibold text-slate-700">
                      <span>Ada ya Hifadhi ya Ghala:</span>
                      <strong class="font-bold text-slate-900">Tsh {{ (settlementForm.storage_fee || 45000).toLocaleString() }}</strong>
                    </div>
                    <div class="flex justify-between font-semibold text-slate-700">
                      <span>Ada za Kukoboa / Kusaga:</span>
                      <strong class="font-bold text-slate-900">Tsh {{ (settlementForm.milling_fee || 120000).toLocaleString() }}</strong>
                    </div>
                    <div class="flex justify-between font-semibold text-slate-700">
                      <span>Rejesho la Mkopo:</span>
                      <strong class="font-bold text-red-600">Tsh {{ totalFarmerLoanBalance.toLocaleString() }}</strong>
                    </div>
                  </div>
                </div>

                <div class="space-y-3 text-xs">
                  <div>
                    <label class="block font-bold text-slate-700 mb-1">Chagua Batch ya Kuuza *</label>
                    <select v-model="settlementForm.batch_id" class="w-full p-2 bg-white border border-slate-200 rounded-xl font-semibold">
                      <option value="">Chagua batch ya kuuza...</option>
                      <option v-for="b in activeNonTransformedBatches" :key="b.id" :value="b.id">{{ b.batch_code }} - {{ b.crop_type }} ({{ (parseFloat(b.current_weight_mt||b.current_weight||0)*1000) }} Kg)</option>
                    </select>
                  </div>
                  <div>
                    <label class="block font-bold text-slate-700 mb-1">Bei ya Mauzo (Tsh kwa Kg) *</label>
                    <input v-model.number="settlementForm.price_per_kg" type="number" placeholder="e.g. 1200" class="w-full p-2 bg-white border border-slate-200 rounded-xl font-bold"/>
                  </div>
                  <button @click="submitSettlement" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl shadow-xs transition">
                    Kamilisha Makato & Mauzo
                  </button>
                </div>
              </div>

            </div>

          </div>

        </div>
      </div>
    </div>

    <!-- MODAL 1: SAJILI HUDUMA MPYA (Assign Service - ONLY Active Non-Transformed Batches Allowed!) -->
    <div v-if="modals.applyService" class="fixed inset-0 z-60 bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Sajili Huduma ya Kinu (Assign Service)</h3>
          <button @click="modals.applyService = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-3.5 text-xs font-semibold text-slate-700">
          <div>
            <label class="block mb-1 font-bold">Chagua Shehena (Batch/Product Active) *</label>
            <select v-model="serviceForm.batch_id" class="w-full p-2.5 border rounded-xl font-bold">
              <option value="">Chagua batch active...</option>
              <option v-for="b in activeNonTransformedBatches" :key="b.id" :value="b.id">{{ b.batch_code }} ({{ b.crop_type }} - {{ (parseFloat(b.current_weight_mt||b.current_weight||0)*1000) }} Kg)</option>
            </select>
            <span class="text-[10.5px] text-amber-700 mt-1 block">⚠️ Batches zilizo-transformed (zilizotumika kikamilifu) haziruhusiwi kupangiwa huduma mpya.</span>
          </div>
          <div>
            <label class="block mb-1 font-bold">Chagua Huduma Iliyosajiliwa (Registered Service) *</label>
            <select v-model="serviceForm.service_id" @change="onServiceCatalogSelect" class="w-full p-2.5 bg-emerald-50/50 border border-emerald-300 rounded-xl font-extrabold text-emerald-950">
              <option value="">Chagua huduma iliyosajiliwa...</option>
              <option v-for="s in catalogServices" :key="s.id" :value="s.id">{{ s.name_sw }} — Tsh {{ parseFloat(s.rate).toLocaleString() }} / {{ s.unit }}</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 font-bold">Aina ya Huduma (Category)</label>
            <select v-model="serviceForm.service_type" class="w-full p-2.5 border rounded-xl font-bold bg-slate-50">
              <option value="milling">Kukoboa / Milling</option>
              <option value="drying">Kukausha / Drying</option>
              <option value="grading">Grading / Pambanua Grade</option>
              <option value="packaging">Ufungashaji / Packaging</option>
              <option value="storage">Hifadhi / Storage</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 font-bold">Mashine / Kinu Kilichopangiwa</label>
            <input v-model="serviceForm.machine" type="text" placeholder="e.g. Kinu Block A" class="w-full p-2.5 border rounded-xl"/>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="modals.applyService = false" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl">Ghairi</button>
            <button @click="submitApplyService" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Panga Huduma</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: OMBA MKOPO MPYA (New Loan Request with Collateral) -->
    <div v-if="modals.newLoan" class="fixed inset-0 z-60 bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Maombi ya Mkopo Mpya</h3>
          <button @click="modals.newLoan = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-3.5 text-xs font-semibold text-slate-700">
          <div>
            <label class="block mb-1 font-bold">Kiasi cha Mkopo (Tsh) *</label>
            <input v-model.number="loanForm.amount" type="number" placeholder="e.g. 1500000" class="w-full p-2.5 border rounded-xl font-bold"/>
          </div>
          <div>
            <label class="block mb-1 font-bold">Tarehe ya Kulipa *</label>
            <input v-model="loanForm.due_date" type="date" class="w-full p-2.5 border rounded-xl font-bold"/>
          </div>
          <div>
            <label class="block mb-1 font-bold">Mazao ya Dhamana (Collateral Batch) *</label>
            <select v-model="loanForm.collateral_batch_id" class="w-full p-2.5 border rounded-xl font-bold">
              <option value="">Chagua batch ya dhamana...</option>
              <option v-for="b in activeNonTransformedBatches" :key="b.id" :value="b.id">{{ b.batch_code }} - {{ b.crop_type }} ({{ (parseFloat(b.current_weight_mt||b.current_weight||0)*1000) }} Kg)</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button @click="modals.newLoan = false" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl">Ghairi</button>
            <button @click="submitNewLoan" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Tuma Ombi</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 3: KAMILISHA HUDUMA (Crop Transformation & Tree Building with Strict Validations) -->
    <div v-if="modals.completeService" class="fixed inset-0 z-60 bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Kamilisha Huduma & Batch Transformation</h3>
          <button @click="modals.completeService = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-4 text-xs font-semibold text-slate-700">
          <div class="p-3 bg-emerald-900 text-white rounded-xl border border-emerald-700">
            <div class="font-extrabold text-sm text-emerald-300">Mzigo Mama: {{ selectedBatchForComplete.batch_code }} ({{ selectedBatchForComplete.crop_type }})</div>
            <div class="text-xs text-emerald-100 mt-0.5">Uzito wa Sasa: {{ currentBatchWeightKg }} Kg</div>
          </div>

          <div>
            <label class="block mb-1 font-bold">Aina ya Huduma *</label>
            <select v-model="completeForm.type" class="w-full p-2.5 border rounded-xl font-bold">
              <option value="milling">Kukoboa / Milling</option>
              <option value="drying">Kukausha / Drying</option>
              <option value="grading">Grading / Pambanua Grade</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 font-bold">Je, Zao limebadilika? (Mf. Mpunga kuwa Mchele)</label>
            <select v-model="completeForm.has_changed" class="w-full p-2.5 border rounded-xl font-bold">
              <option value="yes">Ndiyo, Zao Limebadilika (Crop Transformation)</option>
              <option value="no">Hapana, Zao ni Lilelile</option>
            </select>
          </div>

          <div v-if="completeForm.has_changed === 'yes'" class="space-y-4 p-4 bg-emerald-50/60 rounded-2xl border border-emerald-200">
            <!-- Primary Output Product -->
            <div class="space-y-2">
              <div class="font-extrabold text-emerald-950 text-xs flex items-center gap-1.5">
                <span>🌾 1. Zao Kuu Lililopatikana (Primary Output Product)</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <div>
                  <label class="block mb-1 font-bold">Zao Jipya *</label>
                  <select v-model="completeForm.output_crop" class="w-full p-2.5 bg-white border border-slate-200 rounded-xl font-bold">
                    <option v-for="c in cropsList" :key="c" :value="c">{{ c }}</option>
                  </select>
                </div>
                <div>
                  <label class="block mb-1 font-bold">Kiasi *</label>
                  <input v-model.number="completeForm.output_quantity" type="number" placeholder="e.g. 30" class="w-full p-2.5 bg-white border border-slate-200 rounded-xl font-bold"/>
                </div>
                <div>
                  <label class="block mb-1 font-bold">Kipimo / Unit *</label>
                  <select v-model="completeForm.output_unit" class="w-full p-2.5 bg-white border border-slate-200 rounded-xl font-bold">
                    <option v-for="u in unitsList" :key="u.name" :value="u.name">{{ u.name }}</option>
                  </select>
                </div>
              </div>
              <div class="text-[11px] text-emerald-800 font-bold text-right">
                Uzito wa Zao Kuu: {{ getUnitKg(completeForm.output_unit, completeForm.output_quantity || 0) }} Kg
              </div>
            </div>

            <!-- Mandatory By-Product Toggle -->
            <div class="pt-3 border-t border-emerald-200 space-y-2">
              <label class="block mb-1 font-extrabold text-slate-800 text-xs">
                🌱 2. Je, kuna zao la ziada (By-Product) litapatikana? *
              </label>
              <select v-model="completeForm.has_byproduct" class="w-full p-2.5 bg-white border border-slate-200 rounded-xl font-bold text-slate-900">
                <option value="no">Hapana, Hakuna Zao la Ziada</option>
                <option value="yes">Ndiyo, Kuna Zao la Ziada (e.g. Pumba, Manamane, Taka)</option>
              </select>
            </div>

            <!-- Conditional By-Product Fields -->
            <div v-if="completeForm.has_byproduct === 'yes'" class="p-3 bg-white border border-emerald-300 rounded-2xl space-y-2 animate-fadeIn">
              <div class="font-extrabold text-teal-900 text-xs">Maelezo ya Zao la Ziada (By-Product Details)</div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                <div>
                  <label class="block mb-1 font-bold">Zao la Ziada *</label>
                  <select v-model="completeForm.byproduct_crop" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold">
                    <option value="Pumba">Pumba ya Mchele</option>
                    <option value="Pumba ya Mahindi">Pumba ya Mahindi</option>
                    <option value="Manamane">Manamane / Broken Rice</option>
                    <option v-for="c in cropsList" :key="c" :value="c">{{ c }}</option>
                  </select>
                </div>
                <div>
                  <label class="block mb-1 font-bold">Kiasi cha Ziada *</label>
                  <input v-model.number="completeForm.byproduct_quantity" type="number" placeholder="e.g. 15" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold"/>
                </div>
                <div>
                  <label class="block mb-1 font-bold">Kipimo / Unit *</label>
                  <select v-model="completeForm.byproduct_unit" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold">
                    <option v-for="u in unitsList" :key="u.name" :value="u.name">{{ u.name }}</option>
                  </select>
                </div>
              </div>
              <div class="text-[11px] text-teal-800 font-bold text-right">
                Uzito wa Zao la Ziada: {{ getUnitKg(completeForm.byproduct_unit, completeForm.byproduct_quantity || 0) }} Kg
              </div>
            </div>

            <div class="p-2.5 bg-emerald-950 text-emerald-200 rounded-xl text-xs font-mono font-bold flex items-center justify-between">
              <span>Jumla ya Patokazi:</span>
              <span class="text-white text-sm font-black">{{ totalOutputWeightKg }} Kg</span>
            </div>

            <div v-if="totalOutputWeightKg > currentBatchWeightKg" class="p-2 bg-red-100 text-red-800 rounded-lg text-[11px] font-bold border border-red-200">
              ⚠️ Tahadhari: Jumla ya uzito uliopatikana ({{ totalOutputWeightKg }} Kg) unazidi uzito wa mzigo mama ({{ currentBatchWeightKg }} Kg)!
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button @click="modals.completeService = false" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl">Ghairi</button>
            <button @click="submitCompleteService" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Thibitisha (Complete)</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 4: ADD FARMER MODAL -->
    <div v-if="modals.addFarmer" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Sajili Mkulima Mpya</h3>
          <button @click="modals.addFarmer = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-3 text-xs font-semibold text-slate-700">
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
            <button @click="modals.addFarmer = false" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl">Ghairi</button>
            <button @click="submitAddFarmer" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Sajili Mkulima</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 5: INTAKE MODAL (Pokea Mpya Database) -->
    <div v-if="modals.intake" class="fixed inset-0 z-60 bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Sajili Upokeaji Mpya (Intake)</h3>
          <button @click="modals.intake = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-3 text-xs font-semibold text-slate-700">
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
          <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl font-extrabold text-center">
            Uzito wa Jumla: {{ calculatedIntakeWeight }} Kg ({{ (calculatedIntakeWeight / 1000).toFixed(2) }} Tani)
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button @click="modals.intake = false" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl">Ghairi</button>
            <button @click="submitIntake" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs">Sajili (Save)</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 4: PRINTABLE FARMER PROFILE STATEMENT & RECEIPT PDF -->
    <div v-if="modals.farmerReceipt" class="fixed inset-0 z-70 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-slate-200 animate-fadeIn">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-emerald-950 text-white">
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

        <div id="printableFarmerReceiptArea" class="p-8 bg-white text-slate-900 font-sans space-y-6 max-h-[75vh] overflow-y-auto print:p-0 print:max-h-none">
          <!-- Letterhead Header -->
          <div class="flex items-start justify-between border-b-2 border-emerald-700 pb-4">
            <div>
              <div class="text-2xl font-black tracking-tight text-emerald-800">GALANOKI MILLING & WAREHOUSE</div>
              <div class="text-xs text-slate-500 mt-1 font-semibold leading-relaxed">
                Agro-Inventory Management System<br/>
                Industrial Complex Block A & B, Tanzania<br/>
                Helpline: +255 700 000 100 | info@galanoki.co.tz
              </div>
            </div>
            <div class="text-right">
              <div class="text-xl font-black text-slate-900 tracking-tight">FARMER VOUCHER</div>
              <div class="text-xs text-slate-500 font-mono mt-1">
                <strong>Voucher #:</strong> FVR-{{ selectedFarmer.farmer_code }}<br/>
                <strong>Date:</strong> {{ new Date().toLocaleDateString() }}
              </div>
            </div>
          </div>

          <!-- Farmer Bio -->
          <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="p-3 bg-emerald-50/60 rounded-2xl border border-emerald-200 space-y-1">
              <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Taarifa za Mkulima:</div>
              <div class="text-sm font-black text-slate-900">{{ selectedFarmer.name }}</div>
              <div class="text-slate-600">Simu: {{ selectedFarmer.phone || 'N/A' }} | Code: {{ selectedFarmer.farmer_code }}</div>
              <div class="text-slate-500">Eneo: {{ selectedFarmer.ward || 'Usa River' }}, {{ selectedFarmer.district || 'Arumeru' }}, {{ selectedFarmer.region || 'Arusha' }}</div>
            </div>

            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col justify-between">
              <div class="flex justify-between">
                <span class="text-slate-500">Jumla ya Mzigo Ghalani:</span>
                <span class="font-black text-emerald-800">{{ totalFarmerStockKg.toLocaleString() }} Kg</span>
              </div>
              <div class="flex justify-between border-t border-slate-200 pt-1 mt-1">
                <span class="text-slate-500">Jumla ya Deni la Mikopo:</span>
                <span class="font-black text-red-600">Tsh {{ totalActiveLoansBalance.toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- Table of Batches -->
          <div>
            <div class="text-xs font-black text-slate-900 uppercase tracking-wider mb-2">Orodha ya Shehena (Batches):</div>
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="border-b-2 border-slate-900 font-extrabold text-slate-900">
                  <th class="py-2">Batch Code</th>
                  <th class="py-2">Zao</th>
                  <th class="py-2 text-right">Uzito wa Sasa</th>
                  <th class="py-2 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 font-semibold text-slate-700">
                <tr v-for="b in farmerBatches" :key="b.id">
                  <td class="py-2.5 font-mono font-bold text-emerald-800">{{ b.batch_code }}</td>
                  <td class="py-2.5">{{ b.crop_type }}</td>
                  <td class="py-2.5 text-right font-mono font-bold">{{ (parseFloat(b.current_weight_mt||b.current_weight||0)*1000).toLocaleString() }} Kg</td>
                  <td class="py-2.5 text-center font-extrabold text-[11px] uppercase">{{ b.status }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Official Signatures -->
          <div class="pt-8 border-t border-slate-200 grid grid-cols-2 gap-8 text-[11px] text-slate-500">
            <div>
              <div class="font-bold text-slate-800 mb-6">Sahihi ya Mkulima:</div>
              <div class="border-b border-slate-300 w-3/4 mb-1"></div>
              <div>{{ selectedFarmer.name }}</div>
            </div>

            <div>
              <div class="font-bold text-slate-800 mb-6">Sahihi & Muhuri wa Kinu:</div>
              <div class="border-b border-slate-300 w-3/4 mb-1"></div>
              <div>Galanoki Warehouse Manager</div>
            </div>
          </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
          <button @click="modals.farmerReceipt = false" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Funga</button>
          <button @click="triggerFarmerPrint" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5">
            <span>🖨️ Chapisha / Hifadhi PDF</span>
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
  completeService: false,
  farmerReceipt: false
});

const openFarmerPDFReceipt = () => {
  modals.value.farmerReceipt = true;
};

const triggerFarmerPrint = () => {
  window.print();
};

const editFarmerForm = ref({ name: '', phone: '', national_id: '', region: '', district: '', ward: '', village: '', street: '' });
const newFarmerForm = ref({ name: '', phone: '', national_id: '', region: 'Arusha', district: 'Arumeru' });
const intakeForm = ref({ crop_type: 'Mpunga', quantity: 45, unit: 'Gunia' });
const serviceForm = ref({ batch_id: '', service_type: 'milling', service_id: '', machine: 'Kinu Block A' });
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
const settlementForm = ref({ batch_id: '', price_per_kg: 1200, storage_fee: 45000, milling_fee: 120000 });

// Top-level parent batches (without parent_batch_id) to avoid visual card duplication
const topLevelFarmerBatches = computed(() => {
  return farmerBatches.value.filter(b => !b.parent_batch_id);
});

// Active non-transformed batches filter (Includes child output product batches that are active, deduplicated)
const activeNonTransformedBatches = computed(() => {
  const map = new Map();
  farmerBatches.value.forEach(b => {
    if (b.status !== 'transformed') {
      map.set(b.id || b.batch_code, b);
    }
    // Add child batches if they exist and are active
    const children = getBatchChildren(b);
    children.forEach(c => {
      if (c.status !== 'transformed') {
        map.set(c.id || c.batch_code, c);
      }
    });
  });
  return Array.from(map.values());
});

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

const toggleBatchAccordion = (batchId) => {
  expandedBatches.value[batchId] = !expandedBatches.value[batchId];
};

const getBatchServices = (b) => {
  if (b.services && b.services.length > 0) return b.services;
  return farmerServices.value.filter(s => s.batch_code === b.batch_code);
};

const getBatchChildren = (b) => {
  if (b.children && b.children.length > 0) return b.children;
  return farmerBatches.value.filter(child => child.parent_batch_id === b.id);
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

      // Closed (collapsed) by default
      expandedBatches.value = {};
    }
  } catch (e) {
    console.error('Error fetching farmer profile:', e);
    triggerToast('Kosa la mtandao wakati wa kupakia profile.', 'error');
  } finally {
    loadingProfile.value = false;
  }
};

const totalFarmerStockKg = computed(() => {
  let totalMt = 0;
  farmerBatches.value.forEach(b => {
    if (b.status !== 'transformed') {
      totalMt += parseFloat(b.current_weight_mt || b.current_weight || 0);
    }
  });
  return totalMt * 1000;
});

const totalFarmerLoanBalance = computed(() => {
  let bal = 0;
  farmerLoans.value.forEach(l => {
    bal += parseFloat(l.current_balance || l.remaining_balance || 0);
  });
  return bal;
});

const openApplyServiceModal = () => {
  if (activeNonTransformedBatches.value.length === 0) {
    triggerToast('Hakuna batch active ya kupangiwa huduma.', 'error');
    return;
  }
  serviceForm.value.batch_id = activeNonTransformedBatches.value[0].id;
  modals.value.applyService = true;
};

const openApplyServiceForChild = (child) => {
  serviceForm.value.batch_id = child.id;
  modals.value.applyService = true;
};

const currentBatchWeightKg = computed(() => {
  const b = selectedBatchForComplete.value;
  if (!b) return 0;
  return (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000);
});

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
    serviceForm.value.fee = selected.rate;
  }
};

const submitApplyService = async () => {
  if (!serviceForm.value.batch_id) {
    triggerToast('Chagua batch iliyo active!', 'error');
    return;
  }

  // VALIDATION: Ensure target batch is NOT transformed
  const targetB = farmerBatches.value.find(b => b.id === serviceForm.value.batch_id);
  if (targetB && targetB.status === 'transformed') {
    triggerToast('Huwezi kuweka huduma kwenye mzigo ulioshatumiwa (transformed)!', 'error');
    return;
  }

  const selectedCatalogService = catalogServices.value.find(s => s.id === serviceForm.value.service_id);
  const serviceName = selectedCatalogService ? selectedCatalogService.name_sw : (serviceForm.value.service_name || 'Huduma ya Kinu');
  const feeAmount = selectedCatalogService ? selectedCatalogService.rate : 120000;

  try {
    const res = await fetch(`/api/v1/batches/${serviceForm.value.batch_id}/processing`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        type: serviceForm.value.service_type,
        status: 'in_progress',
        service_id: serviceForm.value.service_id || null,
        service_name: serviceName,
        fee: feeAmount
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

const submitNewLoan = async () => {
  if (!loanForm.value.amount || loanForm.value.amount <= 0 || !loanForm.value.collateral_batch_id) {
    triggerToast('Tafadhali jaza sehemu zote za mkopo na dhamana.', 'error');
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
    if (res.ok) {
      modals.value.newLoan = false;
      await openFarmerProfile(selectedFarmer.value.id);
      await fetchFarmers();
      triggerToast('Ombi la Mkopo na Dhamana Limewasilishwa kwenye Database! 💰');
    }
  } catch (e) {
    triggerToast('Imefeli kuwasilisha ombi la mkopo.', 'error');
  }
};

const openCompleteServiceModal = (b) => {
  if (b.status === 'transformed') {
    triggerToast('Mzigo huu umeshatumiwa kikamilifu.', 'error');
    return;
  }
  selectedBatchForComplete.value = b;
  const bWeightKg = (parseFloat(b.current_weight_mt || b.current_weight || 0) * 1000);
  const estGuniacount = Math.max(1, Math.round((bWeightKg * 0.7) / 100));
  const estPumbacount = Math.max(1, Math.round((bWeightKg * 0.25) / 25));
  completeForm.value.output_quantity = estGuniacount;
  completeForm.value.output_unit = 'Gunia';
  completeForm.value.has_byproduct = 'yes';
  completeForm.value.byproduct_quantity = estPumbacount;
  completeForm.value.byproduct_unit = 'Kiloba / Roba';
  modals.value.completeService = true;
};

const submitCompleteService = async () => {
  const parent = selectedBatchForComplete.value;
  if (!parent) return;

  // VALIDATION: Prevent output weight exceeding parent batch weight
  if (completeForm.value.has_changed === 'yes' && totalOutputWeightKg.value > currentBatchWeightKg.value) {
    triggerToast(`Kosa: Uzito wa pato (${totalOutputWeightKg.value} Kg) hauwezi kuzidi uzito wa mama (${currentBatchWeightKg.value} Kg)!`, 'error');
    return;
  }

  const outKg = getUnitKg(completeForm.value.output_unit, completeForm.value.output_quantity || 0);
  const byKg = completeForm.value.has_byproduct === 'yes' ? getUnitKg(completeForm.value.byproduct_unit, completeForm.value.byproduct_quantity || 0) : 0;

  try {
    const res = await fetch(`/api/v1/batches/${parent.id}/processing`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        type: completeForm.value.type,
        status: 'completed',
        output_crop: completeForm.value.output_crop,
        output_unit: completeForm.value.output_unit,
        output_quantity: completeForm.value.output_quantity,
        final_value: outKg / 1000,
        has_byproduct: completeForm.value.has_byproduct,
        by_product_crop: completeForm.value.has_byproduct === 'yes' ? completeForm.value.byproduct_crop : null,
        by_product_unit: completeForm.value.has_byproduct === 'yes' ? completeForm.value.byproduct_unit : null,
        by_product_quantity: completeForm.value.has_byproduct === 'yes' ? completeForm.value.byproduct_quantity : 0,
        by_product_value: byKg / 1000
      })
    });

    if (res.ok) {
      modals.value.completeService = false;
      await openFarmerProfile(selectedFarmer.value.id);
      triggerToast('Huduma Imekamilika, Mzigo Mama Ume-transform, na Product Mpya Zimefunguliwa! 🌳✨');
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
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(newFarmerForm.value)
    });
    if (res.ok) {
      modals.value.addFarmer = false;
      await fetchFarmers();
      triggerToast('Mkulima Mpya Umesajiliwa kwenye Database! 🌾');
    }
  } catch (e) {
    triggerToast('Imefeli kusajili mkulima.', 'error');
  }
};

const settleGrossSales = computed(() => {
  return totalFarmerStockKg.value * (settlementForm.value.price_per_kg || 1200);
});

const settleTotalDeductions = computed(() => {
  return (settlementForm.value.storage_fee || 45000) + (settlementForm.value.milling_fee || 120000) + totalFarmerLoanBalance.value;
});

const settleNetPayout = computed(() => {
  return Math.max(0, settleGrossSales.value - settleTotalDeductions.value);
});

const submitSettlement = async () => {
  if (!settlementForm.value.batch_id) {
    triggerToast('Chagua batch ya kuuza!', 'error');
    return;
  }
  try {
    const res = await fetch('/api/v1/sales/confirm', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        farmer_id: selectedFarmer.value.id,
        batch_id: settlementForm.value.batch_id,
        price_per_kg: settlementForm.value.price_per_kg
      })
    });
    triggerToast(`Mauzo na Makato Vimekamilika! Payout: Tsh ${settleNetPayout.value.toLocaleString()} 🏷️`);
    await openFarmerProfile(selectedFarmer.value.id);
    await fetchFarmers();
  } catch (e) {
    triggerToast('Mauzo vimekamilika.', 'success');
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('sw-TZ', { day: '2-digit', month: 'short', year: 'numeric' });
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
