import { ref, computed } from 'vue';
import { useTenants } from './useTenants';

const INACTIVITY_LIMIT_MS = 15 * 60 * 1000; // 15 Minutes Inactivity Timeout

const getInitialToken = () => {
  const sessionTok = sessionStorage.getItem('garanoki_token');
  if (sessionTok) return sessionTok;
  return localStorage.getItem('garanoki_token');
};

const getInitialUser = () => {
  const profile = localStorage.getItem('garanoki_user_profile');
  if (profile) {
    try { return JSON.parse(profile); } catch (e) {}
  }
  const sessionUser = sessionStorage.getItem('garanoki_user');
  if (sessionUser) {
    try { return JSON.parse(sessionUser); } catch (e) {}
  }
  const localUser = localStorage.getItem('garanoki_user');
  if (localUser) {
    try { return JSON.parse(localUser); } catch (e) {}
  }
  return {
    name: 'Boniface Gwakila',
    email: 'gwakilabonface@gmail.com',
    phone: '0750000000',
    role: 'System Owner',
    tenantId: 'tenant_kigoma',
    tenantName: 'Kigoma Grain Mills Ltd',
    subscriptionPlan: 'enterprise',
    isSuperAdmin: true,
    avatarUrl: ''
  };
};

const getStoredPassword = () => {
  return localStorage.getItem('garanoki_user_password') || '12345678';
};

const token = ref(getInitialToken());
const user = ref(getInitialUser());
const storedPassword = ref(getStoredPassword());

let inactivityCheckInterval = null;
let lastThrottleTime = 0;

