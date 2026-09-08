import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import './style.css';

// Global Fetch Interceptor to automatically append X-Tenant-ID header for multi-tenancy
const nativeFetch = window.fetch;
window.fetch = async function (resource, config = {}) {
  const activeTenantId = localStorage.getItem('garanoki_active_tenant_id') || '';
  const newConfig = { ...config };
  
  if (!newConfig.headers) {
    newConfig.headers = {};
  }

  if (newConfig.headers instanceof Headers) {
    if (!newConfig.headers.has('X-Tenant-ID')) {
      newConfig.headers.append('X-Tenant-ID', activeTenantId);
    }
  } else if (Array.isArray(newConfig.headers)) {
    newConfig.headers.push(['X-Tenant-ID', activeTenantId]);
  } else {
    newConfig.headers['X-Tenant-ID'] = newConfig.headers['X-Tenant-ID'] || activeTenantId;
  }

  return nativeFetch(resource, newConfig);
};

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#app');
