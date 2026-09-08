<template>
  <header class="h-18 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-700/80 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs transition-all duration-300">
    
    <!-- Left Area: Mobile Brand & Search Bar -->
    <div class="flex items-center gap-3 sm:gap-4">
      
      <!-- Menu Box Sidebar Toggle Button (Desktop & Mobile) -->
      <button 
        @click="toggleSidebar" 
        class="p-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50 bg-slate-100/90 dark:bg-slate-800/90 hover:bg-slate-200/90 dark:bg-slate-700/90 rounded-xl transition border border-slate-200/80 dark:border-slate-700/80 shadow-2xs cursor-pointer"
        title="Fungua/Funga Menu"
      >
        <svg class="w-5 h-5 text-slate-700 dark:text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <!-- Mobile App Brand Name (Only visible on small mobile screens) -->
      <div class="flex items-center gap-2 md:hidden">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black flex items-center justify-center text-sm shadow-md">
          ⭐
        </div>
        <span class="font-extrabold text-slate-900 dark:text-slate-50 text-base tracking-tight">GARANOKI</span>
      </div>

      <!-- Compact Search Bar -->
      <div class="relative hidden sm:block w-64 md:w-72 group">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-600 dark:text-emerald-500 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input 
          type="text" 
          v-model="searchQuery"
          :placeholder="t('searchPlaceholder', 'Search farmers, invoices...')" 
          class="w-full pl-9 pr-10 py-2 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-100 dark:bg-slate-800 focus:bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/80 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-200 shadow-2xs"
        />
        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
          <kbd class="hidden md:inline-block px-1.5 py-0.5 text-[9.5px] font-extrabold text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md shadow-2xs">⌘K</kbd>
        </div>
      </div>

      <!-- Active Tenant Badge & Switcher for Super Admin / Tenant -->
      <div class="relative">
        <div 
          @click="isSuperAdmin && (showTenantMenu = !showTenantMenu)"
          class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-xs font-bold text-emerald-900 dark:text-emerald-300 transition shadow-2xs"
          :class="isSuperAdmin ? 'cursor-pointer hover:bg-emerald-100 dark:hover:bg-emerald-900/80' : ''"
          :title="isSuperAdmin ? 'Badili Ghala (Switch Warehouse)' : 'Ghala Yako'"
        >
          <img v-if="activeTenant?.logoUrl" :src="activeTenant.logoUrl" class="w-5 h-5 rounded-md object-cover shrink-0 border border-emerald-300" />
          <span v-else class="text-base shrink-0">🏢</span>
          <div class="text-left hidden sm:block">
            <div class="text-[11px] font-extrabold truncate max-w-[130px] md:max-w-[170px]">
              {{ activeTenant?.name || user?.tenantName || 'Ghala Yako' }}
            </div>
            <div class="text-[9px] uppercase tracking-wider font-black text-emerald-600 dark:text-emerald-400">
              {{ activeTenant?.plan || 'enterprise' }} PLAN
            </div>
          </div>
          <svg v-if="isSuperAdmin" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <!-- Dropdown for Super Admin Tenant Switcher -->
        <transition name="pop">
          <div v-if="showTenantMenu && isSuperAdmin" class="absolute left-0 mt-2 w-64 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-2 z-50 space-y-1">
            <div class="px-3 py-1.5 text-[10.5px] font-black uppercase text-slate-400 tracking-wider">
              Badilisha Ghala (Multi-Tenant)
            </div>
            <button
              v-for="tItem in tenants"
              :key="tItem.id"
              @click="switchTenant(tItem.id)"
              class="w-full text-left p-2.5 rounded-xl text-xs flex items-center justify-between transition cursor-pointer"
              :class="tItem.id === activeTenant?.id ? 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-900 dark:text-emerald-300 font-extrabold border border-emerald-200 dark:border-emerald-800' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold'"
            >
              <div>
                <div class="truncate font-black">{{ tItem.name }}</div>
                <div class="text-[10px] text-slate-400 capitalize">{{ tItem.ownerName }} • {{ tItem.plan }}</div>
              </div>
              <span v-if="tItem.id === activeTenant?.id" class="text-emerald-600 font-black text-sm">✓</span>
            </button>
          </div>
        </transition>
      </div>

    </div>

    <!-- Right Controls Area -->
    <div class="flex items-center gap-2 sm:gap-3">
      
      <!-- Language Switcher Pill -->
      <button 
        @click="toggleLanguage" 
        class="px-2.5 sm:px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-xs font-black rounded-xl border border-slate-200/80 dark:border-slate-700/80 transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center gap-1.5 shadow-2xs cursor-pointer"
        :title="currentLang === 'sw' ? 'Switch to English' : 'Badili kwenda Kiswahili'"
      >
        <span class="text-sm leading-none">{{ currentLang === 'sw' ? '🇹🇿' : '🇬🇧' }}</span>
        <span>{{ currentLang.toUpperCase() }}</span>
      </button>

      <!-- Fullscreen / Expand Screen Toggle Button -->
      <button 
        @click="toggleFullscreen"
        class="p-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:bg-slate-700/80 rounded-xl transition-all duration-300 border border-slate-200/60 dark:border-slate-700/60 transform hover:scale-105 active:scale-95 cursor-pointer flex items-center gap-1.5"
        :title="isFullscreen ? (currentLang === 'sw' ? 'Punguza Skrini' : 'Exit Full Screen') : (currentLang === 'sw' ? 'Tanua Skrini (Full Screen)' : 'Full Screen')"
      >
        <svg v-if="!isFullscreen" class="w-5 h-5 text-slate-700 dark:text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
        </svg>
        <svg v-else class="w-5 h-5 text-slate-700 dark:text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15L4 20m0 0h4m-4 0v-4m16 4l-5-5m5 5v-4m0 4h-4M9 9L4 4m0 0h4M4 4v4m16-4l-5 5m5-5v4m0-4h-4" />
        </svg>
      </button>

      <!-- Notifications Bell -->
      <div class="relative">
        <button 
          @click="showNotifs = !showNotifs"
          class="relative p-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:bg-slate-700/80 rounded-xl transition-all duration-200 border border-slate-200/60 dark:border-slate-700/60 transform hover:scale-105 active:scale-95"
          :title="t('notifications')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          <span class="w-4 h-4 rounded-full bg-red-500 text-white font-black text-[9.5px] flex items-center justify-center absolute -top-1 -right-1 border-2 border-white dark:border-slate-900 shadow-sm animate-pulse">3</span>
        </button>

        <!-- Notifications Animated Popover -->
        <transition name="pop">
          <div v-if="showNotifs" class="absolute right-0 mt-3 w-72 sm:w-80 max-w-[92vw] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-700/80 overflow-hidden z-50 p-4 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
              <span class="text-xs font-black text-slate-900 dark:text-slate-50 uppercase tracking-wider">{{ t('notifications') }} (3)</span>
              <button @click="showNotifs = false" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 text-xs font-bold">{{ t('close') }}</button>
            </div>
            <div class="space-y-2 text-xs">
              <div class="p-2.5 bg-amber-50/80 dark:bg-amber-900/40 border border-amber-200/70 dark:border-amber-700/50 rounded-xl flex items-start gap-2.5">
                <span class="text-base">💳</span>
                <div>
                  <div class="font-bold text-amber-900 dark:text-amber-400">3 Loan Applications</div>
                  <div class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5">Requires your quick approval.</div>
                </div>
              </div>
              <div class="p-2.5 bg-emerald-50/80 dark:bg-emerald-900/40 border border-emerald-200/70 dark:border-emerald-700/50 rounded-xl flex items-start gap-2.5">
                <span class="text-base">📦</span>
                <div>
                  <div class="font-bold text-emerald-900 dark:text-emerald-400">New Paddy Consignment</div>
                  <div class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">45.2 Tons received in Bin 01.</div>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>

      <!-- Theme Switcher Button -->
      <button 
        @click="toggleDark"
        class="p-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:text-slate-50 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:bg-slate-700/80 rounded-xl transition-all duration-300 border border-slate-200/60 dark:border-slate-700/60 transform hover:scale-105 active:scale-95 cursor-pointer flex items-center gap-1.5"
        :title="isDark ? t('lightMode', 'Switch to Light Mode') : t('darkMode', 'Switch to Dark Mode')"
      >
        <svg v-if="!isDark" class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.591zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.061-1.06l-1.591-1.591a.75.75 0 10-1.06 1.061l1.59 1.59zM12 18.75a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.75-.75zM6.166 17.834a.75.75 0 00-1.061 1.06l1.591 1.591a.75.75 0 101.06-1.061l-1.59-1.591zM4.5 12a.75.75 0 01-.75.75H1.5a.75.75 0 010-1.5h2.25a.75.75 0 01.75.75zM6.166 6.166a.75.75 0 001.06-1.06L5.635 3.515a.75.75 0 00-1.06 1.061l1.591 1.59z"/></svg>
        <svg v-else class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z"/></svg>
      </button>

      <div class="w-px h-6 bg-slate-200/80 dark:bg-slate-700/80 mx-0.5"></div>

      <!-- User Profile Avatar Card -->
      <div class="relative">
        <button 
          @click="showProfile = !showProfile"
          class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 dark:bg-slate-800 transition-all duration-200 cursor-pointer"
        >
          <div class="w-8.5 h-8.5 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-xs flex items-center justify-center shadow-md shadow-emerald-600/20 ring-2 ring-emerald-500/20 transform hover:scale-105 transition overflow-hidden">
            <img v-if="user?.avatarUrl" :src="user.avatarUrl" class="w-full h-full object-cover" />
            <span v-else>{{ userInitials }}</span>
          </div>
          <div class="hidden lg:block text-left pr-1">
            <div class="text-xs font-extrabold text-slate-900 dark:text-slate-50 leading-tight">{{ user?.name || 'Boniface Gwakila' }}</div>
            <div class="text-[10px] font-bold text-emerald-600 dark:text-emerald-500">{{ user?.role || t('systemOwner') }}</div>
          </div>
        </button>

        <!-- User Profile Dropdown Menu -->
        <transition name="pop">
          <div v-if="showProfile" class="absolute right-0 mt-3 w-60 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-700/80 overflow-hidden z-50 p-2 space-y-1 text-xs font-bold text-slate-700 dark:text-slate-200">
            <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl mb-1 border border-slate-100 dark:border-slate-800 flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-xs flex items-center justify-center shadow-md overflow-hidden shrink-0">
                <img v-if="user?.avatarUrl" :src="user.avatarUrl" class="w-full h-full object-cover" />
                <span v-else>{{ userInitials }}</span>
              </div>
              <div class="min-w-0">
                <div class="font-extrabold text-slate-900 dark:text-slate-50 truncate">{{ user?.name || 'Boniface Gwakila' }}</div>
                <div class="text-[10.5px] text-slate-500 dark:text-slate-400 font-medium truncate">{{ user?.email || 'gwakilabonface@gmail.com' }}</div>
              </div>
            </div>
            <router-link to="/settings" @click="showProfile = false" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-slate-100 dark:bg-slate-800 transition">
              <span>⚙️ {{ t('profileSettings', 'Mipangilio ya Akaunti') }}</span>
            </router-link>
            <button @click="openLogoutModal" class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 transition cursor-pointer">
              <span>🚪 {{ t('logout', 'Kutoka Mfumoni') }}</span>
            </button>
          </div>
        </transition>
      </div>

    </div>
  </header>

  <!-- Custom Styled Logout Confirmation Modal -->
  <transition name="fade">
    <div v-if="showLogoutModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl p-6 max-w-sm w-full space-y-4 text-center transform transition-all">
        
        <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-100 flex items-center justify-center text-2xl mx-auto shadow-inner">
          🚪
        </div>

        <div class="space-y-1">
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-50">{{ t('logout') }}?</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
            Are you sure you want to log out of <strong>GARANOKI</strong>? Your active session will be securely closed.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-2.5 pt-2">
          <button 
            @click="showLogoutModal = false"
            class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl border border-slate-200/80 dark:border-slate-700/80 transition cursor-pointer"
          >
            {{ t('cancel') }}
          </button>
          <button 
            @click="executeLogout"
            class="py-2.5 px-4 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-red-900/30 border border-red-400/30 transition cursor-pointer"
          >
            {{ t('logout') }} →
          </button>
        </div>

      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useLayout } from '../composables/useLayout';
