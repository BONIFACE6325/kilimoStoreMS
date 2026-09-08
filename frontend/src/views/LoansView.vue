<template>
  <div class="space-y-6">
    
    <!-- Top Header Banner & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 rounded-2xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 text-lg">💰</span>
          <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Mikopo & Dhamana za Mazao</h1>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">
          Usimamizi kamili wa mikopo inayotolewa kwa wakulima kwa kuwekea dhamana mazao yao yaliyopo ghalani (0% Interest).
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button 
          @click="fetchLoansData"
          class="p-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl transition cursor-pointer text-xs font-bold flex items-center gap-1.5"
          title="Refresh Data"
        >
          <span>🔄</span>
          <span>Refresh</span>
        </button>
        <button 
          @click="openNewLoanModal"
          class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center gap-2"
        >
          <span>+ Sajili Mkopo Mpya</span>
        </button>
      </div>
    </div>

    <!-- Executive Loan Metrics KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      
      <!-- KPI 1: Total Disbursed Loans -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jumla ya Mikopo</span>
          <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm shadow-inner">
            💳
          </div>
        </div>
        <div>
          <div class="text-lg font-black text-slate-900 dark:text-white tracking-tight">
            TZS {{ formatCurrency(totalPrincipalAmount) }}
          </div>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Iliyotolewa:</span>
          <span class="font-extrabold text-blue-600 dark:text-blue-400">{{ loansList.length }} Mikopo</span>
        </div>
      </div>

      <!-- KPI 2: Outstanding Balance -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Salio Linalodaiwa</span>
          <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shadow-inner">
            ⏳
          </div>
        </div>
        <div>
          <div class="text-lg font-black text-amber-600 dark:text-amber-400 tracking-tight">
            TZS {{ formatCurrency(totalOutstandingBalance) }}
          </div>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Hali:</span>
          <span class="font-extrabold text-amber-600 dark:text-amber-400">{{ activeLoansCount }} Inayoendelea</span>
        </div>
      </div>

      <!-- KPI 3: Recovered / Repaid Funds -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jumla Marejesho</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shadow-inner">
            ✅
          </div>
        </div>
        <div>
          <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
            TZS {{ formatCurrency(totalRepaidAmount) }}
          </div>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Mrejesho Pct:</span>
          <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ recoveryRate }}%</span>
        </div>
      </div>

      <!-- KPI 4: Active Loans Count -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mikopo Hai</span>
          <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm shadow-inner">
            📊
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ activeLoansCount }} <span class="text-xs font-bold text-slate-400">Loans</span>
          </div>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Inayodaiwa:</span>
          <span class="font-extrabold text-indigo-600 dark:text-indigo-400">Wakulima {{ activeLoansCount }}</span>
        </div>
      </div>

      <!-- KPI 5: Settled Loans Count -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mikopo Iliyokamilika</span>
          <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm shadow-inner">
            🏆
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ settledLoansCount }} <span class="text-xs font-bold text-slate-400">Settled</span>
          </div>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Kufunga Mkopo:</span>
          <span class="font-extrabold text-purple-600 dark:text-purple-400">100% Repaid</span>
        </div>
      </div>

      <!-- KPI 6: Interest Rate Policy -->
      <div class="bg-white dark:bg-slate-900 p-4.5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-2 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sera ya Riba</span>
          <div class="w-8 h-8 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-sm shadow-inner">
            🛡️
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-teal-600 dark:text-teal-400 tracking-tight">
            0.00% <span class="text-xs font-bold text-slate-400">Riba</span>
          </div>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10.5px]">
          <span class="text-slate-400 font-medium">Vipimo vya Mfumo:</span>
          <span class="font-extrabold text-teal-600 dark:text-teal-400">Strictly Zero Interest</span>
        </div>
      </div>

    </div>

    <!-- Master Loans Data Table & Controls -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden space-y-4 transition-colors">
      
      <!-- Table Filter Bar -->
      <div class="p-6 pb-2 border-b border-slate-100 dark:border-slate-800 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
              <span>📋 Rejesta Kuu ya Mikopo ya Wakulima</span>
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                {{ filteredLoans.length }} Records
              </span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
              Orodha ya mikopo yote iliyotolewa na hali yake ya sasa ya marejesho.
            </p>
          </div>

          <!-- Search Input -->
          <div class="relative w-full sm:w-72">
            <input 
              type="text" 
              v-model="searchQuery"
              placeholder="Tafuta mkulima, namba ya mkopo..." 
              class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 transition"
            />
            <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
          </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/60 overflow-x-auto">
          <button 
            @click="statusFilter = ''"
            :class="statusFilter === '' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-extrabold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
          >
            Mikopo Yote ({{ loansList.length }})
          </button>
          <button 
            @click="statusFilter = 'active'"
            :class="statusFilter === 'active' ? 'bg-amber-600 text-white font-extrabold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5"
          >
            <span>⏳ Inayoendelea</span>
            <span class="px-1.5 py-0.2 rounded-md bg-amber-700 text-[10px] text-white">{{ activeLoansCount }}</span>
          </button>
          <button 
            @click="statusFilter = 'settled'"
            :class="statusFilter === 'settled' ? 'bg-emerald-600 text-white font-extrabold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5"
          >
            <span>✅ Iliyokamilika</span>
            <span class="px-1.5 py-0.2 rounded-md bg-emerald-700 text-[10px] text-white">{{ settledLoansCount }}</span>
          </button>
        </div>
      </div>

      <!-- Table Body -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider text-[10.5px]">
              <th class="py-3.5 px-4">Namba ya Mkopo</th>
              <th class="py-3.5 px-4">Mkulima</th>
              <th class="py-3.5 px-4">Batch ya Dhamana</th>
              <th class="py-3.5 px-4">Kiasi cha Mkopo</th>
              <th class="py-3.5 px-4">Riba</th>
              <th class="py-3.5 px-4">Salio Linalodaiwa</th>
              <th class="py-3.5 px-4">Siku ya Mwisho</th>
              <th class="py-3.5 px-4">Hali</th>
              <th class="py-3.5 px-4 text-right">Vitendo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
            <tr v-if="loading" class="text-center py-8">
              <td colspan="9" class="py-10 text-slate-400 font-bold text-xs">
                ⏳ Inapakia mikopo kutoka kwenye database...
              </td>
            </tr>
            <tr v-else-if="filteredLoans.length === 0" class="text-center py-8">
              <td colspan="9" class="py-10 text-slate-400 font-bold text-xs">
                🔍 Hakuna kumbukumbu ya mkopo uliopatikana kulingana na vigezo vyako.
              </td>
            </tr>
            <tr 
              v-else
              v-for="loan in filteredLoans" 
              :key="loan.id"
              class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
            >
              <!-- Loan Code & Created Date -->
              <td class="py-3.5 px-4">
                <div class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                  <span class="p-1 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">💳</span>
                  <span>{{ loan.loan_code }}</span>
                </div>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ loan.created_at }}</span>
              </td>

              <!-- Borrower Farmer Name -->
              <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-white">
                {{ loan.farmer_name }}
              </td>

              <!-- Collateral Batch Code -->
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold text-[11px] border border-slate-200 dark:border-slate-700 inline-flex items-center gap-1">
                  <span>📦</span>
                  <span>{{ loan.collateral_batch || 'N/A' }}</span>
                </span>
              </td>

              <!-- Principal Amount -->
              <td class="py-3.5 px-4 font-black text-slate-900 dark:text-white">
                TZS {{ formatCurrency(loan.principal_amount) }}
              </td>

              <!-- Interest Rate (Strictly 0.00%) -->
              <td class="py-3.5 px-4 font-extrabold text-emerald-600 dark:text-emerald-400">
                0.00% (No Interest)
              </td>

              <!-- Current Balance -->
              <td class="py-3.5 px-4 font-black" :class="parseFloat(loan.current_balance) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'">
                TZS {{ formatCurrency(loan.current_balance) }}
              </td>

              <!-- Due Date -->
              <td class="py-3.5 px-4 font-bold text-slate-600 dark:text-slate-400">
                {{ loan.due_date || 'N/A' }}
              </td>

              <!-- Status Badge -->
              <td class="py-3.5 px-4">
                <span 
                  :class="loan.status === 'settled' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800'"
                  class="px-2.5 py-1 rounded-lg text-[10.5px] font-black border inline-flex items-center gap-1"
                >
                  <span v-if="loan.status === 'settled'">✅ Iliyokamilika</span>
                  <span v-else>⏳ Active</span>
                </span>
              </td>

              <!-- Action Buttons -->
              <td class="py-3.5 px-4 text-right">
                <button 
                  v-if="loan.status !== 'settled' && parseFloat(loan.current_balance) > 0"
                  @click="openRepayModal(loan)"
                  class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-xs border border-emerald-400/30 transition cursor-pointer"
                >
                  💵 Rejesha Mkopo
                </button>
                <span v-else class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                  ✓ Paid Off
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- MODAL 1: Register New Loan -->
    <transition name="fade">
      <div v-if="showNewLoanModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 max-w-lg w-full space-y-4 transform transition-all">
          
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
              <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-base">💰</span>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Sajili Mkopo Mpya kwa Mkulima</h3>
            </div>
            <button @click="showNewLoanModal = false" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 text-lg font-bold cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitNewLoan" class="space-y-4 text-left">
            
            <!-- Select Farmer -->
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Chagua Mkulima Mwombaji</label>
              <select 
                v-model="loanForm.farmer_id" 
                @change="onFarmerChange"
                required
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              >
                <option value="" disabled>-- Chagua Mkulima --</option>
                <option v-for="farmer in farmersList" :key="farmer.id" :value="farmer.id">
                  {{ farmer.name }} ({{ farmer.phone || 'Hakuna Simu' }})
                </option>
              </select>
            </div>

            <!-- Select Collateral Batch -->
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Chagua Batch ya Mzigo Ghalani (Dhamana)</label>
              <select 
                v-model="loanForm.collateral_batch_id" 
                @change="onBatchChange"
                required
                :disabled="!loanForm.farmer_id"
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40 disabled:opacity-50"
              >
                <option value="" disabled>-- {{ farmerBatches.length > 0 ? 'Chagua Batch ya Dhamana' : 'Hakuna Batch Ghalani kwa Mkulima Huyu' }} --</option>
                <option v-for="batch in farmerBatches" :key="batch.id" :value="batch.id">
                  Batch: {{ batch.batch_code }} - {{ batch.crop_type }} ({{ batch.intake_quantity || batch.current_weight }} {{ batch.intake_unit || 'Gunia' }})
                </option>
              </select>
            </div>

            <!-- Live Collateral Limit Card -->
            <div v-if="selectedBatchInfo" class="p-3.5 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-2xl border border-indigo-200/80 dark:border-indigo-800/60 space-y-1 text-xs">
              <div class="flex items-center justify-between">
                <span class="font-bold text-indigo-900 dark:text-indigo-200">Mzigo Ghalani:</span>
                <span class="font-black text-indigo-700 dark:text-indigo-300">{{ selectedBatchInfo.intake_quantity || selectedBatchInfo.current_weight }} {{ selectedBatchInfo.intake_unit || 'Gunia' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="font-bold text-indigo-900 dark:text-indigo-200">Kikomo cha Mkopo (50% Collateral Limit):</span>
                <span class="font-black text-emerald-600 dark:text-emerald-400">TZS {{ formatCurrency(selectedBatchMaxLoan) }}</span>
              </div>
              <p class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-1">
                ℹ️ Kwa sera ya mfumo, mkopo hauwezi kuzidi 50% ya thamani ya mzigo wa mkulima uliopo ghalani.
              </p>
            </div>

            <!-- Principal Loan Amount Input -->
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Kiasi cha Mkopo Unachoombwa (TZS)</label>
              <input 
                type="number" 
                v-model.number="loanForm.principal_amount" 
                required
                min="1"
                placeholder="e.g. 500000"
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              />
              <span v-if="selectedBatchMaxLoan && loanForm.principal_amount > selectedBatchMaxLoan" class="text-[11px] font-bold text-rose-500 block">
                ⚠️ Kiasi kinazidi kikomo cha TZS {{ formatCurrency(selectedBatchMaxLoan) }}!
              </span>
            </div>

            <!-- Due Date Selector -->
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Tarehe ya Mwisho ya Marejesho (Due Date)</label>
              <input 
                type="date" 
                v-model="loanForm.due_date" 
                required
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              />
            </div>

            <!-- Modal Action Buttons -->
            <div class="grid grid-cols-2 gap-2 pt-3">
              <button 
                type="button"
                @click="showNewLoanModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Ghairi
              </button>
              <button 
                type="submit" 
                :disabled="submitting || (selectedBatchMaxLoan && loanForm.principal_amount > selectedBatchMaxLoan)"
                class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50"
              >
                <span>{{ submitting ? 'Inasajili...' : 'Thibitisha Mkopo →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

    <!-- MODAL 2: Record Loan Repayment -->
    <transition name="fade">
      <div v-if="showRepayModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 max-w-md w-full space-y-4 transform transition-all">
          
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
              <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-base">💵</span>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Rekodi Rejesho la Mkopo</h3>
            </div>
            <button @click="showRepayModal = false" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 text-lg font-bold cursor-pointer">✕</button>
          </div>

          <div v-if="activeRepayLoan" class="p-3.5 bg-slate-50 dark:bg-slate-800/70 rounded-2xl space-y-1 text-xs text-left border border-slate-200 dark:border-slate-700/60">
            <div class="font-extrabold text-slate-900 dark:text-white">Mkopo: {{ activeRepayLoan.loan_code }}</div>
            <div class="text-slate-500 dark:text-slate-400 font-medium">Mkulima: <strong>{{ activeRepayLoan.farmer_name }}</strong></div>
            <div class="text-slate-500 dark:text-slate-400 font-medium">Salio Linalodaiwa Sasa: <strong class="text-amber-600 dark:text-amber-400">TZS {{ formatCurrency(activeRepayLoan.current_balance) }}</strong></div>
          </div>

          <form @submit.prevent="submitRepayment" class="space-y-4 text-left">
            
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Kiasi cha Rejesho (TZS)</label>
                <button 
                  type="button" 
                  @click="repayForm.amount = activeRepayLoan?.current_balance" 
                  class="text-[10.5px] font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer"
                >
                  Lipa Salio Lote
                </button>
              </div>
              <input 
                type="number" 
                v-model.number="repayForm.amount" 
                required
                min="1"
                :max="activeRepayLoan?.current_balance"
                placeholder="e.g. 100000"
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Namba ya Kumbukumbu ya Malipo (Ref No / M-Pesa Code)</label>
              <input 
                type="text" 
                v-model="repayForm.reference_number" 
                placeholder="e.g. QFH8912JK or NMB-TXN-102"
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
              />
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
              <button 
                type="button"
                @click="showRepayModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Ghairi
              </button>
              <button 
                type="submit" 
                :disabled="submitting || !repayForm.amount || repayForm.amount <= 0"
                class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50"
              >
                <span>{{ submitting ? 'Inasave...' : 'Hifadhi Rejesho →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

    <!-- Toast Notification Overlay -->
    <transition name="fade">
      <div 
        v-if="toastMessage" 
        :class="toastType === 'error' ? 'bg-rose-600 text-white' : 'bg-slate-900 text-white'"
        class="fixed bottom-6 right-6 z-50 py-3 px-5 rounded-2xl shadow-xl text-xs font-bold flex items-center gap-3 border border-white/10"
      >
        <span>{{ toastType === 'error' ? '⚠️' : '✅' }} {{ toastMessage }}</span>
        <button @click="toastMessage = ''" class="text-xs opacity-80 hover:opacity-100">✕</button>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const loansList = ref([]);
const farmersList = ref([]);
const batchesList = ref([]);
const loading = ref(false);
const submitting = ref(false);

const searchQuery = ref('');
const statusFilter = ref('');

const showNewLoanModal = ref(false);
const showRepayModal = ref(false);
const activeRepayLoan = ref(null);

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

const loanForm = ref({
  farmer_id: '',
  collateral_batch_id: '',
  principal_amount: null,
  due_date: new Date(Date.now() + 90 * 86400000).toISOString().split('T')[0] // Default 90 days
});

const repayForm = ref({
  amount: null,
  reference_number: ''
});

// Computed Metrics
const totalPrincipalAmount = computed(() => {
  return loansList.value.reduce((acc, l) => acc + parseFloat(l.principal_amount || 0), 0);
});

const totalOutstandingBalance = computed(() => {
  return loansList.value.reduce((acc, l) => acc + parseFloat(l.current_balance || 0), 0);
});

const totalRepaidAmount = computed(() => {
  return Math.max(0, totalPrincipalAmount.value - totalOutstandingBalance.value);
});

const activeLoansCount = computed(() => {
  return loansList.value.filter(l => l.status !== 'settled' && parseFloat(l.current_balance) > 0).length;
});

const settledLoansCount = computed(() => {
  return loansList.value.filter(l => l.status === 'settled' || parseFloat(l.current_balance) <= 0).length;
});

const recoveryRate = computed(() => {
  if (totalPrincipalAmount.value <= 0) return '0.0';
  return ((totalRepaidAmount.value / totalPrincipalAmount.value) * 100).toFixed(1);
});

// Filtered Loans List
const filteredLoans = computed(() => {
  return loansList.value.filter(loan => {
    // Search Filter
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const codeMatch = (loan.loan_code || '').toLowerCase().includes(q);
      const farmerMatch = (loan.farmer_name || '').toLowerCase().includes(q);
      const batchMatch = (loan.collateral_batch || '').toLowerCase().includes(q);
      if (!codeMatch && !farmerMatch && !batchMatch) return false;
    }

    // Status Filter
    if (statusFilter.value) {
      if (statusFilter.value === 'active' && (loan.status === 'settled' || parseFloat(loan.current_balance) <= 0)) {
        return false;
      }
      if (statusFilter.value === 'settled' && loan.status !== 'settled' && parseFloat(loan.current_balance) > 0) {
        return false;
      }
    }

    return true;
  });
});

// Batches belonging to selected farmer
const farmerBatches = computed(() => {
  if (!loanForm.value.farmer_id) return [];
  return batchesList.value.filter(b => b.farmer_id === loanForm.value.farmer_id && b.status !== 'sold');
});

// Selected Collateral Batch Info & 50% Max Loan Limit
const selectedBatchInfo = computed(() => {
  if (!loanForm.value.collateral_batch_id) return null;
  return batchesList.value.find(b => b.id === loanForm.value.collateral_batch_id) || null;
});

const selectedBatchMaxLoan = computed(() => {
  if (!selectedBatchInfo.value) return 0;
  const qty = parseFloat(selectedBatchInfo.value.intake_quantity > 0 ? selectedBatchInfo.value.intake_quantity : selectedBatchInfo.value.current_weight || 0);
  const unit = (selectedBatchInfo.value.intake_unit || '').toLowerCase();
  
  let weightKg = qty;
  if (unit.includes('gunia') || unit.includes('bag')) {
    weightKg = qty * 100; // 1 Bag ~ 100kg
  } else if (unit.includes('mt') || unit.includes('ton')) {
    weightKg = qty * 1000;
  }
  
  const estimatedValue = weightKg * 1000; // TZS 1000 / kg baseline
  return estimatedValue * 0.50; // 50% collateral limit
});

const onFarmerChange = () => {
  loanForm.value.collateral_batch_id = '';
};

const onBatchChange = () => {
  if (selectedBatchMaxLoan.value > 0) {
    loanForm.value.principal_amount = selectedBatchMaxLoan.value;
  }
};

// Fetch Backend Loans, Farmers, and Batches
const fetchLoansData = async () => {
  loading.value = true;
  try {
    const [lRes, fRes, bRes] = await Promise.all([
      fetch('/api/v1/loans'),
      fetch('/api/v1/farmers'),
      fetch('/api/v1/batches')
    ]);

    if (lRes.ok) {
      loansList.value = await lRes.json();
    }
    if (fRes.ok) {
      const fData = await fRes.json();
      farmersList.value = Array.isArray(fData) ? fData : (fData.data || []);
    }
    if (bRes.ok) {
      batchesList.value = await bRes.json();
    }
  } catch (err) {
    console.error('Error loading loans data:', err);
    triggerToast('Imeshindwa kuunganisha na server', 'error');
  } finally {
    loading.value = false;
  }
};

const openNewLoanModal = () => {
  loanForm.value = {
    farmer_id: '',
    collateral_batch_id: '',
    principal_amount: null,
    due_date: new Date(Date.now() + 90 * 86400000).toISOString().split('T')[0]
  };
  showNewLoanModal.value = true;
};

const openRepayModal = (loan) => {
  activeRepayLoan.value = loan;
  repayForm.value = {
    amount: loan.current_balance,
    reference_number: ''
  };
  showRepayModal.value = true;
};

const submitNewLoan = async () => {
  if (!loanForm.value.farmer_id || !loanForm.value.collateral_batch_id || !loanForm.value.principal_amount) {
    triggerToast('Tafadhali jaza taarifa zote zinazohitajika', 'error');
    return;
  }

  submitting.value = true;
  try {
    const res = await fetch('/api/v1/loans', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(loanForm.value)
    });

    const data = await res.json();
    if (res.ok && data.success) {
      triggerToast('Mkopo umesajiliwa kikamilifu!');
      showNewLoanModal.value = false;
      await fetchLoansData();
    } else {
      triggerToast(data.error || data.message || 'Imeshindwa kusajili mkopo', 'error');
    }
  } catch (err) {
    console.error('Error submitting loan:', err);
    triggerToast('Kosa wakati wa kusajili mkopo', 'error');
  } finally {
    submitting.value = false;
  }
};

const submitRepayment = async () => {
  if (!activeRepayLoan.value || !repayForm.value.amount || repayForm.value.amount <= 0) {
    triggerToast('Kiasi cha rejesho kinatakiwa kuwa zaidi ya 0', 'error');
    return;
  }

  submitting.value = true;
  try {
    const res = await fetch(`/api/v1/loans/${activeRepayLoan.value.id}/repay`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(repayForm.value)
    });

    const data = await res.json();
    if (res.ok && data.success) {
      triggerToast('Rejesho limerekodiwa kikamilifu!');
      showRepayModal.value = false;
      await fetchLoansData();
    } else {
      triggerToast(data.error || data.message || 'Imeshindwa kurekodi rejesho', 'error');
    }
  } catch (err) {
    console.error('Error submitting repayment:', err);
    triggerToast('Kosa wakati wa kurekodi rejesho', 'error');
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchLoansData();
});
</script>