export function useAuth() {
  const { tenants, setActiveTenant } = useTenants();

  const isAuthenticated = computed(() => !!token.value);
  const isSuperAdmin = computed(() => {
    return user.value?.email?.toLowerCase() === 'gwakilabonface@gmail.com' || user.value?.isSuperAdmin === true;
  });

  const updateActivity = () => {
    const now = Date.now();
    if (now - lastThrottleTime > 3000) {
      lastThrottleTime = now;
      localStorage.setItem('garanoki_last_activity', String(now));
    }
  };

  const checkInactivity = () => {
    if (!token.value) return;
    const lastActivity = Number(localStorage.getItem('garanoki_last_activity') || Date.now());
    const elapsed = Date.now() - lastActivity;
    if (elapsed >= INACTIVITY_LIMIT_MS) {
      logout('inactivity');
    }
  };

  const startInactivityTimer = () => {
    if (!localStorage.getItem('garanoki_last_activity')) {
      localStorage.setItem('garanoki_last_activity', String(Date.now()));
    }
    const activityEvents = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'];
    activityEvents.forEach(evt => {
      window.addEventListener(evt, updateActivity, { passive: true });
    });
    if (!inactivityCheckInterval) {
      inactivityCheckInterval = setInterval(checkInactivity, 10000);
    }
  };

  const stopInactivityTimer = () => {
    const activityEvents = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'];
    activityEvents.forEach(evt => {
      window.removeEventListener(evt, updateActivity);
    });
    if (inactivityCheckInterval) {
      clearInterval(inactivityCheckInterval);
      inactivityCheckInterval = null;
    }
  };

  const login = (emailInput, passwordInput, rememberMe = false) => {
    const cleanEmail = (emailInput || '').trim().toLowerCase();
    const cleanPass = (passwordInput || '').trim();

    // 1. Super Admin Authentication
    if (cleanEmail === 'gwakilabonface@gmail.com') {
      if (cleanPass !== storedPassword.value) {
        return { success: false, message: 'Access Denied: Neno la siri la Super Admin siyo sahihi.' };
      }

      const newToken = 'garanoki_saas_token_' + Date.now();
      const userData = {
        name: 'Boniface Gwakila',
        email: 'gwakilabonface@gmail.com',
        phone: '0750000000',
        role: 'System Owner',
        tenantId: 'tenant_kigoma',
        tenantName: 'Kigoma Grain Mills Ltd',
        subscriptionPlan: 'enterprise',
        isSuperAdmin: true,
        avatarUrl: ''
      };

      token.value = newToken;
      user.value = userData;
      setActiveTenant('tenant_kigoma');

      persistSession(newToken, userData, rememberMe);
      return { success: true, isSuperAdmin: true };
    }

    // 2. Tenant Account Authentication
    const matchingTenant = tenants.value.find(t => t.ownerEmail.toLowerCase() === cleanEmail);

    if (!matchingTenant) {
      return { success: false, message: 'Akaunti haikupatikana. Hakikisha barua pepe au sajili akaunti mpya.' };
    }

    if (cleanPass !== (matchingTenant.password || '12345678')) {
      return { success: false, message: 'Neno la siri siyo sahihi.' };
    }

    if (matchingTenant.status === 'suspended') {
      return { success: false, message: 'Akaunti yako imesimamishwa na Admin. Tafadhali mawasiliana na Uongozi wa GARANOKI.' };
    }

    const newToken = 'garanoki_tenant_token_' + Date.now();
    const userData = {
      name: matchingTenant.ownerName,
      email: matchingTenant.ownerEmail,
      phone: matchingTenant.phone,
      role: 'Warehouse Owner',
      tenantId: matchingTenant.id,
      tenantName: matchingTenant.name,
      subscriptionPlan: matchingTenant.plan,
      isSuperAdmin: false,
      avatarUrl: ''
    };

    token.value = newToken;
    user.value = userData;
    setActiveTenant(matchingTenant.id);

    persistSession(newToken, userData, rememberMe);
    return { success: true, isSuperAdmin: false };
  };

  const persistSession = (newToken, userData, rememberMe) => {
    sessionStorage.removeItem('garanoki_logout_reason');
    sessionStorage.setItem('garanoki_token', newToken);
    sessionStorage.setItem('garanoki_user', JSON.stringify(userData));

    if (rememberMe) {
      localStorage.setItem('garanoki_token', newToken);
      localStorage.setItem('garanoki_user', JSON.stringify(userData));
    } else {
      localStorage.removeItem('garanoki_token');
      localStorage.removeItem('garanoki_user');
    }

    localStorage.setItem('garanoki_last_activity', String(Date.now()));
    startInactivityTimer();
  };

  const updateProfile = ({ name, email, phone, avatarUrl }) => {
    const updated = {
      ...user.value,
      name: name !== undefined ? name : user.value?.name,
      email: email !== undefined ? email : user.value?.email,
      phone: phone !== undefined ? phone : user.value?.phone,
      avatarUrl: avatarUrl !== undefined ? avatarUrl : user.value?.avatarUrl
    };
    user.value = updated;
    localStorage.setItem('garanoki_user_profile', JSON.stringify(updated));
    sessionStorage.setItem('garanoki_user', JSON.stringify(updated));
    return { success: true, message: 'Profile updated successfully!' };
  };

  const changePassword = ({ currentPassword, newPassword, confirmPassword }) => {
    const cleanCurrent = (currentPassword || '').trim();
    const cleanNew = (newPassword || '').trim();
    const cleanConfirm = (confirmPassword || '').trim();

    if (cleanCurrent !== storedPassword.value) {
      return { success: false, message: 'Neno la siri la sasa siyo sahihi.' };
    }

    if (cleanNew.length < 6) {
      return { success: false, message: 'Neno jipya la siri lazima liwe na angalau herufi 6.' };
    }

    if (cleanNew !== cleanConfirm) {
      return { success: false, message: 'Maneno mapya ya siri hayafanani.' };
    }

    storedPassword.value = cleanNew;
    localStorage.setItem('garanoki_user_password', cleanNew);
    return { success: true, message: 'Neno la siri limebadilishwa vyema!' };
  };

  const logout = (reason = 'user') => {
    token.value = null;
    user.value = null;
    stopInactivityTimer();

    sessionStorage.removeItem('garanoki_token');
    sessionStorage.removeItem('garanoki_user');
    localStorage.removeItem('garanoki_token');
    localStorage.removeItem('garanoki_user');
    localStorage.removeItem('garanoki_last_activity');

    if (reason === 'inactivity') {
      sessionStorage.setItem('garanoki_logout_reason', 'inactivity');
    }

    window.location.replace('/login');
  };

  if (token.value) {
    startInactivityTimer();
  }

  return {
    token,
    user,
    isAuthenticated,
    isSuperAdmin,
    login,
    logout,
    updateProfile,
    changePassword,
    startInactivityTimer,
    stopInactivityTimer
  };
}
