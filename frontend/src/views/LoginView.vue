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
        <source src="https://videos.pexels.com/video-files/3195394/3195394-hd_1280_720_25fps.mp4" type="video/mp4" />
        <source src="https://videos.pexels.com/video-files/856942/856942-hd_1280_720_30fps.mp4" type="video/mp4" />
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

    <!-- TOP HEADER BAR: BRAND LOGO, FARMER AD & LANGUAGE SWITCHER -->
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

      <div class="flex items-center gap-2.5">
        <!-- 🎥 TANGAZO LA WAKULIMA VIDEO BUTTON -->
        <button 
          @click="openAdModal"
          class="px-3.5 py-2 bg-slate-900/80 hover:bg-slate-800/90 backdrop-blur-md text-emerald-400 text-xs font-black rounded-2xl border border-emerald-500/30 hover:border-emerald-400/60 transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg cursor-pointer"
          title="Tazama Tangazo la Wakulima"
        >
          <span class="text-sm animate-pulse">🎥</span>
          <span class="tracking-wide hidden sm:inline">{{ currentLang === 'sw' ? 'Tangazo la Wakulima' : 'Farmer Spotlight' }}</span>
        </button>

        <!-- Language Toggle Pill -->
        <button 
          @click="toggleLanguage" 
          class="px-3.5 py-2 bg-slate-900/80 hover:bg-slate-800/90 backdrop-blur-md text-white text-xs font-black rounded-2xl border border-white/15 transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg cursor-pointer"
          :title="currentLang === 'sw' ? 'Switch to English' : 'Badili kwenda Kiswahili'"
        >
          <span class="text-sm leading-none">{{ currentLang === 'sw' ? '🇹🇿' : '🇬🇧' }}</span>
          <span class="tracking-wider">{{ currentLang.toUpperCase() }}</span>
        </button>
      </div>
    </header>

    <!-- MAIN LANDING FORM CONTAINER -->
    <div class="w-full max-w-xl mx-auto px-4 py-12 z-20 relative">
      
      <!-- ULTRA-MODERN GLASSMORPHISM FLOATING LOGIN CARD -->
      <div class="bg-slate-900/75 backdrop-blur-2xl border border-white/15 rounded-3xl shadow-[0_25px_70px_-15px_rgba(0,0,0,0.8)] overflow-hidden transition-all duration-300 hover:border-white/30">

        <div class="p-8 sm:p-10 space-y-6">
          
          <!-- Mode Tabs: Login vs Register -->
          <div class="grid grid-cols-2 gap-1.5 bg-slate-950/80 p-1.5 rounded-2xl border border-white/10 text-xs font-black">
            <button 
              type="button"
              @click="activeTab = 'login'; errorMessage = '';"
              class="py-2.5 px-3 rounded-xl transition-all duration-200 cursor-pointer flex items-center justify-center gap-1.5"
              :class="activeTab === 'login' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white'"
            >
              <span>🔑</span>
              <span>{{ currentLang === 'sw' ? 'Ingia Mfumoni' : 'Sign In' }}</span>
            </button>
            <button 
              type="button"
              @click="activeTab = 'register'; errorMessage = '';"
              class="py-2.5 px-3 rounded-xl transition-all duration-200 cursor-pointer flex items-center justify-center gap-1.5 relative"
              :class="activeTab === 'register' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white'"
            >
              <span>✨</span>
              <span>{{ currentLang === 'sw' ? 'Sajili Ghala (Siku 14 Bure)' : 'Register (14-Day Free)' }}</span>
            </button>
          </div>

          <!-- Title & Subtitle -->
          <div class="text-center space-y-1.5">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-black uppercase tracking-wider">
              <span>🌾 Agribusiness Multi-Tenant Platform</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
              {{ activeTab === 'login' ? (currentLang === 'sw' ? 'Karibu Mfumoni' : 'Welcome Back') : (currentLang === 'sw' ? 'Sajili Ghala Yako Mpya' : 'Register New Warehouse') }}
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 font-medium">
              {{ activeTab === 'login' ? (currentLang === 'sw' ? 'Ingiza taarifa zako kufungua mfumo wa ghala' : 'Sign in to access your mill & warehouse workspace') : (currentLang === 'sw' ? 'Anza majaribio ya siku 14 bure kabisa bila malipo ya awali' : 'Start your 14-day free trial now') }}
            </p>
          </div>

          <!-- Session Expiry Warning Alert -->
          <div v-if="sessionWarning && activeTab === 'login'" class="p-4 bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs sm:text-sm font-semibold rounded-2xl flex items-start gap-2.5 shadow-inner">
            <span class="text-base shrink-0">⚠️</span>
            <span class="leading-relaxed">{{ sessionWarning }}</span>
          </div>

          <!-- Success Alert -->
          <div v-if="successMessage" class="p-4 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs sm:text-sm font-semibold rounded-2xl flex items-start gap-2.5 shadow-inner">
            <span class="text-base shrink-0">✅</span>
            <span class="leading-relaxed">{{ successMessage }}</span>
          </div>

          <!-- Error Alert -->
          <div v-if="errorMessage" class="p-4 bg-red-500/15 border border-red-500/30 text-red-300 text-xs sm:text-sm font-semibold rounded-2xl flex items-start gap-2.5 shadow-inner animate-shake">
            <span class="text-base shrink-0">🚫</span>
            <span class="leading-relaxed">{{ errorMessage }}</span>
          </div>

          <!-- LOGIN FORM -->
          <form v-if="activeTab === 'login'" @submit.prevent="handleLoginSubmit" class="space-y-4" autocomplete="off">
            
            <!-- Email Field -->
            <div class="space-y-1.5">
              <label class="text-xs font-extrabold text-slate-200 ml-1 flex items-center justify-between">
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
                  class="w-full pl-11 pr-4 py-3 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
              </div>
            </div>

            <!-- Password Field -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between ml-1">
                <label class="text-xs font-extrabold text-slate-200">{{ currentLang === 'sw' ? 'Neno la Siri (Password)' : 'Password' }}</label>
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
                  class="w-full pl-11 pr-12 py-3 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
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
                class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-sm rounded-2xl shadow-xl shadow-emerald-500/25 transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2"
              >
                <svg v-if="loading" class="animate-spin h-5 w-5 text-slate-950" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ loading ? (currentLang === 'sw' ? 'Inahakiki...' : 'Authenticating...') : (currentLang === 'sw' ? 'Ingia Mfumoni →' : 'Sign In →') }}</span>
              </button>
            </div>

          </form>

          <!-- SELF-SERVICE TENANT REGISTRATION FORM -->
          <form v-else @submit.prevent="handleRegisterSubmit" class="space-y-3.5" autocomplete="off">
            
            <!-- Company Name -->
            <div class="space-y-1">
              <label class="text-xs font-extrabold text-slate-200 ml-1">Jina la Kampuni au Ghala *</label>
              <input 
                type="text" 
                v-model="regCompanyName"
                required
                placeholder="mf. Arusha Grain Millers Ltd"
                class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
              />
            </div>

            <!-- Owner Name & Phone Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-extrabold text-slate-200 ml-1">Mwenye Ghala *</label>
                <input 
                  type="text" 
                  v-model="regOwnerName"
                  required
                  placeholder="mf. Juma Hamisi"
                  class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
              </div>
              <div class="space-y-1">
                <label class="text-xs font-extrabold text-slate-200 ml-1">Nambari ya Simu *</label>
                <input 
                  type="text" 
                  v-model="regPhone"
                  required
                  placeholder="+255 7XX XXX XXX"
                  class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
              </div>
            </div>

            <!-- Email & Password Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-xs font-extrabold text-slate-200 ml-1">Barua Pepe (Email) *</label>
                <input 
                  type="email" 
                  v-model="regEmail"
                  required
                  placeholder="juma@arushagrain.co.tz"
                  class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
              </div>
              <div class="space-y-1">
                <label class="text-xs font-extrabold text-slate-200 ml-1">Neno la Siri (Password) *</label>
                <input 
                  type="password" 
                  v-model="regPassword"
                  required
                  placeholder="••••••••"
                  class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-xs sm:text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all shadow-inner"
                />
              </div>
            </div>

            <!-- Select Plan -->
            <div class="space-y-1">
              <label class="text-xs font-extrabold text-slate-200 ml-1">Chagua Kifurushi *</label>
              <select 
                v-model="regPlan" 
                class="w-full px-4 py-2.5 bg-slate-950 border border-slate-700/80 focus:border-emerald-500 rounded-2xl text-xs sm:text-sm font-semibold text-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all"
              >
                <option value="trial">🎁 14-Day Free Trial (Bure Siku 14)</option>
                <option value="starter">🌱 Starter Plan - TSH 50,000 / Mwezi</option>
                <option value="business">🚀 Business Plan - TSH 150,000 / Mwezi</option>
                <option value="enterprise">👑 Enterprise Plan - TSH 500,000 / Mwezi</option>
              </select>
            </div>

            <!-- Submit Registration -->
            <div class="pt-2">
              <button 
                type="submit" 
                :disabled="loading"
                class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-sm rounded-2xl shadow-xl shadow-emerald-500/25 transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2"
              >
                <svg v-if="loading" class="animate-spin h-5 w-5 text-slate-950" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ loading ? 'Inasajili Ghala Yako...' : 'Tengeneza Akaunti na Anza Sasa →' }}</span>
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

    <!-- 🎥 TANGAZO LA WAKULIMA VIDEO AD MODAL -->
    <div v-if="showAdModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-xl transition-all">
      <div class="relative w-full max-w-3xl bg-slate-900 border border-white/20 rounded-3xl shadow-2xl overflow-hidden space-y-0">
        
        <!-- Modal Header -->
        <div class="p-4 sm:p-5 bg-slate-950/90 border-b border-white/10 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <span class="text-xl">🌾</span>
            <div>
              <h3 class="text-sm sm:text-base font-black text-white">
                {{ currentLang === 'sw' ? 'Tangazo la Wakulima & Mavuno Shambani' : 'Farmer & Harvest Commercial' }}
              </h3>
              <p class="text-[11px] text-emerald-400 font-medium">GARANOKI Store & Finance MS</p>
            </div>
          </div>

          <button 
            @click="closeAdModal" 
            class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center font-bold text-base transition-colors cursor-pointer"
          >
            ✕
          </button>
        </div>

        <!-- Video Player Body -->
        <div class="relative aspect-video bg-black flex items-center justify-center">
          <video 
            controls
            autoplay
            class="w-full h-full object-cover"
            src="https://videos.pexels.com/video-files/3195394/3195394-hd_1280_720_25fps.mp4"
          ></video>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-slate-950/90 border-t border-white/10 flex items-center justify-between text-xs text-slate-300">
          <span class="font-semibold text-slate-400">🧑‍🌾 Wakulima Wakagua Mazao Shambani</span>
          <button @click="closeAdModal" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-xl cursor-pointer shadow-md">
            {{ currentLang === 'sw' ? 'Funga Tangazo' : 'Close Ad' }}
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import { useTenants } from '../composables/useTenants';
import { useLanguage } from '../composables/useLanguage';

