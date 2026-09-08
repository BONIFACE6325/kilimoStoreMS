<template>
  <div class="space-y-6 text-slate-800 dark:text-slate-100 font-sans">
    
    <!-- 👑 SUPER ADMIN SAAS PORTAL HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-950 to-emerald-950 text-white p-6 rounded-3xl border border-slate-800 shadow-xl">
      <div class="space-y-1">
        <div class="flex items-center gap-2 text-xs font-bold text-emerald-400 uppercase tracking-widest">
          <span>👑 PLATFORM SAAS GOVERNANCE</span>
          <span>•</span>
          <span>SYSTEM OWNER PORTAL</span>
        </div>
        <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
          <span>🏬 Usimamizi wa Makampuni & Subscriptions (SaaS Admin)</span>
        </h1>
        <p class="text-xs text-slate-300">
          Sajili makampuni mapya, simamia vifurushi vya malipo (Subscriptions), na dhibiti leseni za wateja.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button 
          @click="showRegisterModal = true"
          class="px-5 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-emerald-500/20 transition cursor-pointer flex items-center gap-2"
        >
          <span>➕</span>
          <span>Sajili Ghala / Kampuni Mpya</span>
        </button>
      </div>
    </div>

    <!-- 📊 SAAS KEY PERFORMANCE METRICS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      
      <!-- Total Subscribed Companies -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-1">
        <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Makampuni Yaliyosajiliwa</p>
        <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ tenants.length }}</h3>
        <p class="text-xs text-emerald-600 font-bold">Wateja wote wenye akaunti</p>
      </div>

      <!-- Monthly Recurring Revenue (MRR) -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-emerald-200/80 dark:border-emerald-800/40 shadow-2xs space-y-1">
        <p class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wider">Mapato ya Kila Mwezi (MRR)</p>
        <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400">TZS {{ formatCurrency(totalMrrTzs) }}</h3>
        <p class="text-xs text-slate-400">Monthly Subscription Turnover</p>
      </div>

      <!-- Active 14-Day Free Trials -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-blue-200/80 dark:border-blue-800/40 shadow-2xs space-y-1">
        <p class="text-[11px] font-extrabold text-blue-600 uppercase tracking-wider">Akaunti za Trial (Siku 14)</p>
        <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ trialTenantsCount }}</h3>
        <p class="text-xs text-slate-400">Wateja wanaofanya majaribio</p>
      </div>

      <!-- Active Paid Accounts -->
      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-purple-200/80 dark:border-purple-800/40 shadow-2xs space-y-1">
        <p class="text-[11px] font-extrabold text-purple-600 uppercase tracking-wider">Akaunti Zenye Leseni Hai</p>
        <h3 class="text-2xl font-black text-purple-600 dark:text-purple-400">{{ activeTenantsCount }}</h3>
        <p class="text-xs text-slate-400">Active Companies</p>
      </div>

    </div>

    <!-- 🏬 REGISTERED TENANTS DIRECTORY TABLE -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden space-y-0">
      
      <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
          <span>📋 Daftari la Makampuni & Maghala (Tenant Accounts Directory)</span>
        </h3>
        <span class="text-xs font-bold text-slate-400">Jumla: {{ tenants.length }} Makampuni</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs min-w-[800px]">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-extrabold uppercase border-b border-slate-200/80 dark:border-slate-800">
            <tr>
              <th class="py-3.5 px-4">Kampuni / Ghala</th>
              <th class="py-3.5 px-4">Mmiliki (Contact Person)</th>
              <th class="py-3.5 px-4">Kifurushi (Plan)</th>
              <th class="py-3.5 px-4">Malipo ya Mwezi</th>
              <th class="py-3.5 px-4">Tarehe ya Kuisha</th>
              <th class="py-3.5 px-4">Hali (Status)</th>
              <th class="py-3.5 px-4 text-right">Vitendo (Actions)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
            <tr v-for="t in tenants" :key="t.id" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
              <td class="py-4 px-4 font-black text-slate-900 dark:text-white text-sm">
                <div class="flex items-center gap-2">
                  <span>🏢</span>
                  <div>
                    <span>{{ t.name }}</span>
                    <span class="block text-[10.5px] font-mono text-slate-400 font-normal">ID: {{ t.id }}</span>
                  </div>
                </div>
              </td>

              <td class="py-4 px-4">
                <div class="space-y-0.5">
                  <p class="font-bold text-slate-800 dark:text-slate-200">{{ t.ownerName }}</p>
                  <p class="text-[11px] text-slate-400">{{ t.ownerEmail }}</p>
                  <p class="text-[10.5px] text-emerald-600 font-mono">{{ t.phone }}</p>
                </div>
              </td>

              <td class="py-4 px-4 font-extrabold">
                <span 
                  :class="getPlanBadgeClass(t.plan)"
                  class="px-2.5 py-1 rounded-full text-[10.5px] uppercase tracking-wider border"
                >
                  {{ t.plan }}
                </span>
              </td>

              <td class="py-4 px-4 font-black text-emerald-600 dark:text-emerald-400">
                TZS {{ formatCurrency(t.monthlyPriceTzs) }}
              </td>

              <td class="py-4 px-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                {{ t.expiresAt }}
              </td>

              <td class="py-4 px-4">
                <span 
                  :class="t.status === 'active' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-rose-100 text-rose-700 border-rose-300'"
                  class="px-2.5 py-1 rounded-full text-[10.5px] font-extrabold capitalize border"
                >
                  {{ t.status }}
                </span>
              </td>

              <td class="py-4 px-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button 
                    @click="handleSwitchTenant(t.id)"
                    class="px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-extrabold text-[11px] rounded-lg border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition cursor-pointer"
                    title="Fungua Ghala Hili"
                  >
                    🔍 Ingia
                  </button>

                  <button 
                    @click="handleExtendSubscription(t.id)"
                    class="px-2.5 py-1.5 bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 font-extrabold text-[11px] rounded-lg border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition cursor-pointer"
                    title="Ongeza Siku 30"
                  >
                    📅 +30 Siku
                  </button>

                  <button 
                    @click="handleToggleStatus(t)"
                    :class="t.status === 'active' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                    class="px-2.5 py-1.5 font-extrabold text-[11px] rounded-lg border transition cursor-pointer"
                  >
                    {{ t.status === 'active' ? '⏸️ Sitisha' : '▶️ Anzisha' }}
                  </button>

                  <button 
                    @click="handleDeleteTenant(t.id)"
                    class="px-2 py-1.5 bg-rose-50 text-rose-600 font-bold text-[11px] rounded-lg border border-rose-200 hover:bg-rose-100 transition cursor-pointer"
                    title="Futa Akaunti"
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

    <!-- 💳 SUBSCRIPTION PLANS CONFIGURATION CARDS -->
    <div class="space-y-4">
      <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
        <span>💎 Vifurushi vya Subscription Mfumoni (SaaS Plans & Pricing)</span>
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Starter Plan -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="space-y-1">
            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-xs font-black uppercase">Starter Plan</span>
            <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-2">TZS 50,000 <span class="text-xs text-slate-400 font-normal">/ Mwezi</span></h4>
            <p class="text-xs text-slate-500">Inamfaa mmiliki wa ghala 1 dogo au mchakataji mdogo wa mazao.</p>
          </div>
          <ul class="space-y-2 text-xs font-semibold text-slate-600 dark:text-slate-300 pt-2 border-t border-slate-100 dark:border-slate-800">
            <li class="flex items-center gap-2">✓ Ghala 1 (Capacity 1,000 MT)</li>
            <li class="flex items-center gap-2">✓ Hadi Wakulima 250</li>
            <li class="flex items-center gap-2">✓ Ripoti za PDF na CSV</li>
          </ul>
        </div>

        <!-- Business Plan -->
        <div class="bg-gradient-to-b from-emerald-500/10 to-transparent bg-white dark:bg-slate-900 p-6 rounded-3xl border-2 border-emerald-500 shadow-lg space-y-4 relative">
          <span class="absolute -top-3 right-6 px-3 py-0.5 bg-emerald-500 text-slate-950 rounded-full text-[10.5px] font-black uppercase">Popular</span>
          <div class="space-y-1">
            <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-black uppercase">Business Plan</span>
            <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-2">TZS 150,000 <span class="text-xs text-slate-400 font-normal">/ Mwezi</span></h4>
            <p class="text-xs text-slate-500">Inafaa kwa viwanda vya kinu na maghala ya kibiashara.</p>
          </div>
          <ul class="space-y-2 text-xs font-semibold text-slate-600 dark:text-slate-300 pt-2 border-t border-slate-100 dark:border-slate-800">
            <li class="flex items-center gap-2">✓ Maghala 3 (Capacity 5,000 MT)</li>
            <li class="flex items-center gap-2">✓ Wakulima Wasiokua na Kikomo</li>
            <li class="flex items-center gap-2">✓ Utoaji Mikopo na Settlements</li>
            <li class="flex items-center gap-2">✓ P&L Financial Statements</li>
          </ul>
        </div>

        <!-- Enterprise Plan -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="space-y-1">
            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 rounded-full text-xs font-black uppercase">Enterprise Plan</span>
            <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-2">TZS 500,000 <span class="text-xs text-slate-400 font-normal">/ Mwezi</span></h4>
            <p class="text-xs text-slate-500">Inafaa kwa makampuni makubwa ya nafaka na usafirishaji.</p>
          </div>
          <ul class="space-y-2 text-xs font-semibold text-slate-600 dark:text-slate-300 pt-2 border-t border-slate-100 dark:border-slate-800">
            <li class="flex items-center gap-2">✓ Maghala Yasiyokuwa na Kikomo</li>
            <li class="flex items-center gap-2">✓ Dedicated Support & Custom Reports</li>
            <li class="flex items-center gap-2">✓ Full Audit Trail & Multi-User Roles</li>
          </ul>
        </div>

      </div>
    </div>

    <!-- ➕ MODAL: ONBOARD NEW TENANT COMPANY -->
    <transition name="fade">
      <div v-if="showRegisterModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden animate-fadeIn">
          
          <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="font-black text-base text-slate-900 dark:text-white flex items-center gap-2">
              <span>➕ Sajili Kampuni / Ghala Jipya</span>
            </h3>
            <button @click="showRegisterModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="handleCreateTenant" class="p-6 space-y-4 text-xs font-semibold">
            
            <div v-if="modalError" class="p-3 bg-rose-50 text-rose-600 rounded-xl font-bold">
              {{ modalError }}
            </div>

            <div class="space-y-1.5">
              <label class="font-extrabold text-slate-700 dark:text-slate-300">Jina la Kampuni / Ghala *</label>
              <input 
                type="text" 
                v-model="newTenantForm.companyName" 
                required
                placeholder="mf. Mwanza Grain Terminal Ltd"
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-white"
              />
            </div>

            <div class="space-y-1.5">
              <label class="font-extrabold text-slate-700 dark:text-slate-300">Jina la Mmiliki *</label>
              <input 
                type="text" 
                v-model="newTenantForm.ownerName" 
                required
                placeholder="mf. Juma Hamisi"
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-white"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1.5">
                <label class="font-extrabold text-slate-700 dark:text-slate-300">Barua Pepe (Email) *</label>
                <input 
                  type="email" 
                  v-model="newTenantForm.ownerEmail" 
                  required
                  placeholder="juma@mwanzagrain.co.tz"
                  class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-white"
                />
              </div>

              <div class="space-y-1.5">
                <label class="font-extrabold text-slate-700 dark:text-slate-300">Simu *</label>
                <input 
                  type="text" 
                  v-model="newTenantForm.phone" 
                  required
                  placeholder="+255 754 000 111"
                  class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-white"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1.5">
                <label class="font-extrabold text-slate-700 dark:text-slate-300">Neno la Siri (Password) *</label>
                <input 
                  type="password" 
                  v-model="newTenantForm.password" 
                  required
                  placeholder="••••••••"
                  class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-white"
                />
              </div>

              <div class="space-y-1.5">
                <label class="font-extrabold text-slate-700 dark:text-slate-300">Kifurushi (Plan) *</label>
                <select 
                  v-model="newTenantForm.plan" 
                  class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-white"
                >
                  <option value="trial">14-Day Free Trial</option>
                  <option value="starter">Starter (TZS 50,000/mo)</option>
                  <option value="business">Business (TZS 150,000/mo)</option>
                  <option value="enterprise">Enterprise (TZS 500,000/mo)</option>
                </select>
              </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
              <button 
                type="button" 
                @click="showRegisterModal = false" 
                class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 font-bold rounded-xl"
              >
                Ghairi
              </button>
              <button 
                type="submit" 
                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold rounded-xl shadow-md cursor-pointer"
              >
                Sajili Akaunti Mpya
              </button>
            </div>

          </form>

        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useTenants } from '../composables/useTenants';

