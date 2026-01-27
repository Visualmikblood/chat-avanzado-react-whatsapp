# 🚀 INICIO RÁPIDO - 5 Minutos

Guía express para poner en marcha tu chat application.

## 📝 Pre-requisitos

- ✅ MySQL instalado y corriendo
- ✅ PHP 8+ instalado
- ✅ Node.js 18+ instalado
- ✅ Composer instalado

---

## 🔥 Configuración Express

### 1. Pusher (2 minutos)

1. Ve a [pusher.com](https://pusher.com) → Sign up
2. Create app → Nombre: `chat-app` → Cluster: `eu`
3. Copia tus credenciales (App ID, Key, Secret, Cluster)

### 2. Base de Datos (1 minuto)

```bash
mysql -u root -p
```
```sql
CREATE DATABASE chat_app;
USE chat_app;
SOURCE C:/Users/migue/Documents/proyectos_react/chat_avanzado_con_react/database/schema.sql;
EXIT;
```

### 3. Backend .env (1 minuto)

```bash
cd api
copy .env.example .env
```

Editar `api/.env`:
```env
DB_PASS=tu_password_mysql
PUSHER_APP_ID=tu_app_id
PUSHER_APP_KEY=tu_app_key
PUSHER_APP_SECRET=tu_app_secret
PUSHER_CLUSTER=eu
```

```bash
composer install
```

### 4. Frontend .env (1 minuto)

```bash
cd frontend
copy .env.example .env
```

Editar `frontend/.env`:
```env
VITE_PUSHER_APP_KEY=tu_app_key    # ← MISMO que backend
VITE_PUSHER_CLUSTER=eu            # ← MISMO que backend
```

```bash
npm install
```

### 5. Ejecutar (30 segundos)

**Terminal 1 - Backend:**
```bash
cd api
php -S localhost:8000
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```

### 6. Probar

Abre: [http://localhost:5173](http://localhost:5173)

1. Registra usuario 1
2. Abre ventana incógnito
3. Registra usuario 2
4. Crea conversación entre ambos
5. ¡Envía mensajes! Deberían verse en tiempo real ⚡

---

## 🎯 Comandos Útiles

### Generar JWT Secret
```bash
php -r "echo bin2hex(random_bytes(32));"
```

### Reiniciar todo
```bash
# Ctrl+C en ambas terminales
cd api && php -S localhost:8000
cd frontend && npm run dev
```

### Ver logs de Pusher
Ve a: [dashboard.pusher.com](https://dashboard.pusher.com) → Debug Console

---

## 🆘 Problemas Comunes

| Error | Solución |
|-------|----------|
| "Connection refused" | Backend no está corriendo → `php -S localhost:8000` |
| "Network Error" | Verifica `VITE_API_URL=http://localhost:8000` |
| Mensajes no llegan en tiempo real | Verifica que `PUSHER_APP_KEY` sea IDÉNTICO en frontend y backend |
| CORS | Ya está configurado, reinicia el backend |

---

**Para más detalles, consulta:** [setup_guide.md](file:///C:/Users/migue/.gemini/antigravity/brain/e4399fd0-af98-4e90-b8f8-55b1b2befb72/setup_guide.md)
