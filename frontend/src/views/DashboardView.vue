<template>
  <div class="space-y-6 pb-12">
    
    <!-- Top Header Bar with Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
          Habari za asubuhi, James 👋
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
          Hivi ndivyo vinavyoendelea kwenye ghala lako leo — {{ todayFormatted }}
        </p>
      </div>
      <div class="flex items-center gap-2.5 w-full sm:w-auto">
        <button class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-xl border border-slate-200 transition flex items-center justify-center gap-2 w-1/2 sm:w-auto">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span>Pakua Data</span>
        </button>
        <button class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2 w-1/2 sm:w-auto">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>Muamala Mpya</span>
        </button>
      </div>
    </div>

    <!-- 1. Top KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      
      <!-- Card 1: Wakulima -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-emerald-500/30 transition">
        <div class="flex items-center justify-between">
          <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Wakulima Waliosajiliwa</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">👨‍🌾</div>
        </div>
        <div class="text-2xl font-black text-slate-900 mt-2.5">{{ (kpis.farmers || 2418).toLocaleString() }}</div>
        <div class="text-[11px] text-emerald-600 font-bold mt-1">Akaunti hai ghalani</div>
      </div>

      <!-- Card 2: Shehena Ghalani -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-emerald-500/30 transition">
        <div class="flex items-center justify-between">
          <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Shehena Ghalani</span>
          <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base">📦</div>
        </div>
        <div class="text-2xl font-black text-slate-900 mt-2.5">{{ ((kpis.stock || 184.5) * 1000).toLocaleString() }} Kg</div>
        <div class="text-[11px] text-blue-600 font-bold mt-1">Nafaka iliyo ghalani</div>
      </div>

      <!-- Card 3: Mikopo Isiyolipwa -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-amber-500/30 transition">
        <div class="flex items-center justify-between">
          <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Mikopo Isiyolipwa</span>
          <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base">💳</div>
        </div>
        <div class="text-2xl font-black text-amber-600 mt-2.5">Tsh {{ (kpis.loans || 3700000).toLocaleString() }}</div>
        <div class="text-[11px] text-amber-600 font-bold mt-1">Riba + Mtaji unaodaiwa</div>
      </div>

      <!-- Card 4: Mapato ya Huduma -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs hover:border-emerald-500/30 transition">
        <div class="flex items-center justify-between">
          <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Mapato ya Huduma</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">💰</div>
        </div>
        <div class="text-2xl font-black text-emerald-600 mt-2.5">Tsh {{ (kpis.revenue || 12450000).toLocaleString() }}</div>
        <div class="text-[11px] text-emerald-600 font-bold mt-1">Ada zote za usindikaji</div>
      </div>

    </div>

    <!-- 2. Charts Row 1: Directly Below KPI Cards (Revenue Line & Grain Donut) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Revenue Trend Chart (2 cols) -->
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h2 class="text-base font-extrabold text-slate-900">Mwenendo wa Mapato</h2>
            <p class="text-xs text-slate-500 font-medium">TZS — Miezi 6 iliyopita (Mapato ya Huduma)</p>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <span class="text-xs font-extrabold text-slate-600">Ada za Usindikaji</span>
          </div>
        </div>
        <div class="h-64">
          <Line :data="revenueChartData" :options="revenueChartOptions" />
        </div>
      </div>

      <!-- Grain Distribution Donut Chart (1 col) -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-slate-900">Mchanganuo wa Mazao</h2>
            <span class="text-xs font-bold text-slate-400">Kwa aina (Kg)</span>
          </div>
          <div class="h-48 flex items-center justify-center relative my-2">
            <Doughnut :data="grainChartData" :options="grainChartOptions" />
          </div>
        </div>
        <div class="grid grid-cols-3 gap-2 text-center pt-3 border-t border-slate-100 text-xs">
          <div>
            <div class="font-extrabold text-emerald-600">Mpunga</div>
            <div class="font-black text-slate-900 mt-0.5">45%</div>
          </div>
          <div>
            <div class="font-extrabold text-amber-500">Mahindi</div>
            <div class="font-black text-slate-900 mt-0.5">35%</div>
          </div>
          <div>
            <div class="font-extrabold text-indigo-500">Maharage</div>
            <div class="font-black text-slate-900 mt-0.5">20%</div>
          </div>
        </div>
      </div>

    </div>

    <!-- 3. Charts Row 2: Stock Intake vs Dispatch Bar Chart & Storage Bins Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Intake vs Dispatch Bar Chart -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h2 class="text-base font-extrabold text-slate-900">Mwenendo wa Mzigo Ghalani</h2>
            <p class="text-xs text-slate-500 font-medium">Intake vs Dispatch — miezi 6 iliyopita (Kg)</p>
          </div>
          <div class="flex items-center gap-4 text-xs font-bold">
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span><span class="text-slate-600">Intake</span></div>
            <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span><span class="text-slate-600">Dispatch</span></div>
          </div>
        </div>
        <div class="h-60">
          <Bar :data="stockChartData" :options="stockChartOptions" />
        </div>
      </div>

      <!-- Storage Bins Utilization Grid -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-base font-extrabold text-slate-900">Hali ya Bins za Ghala</h2>
            <p class="text-xs text-slate-500 font-medium">Ujazaji wa kila bin — sasa hivi</p>
          </div>
          <span class="text-xs font-extrabold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">6 Bins Active</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <div v-for="b in bins" :key="b.name" class="p-3.5 rounded-xl border border-slate-200/70 bg-slate-50/70">
            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
              <span>{{ b.name }}</span>
              <span class="font-mono text-[11px] text-slate-500">{{ b.crop }}</span>
            </div>
            <div class="text-base font-black text-slate-900 mt-2">{{ b.weight }} Kg</div>
            <div class="mt-2.5">
              <div class="flex justify-between text-[10px] font-extrabold text-slate-500 mb-1">
                <span>Ujazo</span>
                <span class="text-emerald-700 font-black">{{ b.pct }}%</span>
              </div>
              <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" :style="{ width: b.pct + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- 4. GODOWN & MACHINERY PERFORMANCE ANALYTICS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Godown Space Utilization Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
              <h2 class="text-base font-extrabold text-slate-900">Uchambuzi wa Godown (Warehouse Space)</h2>
              <p class="text-xs text-slate-500 font-medium">Kiwango cha Ujazaji na Hali ya Bins</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-slate-100 text-slate-700">Bay 01 - 06</span>
          </div>

          <div class="grid grid-cols-3 gap-3 p-4 bg-slate-50 rounded-xl mb-4 text-center">
            <div>
              <div class="text-[11px] font-bold text-slate-400 uppercase">Ujazo wa Jumla</div>
              <div class="text-sm sm:text-base font-black text-slate-900 mt-1">500,000 Kg</div>
            </div>
            <div>
              <div class="text-[11px] font-bold text-slate-400 uppercase">Mzigo Uliopo</div>
              <div class="text-sm sm:text-base font-black text-emerald-600 mt-1">{{ ((kpis.stock || 184.5) * 1000).toLocaleString() }} Kg</div>
            </div>
            <div>
              <div class="text-[11px] font-bold text-slate-400 uppercase">Nafasi Wazi</div>
              <div class="text-sm sm:text-base font-black text-slate-600 mt-1">{{ (500000 - (kpis.stock || 184.5) * 1000).toLocaleString() }} Kg</div>
            </div>
          </div>
        </div>

        <div>
          <div class="flex justify-between items-center text-xs font-extrabold mb-2">
            <span class="text-slate-600">Asilimia ya Ujazaji (Occupancy Rate)</span>
            <span class="text-emerald-600 font-black text-sm">36.9%</span>
          </div>
          <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200">
            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500" style="width: 36.9%"></div>
          </div>
        </div>
      </div>

      <!-- Machinery & Services Activity Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
          <div>
            <h2 class="text-base font-extrabold text-slate-900">Uchambuzi wa Mashine na Huduma</h2>
            <p class="text-xs text-slate-500 font-medium">Kiasi cha Kazi na Volume iliyochakatwa</p>
          </div>
          <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Moja kwa moja</span>
        </div>

        <div class="grid grid-cols-3 gap-3">
          
          <!-- Machine Drying -->
          <div class="bg-amber-50/60 border border-amber-200/60 p-3.5 rounded-xl text-center flex flex-col justify-between">
            <div>
              <div class="text-xl mb-1">☀️</div>
              <div class="text-[10px] font-black text-amber-800 uppercase tracking-wider">Kukausha</div>
              <div class="text-sm font-black text-slate-900 mt-1">14 Kazi</div>
            </div>
            <div class="mt-2 pt-2 border-t border-amber-200/50">
              <div class="text-xs font-black text-amber-700">42,500 Kg</div>
              <div class="text-[9.5px] font-bold text-slate-500 mt-0.5">3 Active / 11 Done</div>
            </div>
          </div>

          <!-- Machine Milling -->
          <div class="bg-indigo-50/60 border border-indigo-200/60 p-3.5 rounded-xl text-center flex flex-col justify-between">
            <div>
              <div class="text-xl mb-1">⚙️</div>
              <div class="text-[10px] font-black text-indigo-800 uppercase tracking-wider">Kukoboa</div>
              <div class="text-sm font-black text-slate-900 mt-1">28 Kazi</div>
            </div>
            <div class="mt-2 pt-2 border-t border-indigo-200/50">
              <div class="text-xs font-black text-indigo-700">89,200 Kg</div>
              <div class="text-[9.5px] font-bold text-slate-500 mt-0.5">5 Active / 23 Done</div>
            </div>
          </div>

          <!-- Machine Grading -->
          <div class="bg-teal-50/60 border border-teal-200/60 p-3.5 rounded-xl text-center flex flex-col justify-between">
            <div>
              <div class="text-xl mb-1">📊</div>
              <div class="text-[10px] font-black text-teal-800 uppercase tracking-wider">Kugredi</div>
              <div class="text-sm font-black text-slate-900 mt-1">19 Kazi</div>
            </div>
            <div class="mt-2 pt-2 border-t border-teal-200/50">
              <div class="text-xs font-black text-teal-700">54,000 Kg</div>
              <div class="text-[9.5px] font-bold text-slate-500 mt-0.5">Grading Machine</div>
            </div>
          </div>

        </div>
      </div>

    </div>

    <!-- 5. Recent Transactions Table -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-base font-extrabold text-slate-900">Miamala ya Hivi Karibuni</h2>
          <p class="text-xs text-slate-500 font-medium">Moja kwa moja — shughuli za hivi karibuni ghalani</p>
        </div>
        <router-link to="/farmers" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Tazama Wakulima Wote →</router-link>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
          <thead class="bg-slate-50 text-slate-500 font-extrabold border-b uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Tarehe</th>
              <th class="py-3 px-4">Mkulima</th>
              <th class="py-3 px-4">Aina ya Shughuli</th>
              <th class="py-3 px-4">Zao / Batch</th>
              <th class="py-3 px-4">Uzito (Kg)</th>
              <th class="py-3 px-4">Kiasi (TZS)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium">
            <tr v-for="t in transactions" :key="t.id" class="hover:bg-slate-50 transition">
              <td class="py-3 px-4 text-slate-500 font-mono">{{ t.date }}</td>
              <td class="py-3 px-4 font-bold text-slate-900">{{ t.farmer }}</td>
              <td class="py-3 px-4">
                <span :class="t.typeClass" class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase border">
                  {{ t.type }}
                </span>
              </td>
              <td class="py-3 px-4 text-slate-600">{{ t.crop }}</td>
              <td class="py-3 px-4 font-bold text-slate-900">{{ t.weight }} Kg</td>
              <td class="py-3 px-4 font-black text-emerald-600">Tsh {{ t.amount.toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import { Line, Bar, Doughnut } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend);

const kpis = ref({
  farmers: 2418,
  stock: 184.5,
  loans: 3700000,
  revenue: 12450000
});

const todayFormatted = computed(() => {
  return new Date().toLocaleDateString('sw-TZ', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
});

const revenueChartData = ref({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [{
    label: 'Mapato ya Huduma (TZS)',
    data: [1800000, 2400000, 3100000, 2900000, 4200000, 5100000],
    borderColor: '#10b981',
    backgroundColor: 'rgba(16, 185, 129, 0.08)',
    borderWidth: 3,
    fill: true,
    tension: 0.4
  }]
});

const revenueChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
  }
};

const grainChartData = ref({
  labels: ['Mpunga', 'Mahindi', 'Maharage'],
  datasets: [{
    data: [45, 35, 20],
    backgroundColor: ['#10b981', '#f59e0b', '#6366f1'],
    borderWidth: 0
  }]
});

const grainChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } }
};

