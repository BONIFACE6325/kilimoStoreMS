<template>
  <div class="min-h-screen w-full relative flex items-center justify-center overflow-hidden font-sans select-none bg-slate-950">
    
    <!-- 🌾 CINEMATIC FARM BACKGROUND WITH SLOW ANIMATED PAN (Ka Video ka Mkulima Shamba Effect) -->
    <div class="absolute inset-0 z-0 overflow-hidden">
      
      <!-- Video Element with High-Quality Agriculture Loop -->
      <video 
        autoplay 
        loop 
        muted 
        playsinline 
        poster="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=1920&q=80"
        class="absolute inset-0 w-full h-full object-cover scale-105 filter brightness-[0.7] contrast-[1.1] animate-slow-pan pointer-events-none"
      >
        <source src="https://assets.mixkit.co/videos/preview/mixkit-farmer-walking-through-a-field-of-wheat-42867-large.mp4" type="video/mp4" />
        <source src="https://cdn.pixabay.com/video/2019/04/20/22907-331560939_large.mp4" type="video/mp4" />
      </video>

      <!-- High Resolution Shamba Photo Fallback with Animated Zoom -->
      <div 
        class="absolute inset-0 bg-cover bg-center animate-kenburns transition-all duration-1000 z-0"
        style="background-image: url('https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=1920&q=80');"
      ></div>

      <!-- Modern Gradient Overlays for Sunlight Glow & Dark Contrast -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/65 to-slate-950/40 z-10"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/40 via-transparent to-slate-950/70 z-10"></div>
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-emerald-500/10 blur-[120px] rounded-full pointer-events-none z-10"></div>
    </div>

    <!-- Floating Ambient Atmospheric Dust & Sparkles -->
    <div class="absolute inset-0 z-10 pointer-events-none opacity-30">
      <div class="absolute top-1/4 left-1/5 w-2 h-2 rounded-full bg-emerald-400 animate-ping"></div>
      <div class="absolute top-2/3 left-3/4 w-1.5 h-1.5 rounded-full bg-amber-300 animate-pulse"></div>
      <div class="absolute top-1/3 left-2/3 w-2 h-2 rounded-full bg-teal-300 animate-ping" style="animation-delay: 1.5s;"></div>
    </div>

    <!-- TOP HEADER BAR: BRAND LOGO & LANGUAGE SWITCHER -->
    <header class="absolute top-0 left-0 right-0 z-30 p-5 sm:p-8 flex items-center justify-between max-w-7xl mx-auto w-full">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-500 via-teal-400 to-emerald-300 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-emerald-500/30 ring-2 ring-white/20">
          ⭐
        </div>
        <div>
          <span class="font-extrabold text-white text-lg tracking-tight leading-none">GARANOKI</span>
          <span class="block text-[10px] text-emerald-400 font-extrabold uppercase tracking-widest">Store & Finance MS</span>
        </div>
      </div>

      <!-- Language Toggle Pill -->
      <button 
        @click="toggleLanguage" 
        class="px-3.5 py-2 bg-slate-900/80 hover:bg-slate-800/90 backdrop-blur-md text-white text-xs font-black rounded-2xl border border-white/15 transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg cursor-pointer"
        :title="currentLang === 'sw' ? 'Switch to English' : 'Badili kwenda Kiswahili'"
      >
        <span class="text-sm leading-none">{{ currentLang === 'sw' ? '🇹🇿' : '🇬🇧' }}</span>
        <span class="tracking-wider">{{ currentLang.toUpperCase() }}</span>
      </button>
    </header>

    <!-- MAIN LANDING FORM CONTAINER -->
    <div class="w-full max-w-md mx-auto px-4 py-12 z-20 relative">
      
      <!-- ULTRA-MODERN GLASSMORPHISM FLOATING LOGIN CARD -->
      <div class="bg-slate-900/75 backdrop-blur-2xl border border-white/15 rounded-3xl shadow-[0_25px_70px_-15px_rgba(0,0,0,0.8)] overflow-hidden transition-all duration-300 hover:border-emerald-500/40">
        
        <!-- Card Header Accent Glow -->
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-300"></div>

        <div class="p-6 sm:p-8 space-y-6">
          
          <!-- Title & Subtitle -->
          <div class="text-center space-y-1.5">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-black uppercase tracking-wider mb-1">
              <span>🌾 Agribusiness Portal</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
              {{ currentLang === 'sw' ? 'Karibu Mfumoni' : 'Welcome Back' }}
            </h2>
            <p class="text-xs text-slate-300 font-medium">
              {{ currentLang === 'sw' ? 'Ingiza taarifa zako kufungua mfumo wa ghala' : 'Sign in to access your mill & warehouse workspace' }}
            </p>
          </div>

          <!-- Session Expiry Warning Alert -->
          <div v-if="sessionWarning" class="p-3.5 bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-semibold rounded-2xl flex items-start gap-2.5 shadow-inner">
            <span class="text-base shrink-0">⚠️</span>
            <span class="leading-relaxed">{{ sessionWarning }}</span>
          </div>

          <!-- Error Alert -->
          <div v-if="errorMessage" class="p-3.5 bg-red-500/15 border border-red-500/30 text-red-300 text-xs font-semibold rounded-2xl flex items-start gap-2.5 shadow-inner animate-shake">
            <span class="text-base shrink-0">🚫</span>
            <span class="leading-relaxed">{{ errorMessage }}</span>
          </div>

          <!-- LOGIN FORM -->
          <form @submit.prevent="handleLoginSubmit" class="space-y-4" autocomplete="off">
            
            <!-- Email Field -->
            <div class="space-y-1.5">
              <label class="text-xs font-extrabold text-slate-200 ml-1 flex items-center justify-between">
                <span>{{ currentLang === 'sw' ? 'Barua Pepe (Email)' : 'Email Address' }}</span>
              </label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-400 transition-colors">
                  <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input 
                  type="email" 
                  v-model="email"
                  required
                  placeholder="gwakilabonface@gmail.com"
                  class="w-full pl-10 pr-4 py-3 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
              </div>
            </div>

            <!-- Password Field -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between ml-1">
                <label class="text-xs font-extrabold text-slate-200">{{ currentLang === 'sw' ? 'Neno la Siri (Password)' : 'Password' }}</label>
                <a href="#" @click.prevent="showForgotNotice" class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300 transition-colors">
                  {{ currentLang === 'sw' ? 'Umesahau?' : 'Forgot?' }}
                </a>
              </div>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-400 transition-colors">
                  <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  v-model="password"
                  required
                  placeholder="••••••••"
                  class="w-full pl-10 pr-11 py-3 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
                <button 
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition-colors cursor-pointer"
                  title="Onyesha/Ficha Neno la Siri"
                >
                  <span class="text-sm">{{ showPassword ? '👁️‍🗨️' : '👁️' }}</span>
                </button>
              </div>
            </div>

            <!-- Remember Me Checkbox -->
            <div class="flex items-center gap-2.5 ml-1 pt-1">
              <input 
                type="checkbox" 
                id="rememberMe" 
                v-model="rememberMe"
                class="w-4 h-4 rounded-md border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500/50 cursor-pointer"
              />
              <label for="rememberMe" class="text-xs font-bold text-slate-300 cursor-pointer select-none">
                {{ currentLang === 'sw' ? 'Kumbuka Session Hii (Remember Me)' : 'Remember me on this browser' }}
              </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
              <button 
                type="submit" 
                :disabled="loading"
                class="w-full py-3.5 px-5 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-sm rounded-2xl shadow-xl shadow-emerald-500/25 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
              >
                <svg v-if="loading" class="animate-spin h-5 w-5 text-slate-950" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ loading ? (currentLang === 'sw' ? 'Inahakiki...' : 'Authenticating...') : (currentLang === 'sw' ? 'Ingia Mfumoni →' : 'Sign In →') }}</span>
              </button>
            </div>

          </form>

        </div>

        <!-- Security & System Governance Footer -->
        <div class="bg-slate-950/80 px-6 py-4 border-t border-white/10 flex items-center justify-between text-[11px] font-bold text-slate-400">
          <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>GARANOKI ERP v2.4</span>
          </div>
          <span class="text-emerald-400/80 font-mono">🔒 SSL 256-bit Encrypted</span>
        </div>

      </div>

    </div>

    <!-- FOOTER COPYRIGHT & TRUST BADGES -->
    <footer class="absolute bottom-4 left-0 right-0 z-30 text-center text-xs font-semibold text-slate-400/80">
      <div class="flex flex-wrap items-center justify-center gap-4 text-[11px]">
        <span>© 2026 GARANOKI Store & Finance MS</span>
        <span>•</span>
        <span>All Rights Reserved</span>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import { useLanguage } from '../composables/useLanguage';

