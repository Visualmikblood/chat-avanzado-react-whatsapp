/**
 * Sidebar Component
 * Barra lateral con lista de conversaciones
 */

import { useChat } from '../../context/ChatContext';
import ConversationItem from './ConversationItem';
import ProfileModal from '../profile/ProfileModal';
import { useState } from 'react';

const Sidebar = ({ user, onLogout, onNewChat }) => {
  const { conversations, selectConversation, activeConversation } = useChat();
  const [showProfile, setShowProfile] = useState(false);

  return (
    <div className="sidebar">
      {/* Header del sidebar */}
      <div className="sidebar-header">
        <div className="user-avatar" onClick={() => setShowProfile(true)} style={{ cursor: 'pointer' }}>
          {user?.avatar_url ? (
            <img src={user.avatar_url} alt={user.username} />
          ) : (
            <div className="avatar-placeholder">
              {user?.username?.charAt(0).toUpperCase()}
            </div>
          )}
        </div>

        <div className="sidebar-actions">
          <button className="icon-button" title="Nueva conversación" onClick={onNewChat}>
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M19.005 3.175H4.674C3.642 3.175 3 3.789 3 4.821V21.02l3.544-3.514h12.461c1.033 0 2.064-1.06 2.064-2.093V4.821c-.001-1.032-1.032-1.646-2.064-1.646zm-4.989 9.869H7.041V11.1h6.975v1.944zm3-4H7.041V7.1h9.975v1.944z"></path>
            </svg>
          </button>

          <button className="icon-button" title="Menú">
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path>
            </svg>
          </button>

          <button className="icon-button logout-button" title="Cerrar sesión" onClick={onLogout}>
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"></path>
            </svg>
          </button>
        </div>
      </div>

      {/* Buscador (opcional - por ahora solo visual) */}
      <div className="sidebar-search">
        <div className="search-container">
          <svg viewBox="0 0 24 24" width="24" height="24" className="search-icon">
            <path fill="currentColor" d="M15.009 13.805h-.636l-.22-.219a5.184 5.184 0 0 0 1.256-3.386 5.207 5.207 0 1 0-5.207 5.208 5.183 5.183 0 0 0 3.385-1.255l.221.22v.635l4.004 3.999 1.194-1.195-3.997-4.007zm-4.808 0a3.605 3.605 0 1 1 0-7.21 3.605 3.605 0 0 1 0 7.21z"></path>
          </svg>
          <input type="text" placeholder="Buscar o empezar un nuevo chat" />
        </div>
      </div>

      {/* Lista de conversaciones */}
      <div className="conversations-list">
        {conversations.length === 0 ? (
          <div className="no-conversations">
            <p>No tienes conversaciones aún</p>
            <button onClick={onNewChat} className="start-chat-btn">
              Iniciar un chat
            </button>
          </div>
        ) : (
          conversations.map((conversation) => (
            <ConversationItem
              key={conversation.id}
              conversation={conversation}
              isActive={activeConversation?.id === conversation.id}
              onClick={() => selectConversation(conversation)}
            />
          ))
        )}
      </div>

      {/* Modal de perfil */}
      {showProfile && (
        <ProfileModal onClose={() => setShowProfile(false)} />
      )}
    </div>
  );
};

export default Sidebar;
