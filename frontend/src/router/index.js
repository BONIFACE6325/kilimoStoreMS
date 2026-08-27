import { createRouter, createWebHistory } from 'vue-router';
import DashboardView from '../views/DashboardView.vue';
import FarmersView from '../views/FarmersView.vue';

const routes = [
  { path: '/', name: 'Dashboard', component: DashboardView },
  { path: '/farmers', name: 'Farmers', component: FarmersView },
  { path: '/receiving', name: 'Receiving', component: () => import('../views/ReceivingView.vue') },
  { path: '/inventory', name: 'Inventory', component: () => import('../views/InventoryView.vue') },
  { path: '/services', name: 'Services', component: () => import('../views/ServicesView.vue') },
  { path: '/loans', name: 'Loans', component: () => import('../views/LoansView.vue') },
  { path: '/buyers', name: 'Buyers', component: () => import('../views/BuyersView.vue') },
  { path: '/sales', name: 'Sales', component: () => import('../views/SalesView.vue') },
  { path: '/accounting', name: 'Accounting', component: () => import('../views/AccountingView.vue') },
  { path: '/reports', name: 'Reports', component: () => import('../views/ReportsView.vue') },
  { path: '/settings', name: 'Settings', component: () => import('../views/SettingsView.vue') },
  { path: '/settlement', name: 'Settlement', component: () => import('../views/SettlementView.vue') }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
