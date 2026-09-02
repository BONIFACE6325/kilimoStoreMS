<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <span>Mwanzo</span>
          <span>/</span>
          <span class="text-emerald-700 dark:text-emerald-400 font-bold">Orodha ya Huduma</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-slate-50 tracking-tight flex items-center gap-2">
          <span>🛠️ Usimamizi wa Ada na Huduma za Kinu</span>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
          Sajili, hariri, na usimamie fomula za bei za huduma za Galanoki, aina za mazao na vipimo vya uzito.
        </p>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <button 
          @click="showCropModal = true"
          class="px-3.5 py-2 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl font-extrabold text-xs transition flex items-center gap-1.5"
        >
          <span>🌱 + Sajili Zao</span>
        </button>
        <button 
          @click="showUnitModal = true"
          class="px-3.5 py-2 bg-teal-50 dark:bg-teal-900/40 hover:bg-teal-100 text-teal-800 dark:text-teal-400 border border-teal-200 dark:border-teal-700/50 rounded-2xl font-extrabold text-xs transition flex items-center gap-1.5"
        >
          <span>⚖️ + Sajili Kipimo</span>
        </button>
        <button 
          @click="openAddModal"
          class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-2xl font-extrabold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          <span>+ Sajili Huduma Mpya</span>
        </button>
      </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-gradient-to-tr from-emerald-900 to-teal-900 text-white p-5 rounded-3xl shadow-md border border-emerald-800 flex items-center justify-between">
        <div>
          <div class="text-xs font-bold uppercase text-emerald-200 tracking-wider">Jumla ya Huduma</div>
          <div class="text-3xl font-black text-white mt-1">{{ services.length }}</div>
          <div class="text-[11px] text-emerald-300 mt-0.5">Huduma zilizosajiliwa kinu</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-white/15 dark:bg-slate-900/15 backdrop-blur-xs flex items-center justify-center text-xl">
          ⚙️
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex items-center justify-between">
        <div>
          <div class="text-xs font-bold uppercase text-slate-400 tracking-wider">Mazao Yaliyosajiliwa</div>
          <div class="text-3xl font-black text-emerald-700 dark:text-emerald-400 mt-1">{{ cropsList.length }}</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Crops Master List</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xl">
          🌱
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex items-center justify-between">
        <div>
          <div class="text-xs font-bold uppercase text-slate-400 tracking-wider">Vipimo Vya Bei</div>
          <div class="text-3xl font-black text-teal-700 dark:text-teal-400 mt-1">{{ unitsList.length }}</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Units Master List</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-900/40 text-teal-700 dark:text-teal-400 flex items-center justify-center text-xl">
          ⚖️
        </div>
      </div>

      <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex items-center justify-between">
        <div>
          <div class="text-xs font-bold uppercase text-slate-400 tracking-wider">Huduma za Kukoboa</div>
          <div class="text-3xl font-black text-amber-600 mt-1">{{ countByCategory('milling') }}</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Milling Services</div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl">
          🌾
        </div>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="relative w-full sm:w-80">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Tafuta huduma kwa jina au category..." 
          class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-semibold focus:bg-white dark:bg-slate-900 focus:border-emerald-500 transition"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <button 
          @click="selectedCategoryFilter = ''" 
          :class="!selectedCategoryFilter ? 'bg-emerald-800 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:bg-slate-700'"
          class="px-3.5 py-2 rounded-xl text-xs font-bold transition"
        >
          Zote
        </button>
        <button 
          @click="selectedCategoryFilter = 'milling'" 
          :class="selectedCategoryFilter === 'milling' ? 'bg-emerald-800 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:bg-slate-700'"
          class="px-3.5 py-2 rounded-xl text-xs font-bold transition"
        >
          Kukoboa
        </button>
        <button 
          @click="selectedCategoryFilter = 'drying'" 
          :class="selectedCategoryFilter === 'drying' ? 'bg-emerald-800 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:bg-slate-700'"
          class="px-3.5 py-2 rounded-xl text-xs font-bold transition"
        >
          Kukausha
        </button>
        <button 
          @click="selectedCategoryFilter = 'grading'" 
          :class="selectedCategoryFilter === 'grading' ? 'bg-emerald-800 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:bg-slate-700'"
          class="px-3.5 py-2 rounded-xl text-xs font-bold transition"
        >
          Grading
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-16">
      <div class="inline-block animate-spin w-8 h-8 border-4 border-emerald-600 border-t-transparent rounded-full mb-2"></div>
      <div class="text-xs text-slate-500 dark:text-slate-400 font-bold">Inapakia orodha ya huduma...</div>
    </div>

    <!-- Services Grid -->
    <div v-else-if="filteredServices.length === 0" class="bg-white dark:bg-slate-900 p-12 rounded-3xl border border-slate-200 dark:border-slate-700 text-center space-y-3">
      <div class="text-4xl">🛠️</div>
      <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Hakuna Huduma Zilizopatikana</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto font-medium">Jaribu kubadilisha neno la kutafuta au bofya kitufe cha "+ Sajili Huduma Mpya" kuongeza huduma mpya.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div 
        v-for="s in filteredServices" 
        :key="s.id" 
        class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs hover:border-emerald-300 dark:border-emerald-500/30 hover:shadow-md transition space-y-4 flex flex-col justify-between"
      >
        <div class="space-y-2.5">
          <div class="flex items-center justify-between flex-wrap gap-2">
            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 rounded-lg text-[10.5px] font-black uppercase tracking-wider">
              Kipimo: {{ s.unit || 'Kg' }}
            </span>
            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-lg text-[10.5px] font-bold">
              Zao: {{ s.crop_type || 'Zote (All)' }}
            </span>
          </div>

          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-50 leading-snug">{{ s.name_sw }}</h3>
            <p v-if="s.description" class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">{{ s.description }}</p>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div>
            <div class="text-[10px] uppercase font-bold text-slate-400">Bei Kwa Kipimo</div>
            <div class="text-lg font-black text-emerald-700 dark:text-emerald-400">Tsh {{ parseFloat(s.rate || 0).toLocaleString() }}</div>
          </div>

          <div class="flex items-center gap-2">
            <button 
              @click="openEditModal(s)" 
              class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 dark:bg-emerald-500/10 hover:text-emerald-800 dark:text-emerald-400 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs transition"
            >
              Hariri
            </button>
            <button 
              @click="deleteService(s.id)" 
              class="px-3 py-1.5 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 font-bold rounded-xl text-xs transition"
            >
              Futa
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 1: SAJILI / HARIRI HUDUMA -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">{{ form.id ? 'Hariri Huduma' : 'Sajili Huduma Mpya' }}</h3>
          <button @click="closeModal" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>

        <div class="p-6 space-y-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <div>
            <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Jina la Huduma (Swahili) *</label>
            <input 
              v-model="form.name_sw" 
              type="text" 
              placeholder="e.g. Kukoboa Mpunga" 
              class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-900 dark:text-slate-50 focus:bg-white dark:bg-slate-900 focus:border-emerald-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Category *</label>
              <select v-model="form.category" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                <option value="milling">Kukoboa (Milling)</option>
                <option value="drying">Kukausha (Drying)</option>
                <option value="grading">Grading / Sorting</option>
                <option value="storage">Hifadhi (Storage)</option>
                <option value="packaging">Packaging</option>
              </select>
            </div>

            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="font-bold text-slate-800 dark:text-slate-100">Aina ya Zao</label>
                <button @click="showCropModal = true" class="text-[10.5px] font-extrabold text-emerald-700 dark:text-emerald-400 hover:underline">+ Sajili Zao</button>
              </div>
              <select v-model="form.crop_type" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                <option value="">Zote (All)</option>
                <option v-for="c in cropsList" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="font-bold text-slate-800 dark:text-slate-100">Kipimo cha Bei *</label>
                <button @click="showUnitModal = true" class="text-[10.5px] font-extrabold text-teal-700 dark:text-teal-400 hover:underline">+ Sajili Kipimo</button>
              </div>
              <select v-model="form.unit" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">
                <option v-for="u in unitsList" :key="u.name" :value="u.name">{{ u.name }}</option>
              </select>
            </div>

            <div>
              <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Bei kwa Kipimo (Tsh) *</label>
              <input 
                v-model.number="form.rate" 
                type="number" 
                placeholder="e.g. 70" 
                class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-900 dark:text-slate-50 focus:bg-white dark:bg-slate-900 focus:border-emerald-500"
              />
            </div>
          </div>

          <div>
            <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Maelezo ya Huduma (Optional)</label>
            <textarea 
              v-model="form.description" 
              rows="2" 
              placeholder="e.g. Ada ya kukoboa mpunga kwa kilo moja" 
              class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-medium"
            ></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button @click="closeModal" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl">Ghairi</button>
            <button @click="saveService" class="px-5 py-2 bg-emerald-600 text-white font-bold rounded-xl shadow-xs hover:bg-emerald-700 transition">
              {{ form.id ? 'Hifadhi Mabadiliko' : 'Sajili Huduma' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: SAJILI ZAO JIPYA (Full Crops Control) -->
    <div v-if="showCropModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-emerald-800 flex items-center justify-between bg-gradient-to-r from-emerald-900 to-teal-900 text-white">
          <h3 class="text-base font-extrabold">🌱 Usimamizi wa Mazao</h3>
          <button @click="showCropModal = false" class="text-emerald-200 hover:text-white p-1">✕</button>
        </div>
        <div class="p-6 space-y-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
          <div>
            <label class="block mb-1 font-bold text-slate-800 dark:text-slate-100">Jina la Zao Jipya *</label>
            <div class="flex gap-2">
              <input 
                v-model="newCropInput" 
                type="text" 
                placeholder="e.g. Alizeti, Ngano, Pamba" 
                class="flex-1 p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl font-bold"
              />
              <button @click="handleSaveCrop" class="px-4 py-2.5 bg-emerald-600 text-white font-extrabold rounded-xl shadow-xs hover:bg-emerald-700 transition">
                Sajili
              </button>
            </div>
          </div>

          <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <div class="text-xs font-extrabold text-slate-900 dark:text-slate-50">Orodha ya Mazao Yaliyopo (Full Control):</div>
            <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
              <div 
                v-for="c in cropsList" 
                :key="c" 
                class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl"
              >
                <span class="font-bold text-slate-800 dark:text-slate-100">{{ c }}</span>
                <button @click="deleteCrop(c)" class="text-red-500 hover:text-red-700 dark:text-red-400 font-extrabold text-xs">
                  Futa
                </button>
              </div>
            </div>
          </div>

          <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
            <button @click="showCropModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl">Funga</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 3: SAJILI NA EDIT MILINGANYO YA VIPIMO (Full Unit Conversion Control) -->
    <div v-if="showUnitModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        <div class="px-6 py-4 border-b border-teal-800 flex items-center justify-between bg-gradient-to-r from-teal-900 to-emerald-900 text-white">
          <h3 class="text-base font-black flex items-center gap-2">
            <span>⚖️ Usimamizi wa Milinganyo ya Vipimo</span>
          </h3>
          <button @click="showUnitModal = false" class="text-teal-200 hover:text-white p-1 cursor-pointer">✕</button>
        </div>

        <div class="p-6 space-y-5 text-xs font-semibold text-slate-700 dark:text-slate-200 max-h-[85vh] overflow-y-auto">

          <!-- DUAL DROPDOWN & DUAL INPUT EQUIVALENCE CONTROL BUILDER -->
          <div class="bg-gradient-to-br from-slate-900 via-slate-950 to-emerald-950 text-white p-5 rounded-3xl shadow-xl space-y-4 border border-teal-500/30">
            <div class="grid grid-cols-1 sm:grid-cols-11 gap-3 items-center">
              <!-- Left Side: Unit A Dropdown & Input -->
              <div class="sm:col-span-5 space-y-2 bg-white/5 dark:bg-slate-900/5 p-3 rounded-2xl border border-white/10">
                <select v-model="eqForm.unitA" class="w-full p-2.5 bg-slate-900 border border-white/20 rounded-xl font-black text-white text-xs">
                  <option v-for="u in unitsList" :key="u.name" :value="u.name">{{ u.name }}</option>
                </select>
                <input v-model.number="eqForm.amountA" type="number" placeholder="1" class="w-full p-2 bg-white/10 dark:bg-slate-900/10 border border-white/15 rounded-xl font-black text-white text-sm text-center focus:bg-white/20 dark:bg-slate-900/20 focus:outline-hidden" />
              </div>

              <!-- Equals Sign Badge -->
              <div class="sm:col-span-1 flex items-center justify-center">
                <span class="w-9 h-9 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-400 flex items-center justify-center font-black text-lg shadow-sm">=</span>
              </div>

              <!-- Right Side: Unit B Dropdown & Input -->
              <div class="sm:col-span-5 space-y-2 bg-white/5 dark:bg-slate-900/5 p-3 rounded-2xl border border-white/10">
                <select v-model="eqForm.unitB" class="w-full p-2.5 bg-slate-900 border border-white/20 rounded-xl font-bold text-white text-xs">
                  <option v-for="u in unitsList" :key="u.name" :value="u.name">{{ u.name }}</option>
                </select>
                <input v-model.number="eqForm.amountB" type="number" placeholder="50" class="w-full p-2 bg-white/10 dark:bg-slate-900/10 border border-white/15 rounded-xl font-black text-white text-sm text-center focus:bg-white/20 dark:bg-slate-900/20 focus:outline-hidden" />
              </div>
            </div>

            <!-- Dynamic Result & Save Button -->
            <div class="p-3.5 bg-emerald-950/80 border border-emerald-500/30 rounded-2xl flex items-center justify-between gap-3">
              <div class="text-sm font-extrabold text-white">
                <span class="text-teal-300 font-mono">{{ eqForm.amountA || 1 }} {{ eqForm.unitA }}</span> = 
                <span class="text-emerald-400 font-mono underline decoration-emerald-400 font-black ml-1">{{ calculatedEquivalenceSummary }}</span>
              </div>
              <button @click="saveEquivalenceRule" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold rounded-xl shadow-md transition cursor-pointer text-xs flex items-center gap-1">
                <span>💾 Hifadhi</span>
              </button>
            </div>
          </div>

          <!-- Quick Add New Unit -->
          <div class="p-3 bg-teal-50/60 dark:bg-teal-900/40 border border-teal-200 dark:border-teal-700/50 rounded-2xl flex items-center gap-2">
            <input v-model="newUnitInput.name" type="text" placeholder="+ Sajili Kipimo Kipya (e.g. Lumbesa)" class="flex-1 p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-xs"/>
            <button @click="handleQuickAddUnit" class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white font-extrabold text-xs rounded-xl shadow-xs transition cursor-pointer whitespace-nowrap">
              + Sajili
            </button>
          </div>

          <!-- Unit Equivalences Table -->
          <div class="space-y-2 pt-1 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
              <div class="text-xs font-extrabold text-slate-900 dark:text-slate-50">📊 Jedwali la Milinganyo ya Vipimo (Registered Equivalences):</div>
              <span class="text-[10.5px] text-slate-400 font-mono">{{ unitsList.length }} Registered Units</span>
            </div>

            <div class="max-h-64 overflow-y-auto space-y-2 pr-1">
              <div 
                v-for="u in unitsList" 
                :key="u.name" 
                class="p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl hover:border-teal-300 transition"
              >
                <!-- INLINE EDIT MODE -->
                <div v-if="editingUnitName === u.name" class="space-y-2">
                  <div class="font-extrabold text-teal-950 dark:text-teal-400 text-xs flex items-center justify-between">
                    <span>✏️ Hariri Kipimo na Uzito:</span>
                    <button @click="editingUnitName = null" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 font-black text-xs cursor-pointer">✕ Ghairi</button>
                  </div>
                  <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                    <div class="sm:col-span-7">
                      <label class="block text-[9.5px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Jina la Kipimo (Name)</label>
                      <input 
                        v-model="editUnitForm.name" 
                        type="text" 
                        class="w-full p-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl font-extrabold text-xs text-slate-900 dark:text-slate-50"
                      />
                    </div>
                    <div class="sm:col-span-5">
                      <label class="block text-[9.5px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Mlinganyo wa Kg (Ratio)</label>
                      <input 
                        v-model.number="editUnitForm.kg" 
                        type="number" 
                        class="w-full p-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl font-extrabold text-xs text-emerald-800 dark:text-emerald-400"
                      />
                    </div>
                  </div>
                  <div class="flex justify-end gap-1.5 pt-1">
                    <button @click="editingUnitName = null" class="px-3 py-1 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-lg text-xs cursor-pointer">Ghairi</button>
                    <button @click="saveUnitEdit(u.name)" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-lg text-xs shadow-xs cursor-pointer">💾 Hifadhi</button>
                  </div>
                </div>

                <!-- DISPLAY MODE -->
                <div v-else class="flex items-center justify-between gap-2">
                  <div class="space-y-0.5">
                    <div class="font-extrabold text-slate-900 dark:text-slate-50 text-xs flex items-center gap-1.5 flex-wrap">
                      <span>{{ u.name }}</span>
                      <span v-if="u.formulaText" class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-400 border border-emerald-300/80 dark:border-emerald-700/60 rounded-lg text-[10px] font-black font-mono">
                        🔄 {{ u.formulaText }}
                      </span>
                    </div>
                    <div class="text-[11px] text-teal-800 dark:text-teal-400 font-bold font-mono">
                      1 {{ u.name }} = <span class="text-emerald-700 dark:text-emerald-400 font-black">{{ u.kg }} Kg</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-1.5 shrink-0">
                    <div class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-xl text-xs font-mono font-extrabold text-slate-700 dark:text-slate-200 select-none">
                      {{ u.kg }} Kg
                    </div>
                    <button @click="startEditUnit(u)" class="px-2.5 py-1 text-teal-700 dark:text-teal-400 hover:text-teal-900 dark:text-teal-400 hover:bg-teal-50 dark:bg-teal-900/40 border border-teal-200 dark:border-teal-700/50 rounded-xl transition font-extrabold text-xs cursor-pointer flex items-center gap-1">
                      <span>✏️ Badili</span>
                    </button>
                    <button @click="handleDeleteUnit(u.name)" class="px-2.5 py-1 text-red-500 hover:text-red-700 dark:text-red-400 hover:bg-red-50 dark:bg-red-500/10 rounded-xl transition font-extrabold text-xs cursor-pointer">
                      Futa
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
            <button @click="showUnitModal = false" class="px-5 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-800 dark:text-slate-100 font-extrabold rounded-xl transition">Funga</button>
          </div>
        </div>
      </div>
    </div>

    <!-- TOAST NOTIFICATION CARD -->
    <div v-if="toast.show" 
         class="fixed top-6 right-6 z-100 max-w-md w-full p-4 rounded-2xl shadow-2xl backdrop-blur-xl border flex items-start gap-3.5 text-white transition-all transform animate-fadeIn duration-200"
         :class="toast.type === 'error' 
           ? 'bg-gradient-to-r from-rose-950/95 via-red-950/95 to-slate-900/95 border-rose-500/80 shadow-rose-950/50' 
           : 'bg-gradient-to-r from-emerald-950/95 via-teal-950/95 to-slate-900/95 border-emerald-500/80 shadow-emerald-950/50'">
      
      <div class="p-2 rounded-xl flex-shrink-0 flex items-center justify-center text-lg leading-none"
           :class="toast.type === 'error' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'">
        <span>{{ toast.type === 'error' ? '⚠️' : '✅' }}</span>
      </div>

      <div class="flex-1 min-w-0 pr-1 space-y-0.5">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-black uppercase tracking-wider" :class="toast.type === 'error' ? 'text-rose-400' : 'text-emerald-400'">
            {{ toast.type === 'error' ? 'Onyo la Mfumo' : 'Taarifa ya Mfumo' }}
          </span>
          <button @click="toast.show = false" class="text-white/60 hover:text-white text-xs font-bold p-0.5 rounded-lg hover:bg-white/10 dark:bg-slate-900/10 transition cursor-pointer">✕</button>
        </div>
        <div class="font-extrabold text-xs text-white/95 leading-relaxed break-words">
          {{ toast.message }}
        </div>
      </div>
    </div>

    <!-- CUSTOM BEAUTIFUL CONFIRMATION DIALOG MODAL -->
    <div v-if="confirmModal.show" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-800 p-6 space-y-4 text-center animate-fadeIn">
        <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-500 flex items-center justify-center text-2xl mx-auto shadow-inner">
          ⚠️
        </div>
        <div class="space-y-1">
          <h3 class="text-sm font-black text-slate-900 dark:text-slate-50">{{ confirmModal.title }}</h3>
          <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 leading-relaxed">{{ confirmModal.message }}</p>
        </div>
        <div class="flex items-center gap-2 pt-2">
          <button @click="confirmModal.show = false" class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl transition cursor-pointer">
            Ghairi
          </button>
          <button @click="executeConfirmAction" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer">
            Ndio, Futa
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAgroMaster } from '../composables/useAgroMaster.js';

const { cropsList, unitsList, addCrop, deleteCrop, addUnit, updateUnitRatio, updateUnit, deleteUnit, getUnitKg, convertUnits } = useAgroMaster();

const editingUnitName = ref(null);
const editUnitForm = ref({ name: '', kg: 1 });

const startEditUnit = (u) => {
  editingUnitName.value = u.name;
  editUnitForm.value = { name: u.name, kg: u.kg };
};

const saveUnitEdit = (oldName) => {
  const newName = (editUnitForm.value.name || '').trim();
  const newKg = Number(editUnitForm.value.kg);
  if (!newName || isNaN(newKg) || newKg <= 0) {
    triggerToast('Weka jina na kilo sahihi za kipimo!', 'error');
    return;
  }
  const success = updateUnit(oldName, newName, newKg);
  if (success) {
    editingUnitName.value = null;
    triggerToast(`Kipimo cha "${newName}" kimeboreshwa kikamilifu! (1 ${newName} = ${newKg} Kg) ✓`);
  } else {
    triggerToast('Jina la kipimo hiki tayari linatumiwa na kipimo kingine!', 'error');
  }
};

const services = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const selectedCategoryFilter = ref('');

const showModal = ref(false);
const showCropModal = ref(false);
const showUnitModal = ref(false);

const newCropInput = ref('');
const newUnitInput = ref({ name: '', kg: 100 });

const eqForm = ref({
  unitA: 'Gunia (Bag)',
  amountA: 1,
  unitB: 'Kilo (Kg)',
  amountB: 100
});

const calculatedEquivalenceSummary = computed(() => {
  const amtA = Number(eqForm.value.amountA) || 1;
  const amtB = Number(eqForm.value.amountB) || 1;
  const unitBName = eqForm.value.unitB || 'Kilo (Kg)';
  const unitBKg = getUnitKg(unitBName, 1);
  const totalKg = (amtB / amtA) * unitBKg;
  if (unitBName.toLowerCase().includes('kg') || unitBName.toLowerCase().includes('kilo')) {
    return `${amtB} ${unitBName}`;
  }
  return `${amtB} ${unitBName} (${totalKg.toLocaleString()} Kg)`;
});

const saveEquivalenceRule = () => {
  const nameA = (eqForm.value.unitA || '').trim();
  const nameB = (eqForm.value.unitB || '').trim();
  const amtA = Number(eqForm.value.amountA) || 0;
  const amtB = Number(eqForm.value.amountB) || 0;

  if (!nameA || !nameB || amtA <= 0 || amtB <= 0) {
    triggerToast('Weka kiasi na chagua vipimo vyote viwili sahihi!', 'error');
    return;
  }

  if (nameA.toLowerCase() === nameB.toLowerCase()) {
    triggerToast('Huwezi kulinganisha kipimo na chenyewe! Chagua vipimo viwili tofauti.', 'error');
    return;
  }

  const unitBKg = getUnitKg(nameB, 1);
  const ratioInKg = (amtB / amtA) * unitBKg;
  const formulaStr = `${amtA} ${nameA} = ${amtB} ${nameB}`;

  const exists = unitsList.value.some(u => u.name.toLowerCase() === nameA.toLowerCase());
  if (exists) {
    updateUnitRatio(nameA, ratioInKg, formulaStr);
    triggerToast(`Mlinganyo umehifadhiwa na kuonekana chini kwenye jedwali: ${formulaStr} ✓`);
  } else {
    addUnit(nameA, ratioInKg, formulaStr);
    triggerToast(`Mlinganyo mpya umehifadhiwa na kuonekana chini kwenye jedwali: ${formulaStr} ✓`);
  }
};

const handleQuickAddUnit = () => {
  if (!newUnitInput.value.name) {
    triggerToast('Weka jina la kipimo kipya!', 'error');
    return;
  }
  const name = newUnitInput.value.name.trim();
  const exists = unitsList.value.some(u => u.name.toLowerCase() === name.toLowerCase());
  if (exists) {
    triggerToast(`Kipimo cha "${name}" tayari kipo kwenye orodha! Badilisha mlinganyo wake hapo juu.`, 'error');
    return;
  }
  const success = addUnit(name, 1);
  if (success) {
    eqForm.value.unitA = name;
    triggerToast(`Kipimo cha "${name}" kimesajiliwa! Sasa weka mlinganyo wake hapo juu. ⚖️`);
    newUnitInput.value.name = '';
  } else {
    triggerToast('Kipimo hiki hakikukubaliwa.', 'error');
  }
};

const toast = ref({ show: false, message: '', type: 'success' });

const triggerToast = (msg, type = 'success') => {
  toast.value = { show: true, message: msg, type };
  setTimeout(() => { toast.value.show = false; }, 3500);
};

const handleUpdateUnitKg = (unitName, val) => {
  const num = Number(val);
  if (isNaN(num) || num <= 0) return;
  updateUnitRatio(unitName, num);
  triggerToast(`Mlinganyo wa "${unitName}" umebadilishwa kuwa: 1 ${unitName} = ${num} Kg ✓`);
};

const confirmModal = ref({
  show: false,
  title: '',
  message: '',
  onConfirm: null
});

const executeConfirmAction = () => {
  if (confirmModal.value.onConfirm) {
    confirmModal.value.onConfirm();
  }
  confirmModal.value.show = false;
};

const handleDeleteUnit = (unitName) => {
  confirmModal.value = {
    show: true,
    title: `Futa Kipimo: "${unitName}"`,
    message: `Je, una uhakika unataka kufuta kipimo cha "${unitName}"? Mizigo na Huduma zote zilizosajiliwa awali kwa kutumia kipimo hiki ZITABAKI SALAMA na uzito wake hautapotea.`,
    onConfirm: () => {
      deleteUnit(unitName);
      triggerToast(`Kipimo cha "${unitName}" kimefutwa kwenye orodha ✓`);
    }
  };
};

const handleSaveCrop = () => {
  if (!newCropInput.value) {
    triggerToast('Weka jina la zao!', 'error');
    return;
  }
  const success = addCrop(newCropInput.value);
  if (success) {
    triggerToast(`Zao la "${newCropInput.value}" limesajiliwa kikamilifu! 🌱`);
    newCropInput.value = '';
  } else {
    triggerToast('Zao hili tayari lipo au halifai.', 'error');
  }
};

const handleSaveUnit = () => {
  if (!newUnitInput.value.name) {
    triggerToast('Weka jina la kipimo!', 'error');
    return;
  }
  const success = addUnit(newUnitInput.value.name, newUnitInput.value.kg || 1);
  if (success) {
    triggerToast(`Kipimo cha "${newUnitInput.value.name}" kimesajiliwa kikamilifu! ⚖️`);
    newUnitInput.value = { name: '', kg: 1 };
  } else {
    triggerToast('Kipimo hili tayari lipo au halifai.', 'error');
  }
};

const defaultServices = [
  { id: 1, name_sw: 'Kukoboa (Sembe/Mpunga)', category: 'milling', rate: 70.00, unit: 'kg', crop_type: 'Mpunga/Mahindi', description: 'Ada ya kukoboa nafaka kwa kilo.' },
  { id: 2, name_sw: 'Kusogeza kwenye kinu', category: 'milling', rate: 300.00, unit: 'gunia', crop_type: 'Zote', description: 'Ada ya kubeba na kusogeza gunia kwenye kinu.' },
  { id: 3, name_sw: 'Kuanika mpunga (Drying)', category: 'drying', rate: 1000.00, unit: 'gunia', crop_type: 'Mpunga', description: 'Ada ya kuanika mpunga juani kwa gunia.' },
  { id: 4, name_sw: 'Kugiredi (Grading)', category: 'grading', rate: 8.00, unit: 'kg', crop_type: 'Mchele', description: 'Ada ya kupambanua daraja la mchele.' },
  { id: 5, name_sw: 'Kudoloti (Color sorting)', category: 'grading', rate: 22.00, unit: 'kg', crop_type: 'Mchele', description: 'Kutenganisha mchele mweusi/mwekundu kwa mashine ya rangi.' },
  { id: 6, name_sw: 'Kuanika + Kuchanganya', category: 'drying', rate: 1500.00, unit: 'gunia', crop_type: 'Mpunga', description: 'Ada ya kuanika na kuchanganya mpunga.' },
  { id: 7, name_sw: 'Kuchanganya Mchele na Mafuta', category: 'milling', rate: 2.50, unit: 'kg', crop_type: 'Mchele', description: 'Polishing na kurutubisha mchele.' },
  { id: 8, name_sw: 'Kupanga stoko (Warehouse)', category: 'storage', rate: 700.00, unit: 'gunia', crop_type: 'Zote', description: 'Ada ya kupanga magunia ghalani.' },
  { id: 9, name_sw: 'Wafanyakazi (Labor)', category: 'milling', rate: 1000.00, unit: 'gunia', crop_type: 'Zote', description: 'Gharama za vibarua vya kinu.' }
];

const form = ref({
  id: null,
  name_sw: '',
  category: 'milling',
  crop_type: '',
  unit: 'kg',
  rate: 70,
  description: ''
});

const fetchServices = async () => {
  loading.value = true;
  try {
    const res = await fetch('/api/v1/services');
    if (res.ok) {
      const data = await res.json();
      if (Array.isArray(data) && data.length > 0) {
        services.value = data;
      } else {
        services.value = defaultServices;
      }
    } else {
      services.value = defaultServices;
    }
  } catch (e) {
    console.error('Error fetching services:', e);
    services.value = defaultServices;
  } finally {
    loading.value = false;
  }
};

const countByCategory = (cat) => {
  return services.value.filter(s => (s.category || '').toLowerCase() === cat.toLowerCase()).length;
};

const filteredServices = computed(() => {
  return services.value.filter(s => {
    const q = searchQuery.value.toLowerCase();
    const matchQ = !q || (s.name_sw && s.name_sw.toLowerCase().includes(q)) || (s.category && s.category.toLowerCase().includes(q));
    const matchCat = !selectedCategoryFilter.value || (s.category || '').toLowerCase() === selectedCategoryFilter.value.toLowerCase();
    return matchQ && matchCat;
  });
});

const openAddModal = () => {
  form.value = {
    id: null,
    name_sw: '',
    category: 'milling',
    crop_type: '',
    unit: 'kg',
    rate: 70,
    description: ''
  };
  showModal.value = true;
};

const openEditModal = (s) => {
  form.value = {
    id: s.id,
    name_sw: s.name_sw || '',
    category: s.category || 'milling',
    crop_type: s.crop_type || '',
    unit: s.unit || 'kg',
    rate: s.rate || 0,
    description: s.description || ''
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveService = async () => {
  if (!form.value.name_sw || !form.value.rate) {
    triggerToast('Jaza jina la huduma na bei sahihi!', 'error');
    return;
  }

  const payload = {
    name_sw: form.value.name_sw,
    name_en: form.value.name_sw,
    category: form.value.category,
    crop_type: form.value.crop_type || null,
    unit: form.value.unit,
    rate: form.value.rate,
    description: form.value.description
  };

  try {
    const url = form.value.id ? `/api/v1/services/${form.value.id}` : '/api/v1/services';
    const method = form.value.id ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (res.ok && (data.success || data.id || data.service)) {
      triggerToast(form.value.id ? 'Mabadiliko ya huduma yamehifadhiwa! ✓' : 'Huduma mpya imesajiliwa kikamilifu! 🛠️');
      closeModal();
      await fetchServices();
    } else {
      if (!form.value.id) {
        services.value.push({ id: Date.now(), ...payload });
      } else {
        const idx = services.value.findIndex(x => x.id === form.value.id);
        if (idx !== -1) services.value[idx] = { ...services.value[idx], ...payload };
      }
      triggerToast('Huduma imehifadhiwa kikamilifu!');
      closeModal();
    }
  } catch (e) {
    console.error('Error saving service:', e);
    if (!form.value.id) {
      services.value.push({ id: Date.now(), ...payload });
    } else {
      const idx = services.value.findIndex(x => x.id === form.value.id);
      if (idx !== -1) services.value[idx] = { ...services.value[idx], ...payload };
    }
    triggerToast('Huduma imehifadhiwa kikamilifu!');
    closeModal();
  }
};

const deleteService = async (id) => {
  if (!confirm('Je, una uhakika unataka kufuta huduma hii?')) return;

  try {
    const res = await fetch(`/api/v1/services/${id}`, { method: 'DELETE' });
    if (res.ok) {
      triggerToast('Huduma imefutwa kikamilifu! 🗑️');
      await fetchServices();
    } else {
      services.value = services.value.filter(s => s.id !== id);
      triggerToast('Huduma imefutwa!');
    }
  } catch (e) {
    services.value = services.value.filter(s => s.id !== id);
    triggerToast('Huduma imefutwa!');
  }
};

onMounted(() => {
  fetchServices();
});
</script>
