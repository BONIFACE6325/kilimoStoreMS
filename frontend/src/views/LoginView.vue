<template>
  <div class="min-h-screen w-full flex items-center justify-center bg-[#050B14] relative overflow-hidden font-sans">
    
    <!-- Abstract Premium Background -->
    <div class="absolute inset-0 z-0 opacity-40">
      <div class="absolute -top-[30%] -left-[10%] w-[70%] h-[70%] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-blue-900/40 via-[#050B14]/0 to-[#050B14]/0 blur-3xl pointer-events-none"></div>
      <div class="absolute top-[60%] left-[60%] w-[60%] h-[60%] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-emerald-900/30 via-[#050B14]/0 to-[#050B14]/0 blur-3xl pointer-events-none"></div>
    </div>
    
    <!-- Grid overlay -->
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjAuMDUiIGZpbGw9Im5vbmUiIG9wYWNpdHk9IjAuMTUiPjxwYXRoIGQ9Ik0wIDQwaDQwTTAgMGgwIiAvPjxwYXRoIGQ9Ik00MCAwaDB2NDAiIC8+PC9nPjwvc3ZnPg==')] opacity-30 z-0"></div>

    <div class="w-full max-w-5xl mx-auto flex flex-col lg:flex-row items-center justify-center p-6 z-10 relative">
      
      <!-- Left Hero & Branding Section -->
      <div class="w-full lg:w-1/2 p-6 lg:p-12 space-y-10 text-left">
        <!-- Brand Header -->
        <div class="flex items-center gap-4 animate-fade-in-up">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-blue-600 flex items-center justify-center text-white font-black text-3xl shadow-[0_0_30px_rgba(16,185,129,0.3)] border border-white/10">
            G
          </div>
          <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">
              GARANOKI
            </h1>
            <p class="text-[11px] text-emerald-400 font-bold uppercase tracking-[0.2em] mt-1">
              Enterprise Systems
            </p>
          </div>
        </div>

        <!-- Headline -->
        <div class="space-y-4 animate-fade-in-up" style="animation-delay: 100ms;">
          <h2 class="text-4xl lg:text-5xl font-semibold text-white leading-[1.15] tracking-tight">
            Next-Generation <br/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-blue-500">Agribusiness Control</span>
          </h2>
          <p class="text-base text-slate-400 leading-relaxed max-w-md font-light">
            Secure, real-time management of grain inventory, farmer portfolios, and financial settlements across your entire operation.
          </p>
        </div>

        <!-- Trust Badges -->
        <div class="pt-8 border-t border-slate-800/60 flex flex-wrap items-center gap-6 animate-fade-in-up" style="animation-delay: 200ms;">
          <div class="flex flex-col gap-1 text-xs">
            <span class="text-white font-semibold flex items-center gap-2" title="Mfumo unatumia encryption ya hali ya juu."><div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> End-to-End Encryption</span>
          </div>
          <div class="flex flex-col gap-1 text-xs">
            <span class="text-white font-semibold flex items-center gap-2" title="Hujifunga ukikaa dakika 15 bila kutumika."><div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div> 15m Auto-Logout</span>
          </div>
          <div class="flex flex-col gap-1 text-xs">
            <span class="text-white font-semibold flex items-center gap-2" title="Session inafutwa ukifunga browser."><div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div> Secure Sessions</span>
          </div>
        </div>
      </div>

      <!-- Right Login Card -->
      <div class="w-full lg:w-[420px] shrink-0 mt-10 lg:mt-0 animate-fade-in-up" style="animation-delay: 300ms;">
        <div class="bg-[#0A101C]/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.7)] overflow-hidden">
          
          <div class="p-8">
            <div class="space-y-2 mb-8 text-center sm:text-left">
              <h3 class="text-2xl font-semibold text-white tracking-tight">Welcome Back</h3>
              <p class="text-sm text-slate-400">Sign in to your owner workspace</p>
            </div>

            <!-- Session Expiry Warning -->
            <div v-if="sessionWarning" class="mb-6 p-3.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm rounded-xl flex items-start gap-3 transition-all">
              <span class="font-medium leading-tight">{{ sessionWarning }}</span>
            </div>

            <!-- Error Alert -->
            <div v-if="errorMessage" class="mb-6 p-3.5 bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-xl flex items-start gap-3 transition-all">
              <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <span class="font-medium">{{ errorMessage }}</span>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleLoginSubmit" class="space-y-5" autocomplete="off">
              <!-- Hack to disable browser autofill -->
              <input style="display:none" type="text" name="fakeusernameremembered"/>
              <input style="display:none" type="password" name="fakepasswordremembered"/>

              <!-- Email Field -->
              <div class="space-y-2">
                <label class="text-[13px] font-medium text-slate-300 ml-1">Email Address</label>
                <div class="relative group">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                  </div>
                  <input 
                    type="email" 
                    v-model="email"
                    required
                    autocomplete="new-password"
                    class="w-full pl-11 pr-4 py-3.5 bg-[#0F1623] border border-slate-700/60 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner"
                    placeholder="Enter your email"
                  />
                </div>
              </div>

              <!-- Password Field -->
              <div class="space-y-2">
                <div class="flex items-center justify-between ml-1">
                  <label class="text-[13px] font-medium text-slate-300">Password</label>
                  <a href="#" @click.prevent="showForgotNotice" class="text-[12px] font-medium text-slate-500 hover:text-emerald-400 transition-colors">Forgot?</a>
                </div>
                <div class="relative group">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                  </div>
                  <input 
                    :type="showPassword ? 'text' : 'password'" 
                    v-model="password"
                    required
                    autocomplete="new-password"
                    class="w-full pl-11 pr-11 py-3.5 bg-[#0F1623] border border-slate-700/60 rounded-xl text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50 transition-all shadow-inner"
                    placeholder="Enter your password"
                  />
                  <button 
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-white transition-colors"
                  >
                    <svg v-if="!showPassword" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.03 10.03 0 013.682-.863c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-6.33-6.33a3 3 0 104.243 4.243"/></svg>
                  </button>
                </div>
              </div>

              <!-- Remember Me Checkbox -->
              <div class="flex items-center gap-2 ml-1">
                <input 
                  type="checkbox" 
                  id="rememberMe" 
                  v-model="rememberMe"
                  class="w-4 h-4 rounded border-slate-700 bg-[#0F1623] text-emerald-500 focus:ring-emerald-500/50 focus:ring-offset-[#0A101C]"
                >
                <label for="rememberMe" class="text-[13px] font-medium text-slate-300 cursor-pointer select-none">
                  Kumbuka Session Hii (Remember Me)
                </label>
              </div>

              <!-- Submit Button -->
              <div class="pt-2">
                <button 
                  type="submit" 
                  :disabled="loading"
                  class="w-full py-3.5 px-4 bg-white hover:bg-slate-100 text-slate-900 font-semibold text-[14px] rounded-xl shadow-[0_0_20px_rgba(255,255,255,0.1)] transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                >
                  <svg v-if="loading" class="animate-spin h-4 w-4 text-slate-900" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ loading ? 'Authenticating...' : 'Sign In' }}</span>
                </button>
              </div>
            </form>
          </div>
          
          <div class="bg-[#050B14]/80 px-8 py-5 border-t border-slate-700/50 flex items-center justify-between">
            <span class="text-[11px] text-slate-500 font-medium">© 2026 Garanoki Ltd.</span>
            <span class="text-[11px] text-emerald-500/70 font-medium tracking-wide">SYSTEM V2.0</span>
          </div>

        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { login } = useAuth();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const rememberMe = ref(false);
const loading = ref(false);
const errorMessage = ref('');
const sessionWarning = ref('');

onMounted(() => {
  if (sessionStorage.getItem('garanoki_logout_reason') === 'inactivity') {
    sessionWarning.value = '⚠️ Session yako ime-expire kutokana na kutokutumia mfumo kwa dakika 15. Tafadhali ingia tena.';
    sessionStorage.removeItem('garanoki_logout_reason');
  }
});

const handleLoginSubmit = () => {
  loading.value = true;
  errorMessage.value = '';
  sessionWarning.value = '';

  setTimeout(() => {
    loading.value = false;

    // Strict credential check via useAuth composable
    const res = login(email.value, password.value, rememberMe.value);
    
    if (!res.success) {
      errorMessage.value = res.message;
      return;
    }

    // Success -> redirect cleanly to Dashboard
    window.location.replace('/');
  }, 600);
};

const showForgotNotice = () => {
  alert('Please contact the System Administrator to reset your password.');
};
</script>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
