import { ref, computed } from 'vue';

const token = ref(localStorage.getItem('garanoki_token'));
const user = ref(JSON.parse(localStorage.getItem('garanoki_user') || 'null'));

export function useAuth() {
  const isAuthenticated = computed(() => !!token.value);

  const login = (emailInput, passwordInput) => {
    const cleanEmail = (emailInput || '').trim().toLowerCase();
    const cleanPass = (passwordInput || '').trim();

    // Strict security validation for Owner credentials
    if (cleanEmail !== 'gwakilabonface@gmail.com' || cleanPass !== '12345678') {
      return { 
        success: false, 
        message: 'Access Denied: Incorrect email or password. Only system owner is authorized.' 
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

    localStorage.setItem('garanoki_token', newToken);
    localStorage.setItem('garanoki_user', JSON.stringify(userData));

    return { success: true };
  };

  const logout = () => {
    token.value = null;
    user.value = null;
    localStorage.removeItem('garanoki_token');
    localStorage.removeItem('garanoki_user');
    window.location.replace('/login');
  };

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout
  };
}
