<template>
  <div v-if="$route.path === '/login' || !isAuthenticated" class="min-h-screen w-full">
    <router-view />
  </div>
  <div v-else class="h-screen w-full flex overflow-hidden bg-slate-50 text-slate-800 antialiased font-sans">
    <!-- Sidebar Navigation -->
    <Sidebar />

    <!-- Main Content Panel -->
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden">
      <Topbar />
      
      <main class="flex-1 p-3 sm:p-5 lg:p-6 overflow-y-auto w-full min-w-0">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuth } from './composables/useAuth';
import Sidebar from './components/Sidebar.vue';
import Topbar from './components/Topbar.vue';

const route = useRoute();
const { isAuthenticated } = useAuth();

onMounted(() => {
  const saved = localStorage.getItem('garanoki_theme');
  if (saved === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('garanoki_theme', 'light');
  }
});
</script>
