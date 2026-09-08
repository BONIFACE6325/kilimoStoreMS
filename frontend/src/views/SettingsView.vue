<template>
  <div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-2xs">
      <h1 class="text-xl font-bold text-slate-900 dark:text-slate-50 tracking-tight">
        ⚙️ {{ t('settingsManagement', 'Mipangilio ya Mfumo & Akaunti') }}
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
        {{ t('settingsSubtitle', 'Badili taarifa za kinu, maelezo ya mmiliki na lugha') }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { useLanguage } from '../composables/useLanguage.js';

const { t } = useLanguage();
</script>
