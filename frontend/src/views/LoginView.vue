<template>
  <div class="min-h-screen w-full relative flex items-center justify-center overflow-hidden font-sans select-none bg-slate-950">
    
    <!-- 🌾 CINEMATIC FARM BACKGROUND SLIDESHOW WITH POWERPOINT TRANSITIONS -->
    <div class="absolute inset-0 z-0 overflow-hidden">
      
      <!-- Video Element with High-Quality Agriculture Loop (If supported) -->
      <video 
        v-if="showVideo"
        autoplay 
        loop 
        muted 
        playsinline 
        class="absolute inset-0 w-full h-full object-cover scale-105 filter brightness-[0.65] contrast-[1.1] animate-slow-pan pointer-events-none z-0"
        @error="showVideo = false"
      >
        <source src="https://cdn.pixabay.com/video/2019/04/20/22907-331560939_large.mp4" type="video/mp4" />
      </video>

      <!-- Multi-Image Slideshow with PowerPoint-Style Dynamic Animations -->
      <div 
        v-for="(img, idx) in bgImages" 
        :key="idx"
        class="absolute inset-0 bg-cover bg-center pointer-events-none"
        :class="[
          idx === currentBgIndex 
            ? `${pptTransitions[idx % pptTransitions.length]} z-0` 
            : 'opacity-0 -z-10'
        ]"
        :style="{ backgroundImage: `url('${img.url}')` }"
      ></div>

      <!-- Modern Gradient Overlays for Sunlight Glow & Dark Contrast -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-950/40 z-10"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/50 via-transparent to-slate-950/80 z-10"></div>
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-emerald-500/10 blur-[120px] rounded-full pointer-events-none z-10"></div>
    </div>

    <!-- 🔮 FLOATING GLOWING ATMOSPHERIC BUBBLES (PowerPoint Bubble Effect) -->
    <div class="absolute inset-0 z-10 pointer-events-none overflow-hidden">
      <div 
        v-for="n in 12" 
        :key="n" 
        class="absolute rounded-full bg-gradient-to-t from-emerald-400/30 to-teal-300/20 blur-xs animate-bubble-float shadow-[0_0_15px_rgba(52,211,153,0.4)]"
        :style="getBubbleStyle(n)"
      ></div>
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
    <div class="w-full max-w-xl mx-auto px-4 py-12 z-20 relative">
      
      <!-- ULTRA-MODERN GLASSMORPHISM FLOATING LOGIN CARD -->
      <div class="bg-slate-900/75 backdrop-blur-2xl border border-white/15 rounded-3xl shadow-[0_25px_70px_-15px_rgba(0,0,0,0.8)] overflow-hidden transition-all duration-300 hover:border-white/30">

        <div class="p-8 sm:p-10 space-y-7">
          
          <!-- Title & Subtitle -->
          <div class="text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-black uppercase tracking-wider mb-1">
              <span>🌾 Agribusiness Portal</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
              {{ currentLang === 'sw' ? 'Karibu Mfumoni' : 'Welcome Back' }}
            </h2>
            <p class="text-sm text-slate-300 font-medium">
              {{ currentLang === 'sw' ? 'Ingiza taarifa zako kufungua mfumo wa ghala' : 'Sign in to access your mill & warehouse workspace' }}
            </p>
          </div>

          <!-- Session Expiry Warning Alert -->
          <div v-if="sessionWarning" class="p-4 bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs sm:text-sm font-semibold rounded-2xl flex items-start gap-2.5 shadow-inner">
            <span class="text-base shrink-0">⚠️</span>
            <span class="leading-relaxed">{{ sessionWarning }}</span>
          </div>

          <!-- Error Alert -->
          <div v-if="errorMessage" class="p-4 bg-red-500/15 border border-red-500/30 text-red-300 text-xs sm:text-sm font-semibold rounded-2xl flex items-start gap-2.5 shadow-inner animate-shake">
            <span class="text-base shrink-0">🚫</span>
            <span class="leading-relaxed">{{ errorMessage }}</span>
          </div>

          <!-- LOGIN FORM -->
          <form @submit.prevent="handleLoginSubmit" class="space-y-5" autocomplete="off">
            
            <!-- Email Field -->
            <div class="space-y-2">
              <label class="text-xs sm:text-sm font-extrabold text-slate-200 ml-1 flex items-center justify-between">
                <span>{{ currentLang === 'sw' ? 'Barua Pepe (Email)' : 'Email Address' }}</span>
              </label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-400 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </div>
                <input 
                  type="email" 
                  v-model="email"
                  required
                  placeholder="gwakilabonface@gmail.com"
                  class="w-full pl-11 pr-4 py-3.5 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-sm sm:text-base font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
              </div>
            </div>

            <!-- Password Field -->
            <div class="space-y-2">
              <div class="flex items-center justify-between ml-1">
                <label class="text-xs sm:text-sm font-extrabold text-slate-200">{{ currentLang === 'sw' ? 'Neno la Siri (Password)' : 'Password' }}</label>
                <a href="#" @click.prevent="showForgotNotice" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 transition-colors">
                  {{ currentLang === 'sw' ? 'Umesahau?' : 'Forgot?' }}
                </a>
              </div>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-400 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  v-model="password"
                  required
                  placeholder="••••••••"
                  class="w-full pl-11 pr-12 py-3.5 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-sm sm:text-base font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
                <button 
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-white transition-colors cursor-pointer"
                  title="Onyesha/Ficha Neno la Siri"
                >
                  <span class="text-base">{{ showPassword ? '👁️‍🗨️' : '👁️' }}</span>
                </button>
              </div>
            </div>

            <!-- Remember Me Checkbox -->
            <div class="flex items-center gap-2.5 ml-1 pt-1">
              <input 
                type="checkbox" 
                id="rememberMe" 
                v-model="rememberMe"
                class="w-4.5 h-4.5 rounded-md border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500/50 cursor-pointer"
              />
              <label for="rememberMe" class="text-xs sm:text-sm font-bold text-slate-300 cursor-pointer select-none">
                {{ currentLang === 'sw' ? 'Kumbuka Session Hii (Remember Me)' : 'Remember me on this browser' }}
              </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
              <button 
                type="submit" 
                :disabled="loading"
                class="w-full py-4 px-6 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-base rounded-2xl shadow-xl shadow-emerald-500/25 transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2"
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
        <div class="bg-slate-950/80 px-8 py-4.5 border-t border-white/10 flex items-center justify-between text-xs font-bold text-slate-400">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>GARANOKI ERP v2.4</span>
          </div>
          <span class="text-emerald-400/80 font-mono">🔒 SSL 256-bit Encrypted</span>
        </div>

      </div>

    </div>

    <!-- ELEGANT DOT INDICATORS -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2.5 bg-slate-950/60 backdrop-blur-xl px-4 py-2 rounded-full border border-white/10 shadow-2xl">
      <button 
        v-for="(img, idx) in bgImages" 
        :key="idx"
        @click="setBgIndex(idx)"
        class="transition-all duration-500 cursor-pointer rounded-full"
        :class="[
          idx === currentBgIndex 
            ? 'w-7 h-2.5 bg-gradient-to-r from-emerald-400 to-teal-300 shadow-md shadow-emerald-500/50' 
            : 'w-2.5 h-2.5 bg-white/30 hover:bg-white/70 hover:scale-125'
        ]"
        :title="currentLang === 'sw' ? img.swTitle : img.enTitle"
      ></button>
    </div>

    <!-- FOOTER COPYRIGHT & TRUST BADGES -->
    <footer class="absolute bottom-3 left-0 right-0 z-20 text-center text-xs font-semibold text-slate-400/60 pointer-events-none">
      <div class="flex flex-wrap items-center justify-center gap-4 text-[11px]">
        <span>© 2026 GARANOKI Store & Finance MS</span>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
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
const showVideo = ref(true);

