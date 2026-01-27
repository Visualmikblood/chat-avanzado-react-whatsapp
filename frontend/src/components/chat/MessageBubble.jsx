/**
 * MessageBubble Component
 * Burbuja de mensaje individual
 */

import { useState, useRef, useEffect } from 'react';

const MessageBubble = ({ message, isOwnMessage, onDelete, highlightText }) => {
  // API URL base para medios
  const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';
  const [showOptions, setShowOptions] = useState(false);
  const optionsRef = useRef(null);

  // Cerrar menú al hacer clic fuera
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (optionsRef.current && !optionsRef.current.contains(event.target)) {
        setShowOptions(false);
      }
    };

    if (showOptions) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [showOptions]);

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
    // Si hay estado explícito (como 'sending' o 'error'), usarlo
    if (message.status) return message.status;
    
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

  // Función para resaltar texto
  const renderContent = () => {
    if (message.type !== 'text' || !highlightText || !highlightText.trim()) {
      return message.content;
    }

    const parts = message.content.split(new RegExp(`(${highlightText})`, 'gi'));
    return parts.map((part, index) => 
      part.toLowerCase() === highlightText.toLowerCase() ? (
        <span key={index} className="highlight-text">{part}</span>
      ) : (
        part
      )
    );
  };

  return (
    <div className={`message-bubble ${isOwnMessage ? 'own-message' : 'other-message'}`}>
      <div className="message-content">
        
        {/* Options UI - Only for own messages */}
        {isOwnMessage && (
           <div className="message-options" ref={optionsRef}>
              <button 
                className={`message-options-btn ${showOptions ? 'active' : ''}`}
                onClick={(e) => {
                   e.stopPropagation(); 
                   setShowOptions(!showOptions);
                }}
              >
                 <svg viewBox="0 0 19 20" width="19" height="20" className="">
                    <path fill="currentColor" d="M3.8 6.7l5.7 5.7 5.7-5.7 1.6 1.6-7.3 7.2-7.3-7.2 1.6-1.6z"></path>
                 </svg>
              </button>
              
              {showOptions && (
                 <div className="options-menu">
                    <button 
                       className="delete-option"
                       onClick={() => {
                          setShowOptions(false);
                          if(onDelete) onDelete(message.id);
                       }}
                    >
                       Eliminar mensaje
                    </button>
                 </div>
              )}
           </div>
        )}
      
        {!isOwnMessage && (
          <span className="message-sender">{message.sender_username}</span>
        )}
        
        {/* Renderizado condicional según el tipo de mensaje */}
        {message.type === 'image' ? (
          <div className="message-image">
            <img 
              src={message.content.startsWith('http') || message.content.startsWith('blob:') ? message.content : `${API_URL}${message.content}`} 
              alt="Imagen compartida" 
              onClick={() => window.open(message.content.startsWith('http') || message.content.startsWith('blob:') ? message.content : `${API_URL}${message.content}`, '_blank')}
            />
            {message.content !== '/' && <p className="image-caption">{/* Opcional: descripción si existiera */}</p>}
          </div>
        ) : message.type === 'file' ? (
          <div className="message-file">
            <a 
              href={message.content.startsWith('http') || message.content.startsWith('blob:') ? message.content : `${API_URL}${message.content}`}
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
             {renderContent()}
             {/* Meta data inside text for layout */}
             <span className="message-meta">
                <span className="message-time">{formatTime(message.created_at)}</span>
                {isOwnMessage && status && (
                  <span className={`message-status ${status}`}>
                    {status === 'sending' && (
                       <svg viewBox="0 0 24 24" width="16" height="16">
                          <path fill="currentColor" d="M12 20c4.4 0 8-3.6 8-8s-3.6-8-8-8-8 3.6-8 8 3.6 8 8 8zm0-18c5.5 0 10 4.5 10 10s-4.5 10-10 10S2 17.5 2 12 6.5 2 12 2zm1 5h-2v6h6v-2h-4V7z"></path>
                       </svg>
                    )}
                    {status === 'error' && (
                       <svg viewBox="0 0 24 24" width="16" height="16" style={{color: '#f44336'}}>
                          <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path>
                       </svg>
                    )}
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
