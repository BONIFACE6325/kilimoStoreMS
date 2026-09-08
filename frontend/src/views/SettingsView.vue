<template>
  <div class="space-y-6 pb-12">
    
    <!-- Page Header Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <router-link to="/" class="hover:text-emerald-600">{{ t('dashboard', 'Dashboard') }}</router-link>
          <span>/</span>
          <span class="text-slate-700 dark:text-slate-200 font-bold">{{ t('settings', 'Mipangilio') }}</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
          <span>⚙️ {{ t('settingsManagement', 'Mipangilio ya Mfumo & Akaunti') }}</span>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          {{ t('settingsSubtitle', 'Badili taarifa za kinu, maelezo ya mmiliki na lugha') }}
        </p>
      </div>
    </div>

    <!-- Toast Notification Card -->
    <transition name="fade">
      <div 
        v-if="toast.show" 
        class="p-4 rounded-2xl border flex items-center justify-between shadow-lg text-xs font-bold transition-all"
        :class="toast.type === 'error' ? 'bg-red-50 dark:bg-red-950/80 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800' : 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'"
      >
        <div class="flex items-center gap-2.5">
          <span class="text-base">{{ toast.type === 'error' ? '⚠️' : '✅' }}</span>
          <span>{{ toast.message }}</span>
        </div>
        <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600 text-xs font-black">✕</button>
      </div>
    </transition>

    <!-- Settings Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
      <button 
        @click="activeTab = 'profile'"
        :class="[
          'px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all cursor-pointer flex items-center gap-2',
          activeTab === 'profile' 
            ? 'bg-slate-900 text-white dark:bg-emerald-600 shadow-md' 
            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'
        ]"
      >
        <span>👤</span>
        <span>{{ currentLang === 'sw' ? 'Wasifu Wa Akaunti' : 'Account Profile' }}</span>
      </button>

      <button 
        @click="activeTab = 'security'"
        :class="[
          'px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all cursor-pointer flex items-center gap-2',
          activeTab === 'security' 
            ? 'bg-slate-900 text-white dark:bg-emerald-600 shadow-md' 
            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'
        ]"
      >
        <span>🔑</span>
        <span>{{ currentLang === 'sw' ? 'Badili Neno la Siri' : 'Security & Password' }}</span>
      </button>

      <button 
        @click="activeTab = 'system'"
        :class="[
          'px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all cursor-pointer flex items-center gap-2',
          activeTab === 'system' 
            ? 'bg-slate-900 text-white dark:bg-emerald-600 shadow-md' 
            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800'
        ]"
      >
        <span>🏬</span>
        <span>{{ currentLang === 'sw' ? 'Taarifa za Kinu' : 'Mill Preferences' }}</span>
      </button>
    </div>

    <!-- TAB 1: WASIFU NA PICHA YA AKAUNTI -->
    <div v-if="activeTab === 'profile'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Profile Picture Card (1 col) -->
      <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs text-center space-y-4">
        <div class="relative w-28 h-28 mx-auto">
          <div class="w-full h-full rounded-3xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-2xl flex items-center justify-center shadow-xl shadow-emerald-600/20 ring-4 ring-emerald-500/20 overflow-hidden">
            <img v-if="profileForm.avatarUrl" :src="profileForm.avatarUrl" class="w-full h-full object-cover" />
            <span v-else>{{ userInitials }}</span>
          </div>
          <label class="absolute -bottom-2 -right-2 p-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-md cursor-pointer transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <input type="file" accept="image/*" class="hidden" @change="onAvatarFileChange" />
          </label>
        </div>

        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-50">{{ profileForm.name || 'Boniface Gwakila' }}</h3>
          <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ profileForm.role || t('systemOwner') }}</p>
          <p class="text-[11px] text-slate-400 font-mono mt-1">{{ profileForm.email }}</p>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-center gap-2">
          <label class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold cursor-pointer transition">
            <span>📷 {{ currentLang === 'sw' ? 'Pakia Picha' : 'Upload Photo' }}</span>
            <input type="file" accept="image/*" class="hidden" @change="onAvatarFileChange" />
          </label>
          <button 
            v-if="profileForm.avatarUrl"
            @click="removeAvatar"
            class="px-3 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 rounded-xl text-xs font-bold transition cursor-pointer"
          >
            <span>🗑️ {{ currentLang === 'sw' ? 'Ondoa' : 'Remove' }}</span>
          </button>
        </div>
      </div>

      <!-- Profile Edit Form (2 cols) -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
          <h2 class="text-sm font-extrabold text-slate-900 dark:text-slate-50 uppercase tracking-wider">
            {{ currentLang === 'sw' ? 'Maelezo ya Wasifu Wa Akaunti' : 'Account Details' }}
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">Badili taarifa zako za kibinafsi na namba ya mawasiliano.</p>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                {{ currentLang === 'sw' ? 'Jina Kamili' : 'Full Name' }} *
              </label>
              <input 
                v-model="profileForm.name" 
                type="text" 
                required 
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                {{ currentLang === 'sw' ? 'Barua Pepe (Email)' : 'Email Address' }} *
              </label>
              <input 
                v-model="profileForm.email" 
                type="email" 
                required 
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                {{ currentLang === 'sw' ? 'Namba ya Simu' : 'Phone Number' }}
              </label>
              <input 
                v-model="profileForm.phone" 
                type="text" 
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                {{ currentLang === 'sw' ? 'Wadhifa / Role' : 'System Role' }}
              </label>
              <input 
                v-model="profileForm.role" 
                type="text" 
                disabled 
                class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-500 cursor-not-allowed"
              />
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button 
              type="submit"
              class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center gap-2"
            >
              <span>💾</span>
              <span>{{ currentLang === 'sw' ? 'Hifadhi Wasifu' : 'Save Profile Changes' }}</span>
            </button>
          </div>
        </form>
      </div>

    </div>

    <!-- TAB 2: BADILI NENO LA SIRI (SECURITY) -->
    <div v-if="activeTab === 'security'" class="max-w-2xl mx-auto bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-5">
      <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold">
          🔑
        </div>
        <div>
          <h2 class="text-sm font-extrabold text-slate-900 dark:text-slate-50 uppercase tracking-wider">
            {{ currentLang === 'sw' ? 'Badili Neno la Siri (Change Password)' : 'Change Password' }}
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">Weka neno la siri jipya na lenye usalama mkubwa kwa akaunti yako.</p>
        </div>
      </div>

      <form @submit.prevent="savePassword" class="space-y-4">
        
        <!-- Current Password -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            {{ currentLang === 'sw' ? 'Neno la Siri la Sasa (Current Password)' : 'Current Password' }} *
          </label>
          <div class="relative">
            <input 
              v-model="passwordForm.currentPassword" 
              :type="showCurrentPass ? 'text' : 'password'" 
              required 
              placeholder="••••••••"
              class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
            />
            <button 
              type="button" 
              @click="showCurrentPass = !showCurrentPass"
              class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold"
            >
              {{ showCurrentPass ? '👁️‍🗨️' : '👁️' }}
            </button>
          </div>
        </div>

        <!-- New Password -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            {{ currentLang === 'sw' ? 'Neno la Siri Jipya (New Password)' : 'New Password' }} *
          </label>
          <div class="relative">
            <input 
              v-model="passwordForm.newPassword" 
              :type="showNewPass ? 'text' : 'password'" 
              required 
              placeholder="Sio chini ya tarakimu 6"
              class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
            />
            <button 
              type="button" 
              @click="showNewPass = !showNewPass"
              class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold"
            >
              {{ showNewPass ? '👁️‍🗨️' : '👁️' }}
            </button>
          </div>
        </div>

        <!-- Confirm New Password -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            {{ currentLang === 'sw' ? 'Thibitisha Neno la Siri Jipya (Confirm New Password)' : 'Confirm New Password' }} *
          </label>
          <div class="relative">
            <input 
              v-model="passwordForm.confirmPassword" 
              :type="showConfirmPass ? 'text' : 'password'" 
              required 
              placeholder="Rudia neno jipya"
              class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
            />
            <button 
              type="button" 
              @click="showConfirmPass = !showConfirmPass"
              class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold"
            >
              {{ showConfirmPass ? '👁️‍🗨️' : '👁️' }}
            </button>
          </div>
        </div>

        <div class="p-3 bg-amber-50/70 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800/60 text-[11px] text-amber-800 dark:text-amber-300 space-y-1">
          <div class="font-extrabold flex items-center gap-1.5">
            <span>🛡️ Ushauri wa Usalama:</span>
          </div>
          <p>Hakikisha neno lako la siri lina urefu wa angalau tarakimu 6 na usimpe mtu yeyote neno lako la siri.</p>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
          <button 
            type="submit"
            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center gap-2"
          >
            <span>🔒</span>
            <span>{{ currentLang === 'sw' ? 'Badili Neno la Siri Now' : 'Update Password Now' }}</span>
          </button>
        </div>

      </form>
    </div>

    <!-- TAB 3: TAARIFA ZA KAMPUNI NA NEMBO (BRANDING) -->
    <div v-if="activeTab === 'system'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Company Logo Preview Card -->
      <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs text-center space-y-4">
        <div class="relative w-32 h-32 mx-auto">
          <div class="w-full h-full rounded-3xl bg-slate-100 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden shadow-inner">
            <img v-if="companyForm.logoUrl" :src="companyForm.logoUrl" class="w-full h-full object-cover" />
            <div v-else class="text-center p-2">
              <span class="text-3xl block">🏢</span>
              <span class="text-[10px] text-slate-400 font-bold uppercase mt-1 block">Weka Logo</span>
            </div>
          </div>
          <label class="absolute -bottom-2 -right-2 p-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-md cursor-pointer transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <input type="file" accept="image/*" class="hidden" @change="onCompanyLogoChange" />
          </label>
        </div>

        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-50">{{ companyForm.name || activeTenant?.name }}</h3>
          <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ companyForm.slogan || 'Store & Finance MS' }}</p>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-center gap-2">
          <label class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded-xl text-xs font-extrabold cursor-pointer transition border border-emerald-200/80">
            <span>📷 Pakia Logo ya Kampuni</span>
            <input type="file" accept="image/*" class="hidden" @change="onCompanyLogoChange" />
          </label>
          <button 
            v-if="companyForm.logoUrl"
            @click="removeCompanyLogo"
            class="px-3.5 py-2 bg-red-50 hover:bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 rounded-xl text-xs font-extrabold transition cursor-pointer"
          >
            <span>🗑️ Ondoa Logo</span>
          </button>
        </div>
      </div>

      <!-- Company Branding Details Form -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
          <h2 class="text-sm font-extrabold text-slate-900 dark:text-slate-50 uppercase tracking-wider">
            Taarifa za Kinu & Brand ya Kampuni
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">Badili jina la Kampuni/Ghala, Kaulimbiu, na Nembo maalum kwa akaunti hii.</p>
        </div>

        <form @submit.prevent="saveCompanyBranding" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Jina la Kampuni / Ghala *
              </label>
              <input 
                v-model="companyForm.name" 
                type="text" 
                required 
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Namba ya Simu ya Kampuni
              </label>
              <input 
                v-model="companyForm.phone" 
                type="text" 
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              />
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Kaulimbiu / Subtitle (Slogan)
              </label>
              <input 
                v-model="companyForm.slogan" 
                type="text" 
                placeholder="e.g. Processing & Grain Storage Solutions" 
                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
              />
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button 
              type="submit"
              class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-emerald-400/30 transition cursor-pointer flex items-center gap-2"
            >
              <span>💾</span>
              <span>Hifadhi Nembo na Taarifa za Kampuni</span>
            </button>
          </div>
        </form>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAuth } from '../composables/useAuth';
import { useTenants } from '../composables/useTenants';
import { useLanguage } from '../composables/useLanguage';

const { user, updateProfile, changePassword } = useAuth();
const { activeTenant, updateTenantBranding } = useTenants();
const { currentLang, t } = useLanguage();

const activeTab = ref('profile');

const profileForm = ref({
  name: user.value?.name || 'Boniface Gwakila',
  email: user.value?.email || 'gwakilabonface@gmail.com',
  phone: user.value?.phone || '0750000000',
  role: user.value?.role || 'System Owner',
  avatarUrl: user.value?.avatarUrl || ''
});

const companyForm = ref({
  name: activeTenant.value?.name || '',
  phone: activeTenant.value?.phone || '',
  slogan: activeTenant.value?.slogan || 'Store & Finance MS',
  logoUrl: activeTenant.value?.logoUrl || ''
});

watch(activeTenant, (tVal) => {
  if (tVal) {
    companyForm.value = {
      name: tVal.name || '',
      phone: tVal.phone || '',
      slogan: tVal.slogan || 'Store & Finance MS',
      logoUrl: tVal.logoUrl || ''
    };
  }
}, { immediate: true });

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

const showCurrentPass = ref(false);
const showNewPass = ref(false);
const showConfirmPass = ref(false);

const toast = ref({
  show: false,
  message: '',
  type: 'success'
});

const triggerToast = (msg, type = 'success') => {
  toast.value = { show: true, message: msg, type };
  setTimeout(() => {
    toast.value.show = false;
  }, 4000);
};

const userInitials = computed(() => {
  const name = profileForm.value.name || 'Boniface Gwakila';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
});

const onAvatarFileChange = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    triggerToast(currentLang.value === 'sw' ? 'Tafadhali chagua picha iliyo sahihi (PNG, JPG, JPEG).' : 'Please select a valid image file.', 'error');
    return;
  }

  if (file.size > 3 * 1024 * 1024) {
    triggerToast(currentLang.value === 'sw' ? 'Ukubwa wa picha usiwe zaidi ya 3MB.' : 'Image size should not exceed 3MB.', 'error');
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    const dataUrl = e.target.result;
    profileForm.value.avatarUrl = dataUrl;
    updateProfile({ avatarUrl: dataUrl });
    triggerToast(currentLang.value === 'sw' ? 'Picha ya akaunti imepakiwa na kuhifadhiwa kikamilifu! ✅' : 'Profile photo updated successfully! ✅', 'success');
  };
  reader.readAsDataURL(file);
};

const removeAvatar = () => {
  profileForm.value.avatarUrl = '';
  updateProfile({ avatarUrl: '' });
  triggerToast(currentLang.value === 'sw' ? 'Picha ya akaunti imeondolewa.' : 'Profile photo removed.', 'success');
};

const onCompanyLogoChange = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    triggerToast('Tafadhali chagua picha iliyo sahihi (PNG, JPG, JPEG).', 'error');
    return;
  }

  if (file.size > 3 * 1024 * 1024) {
    triggerToast('Ukubwa wa picha ya logo usiwe zaidi ya 3MB.', 'error');
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    const dataUrl = e.target.result;
    companyForm.value.logoUrl = dataUrl;
    updateTenantBranding(activeTenant.value.id, { logoUrl: dataUrl });
    triggerToast('Nembo ya Kampuni imepakiwa na kuhifadhiwa kikamilifu! 🏢', 'success');
  };
  reader.readAsDataURL(file);
};

const removeCompanyLogo = () => {
  companyForm.value.logoUrl = '';
  updateTenantBranding(activeTenant.value.id, { logoUrl: '' });
  triggerToast('Nembo ya kampuni imeondolewa.', 'success');
};

const saveCompanyBranding = () => {
  const res = updateTenantBranding(activeTenant.value.id, {
    name: companyForm.value.name,
    phone: companyForm.value.phone,
    slogan: companyForm.value.slogan,
    logoUrl: companyForm.value.logoUrl
  });

  if (res.success) {
    triggerToast('Taarifa na Brand ya Kampuni zimehifadhiwa kikamilifu! 🏢', 'success');
  } else {
    triggerToast(res.message || 'Hitilafu imetokea.', 'error');
  }
};

const saveProfile = () => {
  const res = updateProfile({
    name: profileForm.value.name,
    email: profileForm.value.email,
    phone: profileForm.value.phone,
    avatarUrl: profileForm.value.avatarUrl
  });

  if (res.success) {
    triggerToast(currentLang.value === 'sw' ? 'Taarifa za wasifu zimehifadhiwa kikamilifu! ✅' : 'Profile changes saved successfully! ✅', 'success');
  } else {
    triggerToast(res.message || 'Hitilafu imetokea.', 'error');
  }
};

const savePassword = () => {
  const res = changePassword({
    currentPassword: passwordForm.value.currentPassword,
    newPassword: passwordForm.value.newPassword,
    confirmPassword: passwordForm.value.confirmPassword
  });

  if (res.success) {
    passwordForm.value.currentPassword = '';
    passwordForm.value.newPassword = '';
    passwordForm.value.confirmPassword = '';
    triggerToast(currentLang.value === 'sw' ? 'Neno la siri limebadilishwa kikamilifu! 🔒' : 'Password updated successfully! 🔒', 'success');
  } else {
    triggerToast(res.message || 'Hitilafu ya neno la siri.', 'error');
  }
};

onMounted(() => {
  if (user.value) {
    profileForm.value = {
      name: user.value.name || 'Boniface Gwakila',
      email: user.value.email || 'gwakilabonface@gmail.com',
      phone: user.value.phone || '0750000000',
      role: user.value.role || 'System Owner',
      avatarUrl: user.value.avatarUrl || ''
    };
  }
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
