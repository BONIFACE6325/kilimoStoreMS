<template>
  <div v-if="totalItems > 0" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 bg-slate-50/70 dark:bg-slate-900/60 border-t border-slate-200/70 dark:border-slate-800 text-xs font-medium text-slate-600 dark:text-slate-400 rounded-b-xl">
    
    <!-- Left: Items Info & Per Page Selector -->
    <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
      <div class="flex items-center gap-1.5 whitespace-nowrap">
        <span>{{ t('showing', 'Inaonyesha') }}</span>
        <span class="font-bold text-slate-900 dark:text-slate-200 font-mono">{{ startItem }}</span>
        <span>-</span>
        <span class="font-bold text-slate-900 dark:text-slate-200 font-mono">{{ endItem }}</span>
        <span>{{ t('of', 'kati ya') }}</span>
        <span class="font-black text-slate-900 dark:text-slate-100 font-mono">{{ totalItems }}</span>
        <span>{{ t('entries', 'rekodi') }}</span>
      </div>

      <!-- Per Page Dropdown -->
      <div v-if="showPerPageSelector" class="flex items-center gap-1 text-[11px]">
        <select 
          :value="perPage" 
          @change="changePerPage($event)"
          class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 text-xs font-bold cursor-pointer"
        >
          <option v-for="opt in perPageOptions" :key="opt" :value="opt">
            {{ opt }} / {{ currentLang === 'sw' ? 'ukuta' : 'page' }}
          </option>
        </select>
      </div>
    </div>

    <!-- Right: Page Controls -->
    <div class="flex items-center gap-1.5 w-full sm:w-auto justify-center sm:justify-end">
      
      <!-- Prev Button -->
      <button
        @click="goToPage(currentPage - 1)"
        :disabled="currentPage <= 1"
        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 font-bold disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1 shadow-2xs"
        :title="currentLang === 'sw' ? 'Ukurasa uliopita' : 'Previous page'"
      >
        <span>←</span>
        <span class="hidden md:inline">{{ t('prev', 'Iliyopita') }}</span>
      </button>

      <!-- Page Numbers -->
      <div class="flex items-center gap-1">
        <button
          v-for="page in displayedPages"
          :key="page"
          @click="page !== '...' && goToPage(page)"
          :disabled="page === '...'"
          :class="[
            page === currentPage
              ? 'bg-emerald-600 dark:bg-emerald-500 text-white font-black shadow-xs ring-2 ring-emerald-500/30'
              : page === '...'
              ? 'bg-transparent text-slate-400 dark:text-slate-500 cursor-default'
              : 'bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-bold hover:text-emerald-600 dark:hover:text-emerald-400'
          ]"
          class="min-w-[30px] h-[30px] px-1.5 flex items-center justify-center rounded-lg text-xs transition cursor-pointer font-mono"
        >
          {{ page }}
        </button>
      </div>

      <!-- Next Button -->
      <button
        @click="goToPage(currentPage + 1)"
        :disabled="currentPage >= totalPages"
        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 font-bold disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer flex items-center gap-1 shadow-2xs"
        :title="currentLang === 'sw' ? 'Ukurasa unaofuata' : 'Next page'"
      >
        <span class="hidden md:inline">{{ t('next', 'Inayofuata') }}</span>
        <span>→</span>
      </button>

    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useLanguage } from '../composables/useLanguage';

const { t, currentLang } = useLanguage();

const props = defineProps({
  currentPage: {
    type: Number,
    default: 1
  },
  totalItems: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    default: 10
  },
  perPageOptions: {
    type: Array,
    default: () => [5, 10, 20, 50, 100]
  },
  showPerPageSelector: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['update:currentPage', 'update:perPage']);

const totalPages = computed(() => Math.ceil(props.totalItems / props.perPage) || 1);

const startItem = computed(() => {
  if (props.totalItems === 0) return 0;
  return (props.currentPage - 1) * props.perPage + 1;
});

const endItem = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems);
});

const goToPage = (page) => {
  if (typeof page === 'number' && page >= 1 && page <= totalPages.value && page !== props.currentPage) {
    emit('update:currentPage', page);
  }
};

const changePerPage = (event) => {
  const newPerPage = Number(event.target.value);
  emit('update:perPage', newPerPage);
  emit('update:currentPage', 1);
};

const displayedPages = computed(() => {
  const total = totalPages.value;
  const current = props.currentPage;

  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }

  const pages = [];
  pages.push(1);

  if (current > 3) {
    pages.push('...');
  }

  const start = Math.max(2, current - 1);
  const end = Math.min(total - 1, current + 1);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  if (current < total - 2) {
    pages.push('...');
  }

  pages.push(total);

  return pages;
});
</script>