const pptTransitions = [
  'animate-ppt-bubble',
  'animate-ppt-zoom-blur',
  'animate-ppt-diamond',
  'animate-ppt-sweep',
  'animate-ppt-ripple'
];

const bgImages = ref([
  {
    swTitle: 'Mahindi',
    enTitle: 'Corn Field',
    url: 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=1920&q=80'
  },
  {
    swTitle: 'Ngano',
    enTitle: 'Wheat Field',
    url: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1920&q=80'
  },
  {
    swTitle: 'Maghala',
    enTitle: 'Grain Storage',
    url: 'https://images.unsplash.com/photo-1595841696677-6489ff3f8cd1?auto=format&fit=crop&w=1920&q=80'
  },
  {
    swTitle: 'Shamba Kijani',
    enTitle: 'Green Crops',
    url: 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&w=1920&q=80'
  },
  {
    swTitle: 'Mavuno',
    enTitle: 'Harvesting',
    url: 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?auto=format&fit=crop&w=1920&q=80'
  }
]);

const currentBgIndex = ref(0);
let bgInterval = null;

const getBubbleStyle = (n) => {
  const sizes = [14, 28, 20, 36, 18, 30, 44, 22, 16, 40, 26, 18];
  const lefts = [8, 22, 42, 58, 72, 85, 14, 34, 52, 68, 80, 94];
  const delays = [0, 1.5, 2.8, 0.6, 3.4, 2.1, 0.3, 2.5, 4.0, 1.2, 3.0, 0.1];
  const durations = [8, 11, 9, 13, 10, 12, 9.5, 10.5, 12.5, 8.5, 11.5, 9];
  
  return {
    width: `${sizes[n - 1]}px`,
    height: `${sizes[n - 1]}px`,
    left: `${lefts[n - 1]}%`,
    bottom: '-50px',
    animationDelay: `${delays[n - 1]}s`,
    animationDuration: `${durations[n - 1]}s`
  };
};

