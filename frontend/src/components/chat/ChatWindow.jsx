import { useState, useEffect, useRef } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useChat } from '../../context/ChatContext';
import MessageBubble from './MessageBubble';
import EmojiPicker from './EmojiPicker';
import AttachmentMenu from './AttachmentMenu';

const ChatWindow = ({ conversation, onBackToList }) => {
  const { user } = useAuth();
  const { messages, sendMessage } = useChat();
  const [messageText, setMessageText] = useState('');
  const [sending, setSending] = useState(false);
  const [showEmojiPicker, setShowEmojiPicker] = useState(false);
  const [showAttachMenu, setShowAttachMenu] = useState(false);
  const messagesEndRef = useRef(null);
  const emojiPickerRef = useRef(null);
  const emojiButtonRef = useRef(null);
  const attachMenuRef = useRef(null);
  const attachButtonRef = useRef(null);
  const fileInputRef = useRef(null);

  // Scroll automático al último mensaje
  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  // Cerrar menús al hacer clic fuera
  useEffect(() => {
    const handleClickOutside = (event) => {
      // Emoji Picker
      if (
        showEmojiPicker &&
        emojiPickerRef.current &&
        !emojiPickerRef.current.contains(event.target) &&
        emojiButtonRef.current &&
        !emojiButtonRef.current.contains(event.target)
      ) {
        setShowEmojiPicker(false);
      }
      
      // Attachment Menu
      if (
        showAttachMenu &&
        attachMenuRef.current &&
        !attachMenuRef.current.contains(event.target) &&
        attachButtonRef.current &&
        !attachButtonRef.current.contains(event.target)
      ) {
        setShowAttachMenu(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [showEmojiPicker, showAttachMenu]);

  const handleSend = async (e) => {
    e.preventDefault();
    
    if (!messageText.trim() || sending) return;

    setSending(true);
    // Send text message (default type 'text')
    const result = await sendMessage(messageText);
    
    if (result.success) {
      setMessageText('');
    }
    
    setSending(false);
  };

  // Handle file selection
  const handleFileSelect = async (e) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      
      // Determinar tipo basado en mimetype
      let type = 'file';
      if (file.type.startsWith('image/')) {
        type = 'image';
      }
      
      setSending(true);
      setShowAttachMenu(false);
      
      // Send message with file (content is filename/description for now, but backend overwrites or uses it)
      // Actually backend expects 'content' as string, but sendMessage handles FormData creation.
      // We pass the file object as the 3rd argument.
      // Content can be the filename or empty (if we want backend to set it).
      // Let's pass the filename as content strictly for description or fallback.
      const result = await sendMessage(file.name, type, file);
      
      if (refreshFileInput) {
          e.target.value = ''; // Reset input
      }
      
      setSending(false);
    }
  };

  const handleAttachOptionClick = (type) => {
    if (fileInputRef.current) {
        // Accept only images if type is image
        if (type === 'image') {
            fileInputRef.current.accept = "image/*";
        } else {
            fileInputRef.current.accept = "*";
        }
        fileInputRef.current.click();
    }
  };
  
  // Helper to reset file input
  const refreshFileInput = () => {
      if(fileInputRef.current) fileInputRef.current.value = "";
  }

  const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend(e);
    }
  };

  const handleEmojiSelect = (emoji) => {
    setMessageText((prev) => prev + emoji);
    setShowEmojiPicker(false);
  };

  const toggleEmojiPicker = () => {
    setShowEmojiPicker((prev) => !prev);
  };

  // Obtener información del contacto
  const contact = conversation.other_user || conversation.participants?.[0];
  const contactName = contact?.username || conversation.name || 'Grupo';
  const isOnline = contact?.status === 'online';

  return (
    <div className="chat-window">
      {/* Header del chat */}
      <div className="chat-header">
        <div className="chat-header-content">
          {/* Back button for mobile */}
          {onBackToList && (
            <button className="icon-button back-button mobile-only" onClick={onBackToList}>
              <svg viewBox="0 0 24 24" width="24" height="24">
                <path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"></path>
              </svg>
            </button>
          )}
          
          <div className="contact-avatar">
            {contact?.avatar_url ? (
              <img src={contact.avatar_url} alt={contactName} />
            ) : (
              <div className="avatar-placeholder">
                {contactName.charAt(0).toUpperCase()}
              </div>
            )}
          </div>

          <div className="contact-info">
            <h3>{contactName}</h3>
            <span className={`status ${isOnline ? 'online' : ''}`}>
              {isOnline ? 'en línea' : 'desconectado'}
            </span>
          </div>
        </div>

        <div className="chat-header-actions">
          <button className="icon-button">
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M15.9 14.3H15l-.3-.3c1-1.1 1.6-2.7 1.6-4.3 0-3.7-3-6.7-6.7-6.7S3 6 3 9.7s3 6.7 6.7 6.7c1.6 0 3.2-.6 4.3-1.6l.3.3v.8l5.1 5.1 1.5-1.5-5-5.2zm-6.2 0c-2.6 0-4.6-2.1-4.6-4.6s2.1-4.6 4.6-4.6 4.6 2.1 4.6 4.6-2 4.6-4.6 4.6z"></path>
            </svg>
          </button>
          <button className="icon-button">
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M12 7a2 2 0 1 0-.001-4.001A2 2 0 0 0 12 7zm0 2a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 9zm0 6a2 2 0 1 0-.001 3.999A2 2 0 0 0 12 15z"></path>
            </svg>
          </button>
        </div>
      </div>

      {/* Mensajes */}
      <div className="chat-messages">
        <div className="messages-container">
          {messages.length === 0 ? (
            <div className="no-messages">
              <p>No hay mensajes aún. ¡Envía el primero! 👋</p>
            </div>
          ) : (
            messages.map((message) => (
              <MessageBubble
                key={message.id}
                message={message}
                isOwnMessage={message.sender_id === user?.id}
              />
            ))
          )}
          <div ref={messagesEndRef} />
        </div>
      </div>

      {/* Input para escribir mensajes */}
      <div className="chat-input">
        <button 
          ref={emojiButtonRef}
          className={`icon-button emoji-button ${showEmojiPicker ? 'active' : ''}`}
          onClick={toggleEmojiPicker}
        >
          <svg viewBox="0 0 24 24" width="24" height="24">
            <path fill="currentColor" d="M9.153 11.603c.795 0 1.439-.879 1.439-1.962s-.644-1.962-1.439-1.962-1.439.879-1.439 1.962.644 1.962 1.439 1.962zm-3.204 1.362c-.026-.307-.131 5.218 6.063 5.551 6.066-.25 6.066-5.551 6.066-5.551-6.078 1.416-12.129 0-12.129 0zm11.363 1.108s-.669 1.959-5.051 1.959c-3.505 0-5.388-1.164-5.607-1.959 0 0 5.912 1.055 10.658 0zM11.804 1.011C5.609 1.011.978 6.033.978 12.228s4.826 10.761 11.021 10.761S23.02 18.423 23.02 12.228c.001-6.195-5.021-11.217-11.216-11.217zM12 21.354c-5.273 0-9.381-3.886-9.381-9.159s3.942-9.548 9.215-9.548 9.548 4.275 9.548 9.548c-.001 5.272-4.109 9.159-9.382 9.159zm3.108-9.751c.795 0 1.439-.879 1.439-1.962s-.644-1.962-1.439-1.962-1.439.879-1.439 1.962.644 1.962 1.439 1.962z"></path>
          </svg>
        </button>

        {/* Emoji Picker */}
        {showEmojiPicker && (
          <div ref={emojiPickerRef}>
            <EmojiPicker 
              onEmojiSelect={handleEmojiSelect}
              onClose={() => setShowEmojiPicker(false)}
            />
          </div>
        )}

        {/* Attachment Menu */}
        {showAttachMenu && (
          <div ref={attachMenuRef}>
             <AttachmentMenu 
                onSelect={handleAttachOptionClick}
                onClose={() => setShowAttachMenu(false)}
             />
          </div>
        )}
        
        {/* Hidden File Input */}
        <input 
            type="file" 
            ref={fileInputRef} 
            style={{ display: 'none' }} 
            onChange={handleFileSelect}
        />

        <button 
           ref={attachButtonRef} 
           className={`icon-button ${showAttachMenu ? 'active' : ''}`}
           onClick={() => setShowAttachMenu(!showAttachMenu)}
        >
          <svg viewBox="0 0 24 24" width="24" height="24">
            <path fill="currentColor" d="M1.816 15.556v.002c0 1.502.584 2.912 1.646 3.972s2.472 1.647 3.974 1.647a5.58 5.58 0 0 0 3.972-1.645l9.547-9.548c.769-.768 1.147-1.767 1.058-2.817-.079-.968-.548-1.927-1.319-2.698-1.594-1.592-4.068-1.711-5.517-.262l-7.916 7.915c-.881.881-.792 2.25.214 3.261.959.958 2.423 1.053 3.263.215l5.511-5.512c.28-.28.267-.722.053-.936l-.244-.244c-.191-.191-.567-.349-.957.04l-5.506 5.506c-.18.18-.635.127-.976-.214-.098-.097-.576-.613-.213-.973l7.915-7.917c.818-.817 2.267-.699 3.23.262.5.501.802 1.1.849 1.685.051.573-.156 1.111-.589 1.543l-9.547 9.549a3.97 3.97 0 0 1-2.829 1.171 3.975 3.975 0 0 1-2.83-1.173 3.973 3.973 0 0 1-1.172-2.828c0-1.071.415-2.076 1.172-2.83l7.209-7.211c.157-.157.264-.579.028-.814L11.5 4.36a.572.572 0 0 0-.834.018l-7.205 7.207a5.577 5.577 0 0 0-1.645 3.971z"></path>
          </svg>
        </button>

        <form onSubmit={handleSend} className="input-form">
          <input
            type="text"
            placeholder="Escribe un mensaje aquí"
            value={messageText}
            onChange={(e) => setMessageText(e.target.value)}
            onKeyPress={handleKeyPress}
            disabled={sending}
          />
        </form>

        <button 
          className="send-button" 
          onClick={handleSend}
          disabled={!messageText.trim() || sending}
        >
          <svg viewBox="0 0 24 24" width="24" height="24">
            <path fill="currentColor" d="M1.101 21.757L23.8 12.028 1.101 2.3l.011 7.912 13.623 1.816-13.623 1.817-.011 7.912z"></path>
          </svg>
        </button>
      </div>
    </div>
  );
};

export default ChatWindow;
