/**
 * NewChatModal Component
 * Modal para crear un nuevo chat
 */

import { useState } from 'react';
import { useChat } from '../../context/ChatContext';

const NewChatModal = ({ onClose }) => {
  const { searchUsers, createConversation } = useChat();
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [searching, setSearching] = useState(false);

  const handleSearch = async (e) => {
    const value = e.target.value;
    setQuery(value);

    if (value.length < 2) {
      setResults([]);
      return;
    }

    setSearching(true);
    const users = await searchUsers(value);
    setResults(users);
    setSearching(false);
  };

  const handleSelectUser = async (userId) => {
    const result = await createConversation(userId);
    if (result.success) {
      onClose();
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content new-chat-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>Nuevo chat</h2>
          <button className="close-button" onClick={onClose}>
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M19.6 4.4L12 12l-7.6-7.6L2.8 5.9 10.4 13.5 2.8 21.1l1.6 1.5L12 15l7.6 7.6 1.6-1.5-7.6-7.6 7.6-7.6z"></path>
            </svg>
          </button>
        </div>

        <div className="modal-search">
          <input
            type="text"
            placeholder="Buscar usuarios..."
            value={query}
            onChange={handleSearch}
            autoFocus
          />
        </div>

        <div className="modal-body">
          {searching ? (
            <div className="search-loading">Buscando...</div>
          ) : results.length > 0 ? (
            <div className="users-list">
              {results.map((user) => (
                <div
                  key={user.id}
                  className="user-item"
                  onClick={() => handleSelectUser(user.id)}
                >
                  <div className="user-avatar">
                    {user.avatar_url ? (
                      <img src={user.avatar_url} alt={user.username} />
                    ) : (
                      <div className="avatar-placeholder">
                        {user.username.charAt(0).toUpperCase()}
                      </div>
                    )}
                  </div>
                  <div className="user-info">
                    <h4>{user.username}</h4>
                    <p>{user.email}</p>
                  </div>
                </div>
              ))}
            </div>
          ) : query.length >= 2 ? (
            <div className="no-results">No se encontraron usuarios</div>
          ) : (
            <div className="search-hint">Escribe al menos 2 caracteres para buscar</div>
          )}
        </div>
      </div>
    </div>
  );
};

export default NewChatModal;
