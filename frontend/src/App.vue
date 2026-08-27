<template>
  <div class="min-h-screen flex bg-slate-50 text-slate-800 antialiased font-sans">
    <!-- Sidebar Navigation -->
    <Sidebar />

    <!-- Main Content Panel -->
    <div class="flex-1 flex flex-col min-w-0">
      <Topbar />
      
      <main class="flex-1 p-4 sm:p-5 lg:p-6 overflow-y-auto w-full">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from './components/Sidebar.vue';
import Topbar from './components/Topbar.vue';
</script>
