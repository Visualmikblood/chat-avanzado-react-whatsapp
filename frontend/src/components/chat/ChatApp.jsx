/**
 * ChatApp Component
 * Componente principal del chat estilo WhatsApp
 */

import { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useChat } from '../../context/ChatContext';
import Sidebar from './Sidebar';
import ChatWindow from './ChatWindow';
import NewChatModal from './NewChatModal';
import './Chat.scss';

const ChatApp = () => {
  const { user, logout } = useAuth();
  const { activeConversation, closeConversation } = useChat();
  const [showNewChat, setShowNewChat] = useState(false);

  return (
    <div className="chat-app">
      {/* Background pattern de WhatsApp */}
      <div className="chat-background"></div>

      {/* Contenedor principal */}
      <div className={`chat-container ${activeConversation ? 'conversation-active' : ''}`}>
        {/* Sidebar con lista de conversaciones */}
        <Sidebar 
          user={user}
          onLogout={logout}
          onNewChat={() => setShowNewChat(true)}
        />

        {/* Ventana de chat */}
        {activeConversation ? (
          <ChatWindow 
            conversation={activeConversation} 
            onBackToList={closeConversation}
          />
        ) : (
          <div className="chat-welcome">
            <div className="welcome-content">
              <div className="welcome-icon">
                <svg viewBox="0 0 303 172" width="360" height="205">
                  <path fill="#EFEAE2" d="M72.3 151.9c-1.1.6-2.4-.3-2.4-1.5v-30.1c0-.8-.7-1.5-1.5-1.5H30.6c-1.8 0-3.3-1.5-3.3-3.3V31.6c0-1.8 1.5-3.3 3.3-3.3h241c1.8 0 3.3 1.5 3.3 3.3v83.9c0 1.8-1.5 3.3-3.3 3.3h-91.4L72.3 151.9z"></path>
                </svg>
              </div>
              <h2>WhatsApp Web</h2>
              <p>Envía y recibe mensajes sin mantener tu teléfono conectado.</p>
              <p className="welcome-footer">🔒 Tus mensajes personales están cifrados de extremo a extremo</p>
            </div>
          </div>
        )}
      </div>

      {/* Modal para crear nuevo chat */}
      {showNewChat && (
        <NewChatModal onClose={() => setShowNewChat(false)} />
      )}
    </div>
  );
};

export default ChatApp;