const stockChartData = ref({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    { label: 'Intake (Kg)', data: [45000, 52000, 61000, 48000, 75000, 89000], backgroundColor: '#6366f1', borderRadius: 6 },
    { label: 'Dispatch (Kg)', data: [30000, 40000, 45000, 35000, 50000, 62000], backgroundColor: '#f59e0b', borderRadius: 6 }
  ]
});

const stockChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
  }
};

const bins = ref([
  { name: 'Bin 01', crop: 'Mpunga', weight: '45,200', pct: 90.4 },
  { name: 'Bin 02', crop: 'Mahindi', weight: '38,400', pct: 76.8 },
  { name: 'Bin 03', crop: 'Maharage', weight: '22,100', pct: 44.2 },
  { name: 'Bin 04', crop: 'Mpunga', weight: '41,000', pct: 82.0 },
  { name: 'Bin 05', crop: 'Alizeti', weight: '18,500', pct: 37.0 },
  { name: 'Bin 06', crop: 'Mahindi', weight: '19,300', pct: 38.6 }
]);

const transactions = ref([
  { id: 1, date: '17 Jun 2026', farmer: 'Amina Mwangi', type: 'Upokeaji', typeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200', crop: 'Mpunga (BT-901)', weight: '1,200', amount: 45000 },
  { id: 2, date: '17 Jun 2026', farmer: 'John Kiprop', type: 'Kukoboa', typeClass: 'bg-indigo-50 text-indigo-700 border-indigo-200', crop: 'Mahindi (BT-884)', weight: '850', amount: 85000 },
  { id: 3, date: '16 Jun 2026', farmer: 'Grace Massawe', type: 'Mkopo', typeClass: 'bg-amber-50 text-amber-700 border-amber-200', crop: 'Dhamana Mpunga', weight: '2,500', amount: 1500000 },
  { id: 4, date: '16 Jun 2026', farmer: 'Rashid Bakari', type: 'Kukausha', typeClass: 'bg-teal-50 text-teal-700 border-teal-200', crop: 'Maharage (BT-720)', weight: '950', amount: 38000 }
]);
</script>
