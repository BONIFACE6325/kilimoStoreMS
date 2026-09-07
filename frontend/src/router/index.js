import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/DashboardView.vue';
import FarmersView from '../views/FarmersView.vue';
import ReceivingView from '../views/ReceivingView.vue';
import InventoryView from '../views/InventoryView.vue';
import ServicesView from '../views/ServicesView.vue';
import LoansView from '../views/LoansView.vue';
import BuyersView from '../views/BuyersView.vue';
import SalesView from '../views/SalesView.vue';
import AccountingView from '../views/AccountingView.vue';
import ReportsView from '../views/ReportsView.vue';
import SettingsView from '../views/SettingsView.vue';
import SettlementView from '../views/SettlementView.vue';
import { useAuth } from '../composables/useAuth';

const routes = [
  { path: '/login', name: 'Login', component: LoginView },
  { path: '/', name: 'Dashboard', component: DashboardView },
  { path: '/farmers', name: 'Farmers', component: FarmersView },
  { path: '/receiving', name: 'Receiving', component: ReceivingView },
  { path: '/inventory', name: 'Inventory', component: InventoryView },
  { path: '/services', name: 'Services', component: ServicesView },
  { path: '/loans', name: 'Loans', component: LoansView },
  { path: '/buyers', name: 'Buyers', component: BuyersView },
  { path: '/sales', name: 'Sales', component: SalesView },
  { path: '/accounting', name: 'Accounting', component: AccountingView },
  { path: '/reports', name: 'Reports', component: ReportsView },
  { path: '/settings', name: 'Settings', component: SettingsView },
  { path: '/settlement', name: 'Settlement', component: SettlementView },
  { path: '/:pathMatch(.*)*', redirect: '/' }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Authentication & Session Protection Navigation Guard
router.beforeEach((to, from, next) => {
  const { isAuthenticated } = useAuth();

  if (to.path !== '/login' && !isAuthenticated.value) {
    next({ name: 'Login' });
  } else if (to.path === '/login' && isAuthenticated.value) {
    next({ name: 'Dashboard' });
  } else {
    next();
  }
});

// Automatic Chunk Error Recovery & Cache Busting
router.onError((error, to) => {
  if (
    error.message.includes('Failed to fetch dynamically imported module') ||
    error.message.includes('Importing a module script failed') ||
    error.message.includes('error loading dynamically imported module')
  ) {
    window.location.href = to.fullPath;
  }
});

export default router;
