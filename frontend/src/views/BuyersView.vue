<template>
  <div class="space-y-6 text-slate-800 dark:text-slate-100">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <router-link to="/" class="hover:text-emerald-600">Dashboard</router-link>
          <span>/</span>
          <span class="text-slate-700 dark:text-slate-200 font-bold">Wanunuzi</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
          <span>🤝 Wanunuzi wa Mashirika</span>
          <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-extrabold">Corporate Ledger</span>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Simamia makampuni ya wanunuzi wa mazao, mawakala wa mauzo na historia ya ankara zao.</p>
      </div>

      <button 
        @click="openNewBuyerModal"
        class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center justify-center gap-2 self-start sm:self-auto"
      >
        <span>➕</span>
        <span>Sajili Mnunuzi Mpya</span>
      </button>
    </div>

    <!-- Real Database KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <!-- 1. Total Buyers -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl text-xl font-bold">
          🏢
        </div>
        <div>
          <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Jumla ya Wanunuzi</p>
          <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ dbStats.total_buyers_count || buyersList.length }}</h3>
          <p class="text-[10.5px] text-slate-400 mt-0.5">Makampuni yaliyosajiliwa</p>
        </div>
      </div>

      <!-- 2. Total Gross Sales Revenue -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
        <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-xl text-xl font-bold">
          💰
        </div>
        <div>
          <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Jumla ya Mauzo Yote</p>
          <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">TZS {{ formatCurrency(dbStats.total_sales_revenue || totalSpentAll) }}</h3>
          <p class="text-[10.5px] text-slate-400 mt-0.5">Mapato yote ya manunuzi</p>
        </div>
      </div>

      <!-- 3. Total Deductions -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
        <div class="p-3 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-xl text-xl font-bold">
          📉
        </div>
        <div>
          <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Jumla ya Makato Yote</p>
          <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 mt-0.5">TZS {{ formatCurrency(dbStats.total_deductions) }}</h3>
          <p class="text-[10.5px] text-slate-400 mt-0.5">Hifadhi, Processing na Mikopo</p>
        </div>
      </div>

      <!-- 4. Top Buyer Card (Real DB) -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
        <div class="p-3 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl text-xl font-bold">
          🏆
        </div>
        <div>
          <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Mnunuzi Kinara (Mzigo Mkudwa)</p>
          <h3 class="text-base font-black text-amber-600 dark:text-amber-400 mt-0.5 truncate max-w-[170px]">
            {{ dbStats.top_buyer ? dbStats.top_buyer.name : 'Bado Hakuna' }}
          </h3>
          <p v-if="dbStats.top_buyer" class="text-[10.5px] text-slate-500 font-bold mt-0.5">
            {{ formatCurrency(dbStats.top_buyer.total_quantity) }} Units/Kg | TZS {{ formatCurrency(dbStats.top_buyer.total_spent) }}
          </p>
          <p v-else class="text-[10.5px] text-slate-400 mt-0.5">Bado hakuna manunuzi</p>
        </div>
      </div>
    </div>

    <!-- Main Buyer Log Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
      <!-- Toolbar -->
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
          <span>📋 Orodha ya Wanunuzi Waliosajiliwa na Mauzo Yao</span>
          <span class="px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-[11px] text-slate-600 dark:text-slate-400 font-bold">
            {{ filteredBuyers.length }}
          </span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <div class="relative w-full sm:w-64">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">🔍</span>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Tafuta mnunuzi, namba ya simu..." 
              class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
            />
          </div>
        </div>
      </div>

      <!-- Buyer Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider text-[10.5px]">
              <th class="py-3.5 px-4">Jina la Kampuni</th>
              <th class="py-3.5 px-4">Mtu wa Mawasiliano / Simu</th>
              <th class="py-3.5 px-4">Mzigo Alioununua</th>
              <th class="py-3.5 px-4">Jumla ya Pesa Zilizotumika</th>
              <th class="py-3.5 px-4">Wakulima Alionunua Kwao</th>
              <th class="py-3.5 px-4">Mazao Aliyonunua</th>
              <th class="py-3.5 px-4">Hali</th>
              <th class="py-3.5 px-4 text-right">Vitendo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
            <tr v-if="loading" class="text-center py-8">
              <td colspan="8" class="py-10 text-slate-400 font-bold text-xs">
                ⏳ Inapakia wanunuzi kutoka kwenye database...
              </td>
            </tr>
            <tr v-else-if="filteredBuyers.length === 0" class="text-center py-8">
              <td colspan="8" class="py-10 text-slate-400 font-bold text-xs">
                🔍 Hakuna mnunuzi aliyepatikana kulingana na utafutaji wako.
              </td>
            </tr>
            <tr 
              v-else
              v-for="buyer in filteredBuyers" 
              :key="buyer.id"
              class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
            >
              <!-- Company Name -->
              <td class="py-3.5 px-4">
                <div class="font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                  <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">🏢</span>
                  <span>{{ buyer.name }}</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-0.5">TIN: {{ buyer.tax_number || 'N/A' }}</span>
              </td>

              <!-- Contact Person & Phone -->
              <td class="py-3.5 px-4">
                <div class="font-bold text-slate-800 dark:text-slate-200">{{ buyer.contact_person || 'N/A' }}</div>
                <div class="text-[10.5px] text-slate-500 font-semibold">{{ buyer.phone || 'N/A' }}</div>
              </td>

              <!-- Total Quantity Bought -->
              <td class="py-3.5 px-4 font-black text-amber-600 dark:text-amber-400">
                {{ formatCurrency(buyer.total_quantity) }} Units/Kg
              </td>

              <!-- Total Spent -->
              <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white">
                TZS {{ formatCurrency(buyer.total_spent) }}
              </td>

              <!-- Farmers Supported / Bought From -->
              <td class="py-3.5 px-4 font-bold text-slate-700 dark:text-slate-300">
                <div v-if="buyer.farmers && buyer.farmers.length > 0" class="flex flex-wrap gap-1">
                  <span 
                    v-for="(fName, idx) in buyer.farmers" 
                    :key="idx"
                    class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10.5px] font-extrabold border border-emerald-200/60 dark:border-emerald-800/60"
                  >
                    👤 {{ fName }}
                  </span>
                </div>
                <span v-else class="text-slate-400 font-semibold">Bado hajafanya manunuzi</span>
              </td>

              <!-- Crops Purchased -->
              <td class="py-3.5 px-4">
                <div v-if="buyer.crops && buyer.crops.length > 0" class="flex flex-wrap gap-1">
                  <span 
                    v-for="(cType, idx) in buyer.crops" 
                    :key="idx"
                    class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-[10.5px] font-extrabold border border-blue-200/60 dark:border-blue-800/60"
                  >
                    🌾 {{ cType }}
                  </span>
                </div>
                <span v-else class="text-slate-400 font-semibold">N/A</span>
              </td>

              <!-- Status -->
              <td class="py-3.5 px-4">
                <span 
                  :class="buyer.status === 'active' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border-slate-200 dark:border-slate-700'"
                  class="px-2.5 py-1 rounded-lg text-[10.5px] font-black border inline-flex items-center gap-1"
                >
                  <span>{{ buyer.status === 'active' ? '🟢 Active' : '⚪ Inactive' }}</span>
                </span>
              </td>

              <!-- Actions -->
              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button 
                    @click="viewBuyerHistory(buyer)"
                    title="Historia na Ankara"
                    class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-950 text-slate-700 dark:text-slate-300 hover:text-emerald-600 font-extrabold text-[11px] rounded-lg border border-slate-200 dark:border-slate-700 transition cursor-pointer flex items-center gap-1"
                  >
                    <span>📜 Historia (Ankara)</span>
                  </button>

                  <button 
                    @click="openEditBuyerModal(buyer)"
                    title="Badili Mnunuzi"
                    class="px-2 py-1 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 text-blue-600 dark:text-blue-400 font-bold text-[11px] rounded-lg border border-blue-200 dark:border-blue-800 transition cursor-pointer"
                  >
                    ✏️
                  </button>

                  <button 
                    @click="deleteBuyer(buyer)"
                    title="Futa Mnunuzi"
                    class="px-2 py-1 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 text-rose-600 dark:text-rose-400 font-bold text-[11px] rounded-lg border border-rose-200 dark:border-rose-800 transition cursor-pointer"
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

    <!-- MODAL 1: Add New Buyer -->
    <transition name="fade">
      <div v-if="showNewBuyerModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden animate-fadeIn">
          
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="font-black text-base text-slate-900 dark:text-white flex items-center gap-2">
              <span>🤝</span>
              <span>Sajili Mnunuzi Mpya</span>
            </h3>
            <button @click="showNewBuyerModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitNewBuyer" class="p-6 space-y-4 text-left">
            
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Jina la Kampuni / Mnunuzi *</label>
              <input 
                type="text" 
                v-model="buyerForm.name" 
                required
                placeholder="e.g. AgriCo Ltd"
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Mtu wa Mawasiliano (Contact Person)</label>
              <input 
                type="text" 
                v-model="buyerForm.contact_person" 
                placeholder="e.g. David Ouma"
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Namba ya Simu</label>
                <input 
                  type="text" 
                  v-model="buyerForm.phone" 
                  placeholder="e.g. 0788 111 222"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                />
              </div>

              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Barua Pepe (Email)</label>
                <input 
                  type="email" 
                  v-model="buyerForm.email" 
                  placeholder="e.g. info@agrico.co.tz"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">TIN / Namba ya Kodi (Tax Number)</label>
              <input 
                type="text" 
                v-model="buyerForm.tax_number" 
                placeholder="e.g. TIN-882-991-002"
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              />
            </div>

            <div class="grid grid-cols-2 gap-2 pt-3">
              <button 
                type="button"
                @click="showNewBuyerModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Ghairi
              </button>
              <button 
                type="submit" 
                :disabled="submitting || !buyerForm.name"
                class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50"
              >
                <span>{{ submitting ? 'Inasajili...' : 'Hifadhi Mnunuzi →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

    <!-- MODAL 2: Edit Buyer -->
    <transition name="fade">
      <div v-if="showEditBuyerModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden animate-fadeIn">
          
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="font-black text-base text-slate-900 dark:text-white flex items-center gap-2">
              <span>✏️</span>
              <span>Badili Taarifa za Mnunuzi</span>
            </h3>
            <button @click="showEditBuyerModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitEditBuyer" class="p-6 space-y-4 text-left">
            
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Jina la Kampuni *</label>
              <input 
                type="text" 
                v-model="editBuyerForm.name" 
                required
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Mtu wa Mawasiliano</label>
              <input 
                type="text" 
                v-model="editBuyerForm.contact_person" 
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Namba ya Simu</label>
                <input 
                  type="text" 
                  v-model="editBuyerForm.phone" 
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                />
              </div>

              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Barua Pepe</label>
                <input 
                  type="email" 
                  v-model="editBuyerForm.email" 
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                />
              </div>
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">TIN / Namba ya Kodi</label>
              <input 
                type="text" 
                v-model="editBuyerForm.tax_number" 
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40"
              />
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
              <button 
                type="button"
                @click="showEditBuyerModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Ghairi
              </button>
              <button 
                type="submit" 
                :disabled="submitting || !editBuyerForm.name"
                class="py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-blue-400/30 transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50"
              >
                <span>{{ submitting ? 'Inasave...' : 'Hifadhi Mabadiliko →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

    <!-- MODAL 3: Buyer History Ledger with Farmer Details -->
    <transition name="fade">
      <div v-if="showHistoryModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-3xl overflow-hidden animate-fadeIn">
          
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <div>
              <h3 class="font-black text-base text-slate-900 dark:text-white flex items-center gap-2">
                <span>📜 Historia ya Ankara & Manunuzi</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">({{ selectedBuyerHistory?.name }})</span>
              </h3>
              <p class="text-[11px] text-slate-400 mt-0.5">Inaonyesha ankara zote, mazao aliyonunua, na wakulima alionunua kwao.</p>
            </div>
            <button @click="showHistoryModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">✕</button>
          </div>

          <div class="p-6 overflow-y-auto max-h-[60vh]">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-extrabold border-b border-slate-100 dark:border-slate-800 uppercase text-[10.5px]">
                  <th class="py-2.5 px-3">Ankara #</th>
                  <th class="py-2.5 px-3">Tarehe</th>
                  <th class="py-2.5 px-3">Aina ya Zao</th>
                  <th class="py-2.5 px-3">Mzigo (Units/Kg)</th>
                  <th class="py-2.5 px-3">Mkulima Aliyeuza</th>
                  <th class="py-2.5 px-3">Jumla ya Fedha (TZS)</th>
                  <th class="py-2.5 px-3">Hali</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                <tr v-if="buyerInvoicesHistory.length === 0">
                  <td colspan="7" class="py-8 text-center text-slate-400 font-bold">
                    ℹ️ Mnunuzi huyu bado hana kumbukumbu ya ankara yoyote.
                  </td>
                </tr>
                <tr v-else v-for="inv in buyerInvoicesHistory" :key="inv.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                  <td class="py-2.5 px-3 font-extrabold text-slate-900 dark:text-white">
                    {{ inv.invoice_number }}
                  </td>
                  <td class="py-2.5 px-3 text-slate-500 font-semibold">
                    {{ inv.created_at || 'N/A' }}
                  </td>
                  <td class="py-2.5 px-3 font-bold text-blue-600 dark:text-blue-400">
                    {{ inv.crop_types }}
                  </td>
                  <td class="py-2.5 px-3 font-extrabold text-amber-600 dark:text-amber-400">
                    {{ formatCurrency(inv.total_quantity) }}
                  </td>
                  <td class="py-2.5 px-3 font-bold text-emerald-700 dark:text-emerald-300">
                    👤 {{ inv.farmer_names }}
                  </td>
                  <td class="py-2.5 px-3 font-black text-slate-900 dark:text-white">
                    TZS {{ formatCurrency(inv.subtotal) }}
                  </td>
                  <td class="py-2.5 px-3">
                    <span 
                      :class="inv.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200'"
                      class="px-2 py-0.5 rounded text-[10px] font-black border"
                    >
                      {{ inv.status === 'paid' ? '✅ Paid' : '⏳ Unpaid' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-right">
            <button 
              @click="showHistoryModal = false"
              class="py-2 px-4 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl cursor-pointer"
            >
              Funga
            </button>
          </div>

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

const buyersList = ref([]);
const dbStats = ref({
  total_sales_revenue: 0,
  total_deductions: 0,
  total_buyers_count: 0,
  total_volume_sold: 0,
  top_buyer: null
});

const loading = ref(false);
const submitting = ref(false);

const searchQuery = ref('');

const showNewBuyerModal = ref(false);
const showEditBuyerModal = ref(false);
const showHistoryModal = ref(false);

const selectedBuyerHistory = ref(null);
const buyerInvoicesHistory = ref([]);

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

const buyerForm = ref({
  name: '',
  contact_person: '',
  phone: '',
  email: '',
  tax_number: ''
});

const editBuyerForm = ref({
  id: '',
  name: '',
  contact_person: '',
  phone: '',
  email: '',
  tax_number: ''
});

const totalSpentAll = computed(() => {
  return buyersList.value.reduce((acc, b) => acc + parseFloat(b.total_spent || 0), 0);
});

const filteredBuyers = computed(() => {
  if (!searchQuery.value.trim()) return buyersList.value;
  const q = searchQuery.value.toLowerCase();
  return buyersList.value.filter(b => 
    (b.name && b.name.toLowerCase().includes(q)) ||
    (b.contact_person && b.contact_person.toLowerCase().includes(q)) ||
    (b.phone && b.phone.includes(q)) ||
    (b.tax_number && b.tax_number.toLowerCase().includes(q))
  );
});

const fetchBuyersData = async () => {
  loading.value = true;
  try {
    const [buyersRes, statsRes] = await Promise.all([
      fetch('/api/v1/buyers'),
      fetch('/api/v1/buyers/stats')
    ]);

    if (buyersRes.ok) {
      buyersList.value = await buyersRes.json();
    }
    if (statsRes.ok) {
      dbStats.value = await statsRes.json();
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kupakua taarifa za wanunuzi', 'error');
  } finally {
    loading.value = false;
  }
};

const openNewBuyerModal = () => {
  buyerForm.value = {
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    tax_number: ''
  };
  showNewBuyerModal.value = true;
};

const submitNewBuyer = async () => {
  if (!buyerForm.value.name.trim()) {
    triggerToast('Tafadhali ingiza jina la kampuni', 'error');
    return;
  }

  submitting.value = true;
  try {
    const res = await fetch('/api/v1/buyers', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(buyerForm.value)
    });
    const data = await res.json();
    if (res.ok && data.success) {
      triggerToast('Mnunuzi amesajiliwa kikamilifu!');
      showNewBuyerModal.value = false;
      await fetchBuyersData();
    } else {
      triggerToast(data.error || data.message || 'Imeshindwa kusajili mnunuzi', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kusajili mnunuzi', 'error');
  } finally {
    submitting.value = false;
  }
};

const openEditBuyerModal = (buyer) => {
  editBuyerForm.value = {
    id: buyer.id,
    name: buyer.name,
    contact_person: buyer.contact_person || '',
    phone: buyer.phone || '',
    email: buyer.email || '',
    tax_number: buyer.tax_number || ''
  };
  showEditBuyerModal.value = true;
};

const submitEditBuyer = async () => {
  if (!editBuyerForm.value.name.trim()) {
    triggerToast('Tafadhali ingiza jina la kampuni', 'error');
    return;
  }

  submitting.value = true;
  try {
    const res = await fetch(`/api/v1/buyers/${editBuyerForm.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(editBuyerForm.value)
    });
    const data = await res.json();
    if (res.ok && data.success) {
      triggerToast('Taarifa za mnunuzi zimesasishwa!');
      showEditBuyerModal.value = false;
      await fetchBuyersData();
    } else {
      triggerToast(data.error || data.message || 'Imeshindwa kusasisha mnunuzi', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kusasisha mnunuzi', 'error');
  } finally {
    submitting.value = false;
  }
};

const deleteBuyer = async (buyer) => {
  if (!confirm(`Je, una uhakika unataka kufuta mnunuzi "${buyer.name}"?`)) {
    return;
  }

  try {
    const res = await fetch(`/api/v1/buyers/${buyer.id}`, {
      method: 'DELETE'
    });
    const data = await res.json();
    if (res.ok && data.success) {
      triggerToast('Mnunuzi umefutwa kikamilifu!');
      await fetchBuyersData();
    } else {
      triggerToast(data.error || data.message || 'Imeshindwa kufuta mnunuzi', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kufuta mnunuzi', 'error');
  }
};

const viewBuyerHistory = async (buyer) => {
  selectedBuyerHistory.value = buyer;
  buyerInvoicesHistory.value = [];
  showHistoryModal.value = true;

  try {
    const res = await fetch(`/api/v1/buyers/${buyer.id}/history`);
    if (res.ok) {
      const data = await res.json();
      buyerInvoicesHistory.value = data.invoices || [];
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kupakua historia ya mnunuzi', 'error');
  }
};

onMounted(() => {
  fetchBuyersData();
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
