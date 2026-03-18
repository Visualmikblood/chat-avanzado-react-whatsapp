/**
 * API Service
 * Cliente Axios configurado para comunicarse con el backend PHP
 */

import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

// Crear instancia de Axios
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Interceptor para agregar el token JWT a cada request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar errores de respuesta
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token inválido o expirado
      localStorage.removeItem('token');
      window.location.href = '/';
    }
    return Promise.reject(error);
  }
);

// ==================== AUTH ENDPOINTS ====================

export const authAPI = {
  register: (userData) => api.post('/auth/register', userData),
  login: (credentials) => api.post('/auth/login', credentials),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
  updateProfile: (profileData) => api.put('/auth/profile', profileData)
};

// ==================== CONVERSATION ENDPOINTS ====================

export const conversationAPI = {
  getAll: () => api.get('/conversations'),
  create: (userId) => api.post('/conversations', { user_id: userId }),
  searchUsers: (query) => api.get(`/conversations/search?q=${query}`)
};

// ==================== MESSAGE ENDPOINTS ====================

export const messageAPI = {
  send: (messageData) => {
    // Si es FormData, dejar que axios configure el Content-Type
    if (messageData instanceof FormData) {
      return api.post('/messages', messageData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    }
    return api.post('/messages', messageData);
  },
  getMessages: (conversationId, limit = 50, offset = 0) => 
    api.get(`/messages/${conversationId}?limit=${limit}&offset=${offset}`),
  update: (messageId, content) => api.put(`/messages/${messageId}`, { content }),
  delete: (messageId) => api.delete(`/messages/${messageId}`)
};

export default api;
