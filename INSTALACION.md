# INSTRUCCIONES PASO A PASO - Configuración Inicial

## ✅ PASO 1: Crear la Base de Datos

### Opción A: Desde línea de comandos
```bash
# Abre PowerShell o CMD
mysql -u root -p

# Cuando te pida la contraseña, ingresa la de tu MySQL
# Luego ejecuta:
source C:\Users\migue\Documents\proyectos_react\chat_avanzado_con_react\database\schema.sql
```

### Opción B: Desde MySQL Workbench
1. Abre MySQL Workbench
2. Conectarte a tu servidor local
3. Ve a `File > Open SQL Script`
4. Selecciona el archivo `database/schema.sql`
5. Presiona el botón ⚡ (Execute) para ejecutar el script

**Verificar**: Deberías ver una nueva base de datos llamada `chat_app` con 4 tablas.

---

## ✅ PASO 2: Instalar Dependencias del Backend

```bash
# Navega a la carpeta del proyecto
cd C:\Users\migue\Documents\proyectos_react\chat_avanzado_con_react

# Entra a la carpeta del backend
cd api

# Instala las dependencias de PHP con Composer
composer install
```

**¿Qué instalará?**
- `pusher/pusher-php-server`: SDK para enviar eventos a Pusher desde PHP

---

## ✅ PASO 3: Configurar Variables de Entorno del Backend

```bash
# Desde la carpeta /api
copy .env.example .env
```

Ahora **edita el archivo `api/.env`** con tus datos:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=chat_app
DB_USER=root
DB_PASS=TU_CONTRASEÑA_DE_MYSQL

# JWT Configuration (genera una clave secreta segura)
JWT_SECRET=cb8f4a9d2e1f6b3c7a5d9e8f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b

# Pusher Configuration (lo configuraremos en el PASO 4)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_CLUSTER=eu

# CORS Configuration
ALLOWED_ORIGINS=http://localhost:5173
```

**Nota**: Para generar un `JWT_SECRET` seguro, ejecuta en PowerShell:
```powershell
php -r "echo bin2hex(random_bytes(32));"
```

---

## ✅ PASO 4: Configurar Pusher (Servicio de WebSockets)

### 1. Crear cuenta gratuita en Pusher
- Ve a: https://pusher.com/
- Haz clic en **"Sign Up"** (es gratis para hasta 100 conexiones simultáneas)
- Completa el registro

### 2. Crear una nueva App
- En el dashboard, haz clic en **"Create App"**
- Nombre: `chat-app-localhost`
- Cluster: Elige el más cercano (Europa = `eu`, USA = `us2`)
- Frontend: Selecciona **"Vanilla JS"** (o cualquiera, no importa)
- Backend: Selecciona **"PHP"**
- Haz clic en **"Create app"**

### 3. Obtener credenciales
En la sección **"App Keys"** verás:
- `app_id`: 1234567 (ejemplo)
- `key`: a1b2c3d4e5f6g7h8i9j0 (ejemplo)
- `secret`: x9y8z7w6v5u4t3s2r1q0 (ejemplo)
- `cluster`: eu (ejemplo)

### 4. Copiar credenciales a tus archivos .env

**En `api/.env`** (Backend):
```env
PUSHER_APP_ID=1234567
PUSHER_APP_KEY=a1b2c3d4e5f6g7h8i9j0
PUSHER_APP_SECRET=x9y8z7w6v5u4t3s2r1q0
PUSHER_CLUSTER=eu
```

**En `frontend/.env`** (Frontend - lo crearemos en el siguiente paso):
```env
VITE_PUSHER_APP_KEY=a1b2c3d4e5f6g7h8i9j0
VITE_PUSHER_CLUSTER=eu
```

---

## ✅ PASO 5: Instalar Dependencias del Frontend

```bash
# Desde la raíz del proyecto
cd C:\Users\migue\Documents\proyectos_react\chat_avanzado_con_react

# Entra a la carpeta del frontend
cd frontend

# Instala las dependencias de Node.js
npm install
```

**¿Qué instalará?**
- `react` y `react-dom`: Framework de UI
- `react-router-dom`: Navegación entre páginas
- `axios`: Cliente HTTP para consumir la API
- `pusher-js`: Cliente de Pusher para recibir eventos en tiempo real
- `date-fns`: Manejo de fechas
- `sass`: Preprocesador CSS
- `vite`: Bundler ultra rápido

---

## ✅ PASO 6: Configurar Variables de Entorno del Frontend

```bash
# Desde la carpeta /frontend
copy .env.example .env
```

Ahora **edita el archivo `frontend/.env`**:

```env
# API Backend URL
VITE_API_URL=http://localhost:8000/api

# Pusher Configuration (usa las mismas credenciales del PASO 4)
VITE_PUSHER_APP_KEY=a1b2c3d4e5f6g7h8i9j0
VITE_PUSHER_CLUSTER=eu
```

---

## ✅ PASO 7: Ejecutar la Aplicación

### 1. Iniciar el Backend (en una terminal)

```bash
cd C:\Users\migue\Documents\proyectos_react\chat_avanzado_con_react\api
php -S localhost:8000
```

Deberías ver:
```
PHP 8.x Development Server (http://localhost:8000) started
```

**Deja esta terminal abierta y ejecutándose.**

### 2. Iniciar el Frontend (en OTRA terminal)

```bash
cd C:\Users\migue\Documents\proyectos_react\chat_avanzado_con_react\frontend
npm run dev
```

Deberías ver:
```
VITE v5.x.x  ready in xxx ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
```

**Deja esta terminal abierta también.**

---

## ✅ PASO 8: Verificar que Todo Funciona

1. **Abre tu navegador** y ve a: http://localhost:5173/
2. **Verifica la consola del navegador** (F12):
   - No deberías ver errores de conexión
   - (Aún no hay UI, eso lo haremos después)

---

## 🎉 ¡ESQUELETO COMPLETO!

Ahora tienes:
- ✅ Base de datos MySQL creada con tablas
- ✅ Backend PHP configurado con Pusher
- ✅ Frontend React configurado con Pusher
- ✅ Ambos servidores corriendo en localhost

---

## 🚀 Próximos Pasos (Para la Siguiente Fase)

1. Crear los **Controladores PHP** (AuthController, MessageController)
2. Crear los **Componentes React** (Login, Register, ChatList, ChatWindow)
3. Conectar el frontend con el backend vía API
4. Implementar el envío y recepción de mensajes en tiempo real
5. Diseñar la UI con SASS (estilo Telegram/WhatsApp)

---

## 🆘 Solución de Problemas Comunes

### Error: "composer: command not found"
- **Causa**: Composer no está instalado
- **Solución**: Descarga e instala desde https://getcomposer.org/

### Error: "mysql: command not found"
- **Causa**: MySQL no está en el PATH de Windows
- **Solución**: Usa MySQL Workbench para ejecutar el script SQL

### Error: "Port 8000 is already in use"
- **Causa**: Otro proceso está usando el puerto 8000
- **Solución**: Usa otro puerto: `php -S localhost:8001` y actualiza `frontend/.env`:
  ```env
  VITE_API_URL=http://localhost:8001/api
  ```

### Error: "Cannot find module 'pusher-js'"
- **Causa**: No se instalaron las dependencias del frontend
- **Solución**: `cd frontend` y luego `npm install`

### Pusher no conecta
- **Causa**: Credenciales incorrectas
- **Verificación**: Ve a https://dashboard.pusher.com/ → "Debug Console" y envía un evento de prueba