const router = useRouter();
const { login } = useAuth();
const { currentLang, toggleLanguage } = useLanguage();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const rememberMe = ref(false);
const loading = ref(false);
const errorMessage = ref('');
const sessionWarning = ref('');

onMounted(() => {
  if (sessionStorage.getItem('garanoki_logout_reason') === 'inactivity') {
    sessionWarning.value = currentLang.value === 'sw' 
      ? '⚠️ Session yako ime-expire kutokana na kutokutumia mfumo kwa dakika 15. Tafadhali ingia tena.'
      : '⚠️ Your session expired due to 15 minutes of inactivity. Please log in again.';
    sessionStorage.removeItem('garanoki_logout_reason');
  }
});

const handleLoginSubmit = () => {
  loading.value = true;
  errorMessage.value = '';
  sessionWarning.value = '';

  setTimeout(() => {
    loading.value = false;

    const res = login(email.value, password.value, rememberMe.value);
    
    if (!res.success) {
      errorMessage.value = res.message;
      return;
    }

    window.location.replace('/');
  }, 600);
};

const showForgotNotice = () => {
  const msg = currentLang.value === 'sw'
    ? 'Tafadhali mawasiliana na Mwenye Mfumo (System Owner) au Tumia neno la siri uliloweka kwenye Mipangilio.'
    : 'Please contact the System Owner or use the password configured in Settings.';
  alert(msg);
};
</script>

<style scoped>
@keyframes kenburns {
  0% {
    transform: scale(1) translate(0, 0);
  }
  50% {
    transform: scale(1.08) translate(-1%, -1%);
  }
  100% {
    transform: scale(1) translate(0, 0);
  }
}

.animate-kenburns {
  animation: kenburns 25s ease-in-out infinite alternate;
}

@keyframes slowPan {
  0% {
    transform: scale(1.05) translateY(0);
  }
  50% {
    transform: scale(1.1) translateY(-10px);
  }
  100% {
    transform: scale(1.05) translateY(0);
  }
}

.animate-slow-pan {
  animation: slowPan 30s ease-in-out infinite alternate;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

.animate-shake {
  animation: shake 0.3s ease-in-out;
}
</style>
