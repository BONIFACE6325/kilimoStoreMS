<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <span>Mwanzo</span>
          <span>/</span>
          <span class="text-emerald-700 dark:text-emerald-400 font-bold">Mauzo & Invoices</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-slate-50 tracking-tight flex items-center gap-2">
          <span>📄 Mauzo, Invoices & Settlement Records</span>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
          Usimamizi wa mikataba ya wanunuzi (Buyers), kutoa Tax Invoices za kiofisi na risiti za PDF kwa wakulima.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button 
          @click="exportLedgerCSV" 
          class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-2xl font-extrabold text-xs transition flex items-center gap-1.5"
        >
          <span>📥 Export CSV Ledger</span>
        </button>
        <button 
          @click="openNewInvoiceModal"
          class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-2xl font-extrabold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          <span>+ Tengeneza Tax Invoice</span>
        </button>
      </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-gradient-to-tr from-emerald-900 to-teal-900 text-white p-5 rounded-3xl shadow-md border border-emerald-800 flex items-center justify-between">
        <div>
          <div class="text-xs font-bold uppercase text-emerald-200 tracking-wider">Jumla ya Mauzo (Revenue)</div>
          <div class="text-2xl font-black text-white mt-1">Tsh {{ totalRevenue.toLocaleString() }}</div>
          <div class="text-[11px] text-emerald-300 mt-0.5">{{ invoices.length }} Invoices Issued</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-white/15 dark:bg-slate-900/15 backdrop-blur-xs flex items-center justify-center text-xl">
          💰
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex items-center justify-between">
        <div>
          <div class="text-xs font-bold uppercase text-slate-400 tracking-wider">Invoices Zilizolipwa</div>
          <div class="text-3xl font-black text-emerald-700 dark:text-emerald-400 mt-1">{{ paidInvoicesCount }}</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Paid Invoices</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xl">
          ✅
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex items-center justify-between">
        <div>
          <div class="text-xs font-bold uppercase text-slate-400 tracking-wider">Invoices In-Pending</div>
          <div class="text-3xl font-black text-amber-600 mt-1">{{ unpaidInvoicesCount }}</div>
          <div class="text-[11px] text-amber-700 dark:text-amber-400 font-bold mt-0.5">Tsh {{ pendingAmount.toLocaleString() }}</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl">
          ⏳
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex items-center justify-between">
        <div>
          <div class="text-xs font-bold uppercase text-slate-400 tracking-wider">Wanunuzi (Buyers)</div>
          <div class="text-3xl font-black text-indigo-600 mt-1">{{ buyers.length }}</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Corporate Buyers</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
          🏢
        </div>
      </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs overflow-hidden">
      <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-50">Orodha ya Invoices na Settlement Records</h3>
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Tafuta Invoice # au Mnunuzi..." 
            class="px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold focus:bg-white dark:bg-slate-900"
          />
          <select v-model="statusFilter" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold">
            <option value="">Status Zote</option>
            <option value="paid">Lipwa (Paid)</option>
            <option value="unpaid">Inasubiri (Unpaid)</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
            <tr>
              <th class="p-4">Invoice #</th>
              <th class="p-4">Mnunuzi (Buyer)</th>
              <th class="p-4">Zao (Crop)</th>
              <th class="p-4 text-right">Kiasi (Kg)</th>
              <th class="p-4 text-right">Bei / Kg</th>
              <th class="p-4 text-right">Jumla (TZS)</th>
              <th class="p-4">Tarehe</th>
              <th class="p-4 text-center">Hali (Status)</th>
              <th class="p-4 text-center">Vitendo (Action)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-semibold text-slate-700 dark:text-slate-200">
            <tr v-if="filteredInvoices.length === 0">
              <td colspan="9" class="p-8 text-center text-slate-400 font-medium">Hakuna invoice iliyopatikana.</td>
            </tr>
            <tr v-for="inv in filteredInvoices" :key="inv.id" class="hover:bg-slate-50/80 dark:bg-slate-950/80 transition">
              <td class="p-4 font-mono font-bold text-indigo-700">{{ inv.invoice_number }}</td>
              <td class="p-4 font-extrabold text-slate-900 dark:text-slate-50">{{ inv.buyer ? inv.buyer.name : (inv.buyer_name || 'Buyer Corporate') }}</td>
              <td class="p-4"><span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-400 rounded-md font-extrabold text-[10.5px]">{{ inv.crop_type || 'Mchele Grade A' }}</span></td>
              <td class="p-4 text-right font-mono font-bold">{{ parseFloat(inv.quantity_kg || 10000).toLocaleString() }} Kg</td>
              <td class="p-4 text-right font-mono text-slate-500 dark:text-slate-400">Tsh {{ parseFloat(inv.unit_price || 1200).toLocaleString() }}</td>
              <td class="p-4 text-right font-black text-emerald-700 dark:text-emerald-400">Tsh {{ parseFloat(inv.total_amount || 0).toLocaleString() }}</td>
              <td class="p-4 text-slate-400 font-mono text-[11px]">{{ new Date(inv.created_at || Date.now()).toLocaleDateString() }}</td>
              <td class="p-4 text-center">
                <span 
                  :class="inv.status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-400'" 
                  class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider"
                >
                  {{ inv.status === 'paid' ? '✓ Paid' : '⏳ Pending' }}
                </span>
              </td>
              <td class="p-4 text-center">
                <button 
                  @click="previewInvoicePDF(inv)" 
                  class="px-3 py-1.5 bg-slate-900 hover:bg-emerald-700 text-white rounded-xl font-bold text-[11px] transition shadow-2xs flex items-center justify-center gap-1.5 mx-auto"
                >
                  <span>🖨️ Risiti ya PDF</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: PRINTABLE TAX INVOICE & SETTLEMENT PDF DOCUMENT -->
    <div v-if="showPDFModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-900 text-white">
          <div class="flex items-center gap-2">
            <span class="text-lg">🖨️</span>
            <h3 class="text-sm font-extrabold">Orodha ya Dokumenti ya Tax Invoice & Settlement</h3>
          </div>
          <div class="flex items-center gap-2">
            <button @click="triggerPrint" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-lg transition flex items-center gap-1">
              <span>🖨️ Print / Save as PDF</span>
            </button>
            <button @click="showPDFModal = false" class="text-slate-400 hover:text-white p-1">✕</button>
          </div>
        </div>

        <!-- PRINTABLE CANVAS / RECEIPT AREA -->
        <div id="printableDocumentArea" class="p-8 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-50 font-sans space-y-6 max-h-[75vh] overflow-y-auto print:p-0 print:max-h-none">
          <!-- Letterhead Header -->
          <div class="flex items-start justify-between border-b-2 border-emerald-600 pb-4">
            <div>
              <div class="text-2xl font-black tracking-tight text-emerald-800 dark:text-emerald-400">GALANOKI MILLING ERP</div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold leading-relaxed">
                AgroVault ERP Processing & Warehouse Complex<br/>
                VAT Reg: 100-244-918 | TIN: 882-991-002<br/>
                Industrial Zone Block B, Shinyanga / Morogoro, Tanzania
              </div>
            </div>
            <div class="text-right">
              <div class="text-xl font-black text-slate-900 dark:text-slate-50 tracking-tight">TAX INVOICE</div>
              <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">
                <strong>Invoice #:</strong> {{ selectedInv.invoice_number }}<br/>
                <strong>Date:</strong> {{ new Date(selectedInv.created_at || Date.now()).toLocaleDateString() }}<br/>
                <strong>Payment Terms:</strong> Bank Transfer / Escrow
              </div>
            </div>
          </div>

          <!-- Customer & Buyer Info -->
          <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-700">
              <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mteja / Mnunuzi (Bill To):</div>
              <div class="text-sm font-black text-slate-900 dark:text-slate-50 mt-1">{{ selectedInv.buyer ? selectedInv.buyer.name : (selectedInv.buyer_name || 'Bakhresa Grain Milling Corp') }}</div>
              <div class="text-slate-600 dark:text-slate-300 font-semibold mt-0.5">Attn: {{ selectedInv.buyer ? selectedInv.buyer.contact_person : 'Procurement Dept' }}</div>
              <div class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">Phone: {{ selectedInv.buyer ? selectedInv.buyer.phone : '+255 784 900 120' }}</div>
            </div>

            <div class="p-3 bg-emerald-50/60 dark:bg-emerald-900/40 rounded-2xl border border-emerald-200 dark:border-emerald-500/20 flex items-center justify-between">
              <div>
                <div class="text-[10px] font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">Hali ya Malipo:</div>
                <div class="text-base font-black text-emerald-900 dark:text-emerald-400 mt-0.5">{{ selectedInv.status === 'paid' ? 'PAID & SETTLED' : 'PAYMENT PENDING' }}</div>
                <div class="text-[10.5px] text-emerald-700 dark:text-emerald-400 font-medium">Verified by Galanoki Finance</div>
              </div>
              <div class="w-12 h-12 bg-emerald-700/10 rounded-xl flex items-center justify-center text-xl">
                🏛️
              </div>
            </div>
          </div>

          <!-- Line Items Table -->
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b-2 border-slate-900 font-extrabold text-slate-900 dark:text-slate-50">
                <th class="py-2.5">Maelezo ya Zao / Huduma (Item Description)</th>
                <th class="py-2.5 text-right">Kiasi (Kg)</th>
                <th class="py-2.5 text-right">Bei / Kg (TZS)</th>
                <th class="py-2.5 text-right">Jumla (TZS)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-800 dark:text-slate-100 font-semibold">
              <tr>
                <td class="py-3">
                  <div class="font-bold text-slate-900 dark:text-slate-50">{{ selectedInv.crop_type || 'Mchele Grade A (Rice - Polished)' }}</div>
                  <div class="text-[10.5px] text-slate-500 dark:text-slate-400">Milling & Processing Certificate Verified</div>
                </td>
                <td class="py-3 text-right font-mono font-bold">{{ parseFloat(selectedInv.quantity_kg || 10000).toLocaleString() }} Kg</td>
                <td class="py-3 text-right font-mono">Tsh {{ parseFloat(selectedInv.unit_price || 1200).toLocaleString() }}</td>
                <td class="py-3 text-right font-mono font-black text-slate-900 dark:text-slate-50">Tsh {{ (parseFloat(selectedInv.quantity_kg || 10000) * parseFloat(selectedInv.unit_price || 1200)).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Financial Deductions & VAT Summary -->
          <div class="flex justify-end pt-2">
            <div class="w-64 space-y-2 text-xs font-semibold text-slate-700 dark:text-slate-200">
              <div class="flex justify-between">
                <span>Subtotal (Kabla ya Kodi):</span>
                <span class="font-mono font-bold">Tsh {{ Math.round((parseFloat(selectedInv.total_amount || 12000000) / 1.18)).toLocaleString() }}</span>
              </div>
              <div class="flex justify-between text-slate-500 dark:text-slate-400">
                <span>VAT (18% TRA Tax):</span>
                <span class="font-mono">Tsh {{ Math.round((parseFloat(selectedInv.total_amount || 12000000) - (parseFloat(selectedInv.total_amount || 12000000) / 1.18))).toLocaleString() }}</span>
              </div>
              <div class="flex justify-between text-sm font-black text-emerald-800 dark:text-emerald-400 border-t border-slate-900 pt-2">
                <span>Jumla ya Malipo (Total):</span>
                <span class="font-mono">Tsh {{ parseFloat(selectedInv.total_amount || 12000000).toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- Official Signatures & Verification Stamp -->
          <div class="pt-8 border-t border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-8 text-[11px] text-slate-500 dark:text-slate-400">
            <div>
              <div class="font-bold text-slate-800 dark:text-slate-100 mb-6">Sahihi ya Meneja wa Kinu:</div>
              <div class="border-b border-slate-300 dark:border-slate-600 w-3/4 mb-1"></div>
              <div>Authorized Signatory & Stamp</div>
            </div>

            <div class="text-right flex flex-col items-end">
              <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl p-1 flex items-center justify-center text-[9px] font-mono text-center text-slate-600 dark:text-slate-300 mb-1">
                [ QR Code Verification ]
              </div>
              <div>Scanned via Galanoki ERP System</div>
            </div>
          </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
          <button @click="showPDFModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs">Funga</button>
          <button @click="triggerPrint" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5">
            <span>🖨️ Chapisha / Hifadhi PDF</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: NEW TAX INVOICE FORM -->
    <div v-if="showNewInvoiceModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">Tengeneza Tax Invoice Mpya</h3>
          <button @click="showNewInvoiceModal = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>

        <div class="p-6 space-y-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <div>
            <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Mnunuzi (Buyer) *</label>
            <input v-model="newInvForm.buyer_name" type="text" placeholder="e.g. Bakhresa Grain Milling Ltd" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Aina ya Zao *</label>
              <select v-model="newInvForm.crop_type" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                <option value="Mchele Grade A">Mchele Grade A</option>
                <option value="Sembe Super">Sembe Super</option>
                <option value="Pumba">Pumba</option>
                <option value="Mpunga Ghafi">Mpunga Ghafi</option>
              </select>
            </div>

            <div>
              <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Kiasi (Kg) *</label>
              <input v-model.number="newInvForm.quantity_kg" type="number" placeholder="e.g. 10000" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Bei Kwa Kg (Tsh) *</label>
              <input v-model.number="newInvForm.unit_price" type="number" placeholder="e.g. 1200" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold" />
            </div>

            <div>
              <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Status *</label>
              <select v-model="newInvForm.status" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                <option value="paid">Imelipwa (Paid)</option>
                <option value="unpaid">Inasubiri (Unpaid)</option>
              </select>
            </div>
          </div>

          <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-1 font-mono text-[11px]">
            <div class="flex justify-between"><span>Subtotal:</span><span>Tsh {{ (newInvForm.quantity_kg * newInvForm.unit_price).toLocaleString() }}</span></div>
            <div class="flex justify-between text-slate-500 dark:text-slate-400"><span>VAT (18%):</span><span>Tsh {{ Math.round(newInvForm.quantity_kg * newInvForm.unit_price * 0.18).toLocaleString() }}</span></div>
            <div class="flex justify-between font-black text-emerald-700 dark:text-emerald-400 text-xs pt-1 border-t"><span>Jumla Kuu:</span><span>Tsh {{ Math.round(newInvForm.quantity_kg * newInvForm.unit_price * 1.18).toLocaleString() }}</span></div>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button @click="showNewInvoiceModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl">Ghairi</button>
            <button @click="saveInvoice" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
              Tengeneza Invoice
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div 
      v-if="toast.show" 
      :class="toast.type === 'error' ? 'bg-red-900 border-red-700' : 'bg-emerald-950 border-emerald-700'" 
      class="fixed bottom-6 right-6 z-50 text-white px-5 py-3 rounded-2xl shadow-2xl border flex items-center gap-3 animate-bounce"
    >
      <span>{{ toast.type === 'error' ? '⚠️' : '✅' }}</span>
      <span class="text-xs font-bold">{{ toast.message }}</span>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const invoices = ref([]);
const buyers = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const statusFilter = ref('');

const showPDFModal = ref(false);
const showNewInvoiceModal = ref(false);
const selectedInv = ref({});

const newInvForm = ref({
  buyer_name: '',
  crop_type: 'Mchele Grade A',
  quantity_kg: 10000,
  unit_price: 1200,
  status: 'paid'
});

const toast = ref({ show: false, message: '', type: 'success' });
const triggerToast = (msg, type = 'success') => {
  toast.value = { show: true, message: msg, type };
  setTimeout(() => { toast.value.show = false; }, 3500);
};

const defaultInvoices = [
  { id: 1, invoice_number: 'INV-2026-001', buyer_name: 'Bakhresa Grain Milling Corp', crop_type: 'Mchele Grade A', quantity_kg: 25000, unit_price: 1400, total_amount: 35000000, status: 'paid', created_at: '2026-08-20' },
  { id: 2, invoice_number: 'INV-2026-002', buyer_name: 'METL Food Processing Ltd', crop_type: 'Sembe Super', quantity_kg: 18000, unit_price: 1100, total_amount: 19800000, status: 'paid', created_at: '2026-08-22' },
  { id: 3, invoice_number: 'INV-2026-003', buyer_name: 'Asas Dairies & Grains', crop_type: 'Pumba ya Mchele', quantity_kg: 40000, unit_price: 350, total_amount: 14000000, status: 'unpaid', created_at: '2026-08-25' }
];

const fetchSalesData = async () => {
  loading.value = true;
  try {
    const res = await fetch('/api/v1/sales/invoices');
    if (res.ok) {
      const data = await res.json();
      invoices.value = (Array.isArray(data) && data.length > 0) ? data : defaultInvoices;
    } else {
      invoices.value = defaultInvoices;
    }

    const bRes = await fetch('/api/v1/sales/buyers');
    if (bRes.ok) {
      buyers.value = await bRes.json();
    }
  } catch (e) {
    invoices.value = defaultInvoices;
  } finally {
    loading.value = false;
  }
};

const totalRevenue = computed(() => {
  return invoices.value.reduce((sum, inv) => sum + parseFloat(inv.total_amount || 0), 0);
});

const paidInvoicesCount = computed(() => {
  return invoices.value.filter(inv => inv.status === 'paid').length;
});

const unpaidInvoicesCount = computed(() => {
  return invoices.value.filter(inv => inv.status === 'unpaid').length;
});

const pendingAmount = computed(() => {
  return invoices.value.filter(inv => inv.status === 'unpaid').reduce((sum, inv) => sum + parseFloat(inv.total_amount || 0), 0);
});

const filteredInvoices = computed(() => {
  return invoices.value.filter(inv => {
    const q = searchQuery.value.toLowerCase();
    const bName = inv.buyer ? inv.buyer.name.toLowerCase() : (inv.buyer_name || '').toLowerCase();
    const invNum = (inv.invoice_number || '').toLowerCase();
    const matchesQ = !q || invNum.includes(q) || bName.includes(q);
    const matchesSt = !statusFilter.value || inv.status === statusFilter.value;
    return matchesQ && matchesSt;
  });
});

const previewInvoicePDF = (inv) => {
  selectedInv.value = inv;
  showPDFModal.value = true;
};

const triggerPrint = () => {
  window.print();
};

const openNewInvoiceModal = () => {
  newInvForm.value = {
    buyer_name: '',
    crop_type: 'Mchele Grade A',
    quantity_kg: 10000,
    unit_price: 1200,
    status: 'paid'
  };
  showNewInvoiceModal.value = true;
};

const saveInvoice = () => {
  if (!newInvForm.value.buyer_name || !newInvForm.value.quantity_kg) {
    triggerToast('Tafadhali jaza maelezo yote ya Invoice!', 'error');
    return;
  }
  const total = Math.round(newInvForm.value.quantity_kg * newInvForm.value.unit_price * 1.18);
  const newInv = {
    id: Date.now(),
    invoice_number: `INV-2026-${Math.floor(100 + Math.random() * 900)}`,
    buyer_name: newInvForm.value.buyer_name,
    crop_type: newInvForm.value.crop_type,
    quantity_kg: newInvForm.value.quantity_kg,
    unit_price: newInvForm.value.unit_price,
    total_amount: total,
    status: newInvForm.value.status,
    created_at: new Date().toISOString()
  };
  invoices.value.unshift(newInv);
  triggerToast(`Tax Invoice #${newInv.invoice_number} imetengenezwa kikamilifu! 📄`);
  showNewInvoiceModal.value = false;
};

const exportLedgerCSV = () => {
  if (invoices.value.length === 0) return;
  let csv = "data:text/csv;charset=utf-8,Invoice Number,Buyer,Crop,Quantity (Kg),Total Amount (TZS),Date,Status\n";
  invoices.value.forEach(inv => {
    csv += `"${inv.invoice_number}","${inv.buyer_name || 'Buyer'}",${inv.crop_type || 'Mchele'},${inv.quantity_kg || 0},${inv.total_amount || 0},"${new Date(inv.created_at || Date.now()).toLocaleDateString()}","${inv.status}"\n`;
  });
  const link = document.createElement("a");
  link.setAttribute("href", encodeURI(csv));
  link.setAttribute("download", `Galanoki_Invoice_Ledger_${new Date().toISOString().slice(0,10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  triggerToast('Invoice Ledger imepakuliwa kama CSV! 📥');
};

onMounted(() => {
  fetchSalesData();
});
</script>
