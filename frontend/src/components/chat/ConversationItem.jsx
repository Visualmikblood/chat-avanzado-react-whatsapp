/**
 * ConversationItem Component
 * Item individual en la lista de conversaciones
 */

import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';

const ConversationItem = ({ conversation, isActive, onClick }) => {
  // Obtener información del contacto
  const contact = conversation.other_user || conversation.participants?.[0];
  const contactName = contact?.username || conversation.name || 'Grupo';
  const lastMessage = conversation.last_message || 'Sin mensajes';
  
  // Formatear fecha
  const formatTime = (timestamp) => {
    if (!timestamp) return '';
    
    try {
      const date = new Date(timestamp);
      const now = new Date();
      const diffInHours = (now - date) / (1000 * 60 * 60);
      
      if (diffInHours < 24) {
        // Si es hoy, mostrar hora
        return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
      } else if (diffInHours < 48) {
        return 'Ayer';
      } else {
        // Más de 2 días, mostrar fecha
        return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: '2-digit' });
      }
    } catch (error) {
      return '';
    }
  };

  return (
    <div 
      className={`conversation-item ${isActive ? 'active' : ''}`}
      onClick={onClick}
    >
      <div className="conversation-avatar">
        {contact?.avatar_url ? (
          <img src={contact.avatar_url} alt={contactName} />
        ) : (
          <div className="avatar-placeholder">
            {contactName.charAt(0).toUpperCase()}
          </div>
        )}
      </div>

      <div className="conversation-details">
        <div className="conversation-header">
          <h3 className="conversation-name">{contactName}</h3>
          <span className="conversation-time">
            {formatTime(conversation.last_message_time || conversation.updated_at)}
          </span>
        </div>
        
        <div className="conversation-preview">
          <p className="last-message">{lastMessage}</p>
          {conversation.unread_count > 0 && (
            <span className="unread-badge">{conversation.unread_count}</span>
          )}
        </div>
      </div>
    </div>
  );
};

export default ConversationItem;
