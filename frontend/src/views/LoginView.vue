<template>
  <div class="min-h-screen w-full flex bg-slate-950 text-slate-100 font-sans relative overflow-hidden select-none">
    
    <!-- Dynamic Glowing Background Effects -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/3 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-center p-4 sm:p-6 lg:p-10 z-10 my-auto">
      
      <!-- Left Hero & Branding Section -->
      <div class="w-full lg:w-1/2 p-6 sm:p-10 space-y-8 text-left">
        <!-- Brand Header -->
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-500 to-emerald-400 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-emerald-600/30 ring-2 ring-white/20">
            ⭐
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-none bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">
              GARANOKI
            </h1>
            <p class="text-xs text-emerald-400 font-extrabold uppercase tracking-widest mt-1">
              Store & Financial Management System
            </p>
          </div>
        </div>

        <!-- Headline -->
        <div class="space-y-3">
          <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 inline-flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Garanoki Enterprise Portal
          </span>
          <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight">
            Executive Control & Financial Governance
          </h2>
          <p class="text-sm text-slate-400 leading-relaxed font-medium">
            Official Garanoki system for managing grain intake, milling & drying operations, farmer credit lines, and real-time financial ledgers.
          </p>
        </div>

        <!-- System Value Propositions -->
        <div class="space-y-3 pt-2">
          <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md">
            <div class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-sm shrink-0">🌾</div>
            <div>
              <h4 class="text-xs font-bold text-white">Inventory & Warehouse Control</h4>
              <p class="text-[11.5px] text-slate-400">Comprehensive batch tracking for Paddy, Maize, and Legumes across all bins.</p>
            </div>
          </div>

          <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md">
            <div class="p-2 rounded-lg bg-teal-500/20 text-teal-400 font-bold text-sm shrink-0">💰</div>
            <div>
              <h4 class="text-xs font-bold text-white">Executive Financial Ledger</h4>
              <p class="text-[11.5px] text-slate-400">Real-time service revenue streams, loan recovery tracking, and profit margins.</p>
            </div>
          </div>
        </div>

        <!-- Footer status -->
        <div class="flex items-center gap-2 text-xs text-slate-500 font-mono">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span>Security Protocol: Enforced (JWT Session)</span>
        </div>
      </div>

      <!-- Right Login Card Form Section -->
      <div class="w-full lg:w-1/2 max-w-md p-6 sm:p-8 bg-slate-900/80 backdrop-blur-xl border border-slate-800/90 rounded-3xl shadow-2xl space-y-6">
        
        <!-- Card Title Header -->
        <div class="space-y-1 text-center sm:text-left border-b border-slate-800 pb-4">
          <h3 class="text-xl font-black text-white tracking-tight">System Owner Sign In</h3>
          <p class="text-xs text-slate-400 font-medium">Enter your credentials to unlock GARANOKI</p>
        </div>

        <!-- Error Alert (If any) -->
        <div v-if="errorMessage" class="p-3 bg-red-950/90 border border-red-700/80 text-red-200 text-xs rounded-xl flex items-center gap-2.5 shadow-lg animate-shake">
          <span class="text-base shrink-0">🚫</span>
          <span class="font-medium leading-tight">{{ errorMessage }}</span>
        </div>

        <!-- Form Fields -->
        <form @submit.prevent="handleLoginSubmit" class="space-y-4">
          
          <!-- Email Address Field -->
          <div class="space-y-1.5 text-left">
            <label class="text-xs font-bold text-slate-300 flex items-center justify-between">
              <span>Owner Email Address</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <input 
                type="email" 
                v-model="email"
                required
                placeholder="gwakilabonface@gmail.com"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs font-medium text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 transition shadow-inner"
              />
            </div>
          </div>

          <!-- Password Field -->
          <div class="space-y-1.5 text-left">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold text-slate-300">Password</label>
              <a href="#" @click.prevent="showForgotNotice" class="text-[11px] font-semibold text-emerald-400 hover:underline">Forgot Password?</a>
            </div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              </div>
              <input 
                :type="showPassword ? 'text' : 'password'" 
                v-model="password"
                required
                placeholder="••••••••"
                class="w-full pl-10 pr-10 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs font-medium text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 transition shadow-inner"
              />
              <button 
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300"
              >
                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.03 10.03 0 013.682-.863c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-6.33-6.33a3 3 0 104.243 4.243"/></svg>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="loading"
            class="w-full py-3 px-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-900/50 border border-emerald-400/30 transition transform active:scale-98 flex items-center justify-center gap-2 cursor-pointer mt-4"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ loading ? 'Authenticating Credentials...' : 'Sign In as Owner →' }}</span>
          </button>
        </form>

        <!-- Pre-fill Credentials Button -->
        <div class="pt-3 border-t border-slate-800/90 text-center space-y-2">
          <button 
            @click="fillOwnerCredentials" 
            class="w-full py-2.5 bg-slate-800/90 hover:bg-slate-800 text-emerald-400 hover:text-emerald-300 font-extrabold text-xs rounded-xl border border-slate-700/80 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-md"
          >
            <span>👑 Pre-fill Owner Credentials</span>
          </button>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { login } = useAuth();

const email = ref('gwakilabonface@gmail.com');
const password = ref('12345678');
const showPassword = ref(false);
const loading = ref(false);
const errorMessage = ref('');

const handleLoginSubmit = () => {
  loading.value = true;
  errorMessage.value = '';

  setTimeout(() => {
    loading.value = false;

    // Strict credential check via useAuth composable
    const res = login(email.value, password.value);
    
    if (!res.success) {
      errorMessage.value = res.message;
      return;
    }

    // Success -> redirect cleanly to Dashboard
    window.location.replace('/');
  }, 400);
};

const fillOwnerCredentials = () => {
  email.value = 'gwakilabonface@gmail.com';
  password.value = '12345678';
  errorMessage.value = '';
};

const showForgotNotice = () => {
  alert('Default Owner password is: 12345678.');
};
</script>