const router = useRouter();
const { 
  tenants, 
  registerTenant, 
  updateTenantStatus, 
  extendSubscription, 
  deleteTenant, 
  setActiveTenant,
  totalMrrTzs,
  activeTenantsCount,
  trialTenantsCount 
} = useTenants();

const showRegisterModal = ref(false);
const modalError = ref('');

const newTenantForm = ref({
  companyName: '',
  ownerName: '',
  ownerEmail: '',
  phone: '',
  password: '12345678',
  plan: 'trial'
});

const formatCurrency = (val) => {
  return Number(val || 0).toLocaleString('en-US');
};

const getPlanBadgeClass = (plan) => {
  if (plan === 'trial') return 'bg-amber-100 text-amber-700 border-amber-300';
  if (plan === 'starter') return 'bg-blue-100 text-blue-700 border-blue-300';
  if (plan === 'business') return 'bg-emerald-100 text-emerald-700 border-emerald-300';
  return 'bg-purple-100 text-purple-700 border-purple-300';
};

const handleCreateTenant = () => {
  modalError.value = '';
  const res = registerTenant(newTenantForm.value);
  if (!res.success) {
    modalError.value = res.message;
    return;
  }
  showRegisterModal.value = false;
  newTenantForm.value = { companyName: '', ownerName: '', ownerEmail: '', phone: '', password: '12345678', plan: 'trial' };
  alert('Akaunti ya Kampuni imesajiliwa vyema!');
};

const handleSwitchTenant = (tenantId) => {
  setActiveTenant(tenantId);
  window.location.replace('/');
};

const handleToggleStatus = (t) => {
  const newStatus = t.status === 'active' ? 'suspended' : 'active';
  updateTenantStatus(t.id, newStatus);
};

const handleExtendSubscription = (tenantId) => {
  extendSubscription(tenantId, 30);
  alert('Leseni imeongezwa kwa siku 30!');
};

const handleDeleteTenant = (tenantId) => {
  if (confirm('Je, una uhakika unataka kufuta akaunti hii ya kampuni?')) {
    deleteTenant(tenantId);
  }
};
</script>
