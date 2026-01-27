/**
 * ProfileModal Component
 * Modal para editar el perfil del usuario
 */

import { useState } from 'react';
import { useAuth } from '../../context/AuthContext';

const ProfileModal = ({ onClose }) => {
  const { user, updateUserProfile } = useAuth();
  const [formData, setFormData] = useState({
    username: user?.username || '',
    bio: user?.bio || '',
    avatar_url: user?.avatar_url || ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    // Validar que la URL del avatar no sea una data URI (base64)
    if (formData.avatar_url && formData.avatar_url.startsWith('data:')) {
      setError('Por favor, usa una URL externa para el avatar, no imágenes base64. Ejemplo: https://i.pravatar.cc/150?img=1');
      return;
    }

    setLoading(true);

    try {
      // Limpiar campos vacíos antes de enviar
      const cleanData = {};
      if (formData.username && formData.username.trim()) {
        cleanData.username = formData.username.trim();
      }
      if (formData.bio !== undefined && formData.bio !== null) {
        cleanData.bio = formData.bio.trim();
      }
      if (formData.avatar_url && formData.avatar_url.trim()) {
        cleanData.avatar_url = formData.avatar_url.trim();
      }

      await updateUserProfile(cleanData);
      onClose();
    } catch (err) {
      setError(err.response?.data?.message || 'Error al actualizar el perfil');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content profile-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>Perfil</h2>
          <button className="close-button" onClick={onClose}>
            <svg viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M19.6 4.4L12 12l-7.6-7.6L2.8 5.9 10.4 13.5 2.8 21.1l1.6 1.5L12 15l7.6 7.6 1.6-1.5-7.6-7.6 7.6-7.6z"></path>
            </svg>
          </button>
        </div>

        <form onSubmit={handleSubmit} className="profile-form">
          {error && (
            <div className="error-message">
              <span>⚠️</span>
              <p>{error}</p>
            </div>
          )}

          <div className="profile-avatar-section">
            <div className="avatar-preview">
              {formData.avatar_url ? (
                <img src={formData.avatar_url} alt="Avatar" onError={(e) => {
                  e.target.style.display = 'none';
                  e.target.nextSibling.style.display = 'flex';
                }} />
              ) : (
                <div className="avatar-placeholder">
                  {formData.username.charAt(0).toUpperCase()}
                </div>
              )}
            </div>
            <div className="avatar-info">
              <h3>{formData.username}</h3>
              <p className="hint">Foto de perfil</p>
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="username">Nombre de usuario</label>
            <input
              type="text"
              id="username"
              name="username"
              value={formData.username}
              onChange={handleChange}
              disabled={loading}
              required
              minLength="3"
              maxLength="50"
            />
          </div>

          <div className="form-group">
            <label htmlFor="bio">Estado / Biografía</label>
            <textarea
              id="bio"
              name="bio"
              value={formData.bio}
              onChange={handleChange}
              disabled={loading}
              placeholder="Escribe algo sobre ti..."
              maxLength="255"
              rows="3"
            />
            <span className="char-count">{formData.bio.length}/255</span>
          </div>

          <div className="form-group">
            <label htmlFor="avatar_url">URL de Avatar</label>
            <input
              type="text"
              id="avatar_url"
              name="avatar_url"
              value={formData.avatar_url}
              onChange={handleChange}
              disabled={loading}
              placeholder="https://ejemplo.com/avatar.jpg"
            />
            <p className="hint">
              Pega la URL de una imagen externa. Ejemplos:<br />
              • <code>https://i.pravatar.cc/150?img=1</code><br />
              • <code>https://ui-avatars.com/api/?name=Tu+Nombre&background=00a884&color=fff</code>
            </p>
          </div>

          <div className="modal-actions">
            <button
              type="button"
              className="btn-secondary"
              onClick={onClose}
              disabled={loading}
            >
              Cancelar
            </button>
            <button
              type="submit"
              className="btn-primary"
              disabled={loading}
            >
              {loading ? 'Guardando...' : 'Guardar cambios'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ProfileModal;
