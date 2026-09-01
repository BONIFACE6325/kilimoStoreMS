<template>
  <div>
    <!-- Mobile Backdrop Overlay -->
    <transition name="fade">
      <div 
        v-if="isMobileSidebarOpen" 
        @click="closeMobileSidebar" 
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-40 md:hidden transition-opacity"
      ></div>
    </transition>

    <!-- Sidebar Container -->
    <aside 
      class="bg-slate-950 text-slate-300 h-screen sticky top-0 flex flex-col border-r border-slate-800/80 shrink-0 select-none overflow-hidden z-50 transition-all duration-300 ease-in-out"
      :class="[
        // Desktop collapse logic: Icon-Only Rail mode (w-18 = 72px) vs Expanded (w-64 = 256px)
        isSidebarCollapsed ? 'md:w-18' : 'md:w-64',
        // Mobile drawer logic
        'fixed md:sticky top-0 left-0',
        isMobileSidebarOpen ? 'translate-x-0 w-64 shadow-2xl' : '-translate-x-full md:translate-x-0'
      ]"
    >
      
      <!-- Brand Logo Header -->
      <div class="h-18 flex items-center justify-between border-b border-slate-800/80 bg-slate-950 shrink-0" :class="isSidebarCollapsed ? 'px-2.5 justify-center' : 'px-4.5'">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 via-teal-500 to-emerald-400 flex items-center justify-center text-white font-black text-lg shadow-md shadow-emerald-500/20 ring-1 ring-white/20 shrink-0">
            ⭐
          </div>
          <div v-if="!isSidebarCollapsed">
            <div class="font-extrabold text-white text-base tracking-tight leading-none bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent whitespace-nowrap">
              GARANOKI
            </div>
            <div class="text-[10.5px] text-emerald-400 font-extrabold uppercase tracking-widest mt-1 whitespace-nowrap">
              Store & Finance MS
            </div>
          </div>
        </div>

        <!-- Mobile Close Button -->
        <button v-if="!isSidebarCollapsed" @click="closeMobileSidebar" class="md:hidden text-slate-400 hover:text-white p-1 rounded-lg">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Navigation Groups -->
      <nav class="flex-1 space-y-4 overflow-y-auto" :class="isSidebarCollapsed ? 'px-2 py-4' : 'px-3.5 py-4'">
        
        <!-- Group 1: MAIN -->
        <div class="space-y-1">
          <div v-if="!isSidebarCollapsed" class="px-3 text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5 whitespace-nowrap">Main</div>

          <!-- Dashboard -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path === '/' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? 'Dashboard' : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Dashboard</span>
            </div>
          </router-link>

          <!-- Farmers -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/farmers" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/farmers') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? `Farmers (${farmerCount})` : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Farmers</span>
            </div>
            <span v-if="!isSidebarCollapsed && farmerCount > 0" class="px-2 py-0.5 rounded-lg text-xs font-black bg-emerald-950 text-emerald-400 border border-emerald-800/60 font-mono">{{ farmerCount.toLocaleString() }}</span>
          </router-link>
        </div>

        <!-- Group 2: OPERATIONS -->
        <div class="space-y-1">
          <div v-if="!isSidebarCollapsed" class="px-3 text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5 whitespace-nowrap">Operations</div>

          <!-- Cashbook -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/receiving" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/receiving') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? `Cashbook (${receivingCount})` : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Cashbook</span>
            </div>
            <span v-if="!isSidebarCollapsed && receivingCount > 0" class="px-2 py-0.5 rounded-lg text-xs font-black bg-amber-950 text-amber-400 border border-amber-800/60 font-mono">{{ receivingCount }}</span>
          </router-link>

          <!-- Inventory -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/inventory" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/inventory') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? 'Inventory' : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Inventory</span>
            </div>
          </router-link>

          <!-- Services -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/services" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/services') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? 'Services' : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Services</span>
            </div>
          </router-link>

          <!-- Loans -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/loans" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/loans') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? `Loans (${loanCount})` : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Loans</span>
            </div>
            <span v-if="!isSidebarCollapsed && loanCount > 0" class="px-2 py-0.5 rounded-lg text-xs font-black bg-amber-950 text-amber-400 border border-amber-800/60 font-mono">{{ loanCount }}</span>
          </router-link>
        </div>

        <!-- Group 3: FINANCE -->
        <div class="space-y-1">
          <div v-if="!isSidebarCollapsed" class="px-3 text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5 whitespace-nowrap">Finance</div>

          <!-- Buyers -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/buyers" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/buyers') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? 'Buyers' : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Buyers</span>
            </div>
          </router-link>

          <!-- Sales -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/sales" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/sales') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? 'Sales' : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Sales</span>
            </div>
          </router-link>

          <!-- Income & Expenses -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/accounting" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/accounting') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? 'Income & Expenses' : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Income & Expenses</span>
            </div>
          </router-link>

          <!-- Reports -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/reports" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/reports') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? 'Reports' : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Reports</span>
            </div>
          </router-link>
        </div>

        <!-- Group 4: SYSTEM -->
        <div class="space-y-1">
          <div v-if="!isSidebarCollapsed" class="px-3 text-xs font-black text-slate-400 uppercase tracking-wider mb-1.5 whitespace-nowrap">System</div>

          <!-- Settings -->
          <router-link 
            @click="closeMobileSidebar" 
            to="/settings" 
            class="flex items-center rounded-xl text-xs sm:text-sm font-bold transition-all duration-150 relative group"
            :class="[
              $route.path.startsWith('/settings') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/40 ring-1 ring-emerald-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white',
              isSidebarCollapsed ? 'justify-center py-3 px-0' : 'justify-between px-3.5 py-2.5'
            ]"
            :title="isSidebarCollapsed ? 'Settings' : ''"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Settings</span>
            </div>
          </router-link>
        </div>

      </nav>

      <!-- Footer Operator Profile Card -->
      <div class="border-t border-slate-900 bg-slate-950 flex items-center shrink-0" :class="isSidebarCollapsed ? 'p-2 justify-center' : 'p-3.5 justify-between'">
        <div class="flex items-center gap-3">
          <div class="relative shrink-0">
            <div class="w-8.5 h-8.5 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-700 text-emerald-400 font-extrabold flex items-center justify-center border border-slate-700 text-xs">
              BG
            </div>
            <span class="w-2 h-2 rounded-full bg-emerald-500 border-2 border-slate-950 absolute -bottom-0.5 -right-0.5"></span>
          </div>
          <div v-if="!isSidebarCollapsed">
            <div class="font-bold text-white text-xs leading-tight whitespace-nowrap">Boniface Gwakila</div>
            <div class="text-[10px] text-emerald-400 font-semibold whitespace-nowrap">System Owner</div>
          </div>
        </div>
        <button 
          @click="openLogoutModal"
          class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-red-950/60 hover:bg-red-900/80 text-red-300 hover:text-white border border-red-800/60 transition text-xs font-bold shadow-xs cursor-pointer" 
          title="Logout of System"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          <span v-if="!isSidebarCollapsed">Logout</span>
        </button>
      </div>

    </aside>

    <!-- Custom Styled Logout Confirmation Modal -->
    <transition name="fade">
      <div v-if="showLogoutModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl p-6 max-w-sm w-full space-y-4 text-center transform transition-all">
          
          <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 border border-red-100 flex items-center justify-center text-2xl mx-auto shadow-inner">
            🚪
          </div>

          <div class="space-y-1">
            <h3 class="text-base font-extrabold text-slate-900">Logout of System?</h3>
            <p class="text-xs text-slate-500 font-medium leading-relaxed">
              Are you sure you want to log out of <strong>GARANOKI</strong>? Your active session will be securely closed.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-2.5 pt-2">
            <button 
              @click="showLogoutModal = false"
              class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs rounded-xl border border-slate-200/80 transition cursor-pointer"
            >
              Cancel
            </button>
            <button 
              @click="executeLogout"
              class="py-2.5 px-4 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-red-900/30 border border-red-400/30 transition cursor-pointer"
            >
              Yes, Logout →
            </button>
          </div>

        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useLayout } from '../composables/useLayout';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { isSidebarCollapsed, isMobileSidebarOpen, toggleSidebar, closeMobileSidebar } = useLayout();
const { logout } = useAuth();

const showLogoutModal = ref(false);

const openLogoutModal = () => {
  showLogoutModal.value = true;
};

const executeLogout = () => {
  showLogoutModal.value = false;
  logout();
};

const farmerCount = ref(0);
const receivingCount = ref(0);
const loanCount = ref(0);

const fetchCounts = async () => {
  try {
    const fRes = await fetch('/api/v1/farmers');
    if (fRes.ok) {
      const data = await fRes.json();
      farmerCount.value = Array.isArray(data) ? data.length : (data.data?.length || 0);
    }
    
    const bRes = await fetch('/api/v1/batches');
    if (bRes.ok) {
      const bData = await bRes.json();
      const rec = Array.isArray(bData) ? bData.filter(b => b.status === 'received') : [];
      receivingCount.value = rec.length;
    }

    const lRes = await fetch('/api/v1/loans');
    if (lRes.ok) {
      const lData = await lRes.json();
      const activeL = Array.isArray(lData) ? lData.filter(l => parseFloat(l.current_balance || l.remaining_balance || 0) > 0) : [];
      loanCount.value = activeL.length;
    }
  } catch (e) {
    console.error('Error fetching sidebar counts:', e);
  }
};

onMounted(() => {
  fetchCounts();
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
