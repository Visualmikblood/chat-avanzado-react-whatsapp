/**
 * MessageBubble Component
 * Burbuja de mensaje individual
 */

const MessageBubble = ({ message, isOwnMessage }) => {
  // API URL base para medios
  const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

  // Formatear hora del mensaje
  const formatTime = (timestamp) => {
    try {
      const date = new Date(timestamp);
      return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    } catch (error) {
      return '';
    }
  };

  // Determinar el estado del mensaje
  const getMessageStatus = () => {
    if (!isOwnMessage) return null; // Los mensajes recibidos no tienen estado
    
    if (message.read_at) {
      return 'read'; // Doble check azul
    } else if (message.delivered_at) {
      return 'delivered'; // Doble check gris
    } else {
      return 'sent'; // Check simple gris
    }
  };

  const status = getMessageStatus();

  return (
    <div className={`message-bubble ${isOwnMessage ? 'own-message' : 'other-message'}`}>
      <div className="message-content">
        {!isOwnMessage && (
          <span className="message-sender">{message.sender_username}</span>
        )}
        
        {/* Renderizado condicional según el tipo de mensaje */}
        {message.type === 'image' ? (
          <div className="message-image">
            <img 
              src={message.content.startsWith('http') ? message.content : `${API_URL}${message.content}`} 
              alt="Imagen compartida" 
              onClick={() => window.open(message.content.startsWith('http') ? message.content : `${API_URL}${message.content}`, '_blank')}
            />
            {message.content !== '/' && <p className="image-caption">{/* Opcional: descripción si existiera */}</p>}
          </div>
        ) : message.type === 'file' ? (
          <div className="message-file">
            <a 
              href={message.content.startsWith('http') ? message.content : `${API_URL}${message.content}`}
              target="_blank" 
              rel="noreferrer"
              className="file-link"
            >
              <div className="file-icon">
                <svg viewBox="0 0 24 24" width="24" height="24">
                   <path fill="currentColor" d="M13.5 2H6c-1.103 0-2 .897-2 2v16c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2V8.5L13.5 2zM6 20V4h7v5h5v11H6z"></path>
                </svg>
              </div>
              <span className="file-name">{message.content.split('/').pop()}</span>
            </a>
          </div>
        ) : (
           <p className="message-text">
             {message.content}
             {/* Meta data inside text for layout */}
             <span className="message-meta">
                <span className="message-time">{formatTime(message.created_at)}</span>
                {isOwnMessage && status && (
                  <span className={`message-status ${status}`}>
                    {status === 'sent' && (
                      <svg viewBox="0 0 16 15" width="16" height="15">
                        <path fill="currentColor" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512z"></path>
                      </svg>
                    )}
                    {(status === 'delivered' || status === 'read') && (
                      <svg viewBox="0 0 16 15" width="16" height="15">
                        <path fill="currentColor" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path>
                      </svg>
                    )}
                  </span>
                )}
             </span>
           </p>
        )}

        {/* Time for non-text messages */}
        {message.type !== 'text' && (
           <div className="message-meta media-meta">
              <span className="message-time">{formatTime(message.created_at)}</span>
              {isOwnMessage && status && (
                <span className={`message-status ${status}`}>
                   {/* SVGs repeated for simplicity or refactor into component */}
                   {status === 'sent' && (
                      <svg viewBox="0 0 16 15" width="16" height="15"><path fill="currentColor" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512z"></path></svg>
                    )}
                    {(status === 'delivered' || status === 'read') && (
                      <svg viewBox="0 0 16 15" width="16" height="15"><path fill="currentColor" d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.879a.32.32 0 0 1-.484.033l-.358-.325a.319.319 0 0 0-.484.032l-.378.483a.418.418 0 0 0 .036.541l1.32 1.266c.143.14.361.125.484-.033l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.879a.32.32 0 0 1-.484.033L1.891 7.769a.366.366 0 0 0-.515.006l-.423.433a.364.364 0 0 0 .006.514l3.258 3.185c.143.14.361.125.484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"></path></svg>
                    )}
                </span>
              )}
           </div>
        )}
        
        {!!message.is_edited && (
          <span className="edited-label">editado</span>
        )}
      </div>
    </div>
  );
};

export default MessageBubble;
