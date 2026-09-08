import { ref, computed } from 'vue';

const STORAGE_KEY_TENANTS = 'garanoki_saas_tenants';
const STORAGE_KEY_ACTIVE_TENANT = 'garanoki_active_tenant_id';

const defaultTenants = [
  {
    id: 'tenant_kigoma',
    name: 'Kigoma Grain Mills Ltd',
    ownerName: 'Boniface Gwakila',
    ownerEmail: 'gwakilabonface@gmail.com',
    phone: '+255 764 536 736',
    plan: 'enterprise',
    status: 'active',
    monthlyPriceTzs: 500000,
    createdDate: '2026-01-01',
    expiresAt: '2027-12-31',
    warehouseCount: 1,
    totalStorageMt: 5000
  }
];

const loadInitialTenants = () => {
  const stored = localStorage.getItem(STORAGE_KEY_TENANTS);
  if (stored) {
    try {
      const parsed = JSON.parse(stored);
      if (Array.isArray(parsed)) return parsed;
    } catch (e) {}
  }
  return defaultTenants;
};

const tenants = ref(loadInitialTenants());
const activeTenantId = ref(localStorage.getItem(STORAGE_KEY_ACTIVE_TENANT) || '');

export function useTenants() {

  const saveTenants = () => {
    localStorage.setItem(STORAGE_KEY_TENANTS, JSON.stringify(tenants.value));
  };

  const activeTenant = computed(() => {
    return tenants.value.find(t => t.id === activeTenantId.value) || tenants.value[0] || defaultTenants[0];
  });

  const setActiveTenant = (tenantId) => {
    const found = tenants.value.find(t => t.id === tenantId);
    if (found) {
      activeTenantId.value = tenantId;
      localStorage.setItem(STORAGE_KEY_ACTIVE_TENANT, tenantId);
    }
  };

  const registerTenant = ({ companyName, ownerName, ownerEmail, phone, password, plan = 'trial' }) => {
    const cleanEmail = (ownerEmail || '').trim().toLowerCase();
    const cleanName = (companyName || '').trim();

    if (!cleanName || !cleanEmail) {
      return { success: false, message: 'Tafadhali ingiza jina la kampuni na barua pepe.' };
    }

    const existing = tenants.value.find(t => t.ownerEmail.toLowerCase() === cleanEmail);
    if (existing) {
      return { success: false, message: 'Akaunti yenye email hii tayari ipo kwenye mfumo.' };
    }

    const newTenantId = 'tenant_' + Date.now();
    const now = new Date();
    const expiryDate = new Date(now);
    
    if (plan === 'trial') {
      expiryDate.setDate(now.getDate() + 14); // 14-day free trial
    } else {
      expiryDate.setMonth(now.getMonth() + 1); // 1 Month Paid
    }

    const prices = { trial: 0, starter: 50000, business: 150000, enterprise: 500000 };

    const newTenant = {
      id: newTenantId,
      name: cleanName,
      ownerName: (ownerName || cleanName).trim(),
      ownerEmail: cleanEmail,
      phone: (phone || '').trim(),
      password: password || '12345678',
      plan: plan,
      status: 'active',
      monthlyPriceTzs: prices[plan] || 0,
      createdDate: now.toISOString().split('T')[0],
      expiresAt: expiryDate.toISOString().split('T')[0],
      warehouseCount: 1,
      totalStorageMt: 1000
    };

    tenants.value.unshift(newTenant);
    saveTenants();
    setActiveTenant(newTenantId);

    return { success: true, tenant: newTenant };
  };

  const updateTenantStatus = (tenantId, newStatus) => {
    const t = tenants.value.find(item => item.id === tenantId);
    if (t) {
      t.status = newStatus;
      saveTenants();
      return { success: true, message: `Hali ya akaunti imebadilishwa kuwa ${newStatus}` };
    }
    return { success: false, message: 'Akaunti haikupatikana.' };
  };

  const extendSubscription = (tenantId, days = 30) => {
    const t = tenants.value.find(item => item.id === tenantId);
    if (t) {
      const currentExpiry = new Date(t.expiresAt > new Date().toISOString() ? t.expiresAt : new Date());
      currentExpiry.setDate(currentExpiry.getDate() + days);
      t.expiresAt = currentExpiry.toISOString().split('T')[0];
      t.status = 'active';
      saveTenants();
      return { success: true, message: `Muda wa matumizi umeongezwa kwa siku ${days}` };
    }
    return { success: false, message: 'Akaunti haikupatikana.' };
  };

  const deleteTenant = (tenantId) => {
    tenants.value = tenants.value.filter(t => t.id !== tenantId);
    saveTenants();
    if (activeTenantId.value === tenantId && tenants.value.length > 0) {
      setActiveTenant(tenants.value[0].id);
    }
    return { success: true };
  };

  // MRR (Monthly Recurring Revenue) Calculation
  const totalMrrTzs = computed(() => {
    return tenants.value
      .filter(t => t.status === 'active' && t.plan !== 'trial')
      .reduce((sum, t) => sum + (t.monthlyPriceTzs || 0), 0);
  });

  const activeTenantsCount = computed(() => {
    return tenants.value.filter(t => t.status === 'active').length;
  });

  const trialTenantsCount = computed(() => {
    return tenants.value.filter(t => t.plan === 'trial' && t.status === 'active').length;
  });

  const updateTenantBranding = (tenantId, { name, logoUrl, slogan, phone }) => {
    const t = tenants.value.find(item => item.id === tenantId);
    if (t) {
      if (name !== undefined) t.name = name.trim();
      if (logoUrl !== undefined) t.logoUrl = logoUrl;
      if (slogan !== undefined) t.slogan = slogan.trim();
      if (phone !== undefined) t.phone = phone.trim();
      saveTenants();
      return { success: true, message: 'Taarifa za Nembo na Kampuni zimehifadhiwa kikamilifu! 🏢' };
    }
    return { success: false, message: 'Akaunti haikupatikana.' };
  };

  return {
    tenants,
    activeTenant,
    activeTenantId,
    setActiveTenant,
    registerTenant,
    updateTenantStatus,
    extendSubscription,
    updateTenantBranding,
    deleteTenant,
    totalMrrTzs,
    activeTenantsCount,
    trialTenantsCount
  };
}