const router = useRouter();
const { login } = useAuth();
const { registerTenant } = useTenants();
const { currentLang, toggleLanguage } = useLanguage();

const activeTab = ref('login');
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const rememberMe = ref(false);
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const sessionWarning = ref('');
const showVideo = ref(true);
const showAdModal = ref(false);

// Tenant Registration Form Fields
const regCompanyName = ref('');
const regOwnerName = ref('');
const regEmail = ref('');
const regPhone = ref('');
const regPassword = ref('');
const regPlan = ref('trial');

const openAdModal = () => {
  showAdModal.value = true;
};

const closeAdModal = () => {
  showAdModal.value = false;
};

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
  successMessage.value = '';
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

const handleRegisterSubmit = () => {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  setTimeout(() => {
    loading.value = false;

    const regRes = registerTenant({
      companyName: regCompanyName.value,
      ownerName: regOwnerName.value,
      ownerEmail: regEmail.value,
      phone: regPhone.value,
      password: regPassword.value,
      plan: regPlan.value
    });

    if (!regRes.success) {
      errorMessage.value = regRes.message;
      return;
    }

    successMessage.value = currentLang.value === 'sw'
      ? 'Hongera! Akaunti ya ghala yako imetengenezwa vyema. Inaingia Mfumoni...'
      : 'Congratulations! Your warehouse account is created. Signing in...';

    setTimeout(() => {
      login(regEmail.value, regPassword.value, true);
      window.location.replace('/');
    }, 1000);
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

