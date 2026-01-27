/**
 * Pusher Service
 * Configuración del cliente Pusher para eventos en tiempo real
 */

import Pusher from 'pusher-js';

const PUSHER_KEY = import.meta.env.VITE_PUSHER_APP_KEY;
const PUSHER_CLUSTER = import.meta.env.VITE_PUSHER_CLUSTER || 'eu';

// Inicializar Pusher
const pusher = new Pusher(PUSHER_KEY, {
  cluster: PUSHER_CLUSTER,
  encrypted: true
});

// Habilitar logs en desarrollo
if (import.meta.env.DEV) {
  Pusher.logToConsole = true;
}

/**
 * Suscribirse a un canal de conversación
 * @param {number} conversationId 
 * @param {function} callback Función a ejecutar cuando llegue un mensaje
 * @returns {Object} Canal de Pusher
 */
export const subscribeToConversation = (conversationId, callback) => {
  const channelName = `conversation-${conversationId}`;
  const channel = pusher.subscribe(channelName);
  
  channel.bind('new-message', (data) => {
    console.log('📨 Nuevo mensaje recibido:', data);
    callback(data);
  });
  
  return channel;
};

/**
 * Desuscribirse de un canal
 * @param {number} conversationId 
 */
export const unsubscribeFromConversation = (conversationId) => {
  const channelName = `conversation-${conversationId}`;
  pusher.unsubscribe(channelName);
};

/**
 * Desconectar Pusher completamente
 */
export const disconnectPusher = () => {
  pusher.disconnect();
};

export default pusher;
