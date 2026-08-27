<template>
  <header class="h-18 bg-white/90 backdrop-blur-xl border-b border-slate-200/80 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs transition-all duration-300">
    
    <!-- Left Area: Mobile Brand & Search Bar -->
    <div class="flex items-center gap-3 sm:gap-4">
      
      <!-- Mobile Sidebar Toggle Button -->
      <button 
        @click="toggleSidebar" 
        class="p-2.5 text-slate-600 hover:text-slate-900 bg-slate-100/90 hover:bg-slate-200/90 rounded-xl transition border border-slate-200/80 shadow-2xs md:hidden"
        title="Fungua Menu"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <!-- Mobile App Brand Name (Only visible on small mobile screens) -->
      <div class="flex items-center gap-2 md:hidden">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black flex items-center justify-center text-sm shadow-md">
          ⭐
        </div>
        <span class="font-extrabold text-slate-900 text-base tracking-tight">AgroVault</span>
      </div>

      <!-- Compact Search Bar (Max Width 280px for a clean look) -->
      <div class="relative hidden sm:block w-64 md:w-72 group">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-600 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input 
          type="text" 
          v-model="searchQuery"
          placeholder="Tafuta wakulima, ankara..." 
          class="w-full pl-9 pr-10 py-2 bg-slate-100/80 hover:bg-slate-100 focus:bg-white border border-slate-200/80 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-200 shadow-2xs"
        />
        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
          <kbd class="hidden md:inline-block px-1.5 py-0.5 text-[9.5px] font-extrabold text-slate-400 bg-white border border-slate-200 rounded-md shadow-2xs">⌘K</kbd>
        </div>
      </div>

    </div>

    <!-- Right Controls Area -->
    <div class="flex items-center gap-2 sm:gap-3">
      
      <!-- Language Switcher Pill -->
      <button 
        @click="toggleLang" 
        class="px-2.5 sm:px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-black rounded-xl border border-slate-200/80 transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center gap-1.5 shadow-2xs"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        <span>{{ lang.toUpperCase() }}</span>
      </button>

      <!-- Notifications Bell -->
      <div class="relative">
        <button 
          @click="showNotifs = !showNotifs"
          class="relative p-2.5 text-slate-600 hover:text-slate-900 bg-slate-100/80 hover:bg-slate-200/80 rounded-xl transition-all duration-200 border border-slate-200/60 transform hover:scale-105 active:scale-95"
          title="Arifa"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          <span class="w-4 h-4 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 text-white font-black text-[9.5px] flex items-center justify-center absolute -top-1 -right-1 border-2 border-white shadow-sm animate-pulse">3</span>
        </button>

        <!-- Notifications Animated Popover -->
        <transition name="pop">
          <div v-if="showNotifs" class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden z-50 p-4 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
              <span class="text-xs font-black text-slate-900 uppercase tracking-wider">Arifa za Mfumo (3)</span>
              <button @click="showNotifs = false" class="text-slate-400 hover:text-slate-600 text-xs font-bold">Funga</button>
            </div>
            <div class="space-y-2 text-xs">
              <div class="p-2.5 bg-amber-50/80 border border-amber-200/70 rounded-xl flex items-start gap-2.5">
                <span class="text-base">💳</span>
                <div>
                  <div class="font-bold text-amber-900">Maombi 3 ya Mikopo</div>
                  <div class="text-[11px] text-amber-700 mt-0.5">Yanahitaji uidhinishaji wako wa haraka.</div>
                </div>
              </div>
              <div class="p-2.5 bg-emerald-50/80 border border-emerald-200/70 rounded-xl flex items-start gap-2.5">
                <span class="text-base">📦</span>
                <div>
                  <div class="font-bold text-emerald-900">Shehena Mpya ya Mpunga</div>
                  <div class="text-[11px] text-emerald-700 mt-0.5">Tani 45.2 imepokelewa kwenye Bin 01.</div>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>

      <!-- Theme Switcher -->
      <button 
        @click="isDark = !isDark"
        class="p-2.5 text-slate-600 hover:text-slate-900 bg-slate-100/80 hover:bg-slate-200/80 rounded-xl transition-all duration-300 border border-slate-200/60 transform hover:rotate-45 active:scale-95"
        title="Badili Mandhari"
      >
        <svg v-if="!isDark" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg v-else class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      </button>

      <div class="w-px h-6 bg-slate-200/80 mx-0.5"></div>

      <!-- User Profile Avatar Card -->
      <div class="relative">
        <button 
          @click="showProfile = !showProfile"
          class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 transition-all duration-200"
        >
          <div class="w-8.5 h-8.5 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-xs flex items-center justify-center shadow-md shadow-emerald-600/20 ring-2 ring-emerald-500/20 transform hover:scale-105 transition">
            JM
          </div>
          <div class="hidden lg:block text-left pr-1">
            <div class="text-xs font-extrabold text-slate-900 leading-tight">James Makori</div>
            <div class="text-[10px] font-bold text-emerald-600">Manager</div>
          </div>
        </button>

        <!-- User Profile Dropdown Menu -->
        <transition name="pop">
          <div v-if="showProfile" class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden z-50 p-2 space-y-1 text-xs font-bold text-slate-700">
            <div class="p-3 bg-slate-50 rounded-xl mb-1 border border-slate-100">
              <div class="font-extrabold text-slate-900">James Makori</div>
              <div class="text-[10.5px] text-slate-500 font-medium">james.makori@agrovault.co.tz</div>
            </div>
            <router-link to="/settings" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-slate-100 transition">
              <span>⚙️ Mipangilio ya Wasifu</span>
            </router-link>
            <button @click="handleLogout" class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-red-50 text-red-600 transition">
              <span>🚪 Ondoka kwenye Mfumo</span>
            </button>
          </div>
        </transition>
      </div>

    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { useLayout } from '../composables/useLayout';

const { toggleSidebar } = useLayout();

const searchQuery = ref('');
const lang = ref(localStorage.getItem('lang') || 'sw');
const showNotifs = ref(false);
const showProfile = ref(false);
const isDark = ref(false);

const toggleLang = () => {
  lang.value = lang.value === 'sw' ? 'en' : 'sw';
  localStorage.setItem('lang', lang.value);
};

const handleLogout = () => {
  localStorage.removeItem('token');
  window.location.reload();
};
</script>

<style scoped>
.pop-enter-active, .pop-leave-active {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.pop-enter-from, .pop-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-8px);
}
</style>
