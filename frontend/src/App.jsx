/**
 * App.jsx
 * Componente raíz de la aplicación
 */

import { useAuth } from './context/AuthContext';
import Login from './components/auth/Login';
import ChatApp from './components/chat/ChatApp';
import './styles/main.scss';

function App() {
  const { isAuthenticated, loading } = useAuth();

  if (loading) {
    return (
      <div className="loading-container">
        <div className="loading-spinner"></div>
        <p>Cargando...</p>
      </div>
    );
  }

  return isAuthenticated ? <ChatApp /> : <Login />;
}

export default App;
