<template>
  <div class="space-y-6 text-slate-800 dark:text-slate-100">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <router-link to="/" class="hover:text-emerald-600">Dashboard</router-link>
          <span>/</span>
          <span class="text-slate-700 dark:text-slate-200 font-bold">Mauzo & Ankara</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
          <span>🧾 Mauzo na Ankara</span>
          <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-extrabold">Invoices & Payouts</span>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Simamia miamala ya wanunuzi, ankara za mauzo, na kumbukumbu za malipo ya wakulima.</p>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <button 
          @click="openNewSaleModal"
          class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center justify-center gap-2"
        >
          <span>➕</span>
          <span>Sajili Mauzo & Ankara Mpya</span>
        </button>
      </div>
    </div>

    <!-- Navigation Tabs & KPIs -->
    <div class="space-y-4">
      <!-- Tabs -->
      <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-1">
        <button 
          @click="activeTab = 'invoices'"
          :class="activeTab === 'invoices' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
          class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
        >
          <span>📄 Ankara za Mauzo (Invoices)</span>
          <span class="px-1.5 py-0.2 rounded-md bg-emerald-700 text-[10px] text-white">{{ invoicesList.length }}</span>
        </button>

        <button 
          @click="activeTab = 'settlements'"
          :class="activeTab === 'settlements' ? 'bg-emerald-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 font-bold'"
          class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2"
        >
          <span>💵 Malipo ya Wakulima (Settlements)</span>
          <span class="px-1.5 py-0.2 rounded-md bg-emerald-700 text-[10px] text-white">{{ settlementsList.length }}</span>
        </button>
      </div>

      <!-- KPI Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl text-xl font-bold">
            💵
          </div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Jumla ya Mapato</p>
            <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">TZS {{ formatCurrency(totalRevenue) }}</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Mapato ghafi ya mauzo</p>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-xl text-xl font-bold">
            📄
          </div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Jumla ya Ankara</p>
            <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ invoicesList.length }}</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">{{ paidInvoicesCount }} zimelipwa, {{ unpaidInvoicesCount }} zinadaiwa</p>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl text-xl font-bold">
            ⏳
          </div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Ankara Zinazodaiwa</p>
            <h3 class="text-xl font-black text-amber-600 dark:text-amber-400 mt-0.5">TZS {{ formatCurrency(unpaidInvoicesAmount) }}</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Mabaki hayajamalizwa</p>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-4">
          <div class="p-3 bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl text-xl font-bold">
            🌾
          </div>
          <div>
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Payouts kwa Wakulima</p>
            <h3 class="text-xl font-black text-purple-600 dark:text-purple-400 mt-0.5">TZS {{ formatCurrency(totalFarmerPayouts) }}</h3>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Baada ya makato yote</p>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 1: Invoices Ledger Table -->
    <div v-if="activeTab === 'invoices'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
      <!-- Toolbar -->
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
          <span>📋 Orodha ya Ankara za Mauzo (Invoices Ledger)</span>
          <span class="px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-[11px] text-slate-600 dark:text-slate-400 font-bold">
            {{ filteredInvoices.length }}
          </span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <select 
            v-model="invoiceStatusFilter"
            class="py-2 px-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
          >
            <option value="">Status Zote</option>
            <option value="paid">✅ Paid (Zilizolipwa)</option>
            <option value="unpaid">⏳ Unpaid (Zinazodaiwa)</option>
          </select>

          <div class="relative w-full sm:w-60">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">🔍</span>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Tafuta namba ya ankara, mnunuzi..." 
              class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
            />
          </div>
        </div>
      </div>

      <!-- Table Body -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider text-[10.5px]">
              <th class="py-3.5 px-4">Ankara #</th>
              <th class="py-3.5 px-4">Mnunuzi (Buyer)</th>
              <th class="py-3.5 px-4">Mkulima Aliyeuza</th>
              <th class="py-3.5 px-4">Jumla ya Fedha (TZS)</th>
              <th class="py-3.5 px-4">Tarehe</th>
              <th class="py-3.5 px-4">Hali</th>
              <th class="py-3.5 px-4 text-right">Vitendo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
            <tr v-if="loading" class="text-center py-8">
              <td colspan="7" class="py-10 text-slate-400 font-bold text-xs">
                ⏳ Inapakia ankara kutoka kwenye database...
              </td>
            </tr>
            <tr v-else-if="filteredInvoices.length === 0" class="text-center py-8">
              <td colspan="7" class="py-10 text-slate-400 font-bold text-xs">
                🔍 Hakuna ankara iliyopatikana.
              </td>
            </tr>
            <tr 
              v-else
              v-for="inv in filteredInvoices" 
              :key="inv.id"
              class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
            >
              <!-- Invoice Number -->
              <td class="py-3.5 px-4">
                <div class="font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                  <span class="p-1 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">🧾</span>
                  <span>{{ inv.invoice_number }}</span>
                </div>
              </td>

              <!-- Buyer Name -->
              <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200">
                {{ inv.buyer ? inv.buyer.name : 'Mnunuzi wa Jumla' }}
              </td>

              <!-- Farmer Name -->
              <td class="py-3.5 px-4 font-bold text-emerald-700 dark:text-emerald-300">
                👤 {{ getInvoiceFarmerName(inv) }}
              </td>

              <!-- Total Amount -->
              <td class="py-3.5 px-4 font-black text-emerald-600 dark:text-emerald-400">
                TZS {{ formatCurrency(inv.subtotal) }}
              </td>

              <!-- Created Date -->
              <td class="py-3.5 px-4 text-slate-500 font-semibold text-[11px]">
                {{ inv.created_at ? new Date(inv.created_at).toISOString().split('T')[0] : 'N/A' }}
              </td>

              <!-- Status Badge -->
              <td class="py-3.5 px-4">
                <span 
                  :class="inv.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800'"
                  class="px-2.5 py-1 rounded-lg text-[10.5px] font-black border inline-flex items-center gap-1"
                >
                  <span>{{ inv.status === 'paid' ? '✅ Imelipwa (Paid)' : '⏳ Inadaiwa (Unpaid)' }}</span>
                </span>
              </td>

              <!-- Actions -->
              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button 
                    @click="viewInvoiceDoc(inv)"
                    title="Onyesha Ankara"
                    class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 font-extrabold text-[11px] rounded-lg border border-emerald-200 dark:border-emerald-800 transition cursor-pointer flex items-center gap-1"
                  >
                    <span>👁️ Ankara</span>
                  </button>

                  <button 
                    v-if="inv.status !== 'paid'"
                    @click="markInvoiceAsPaid(inv)"
                    title="Thibitisha Malipo"
                    class="px-2 py-1 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 text-blue-600 dark:text-blue-400 font-bold text-[11px] rounded-lg border border-blue-200 dark:border-blue-800 transition cursor-pointer"
                  >
                    ✅ Mark Paid
                  </button>

                  <button 
                    @click="deleteInvoiceRecord(inv)"
                    title="Futa Ankara"
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

    <!-- TAB 2: Farmer Settlements Ledger Table -->
    <div v-if="activeTab === 'settlements'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
      <!-- Toolbar -->
      <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50">
        <div class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center gap-2">
          <span>💵 Kumbukumbu za Malipo ya Wakulima (Farmer Settlements Ledger)</span>
          <span class="px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-[11px] text-slate-600 dark:text-slate-400 font-bold">
            {{ settlementsList.length }}
          </span>
        </div>
      </div>

      <!-- Settlements Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 font-extrabold border-b border-slate-100 dark:border-slate-800 uppercase tracking-wider text-[10.5px]">
              <th class="py-3.5 px-4">Ref #</th>
              <th class="py-3.5 px-4">Mkulima</th>
              <th class="py-3.5 px-4">Ankara #</th>
              <th class="py-3.5 px-4">Mapato Ghafi (Gross)</th>
              <th class="py-3.5 px-4">Jumla Makato (Deductions)</th>
              <th class="py-3.5 px-4">Net Payout</th>
              <th class="py-3.5 px-4">Njia ya Malipo</th>
              <th class="py-3.5 px-4">Hali</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
            <tr v-if="settlementsList.length === 0" class="text-center py-8">
              <td colspan="8" class="py-10 text-slate-400 font-bold text-xs">
                ℹ️ Hakuna kumbukumbu za malipo ya wakulima bado.
              </td>
            </tr>
            <tr 
              v-else
              v-for="settle in settlementsList" 
              :key="settle.id"
              class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors"
            >
              <td class="py-3.5 px-4 font-mono font-bold text-slate-700 dark:text-slate-300 text-[11px]">
                {{ settle.payment_reference || 'SETT-' + settle.id.substring(0, 6) }}
              </td>

              <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-white">
                {{ settle.farmer ? settle.farmer.name : 'N/A' }}
              </td>

              <td class="py-3.5 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                {{ settle.invoice ? settle.invoice.invoice_number : 'N/A' }}
              </td>

              <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200">
                TZS {{ formatCurrency(settle.gross_amount) }}
              </td>

              <td class="py-3.5 px-4 font-bold text-rose-600 dark:text-rose-400">
                - TZS {{ formatCurrency(settle.total_deductions) }}
              </td>

              <td class="py-3.5 px-4 font-black text-emerald-600 dark:text-emerald-400">
                TZS {{ formatCurrency(settle.net_payout) }}
              </td>

              <td class="py-3.5 px-4 font-bold text-slate-600 dark:text-slate-400 uppercase text-[10.5px]">
                📱 {{ settle.payment_method || 'mobile_money' }}
              </td>

              <td class="py-3.5 px-4">
                <span class="px-2.5 py-1 rounded-lg text-[10.5px] font-black border bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800 inline-flex items-center gap-1">
                  ✅ {{ settle.payment_status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL 1: Create Invoice & Sale Wizard -->
    <transition name="fade">
      <div v-if="showNewSaleModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-xl overflow-hidden animate-fadeIn">
          
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="font-black text-base text-slate-900 dark:text-white flex items-center gap-2">
              <span>🧾</span>
              <span>Sajili Mauzo & Ankara Mpya</span>
            </h3>
            <button @click="showNewSaleModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="submitSaleConfirm" class="p-6 space-y-4 text-left">
            
            <!-- Buyer Selector / Buyer Name -->
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Mnunuzi (Buyer) *</label>
              <div class="grid grid-cols-2 gap-2">
                <select 
                  v-model="saleForm.buyer_id" 
                  @change="onBuyerSelectChange"
                  class="py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
                >
                  <option value="">-- Chagua Mnunuzi --</option>
                  <option v-for="b in buyersOptions" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
                <input 
                  type="text" 
                  v-model="saleForm.buyer_name" 
                  required
                  placeholder="Au ingiza jina la mnunuzi..."
                  class="py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
                />
              </div>
            </div>

            <!-- Batch Selector -->
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Chagua Mzigo unaouzwa (Batch Ghalani) *</label>
              <select 
                v-model="saleForm.batch_id" 
                @change="triggerDeductionPreview"
                required
                class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
              >
                <option value="">-- Chagua Batch iliyopo ghalani --</option>
                <option v-for="b in activeBatches" :key="b.id" :value="b.id">
                  {{ b.batch_code }} - {{ b.crop_type }} ({{ b.farmer ? b.farmer.name : 'Mkulima' }}) | Imetunzwa: {{ b.current_weight_mt || b.intake_quantity }} {{ b.intake_unit || 'Units' }}
                </option>
              </select>
            </div>

            <!-- Sold Quantity & Unit Price -->
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Kiasi kinachouzwa (Units/Kg) *</label>
                <input 
                  type="number" 
                  v-model.number="saleForm.sold_weight_kg" 
                  @input="triggerDeductionPreview"
                  required
                  min="0.1"
                  step="any"
                  placeholder="e.g. 50"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
                />
              </div>

              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Bei kwa Kilo/Kipimo (TZS) *</label>
                <input 
                  type="number" 
                  v-model.number="saleForm.price_per_kg" 
                  @input="triggerDeductionPreview"
                  required
                  min="1"
                  placeholder="e.g. 1500"
                  class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none"
                />
              </div>
            </div>

            <!-- Live Calculation & Deductions Preview Card -->
            <div v-if="deductionsPreview" class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
              <div class="font-extrabold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-1.5 flex justify-between">
                <span>📊 Uchanganuzi wa Ankara & Deductions</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-black">{{ deductionsPreview.crop_type }} ({{ deductionsPreview.farmer_name }})</span>
              </div>

              <div class="flex justify-between text-slate-900 dark:text-white font-extrabold">
                <span>Jumla ya Mauzo (Gross Sales)</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-black">TZS {{ formatCurrency(deductionsPreview.gross_sales) }}</span>
              </div>

              <div class="border-t border-slate-200 dark:border-slate-700 pt-2 space-y-1 text-[11.5px]">
                <div class="font-bold text-rose-600 dark:text-rose-400">Makato ya Mkulima (Automatic Deductions):</div>
                <div class="flex justify-between text-slate-500"><span>• Ada ya Hifadhi (Storage)</span><span>- TZS {{ formatCurrency(deductionsPreview.deductions.storage_fees) }}</span></div>
                <div class="flex justify-between text-slate-500"><span>• Ada ya Ukaushaji (Drying)</span><span>- TZS {{ formatCurrency(deductionsPreview.deductions.drying_fees) }}</span></div>
                <div class="flex justify-between text-slate-500"><span>• Ada ya Ukoboaji (Milling)</span><span>- TZS {{ formatCurrency(deductionsPreview.deductions.milling_fees) }}</span></div>
                <div class="flex justify-between text-slate-500"><span>• Ada ya Daraja (Grading)</span><span>- TZS {{ formatCurrency(deductionsPreview.deductions.grading_fees) }}</span></div>
                <div class="flex justify-between text-slate-500"><span>• Marejesho ya Mikopo (Loan Principal)</span><span>- TZS {{ formatCurrency(deductionsPreview.deductions.loan_principal) }}</span></div>
              </div>

              <div class="flex justify-between font-black text-sm pt-2 border-t border-slate-200 dark:border-slate-700 text-purple-600 dark:text-purple-400">
                <span>Mkulima Anachukua (Net Payout)</span>
                <span>TZS {{ formatCurrency(deductionsPreview.net_payout) }}</span>
              </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="grid grid-cols-2 gap-2 pt-2">
              <button 
                type="button"
                @click="showNewSaleModal = false"
                class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer"
              >
                Ghairi
              </button>
              <button 
                type="submit" 
                :disabled="submitting || !saleForm.batch_id || !saleForm.sold_weight_kg || !saleForm.price_per_kg"
                class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50"
              >
                <span>{{ submitting ? 'Inathibitisha...' : 'Kamilisha Mauzo & Toa Ankara →' }}</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

    <!-- MODAL 2: Invoice Viewer Document Modal -->
    <transition name="fade">
      <div v-if="showInvoiceModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-2xl overflow-hidden animate-fadeIn">
          
          <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900">
            <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
              <span>🧾 Document ya Ankara ya Mauzo (Invoice)</span>
            </h3>
            <button @click="showInvoiceModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer">✕</button>
          </div>

          <!-- Printable Area -->
          <div id="invoicePrintArea" class="p-8 bg-white text-slate-900 font-sans text-xs space-y-6 max-h-[70vh] overflow-y-auto">
            <!-- Invoice Header -->
            <div class="flex justify-between border-b-2 border-emerald-600 pb-4">
              <div>
                <h2 class="text-2xl font-black text-emerald-600 tracking-tight">KilimoStore MS</h2>
                <p class="text-[11px] text-slate-500 mt-1">
                  Garanoki Main Store & Warehouse, Industrial Area
                </p>
              </div>

              <div class="text-right">
                <h3 class="text-xl font-black text-slate-900 uppercase">SALES INVOICE</h3>
                <p class="text-xs text-slate-600 mt-1">
                  <strong>Invoice #:</strong> {{ selectedInvoiceDoc?.invoice_number }}<br/>
                  <strong>Tarehe:</strong> {{ selectedInvoiceDoc?.created_at ? new Date(selectedInvoiceDoc.created_at).toISOString().split('T')[0] : 'N/A' }}
                </p>
              </div>
            </div>

            <!-- Bill To & Farmer Details -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-slate-500 font-extrabold uppercase text-[10px]">Mteja / Mnunuzi (Bill To):</p>
                <h4 class="text-sm font-black text-slate-900 mt-0.5">{{ selectedInvoiceDoc?.buyer ? selectedInvoiceDoc.buyer.name : 'Mnunuzi wa Jumla' }}</h4>
                <p class="text-slate-600 text-xs">Simu: {{ selectedInvoiceDoc?.buyer?.phone || 'N/A' }} | Email: {{ selectedInvoiceDoc?.buyer?.email || 'N/A' }}</p>
                <p class="text-slate-600 text-xs">TIN: {{ selectedInvoiceDoc?.buyer?.tax_number || 'N/A' }}</p>
              </div>

              <div>
                <p class="text-slate-500 font-extrabold uppercase text-[10px]">Mkulima Aliyeuza Mzigo:</p>
                <h4 class="text-sm font-black text-emerald-700 mt-0.5">👤 {{ getInvoiceFarmerName(selectedInvoiceDoc) }}</h4>
              </div>
            </div>

            <!-- Items Table -->
            <table class="w-full text-left border-collapse border-b border-slate-900 text-xs">
              <thead>
                <tr class="border-b-2 border-slate-900 font-black uppercase text-[10.5px]">
                  <th class="py-2">Maelezo ya Zao (Item Details)</th>
                  <th class="py-2 text-right">Kiasi (Qty)</th>
                  <th class="py-2 text-right">Bei (Unit Price)</th>
                  <th class="py-2 text-right">Jumla (TZS)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr v-for="item in (selectedInvoiceDoc?.items || [])" :key="item.id">
                  <td class="py-2.5 font-bold">
                    {{ item.batch ? item.batch.crop_type : 'Mazao Ghalani' }} (Batch: {{ item.batch ? item.batch.batch_code : 'N/A' }})
                  </td>
                  <td class="py-2.5 text-right font-bold">{{ item.quantity_mt }}</td>
                  <td class="py-2.5 text-right font-bold">TZS {{ formatCurrency(item.unit_price) }}</td>
                  <td class="py-2.5 text-right font-black">TZS {{ formatCurrency(item.total_price) }}</td>
                </tr>
                <tr v-if="!selectedInvoiceDoc?.items || selectedInvoiceDoc.items.length === 0">
                  <td class="py-2.5 font-bold">Manunuzi ya Mazao Ghalani</td>
                  <td class="py-2.5 text-right font-bold">1</td>
                  <td class="py-2.5 text-right font-bold">TZS {{ formatCurrency(selectedInvoiceDoc?.subtotal) }}</td>
                  <td class="py-2.5 text-right font-black">TZS {{ formatCurrency(selectedInvoiceDoc?.subtotal) }}</td>
                </tr>
              </tbody>
            </table>

            <!-- Totals Summary -->
            <div class="w-1/2 ml-auto space-y-1 text-xs">
              <div class="flex justify-between font-black text-sm text-slate-900 pt-1">
                <span>Jumla Kuu (Total Amount):</span>
                <span class="text-emerald-600">TZS {{ formatCurrency(selectedInvoiceDoc?.subtotal) }}</span>
              </div>
            </div>

            <!-- Footer Note -->
            <div class="text-[10.5px] text-slate-400 border-t border-dashed border-slate-300 pt-4">
              * Malipo yote yafanyike kwa akaunti iliyoidhinishwa na KilimoStore MS. Ankara hii imetolewa kikamilifu na mfumo.
            </div>
          </div>

          <!-- Actions -->
          <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex justify-end gap-2">
            <button 
              @click="showInvoiceModal = false"
              class="py-2 px-4 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl cursor-pointer"
            >
              Funga
            </button>
            <button 
              @click="printInvoice"
              class="py-2 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 cursor-pointer flex items-center gap-1.5"
            >
              <span>🖨️ Chapa Ankara (Print)</span>
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

const activeTab = ref('invoices');

const invoicesList = ref([]);
const settlementsList = ref([]);
const buyersOptions = ref([]);
const activeBatches = ref([]);

const loading = ref(false);
const submitting = ref(false);

const searchQuery = ref('');
const invoiceStatusFilter = ref('');

const showNewSaleModal = ref(false);
const showInvoiceModal = ref(false);
const selectedInvoiceDoc = ref(null);

const deductionsPreview = ref(null);

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

const saleForm = ref({
  buyer_id: '',
  buyer_name: '',
  batch_id: '',
  sold_weight_kg: null,
  price_per_kg: null
});

const totalRevenue = computed(() => {
  return invoicesList.value.reduce((acc, inv) => acc + parseFloat(inv.subtotal || 0), 0);
});

const paidInvoicesCount = computed(() => {
  return invoicesList.value.filter(inv => inv.status === 'paid').length;
});

const unpaidInvoicesCount = computed(() => {
  return invoicesList.value.filter(inv => inv.status === 'unpaid').length;
});

const unpaidInvoicesAmount = computed(() => {
  return invoicesList.value
    .filter(inv => inv.status === 'unpaid')
    .reduce((acc, inv) => acc + parseFloat(inv.subtotal || 0), 0);
});

const totalFarmerPayouts = computed(() => {
  return settlementsList.value.reduce((acc, s) => acc + parseFloat(s.net_payout || 0), 0);
});

const getInvoiceFarmerName = (inv) => {
  if (!inv) return 'N/A';
  if (inv.items && inv.items.length > 0) {
    const fNames = inv.items
      .map(item => (item.batch && item.batch.farmer) ? item.batch.farmer.name : null)
      .filter(Boolean);
    if (fNames.length > 0) return [...new Set(fNames)].join(', ');
  }
  return 'N/A';
};

const filteredInvoices = computed(() => {
  return invoicesList.value.filter(inv => {
    const matchesStatus = !invoiceStatusFilter.value || inv.status === invoiceStatusFilter.value;
    const q = searchQuery.value.toLowerCase().trim();
    const buyerName = inv.buyer ? inv.buyer.name.toLowerCase() : '';
    const invNum = inv.invoice_number ? inv.invoice_number.toLowerCase() : '';
    const farmerName = getInvoiceFarmerName(inv).toLowerCase();
    const matchesSearch = !q || invNum.includes(q) || buyerName.includes(q) || farmerName.includes(q);

    return matchesStatus && matchesSearch;
  });
});

const fetchData = async () => {
  loading.value = true;
  try {
    const [invRes, setRes, buyersRes, batchRes] = await Promise.all([
      fetch('/api/v1/sales/invoices'),
      fetch('/api/v1/sales/settlements'),
      fetch('/api/v1/buyers'),
      fetch('/api/v1/batches')
    ]);

    if (invRes.ok) invoicesList.value = await invRes.json();
    if (setRes.ok) settlementsList.value = await setRes.json();
    if (buyersRes.ok) buyersOptions.value = await buyersRes.json();
    if (batchRes.ok) {
      const allB = await batchRes.json();
      activeBatches.value = allB.filter(b => b.status !== 'sold' && (parseFloat(b.current_weight_mt || b.intake_quantity || 0) > 0));
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kupakua data za mauzo', 'error');
  } finally {
    loading.value = false;
  }
};

const onBuyerSelectChange = () => {
  if (saleForm.value.buyer_id) {
    const found = buyersOptions.value.find(b => b.id === saleForm.value.buyer_id);
    if (found) {
      saleForm.value.buyer_name = found.name;
    }
  }
};

const triggerDeductionPreview = async () => {
  if (!saleForm.value.batch_id || !saleForm.value.sold_weight_kg || !saleForm.value.price_per_kg) {
    deductionsPreview.value = null;
    return;
  }

  try {
    const res = await fetch('/api/v1/sales/preview-deductions', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        batch_id: saleForm.value.batch_id,
        sold_weight_kg: saleForm.value.sold_weight_kg,
        price_per_kg: saleForm.value.price_per_kg
      })
    });
    if (res.ok) {
      deductionsPreview.value = await res.json();
    } else {
      deductionsPreview.value = null;
    }
  } catch (err) {
    console.error(err);
    deductionsPreview.value = null;
  }
};

