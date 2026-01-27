# 📋 Documentación de la API Backend

Base URL: `http://localhost:8000`

---

## 🔐 Autenticación

### Registro de Usuario
**POST** `/auth/register`

**Body:**
```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": 1,
      "username": "john_doe",
      "email": "john@example.com",
      "avatar_url": null,
      "status": "offline",
      "created_at": "2026-01-26 19:00:00"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

---

### Login
**POST** `/auth/login`

**Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

---

### Obtener Usuario Actual
**GET** `/auth/me`

**Headers:**
```
Authorization: Bearer <token>
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    ...
  }
}
```

---

### Logout
**POST** `/auth/logout`

**Headers:**
```
Authorization: Bearer <token>
```

---

## 💬 Conversaciones

### Obtener Todas las Conversaciones
**GET** `/conversations`

**Headers:**
```
Authorization: Bearer <token>
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "individual",
      "name": null,
      "updated_at": "2026-01-26 19:00:00",
      "last_message": "Hola!",
      "last_message_time": "2026-01-26 18:55:00",
      "other_user": {
        "id": 2,
        "username": "alice",
        "avatar_url": null,
        "status": "online"
      }
    }
  ]
}
```

---

### Crear Conversación Individual
**POST** `/conversations`

**Headers:**
```
Authorization: Bearer <token>
```

**Body:**
```json
{
  "user_id": 2
}
```

**Respuesta (201):**
```json
{
  "success": true,
  "message": "Conversación creada exitosamente",
  "data": {
    "id": 1,
    "type": "individual",
    "other_user": { ... },
    "created": true
  }
}
```

---

### Buscar Usuarios
**GET** `/conversations/search?q=alice`

**Headers:**
```
Authorization: Bearer <token>
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "username": "alice",
      "email": "alice@example.com",
      "avatar_url": null,
      "status": "online"
    }
  ]
}
```

---

## 📨 Mensajes

### Enviar Mensaje
**POST** `/messages`

**Headers:**
```
Authorization: Bearer <token>
```

**Body:**
```json
{
  "conversation_id": 1,
  "content": "Hola! ¿Cómo estás?",
  "type": "text"
}
```

**Respuesta (201):**
```json
{
  "success": true,
  "message": "Mensaje enviado exitosamente",
  "data": {
    "id": 1,
    "conversation_id": 1,
    "sender_id": 1,
    "sender_username": "john_doe",
    "sender_avatar": null,
    "content": "Hola! ¿Cómo estás?",
    "type": "text",
    "created_at": "2026-01-26 19:00:00",
    "is_edited": false
  }
}
```

**Evento Pusher disparado:**
- Canal: `conversation-{id}`
- Evento: `new-message`
- Data: (mismo objeto que la respuesta)

---

### Obtener Mensajes de una Conversación
**GET** `/messages/{conversation_id}?limit=50&offset=0`

**Headers:**
```
Authorization: Bearer <token>
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "messages": [ ... ],
    "count": 10,
    "limit": 50,
    "offset": 0
  }
}
```

---

### Editar Mensaje
**PUT** `/messages/{message_id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Body:**
```json
{
  "content": "Contenido editado"
}
```

---

### Eliminar Mensaje
**DELETE** `/messages/{message_id}`

**Headers:**
```
Authorization: Bearer <token>
```

---

## 🚨 Códigos de Error

| Código | Descripción |
|--------|------------|
| 200 | OK |
| 201 | Creado |
| 400 | Bad Request (datos inválidos) |
| 401 | No autorizado (token inválido) |
| 403 | Prohibido (sin permisos) |
| 404 | No encontrado |
| 409 | Conflicto (email/username duplicado) |
| 500 | Error del servidor |

---

## 🔔 Eventos de Pusher

### new-message
Disparado cuando se envía un nuevo mensaje.

**Canal:** `conversation-{conversation_id}`

**Data:**
```json
{
  "id": 1,
  "conversation_id": 1,
  "sender_id": 1,
  "sender_username": "john_doe",
  "content": "Hola!",
  "created_at": "2026-01-26 19:00:00"
}
```

---

## 🧪 Probar la API

### Con cURL:

```bash
# Registro
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"test","email":"test@example.com","password":"password123"}'

# Login
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Obtener conversaciones (reemplaza <TOKEN>)
curl -X GET http://localhost:8000/conversations \
  -H "Authorization: Bearer <TOKEN>"
```

### Con Postman:
1. Importa la colección (pronto disponible)
2. Configura la variable `base_url` = `http://localhost:8000`
3. Ejecuta las peticiones en orden
