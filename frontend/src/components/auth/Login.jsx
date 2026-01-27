/**
 * Login Component
 * Formulario de inicio de sesión y registro estilo WhatsApp
 */

import { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import './Auth.scss';

const Login = () => {
  const { login, register } = useAuth();
  const [isRegister, setIsRegister] = useState(false);
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
    setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    let result;
    
    if (isRegister) {
      // Validaciones para registro
      if (!formData.username || !formData.email || !formData.password) {
        setError('Todos los campos son requeridos');
        setLoading(false);
        return;
      }
      
      if (formData.password.length < 6) {
        setError('La contraseña debe tener al menos 6 caracteres');
        setLoading(false);
        return;
      }

      result = await register(formData);
    } else {
      // Validaciones para login
      if (!formData.email || !formData.password) {
        setError('Email y contraseña son requeridos');
        setLoading(false);
        return;
      }

      result = await login({
        email: formData.email,
        password: formData.password
      });
    }

    setLoading(false);

    if (!result.success) {
      setError(result.error);
    }
  };

  const toggleMode = () => {
    setIsRegister(!isRegister);
    setError('');
    setFormData({
      username: '',
      email: '',
      password: ''
    });
  };

  return (
    <div className="auth-container">
      <div className="auth-box">
        <div className="auth-header">
          <div className="whatsapp-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm.029 18.88c-1.161 0-2.305-.292-3.318-.844l-3.677.964.984-3.595c-.607-1.052-.927-2.246-.926-3.468.001-3.825 3.113-6.937 6.937-6.937 1.856.001 3.598.723 4.907 2.034 1.31 1.311 2.031 3.054 2.03 4.908-.001 3.825-3.113 6.938-6.937 6.938z"/>
            </svg>
          </div>
          <h1>WHATSAPP WEB</h1>
          <p className="auth-subtitle">
            {isRegister ? 'Crear cuenta nueva' : 'Inicia sesión para continuar'}
          </p>
        </div>

        <form onSubmit={handleSubmit} className="auth-form">
          {error && (
            <div className="auth-error">
              <span>⚠️</span>
              <p>{error}</p>
            </div>
          )}

          {isRegister && (
            <div className="form-group">
              <input
                type="text"
                name="username"
                placeholder="Nombre de usuario"
                value={formData.username}
                onChange={handleChange}
                disabled={loading}
                autoComplete="username"
              />
            </div>
          )}

          <div className="form-group">
            <input
              type="email"
              name="email"
              placeholder="Email"
              value={formData.email}
              onChange={handleChange}
              disabled={loading}
              autoComplete="email"
            />
          </div>

          <div className="form-group">
            <input
              type="password"
              name="password"
              placeholder="Contraseña"
              value={formData.password}
              onChange={handleChange}
              disabled={loading}
              autoComplete="current-password"
            />
          </div>

          <button type="submit" className="auth-button" disabled={loading}>
            {loading ? (
              <span className="loading-spinner"></span>
            ) : (
              isRegister ? 'Registrarse' : 'Iniciar Sesión'
            )}
          </button>

          <div className="auth-toggle">
            <p>
              {isRegister ? '¿Ya tienes cuenta?' : '¿No tienes cuenta?'}
              <button type="button" onClick={toggleMode} disabled={loading}>
                {isRegister ? 'Inicia sesión' : 'Regístrate'}
              </button>
            </p>
          </div>
        </form>

        <div className="auth-footer">
          <p>🔒 Tus mensajes están cifrados de extremo a extremo</p>
        </div>
      </div>
    </div>
  );
};

export default Login;
