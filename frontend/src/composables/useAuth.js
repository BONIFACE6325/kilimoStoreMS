import { ref, computed, onMounted, onUnmounted } from 'vue';

const INACTIVITY_LIMIT_MS = 15 * 60 * 1000; // 15 Minutes Inactivity Timeout

// Helper to retrieve token from sessionStorage (or localStorage if remember me was enabled)
const getInitialToken = () => {
  const sessionTok = sessionStorage.getItem('garanoki_token');
  if (sessionTok) return sessionTok;
  return localStorage.getItem('garanoki_token');
};

const getInitialUser = () => {
  const sessionUser = sessionStorage.getItem('garanoki_user');
  if (sessionUser) {
    try { return JSON.parse(sessionUser); } catch (e) {}
  }
  const localUser = localStorage.getItem('garanoki_user');
  if (localUser) {
    try { return JSON.parse(localUser); } catch (e) {}
  }
  return null;
};

const token = ref(getInitialToken());
const user = ref(getInitialUser());
let inactivityCheckInterval = null;
let lastThrottleTime = 0;

export function useAuth() {
  const isAuthenticated = computed(() => !!token.value);

  const updateActivity = () => {
    const now = Date.now();
    // Throttle activity updates to once every 3 seconds to avoid unnecessary writes
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
    // Set initial activity timestamp if not set
    if (!localStorage.getItem('garanoki_last_activity')) {
      localStorage.setItem('garanoki_last_activity', String(Date.now()));
    }

    const activityEvents = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'];
    activityEvents.forEach(evt => {
      window.addEventListener(evt, updateActivity, { passive: true });
    });

    if (!inactivityCheckInterval) {
      // Check every 10 seconds for session inactivity expiry
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

    // Strict security validation for Owner credentials
    if (cleanEmail !== 'gwakilabonface@gmail.com' || cleanPass !== '12345678') {
      return { 
        success: false, 
        message: 'Access Denied: Incorrect email or password. Only authorized system owner can log in.' 
      };
    }

    // Generate secure session token and save user details
    const newToken = 'garanoki_owner_token_' + Date.now();
    const userData = {
      name: 'Boniface Gwakila',
      email: 'gwakilabonface@gmail.com',
      role: 'System Owner'
    };

    token.value = newToken;
    user.value = userData;

    // Clear any previous logout reasons
    sessionStorage.removeItem('garanoki_logout_reason');

    // Store token in sessionStorage by default (cleared on browser close)
    sessionStorage.setItem('garanoki_token', newToken);
    sessionStorage.setItem('garanoki_user', JSON.stringify(userData));

    if (rememberMe) {
      localStorage.setItem('garanoki_token', newToken);
      localStorage.setItem('garanoki_user', JSON.stringify(userData));
    } else {
      localStorage.removeItem('garanoki_token');
      localStorage.removeItem('garanoki_user');
    }

    // Set initial activity time
    localStorage.setItem('garanoki_last_activity', String(Date.now()));
    startInactivityTimer();

    return { success: true };
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

  // Auto-init inactivity tracking if authenticated
  if (token.value) {
    startInactivityTimer();
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout,
    startInactivityTimer,
    stopInactivityTimer
  };
}
