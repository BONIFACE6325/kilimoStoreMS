import { ref, computed } from 'vue';

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
  const isAuthenticated = computed(() => !!token.value);

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

    const validPass = storedPassword.value;

    if (cleanEmail !== 'gwakilabonface@gmail.com' || cleanPass !== validPass) {
      return { 
        success: false, 
        message: 'Access Denied: Incorrect email or password. Only authorized system owner can log in.' 
      };
    }

    const newToken = 'garanoki_owner_token_' + Date.now();
    const userData = {
      ...user.value,
      name: user.value?.name || 'Boniface Gwakila',
      email: 'gwakilabonface@gmail.com',
      role: 'System Owner'
    };

    token.value = newToken;
    user.value = userData;

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

    return { success: true };
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
      return { success: false, message: 'Current password is incorrect.' };
    }

    if (cleanNew.length < 6) {
      return { success: false, message: 'New password must be at least 6 characters long.' };
    }

    if (cleanNew !== cleanConfirm) {
      return { success: false, message: 'New password and confirmation do not match.' };
    }

    storedPassword.value = cleanNew;
    localStorage.setItem('garanoki_user_password', cleanNew);
    return { success: true, message: 'Password changed successfully!' };
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
    login,
    logout,
    updateProfile,
    changePassword,
    startInactivityTimer,
    stopInactivityTimer
  };
}