const openNewSaleModal = () => {
  saleForm.value = {
    buyer_id: '',
    buyer_name: '',
    batch_id: '',
    sold_weight_kg: null,
    price_per_kg: null
  };
  deductionsPreview.value = null;
  showNewSaleModal.value = true;
};

const submitSaleConfirm = async () => {
  if (!saleForm.value.batch_id || !saleForm.value.sold_weight_kg || !saleForm.value.price_per_kg || !saleForm.value.buyer_name.trim()) {
    triggerToast('Tafadhali jaza sehemu zote zinazohitajika', 'error');
    return;
  }

  submitting.value = true;
  try {
    const selectedBatch = activeBatches.value.find(b => b.id === saleForm.value.batch_id);
    const farmerId = selectedBatch ? selectedBatch.farmer_id : null;

    const res = await fetch('/api/v1/sales/confirm', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        farmer_id: farmerId,
        batch_id: saleForm.value.batch_id,
        buyer_name: saleForm.value.buyer_name,
        price_per_kg: saleForm.value.price_per_kg,
        sold_weight_kg: saleForm.value.sold_weight_kg
      })
    });

    const data = await res.json();
    if (res.ok && data.success) {
      triggerToast('Mauzo yamekamilika na ankara imetolewa kikamilifu!');
      showNewSaleModal.value = false;
      await fetchData();
    } else {
      triggerToast(data.message || 'Imeshindwa kukamilisha mauzo', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kukamilisha mauzo', 'error');
  } finally {
    submitting.value = false;
  }
};

