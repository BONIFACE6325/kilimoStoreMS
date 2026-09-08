import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let echoInstance = null;

export const getEcho = () => {
  if (!echoInstance) {
    const wsHost = window.location.hostname;
    const wsPort = window.location.protocol === 'https:' ? 443 : 8080;
    const isTls = window.location.protocol === 'https:';

    echoInstance = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY || 'reverb_app_key',
      wsHost: import.meta.env.VITE_REVERB_HOST || wsHost,
      wsPort: import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT) : wsPort,
      wssPort: import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT) : wsPort,
      forceTLS: isTls,
      enabledTransports: ['ws', 'wss'],
    });
  }
  return echoInstance;
};

export const subscribeToTenantUpdates = (tenantId, onUpdateCallback) => {
  if (!tenantId) return null;
  
  try {
    const echo = getEcho();
    const channelName = `tenant.${tenantId}`;
    
    return echo.channel(channelName).listen('.store.updated', (event) => {
      if (typeof onUpdateCallback === 'function') {
        onUpdateCallback(event);
      }
    });
  } catch (err) {
    console.warn('WebSocket connection error:', err);
    return null;
  }
};
