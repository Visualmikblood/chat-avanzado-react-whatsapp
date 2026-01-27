/**
 * Chat Context
 * Maneja el estado global del chat (conversaciones, mensajes)
 */

import { createContext, useContext, useState, useEffect } from 'react';
import { conversationAPI, messageAPI } from '../services/api';
import { subscribeToConversation, unsubscribeFromConversation } from '../services/pusher';
import { useAuth } from './AuthContext';

const ChatContext = createContext();

export const useChat = () => {
  const context = useContext(ChatContext);
  if (!context) {
    throw new Error('useChat debe usarse dentro de ChatProvider');
  }
  return context;
};

export const ChatProvider = ({ children }) => {
  const { user, isAuthenticated } = useAuth();
  const [conversations, setConversations] = useState([]);
  const [activeConversation, setActiveConversation] = useState(null);
  const [messages, setMessages] = useState([]);
  const [loading, setLoading] = useState(false);

  // Cargar conversaciones al autenticarse
  useEffect(() => {
    if (isAuthenticated) {
      loadConversations();
    } else {
      setConversations([]);
      setActiveConversation(null);
      setMessages([]);
    }
  }, [isAuthenticated]);

  // Suscribirse a Pusher para todas las conversaciones
  useEffect(() => {
    // Si no hay usuario, no suscribirse
    if (!user) return;

    // Suscribirse a los canales de todas las conversaciones para actualizar lista y contadores
    const channels = conversations.map(conv => {
      return subscribeToConversation(conv.id, (newMessage) => {
        // Ignorar si el mensaje es nuestro
        if (newMessage.sender_id === user.id) return;

        setConversations(prev => {
          return prev.map(c => {
            if (c.id === newMessage.conversation_id) {
              // Si es la conversación activa, no incrementar contador
              const isChatOpen = activeConversation?.id === c.id;
              
              const updatedConv = {
                ...c,
                last_message: newMessage.content,
                last_message_time: newMessage.created_at,
                unread_count: isChatOpen ? 0 : (parseInt(c.unread_count || 0) + 1)
              };
              
              // Si conversación está abierta, agregar mensaje a la vista
              if (isChatOpen) {
                setMessages(prev => [...prev, newMessage]);
              }
              
              return updatedConv;
            }
            return c;
          }).sort((a, b) => new Date(b.last_message_time || b.updated_at) - new Date(a.last_message_time || a.updated_at));
        });
      });
    });

    return () => {
      conversations.forEach(conv => unsubscribeFromConversation(conv.id));
    };
  }, [conversations.length, activeConversation, user]);

  // Manejo específico para agregar mensajes a la vista activa
  useEffect(() => {
    if (!activeConversation) return;
    
    // Marcar como leída visualmente al abrir
    setConversations(prev => prev.map(c => 
      c.id === activeConversation.id ? { ...c, unread_count: 0 } : c
    ));
  }, [activeConversation]);

  /**
   * Cargar todas las conversaciones del usuario
   */
  const loadConversations = async () => {
    try {
      setLoading(true);
      const response = await conversationAPI.getAll();
      setConversations(response.data.data || []);
    } catch (error) {
      console.error('Error al cargar conversaciones:', error);
    } finally {
      setLoading(false);
    }
  };

  /**
   * Seleccionar una conversación
   */
  const selectConversation = async (conversation) => {
    setActiveConversation(conversation);
    
    // Cargar mensajes de la conversación
    try {
      setLoading(true);
      const response = await messageAPI.getMessages(conversation.id);
      setMessages(response.data.data.messages || []);
    } catch (error) {
      console.error('Error al cargar mensajes:', error);
      setMessages([]);
    } finally {
      setLoading(false);
    }
  };

  /**
   * Crear nueva conversación con un usuario
   */
  const createConversation = async (userId) => {
    try {
      const response = await conversationAPI.create(userId);
      const newConversation = response.data.data;
      
      // Si ya existía, solo seleccionarla
      if (newConversation.already_exists) {
        await loadConversations();
        const existing = conversations.find(c => c.id === newConversation.id);
        if (existing) {
          selectConversation(existing);
        }
      } else {
        // Agregar nueva conversación
        setConversations((prev) => [newConversation, ...prev]);
        selectConversation(newConversation);
      }

      return { success: true };
    } catch (error) {
      console.error('Error al crear conversación:', error);
      return { success: false, error: error.response?.data?.message };
    }
  };

  /**
   * Enviar mensaje
   */
  /**
   * Enviar mensaje (texto o archivo)
   */
  const sendMessage = async (content, type = 'text', file = null) => {
    if (!activeConversation) return;
    
    // Si es texto, validar que no esté vacío
    if (type === 'text' && !content.trim()) return;

    try {
      let payload;
      
      if (file) {
        payload = new FormData();
        payload.append('conversation_id', activeConversation.id);
        payload.append('file', file);
        payload.append('type', type);
      } else {
        payload = {
          conversation_id: activeConversation.id,
          content: content.trim(),
          type: 'text'
        };
      }

      const response = await messageAPI.send(payload);

      const newMessage = response.data.data;
      
      // Agregar mensaje localmente (aparecerá inmediatamente)
      setMessages((prev) => [...prev, newMessage]);

      // Actualizar última vez en la lista de conversaciones
      setConversations(prev => prev.map(c => {
        if (c.id === activeConversation.id) {
          return {
            ...c,
            last_message: newMessage.type === 'text' ? newMessage.content : (newMessage.type === 'image' ? '📷 Foto' : '📎 Archivo'),
            last_message_time: newMessage.created_at,
            unread_count: 0
          };
        }
        return c;
      }).sort((a, b) => new Date(b.last_message_time) - new Date(a.last_message_time)));

      return { success: true };
    } catch (error) {
      console.error('Error al enviar mensaje:', error);
      return { success: false, error: error.response?.data?.message };
    }
  };

  /**
   * Buscar usuarios
   */
  const searchUsers = async (query) => {
    try {
      const response = await conversationAPI.searchUsers(query);
      return response.data.data || [];
    } catch (error) {
      console.error('Error al buscar usuarios:', error);
      return [];
    }
  };

  /**
   * Cerrar conversación activa
   */
  const closeConversation = () => {
    setActiveConversation(null);
    setMessages([]);
  };

  const value = {
    conversations,
    activeConversation,
    messages,
    loading,
    loadConversations,
    selectConversation,
    createConversation,
    sendMessage,
    searchUsers,
    closeConversation
  };

  return <ChatContext.Provider value={value}>{children}</ChatContext.Provider>;
};
