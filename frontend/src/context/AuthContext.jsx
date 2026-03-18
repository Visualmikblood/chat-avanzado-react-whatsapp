 /**
 * Auth Context
 * Maneja el estado de autenticación global
 */

import { createContext, useContext, useState, useEffect } from 'react';
import { authAPI } from '../services/api';

const AuthContext = createContext();

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe usarse dentro de AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [loading, setLoading] = useState(true);

  // Verificar si hay sesión al cargar la app
  useEffect(() => {
    let isMounted = true;

    const checkLoggedIn = async () => {
      const token = localStorage.getItem('token');
      
      if (token) {
        try {
          // Validar token pidiendo los datos reales al servidor
          const response = await authAPI.me();
          if (isMounted) {
            setUser(response.data.data);
            setIsAuthenticated(true);
          }
        } catch (error) {
          console.error('Sesión inválida o expirada');
          // Solo borrar si es error de autenticación (401), no si se cae el server
          if (error.response && error.response.status === 401) {
            localStorage.removeItem('token');
          }
        }
      }
      
      if (isMounted) setLoading(false);
    };

    checkLoggedIn();

    return () => { isMounted = false; };
  }, []);

  const register = async (userData) => {
    try {
      const response = await authAPI.register(userData);
      const { user, token } = response.data.data;

      localStorage.setItem('token', token);
      setUser(user);
      setIsAuthenticated(true);

      return { success: true };
    } catch (error) {
      const message = error.response?.data?.message || 'Error al registrar usuario';
      return { success: false, error: message };
    }
  };

  const login = async (credentials) => {
    try {
      const response = await authAPI.login(credentials);
      const { user, token } = response.data.data;

      localStorage.setItem('token', token);
      setUser(user);
      setIsAuthenticated(true);

      return { success: true };
    } catch (error) {
      const message = error.response?.data?.message || 'Error al iniciar sesión';
      return { success: false, error: message };
    }
  };

  const logout = async () => {
    try {
      await authAPI.logout();
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    } finally {
      localStorage.removeItem('token');
      setUser(null);
      setIsAuthenticated(false);
    }
  };

  const updateUserProfile = async (profileData) => {
    try {
      const response = await authAPI.updateProfile(profileData);
      const updatedUser = response.data.data;
      
      setUser(updatedUser);
      
      return { success: true };
    } catch (error) {
      console.error('Error al actualizar perfil:', error);
      throw error;
    }
  };

  const value = {
    user,
    loading,
    register,
    login,
    logout,
    updateUserProfile,
    isAuthenticated
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
