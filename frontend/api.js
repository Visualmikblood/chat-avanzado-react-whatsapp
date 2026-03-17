import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Crear una instancia de Axios
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor para añadir el token a todas las peticiones automáticamente
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});

// Definir endpoints
export const authAPI = {
  login: (credentials) => api.post('/auth/login', credentials),
  register: (userData) => api.post('/auth/register', userData),
  me: () => api.get('/auth/me'),
  logout: () => api.post('/auth/logout'),
};

export const conversationAPI = {
  getAll: () => api.get('/conversations'),
  create: (userId) => api.post('/conversations', { user_id: userId }),
  searchUsers: (query) => api.get(`/conversations/search?q=${query}`),
};

export const messageAPI = {
  getMessages: (conversationId) => api.get(`/messages/${conversationId}`),
  send: (payload) => {
    if (payload instanceof FormData) {
      return api.post('/messages', payload, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }
    return api.post('/messages', payload);
  },
  delete: (messageId) => api.delete(`/messages/${messageId}`),
};

export default api;