import { useAuth } from '../composables/useAuth';
import { useTenants } from '../composables/useTenants';
import { useLanguage } from '../composables/useLanguage';

const router = useRouter();
const { toggleSidebar } = useLayout();
const { user, isSuperAdmin, logout } = useAuth();
const { tenants, activeTenant, setActiveTenant } = useTenants();
const { currentLang, toggleLanguage, t } = useLanguage();

const searchQuery = ref('');
const showNotifs = ref(false);
const showProfile = ref(false);
const showTenantMenu = ref(false);
const isDark = ref(false);
const isFullscreen = ref(false);
const showLogoutModal = ref(false);

const switchTenant = (tenantId) => {
  setActiveTenant(tenantId);
  showTenantMenu.value = false;
  window.location.reload();
};

const userInitials = computed(() => {
  const name = user.value?.name || 'Boniface Gwakila';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
});

const toggleDark = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('garanoki_theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('garanoki_theme', 'light');
  }
};

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(err => {
      console.error(`Error attempting to enable fullscreen: ${err.message}`);
    });
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    }
  }
};

const handleFullscreenChange = () => {
  isFullscreen.value = !!document.fullscreenElement;
};

const openLogoutModal = () => {
  showProfile.value = false;
  showLogoutModal.value = true;
};

const executeLogout = () => {
  showLogoutModal.value = false;
  logout();
};

onMounted(() => {
  const savedTheme = localStorage.getItem('garanoki_theme');
  if (savedTheme === 'dark') {
    isDark.value = true;
    document.documentElement.classList.add('dark');
  } else {
    isDark.value = false;
    document.documentElement.classList.remove('dark');
  }

  document.addEventListener('fullscreenchange', handleFullscreenChange);
});

onUnmounted(() => {
  document.removeEventListener('fullscreenchange', handleFullscreenChange);
});
</script>

<style scoped>
.pop-enter-active, .pop-leave-active {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.pop-enter-from, .pop-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-8px);
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