const setBgIndex = (idx) => {
  currentBgIndex.value = idx;
  resetBgTimer();
};

const resetBgTimer = () => {
  if (bgInterval) clearInterval(bgInterval);
  bgInterval = setInterval(() => {
    currentBgIndex.value = (currentBgIndex.value + 1) % bgImages.value.length;
  }, 6000);
};

onMounted(() => {
  resetBgTimer();

  if (sessionStorage.getItem('garanoki_logout_reason') === 'inactivity') {
    sessionWarning.value = currentLang.value === 'sw' 
      ? '⚠️ Session yako ime-expire kutokana na kutokutumia mfumo kwa dakika 15. Tafadhali ingia tena.'
      : '⚠️ Your session expired due to 15 minutes of inactivity. Please log in again.';
    sessionStorage.removeItem('garanoki_logout_reason');
  }
});

onUnmounted(() => {
  if (bgInterval) clearInterval(bgInterval);
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
/* 1. PowerPoint Radial Bubble Wipe */
@keyframes pptBubble {
  0% {
    clip-path: circle(0% at 50% 50%);
    transform: scale(1.18);
    filter: blur(10px) brightness(1.2);
    opacity: 0.2;
  }
  60% {
    filter: blur(2px) brightness(1.05);
  }
  100% {
    clip-path: circle(150% at 50% 50%);
    transform: scale(1.05);
    filter: blur(0px) brightness(1);
    opacity: 1;
  }
}
.animate-ppt-bubble {
  animation: pptBubble 1.4s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}

/* 2. PowerPoint Zoom & Blur Morph */
@keyframes pptZoomBlur {
  0% {
    opacity: 0;
    transform: scale(1.3);
    filter: blur(25px) contrast(1.3);
  }
  100% {
    opacity: 1;
    transform: scale(1.05);
    filter: blur(0px) contrast(1);
  }
}
.animate-ppt-zoom-blur {
  animation: pptZoomBlur 1.5s ease-out forwards;
}

/* 3. PowerPoint Diamond Split Wipe */
@keyframes pptDiamond {
  0% {
    clip-path: polygon(50% 50%, 50% 50%, 50% 50%, 50% 50%);
    transform: scale(1.15);
    opacity: 0.3;
  }
  100% {
    clip-path: polygon(50% -60%, 160% 50%, 50% 160%, -60% 50%);
    transform: scale(1.05);
    opacity: 1;
  }
}
.animate-ppt-diamond {
  animation: pptDiamond 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* 4. PowerPoint Diagonal Sweep Wipe */
@keyframes pptSweep {
  0% {
    clip-path: polygon(0 0, 0 0, 0 100%, 0 100%);
    transform: scale(1.12);
    filter: brightness(1.4);
    opacity: 0.3;
  }
  100% {
    clip-path: polygon(0 0, 120% 0, 100% 120%, 0 100%);
    transform: scale(1.05);
    filter: brightness(1);
    opacity: 1;
  }
}
.animate-ppt-sweep {
  animation: pptSweep 1.3s ease-out forwards;
}

/* 5. PowerPoint Corner Ripple Pop */
@keyframes pptRipple {
  0% {
    opacity: 0;
    clip-path: circle(0% at 85% 15%);
    transform: scale(1.2);
  }
  100% {
    opacity: 1;
    clip-path: circle(170% at 85% 15%);
    transform: scale(1.05);
  }
}
.animate-ppt-ripple {
  animation: pptRipple 1.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

/* Ambient Slow Pan for active slide after reveal */
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

/* Floating Ambient Bubbles Animation */
@keyframes bubbleFloat {
  0% {
    transform: translateY(0) scale(0.8);
    opacity: 0;
  }
  15% {
    opacity: 0.7;
  }
  85% {
    opacity: 0.5;
  }
  100% {
    transform: translateY(-110vh) scale(1.5);
    opacity: 0;
  }
}
.animate-bubble-float {
  animation: bubbleFloat linear infinite;
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

