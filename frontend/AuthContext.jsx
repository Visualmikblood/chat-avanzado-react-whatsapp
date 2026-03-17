import { createContext, useState, useContext, useEffect } from 'react';
import { authAPI, default as api } from '../services/api';

const AuthContext = createContext();

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe usarse dentro de un AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [loading, setLoading] = useState(true); // Empezar en true para verificar el token

  // Efecto para verificar si hay un token al cargar la app
  useEffect(() => {
    const checkLoggedIn = async () => {
      const token = localStorage.getItem('token');
      if (token) {
        try {
          // Validar token pidiendo los datos del usuario
          const response = await authAPI.me();
          setUser(response.data.data);
          setIsAuthenticated(true);
        } catch (error) {
          console.error('Sesión inválida o expirada, limpiando token.');
          localStorage.removeItem('token');
        }
      }
      setLoading(false);
    };

    checkLoggedIn();
  }, []);

  const login = async (credentials) => {
    try {
      const response = await authAPI.login(credentials);
      const { token, user } = response.data.data;

      localStorage.setItem('token', token);
      setUser(user);
      setIsAuthenticated(true);
      
      return { success: true };
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Error al iniciar sesión' };
    }
  };

  const register = async (userData) => {
    try {
      const response = await authAPI.register(userData);
      const { token, user } = response.data.data;

      localStorage.setItem('token', token);
      setUser(user);
      setIsAuthenticated(true);

      return { success: true };
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Error al registrarse' };
    }
  };

  const logout = async () => {
    await authAPI.logout().catch(err => console.error("Fallo al notificar logout al backend", err));
    localStorage.removeItem('token');
    setUser(null);
    setIsAuthenticated(false);
    delete api.defaults.headers.common['Authorization'];
  };

  const value = { user, isAuthenticated, loading, login, register, logout, setUser };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};