const viewInvoiceDoc = (inv) => {
  selectedInvoiceDoc.value = inv;
  showInvoiceModal.value = true;
};

const markInvoiceAsPaid = async (inv) => {
  try {
    const res = await fetch(`/api/v1/sales/invoices/${inv.id}/pay`, {
      method: 'PUT'
    });
    const data = await res.json();
    if (res.ok && data.success) {
      triggerToast('Ankara imetiwa alama ya kulipwa!');
      await fetchData();
    } else {
      triggerToast(data.message || 'Imeshindwa kubadili hali ya ankara', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kubadili ankara', 'error');
  }
};

const deleteInvoiceRecord = async (inv) => {
  if (!confirm(`Je, una uhakika unataka kufuta ankara "${inv.invoice_number}"?`)) {
    return;
  }

  try {
    const res = await fetch(`/api/v1/sales/invoices/${inv.id}`, {
      method: 'DELETE'
    });
    const data = await res.json();
    if (res.ok && data.success) {
      triggerToast('Ankara imefutwa kikamilifu!');
      await fetchData();
    } else {
      triggerToast(data.message || 'Imeshindwa kufuta ankara', 'error');
    }
  } catch (err) {
    console.error(err);
    triggerToast('Kosa wakati wa kufuta ankara', 'error');
  }
};

const printInvoice = () => {
  const printContents = document.getElementById('invoicePrintArea').innerHTML;
  const originalContents = document.body.innerHTML;
  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
  window.location.reload();
};

onMounted(() => {
  fetchData();
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
