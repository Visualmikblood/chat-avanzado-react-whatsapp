# 💬 Chat Application - Real-time con React + PHP + Pusher

Aplicación de chat en tiempo real similar a Telegram/WhatsApp, construida con arquitectura moderna y escalable.

## 🚀 Stack Tecnológico

- **Frontend**: React 18 + Vite 5 + SASS
- **Backend**: PHP 8+ + Composer
- **Base de Datos**: MySQL 8+
- **Tiempo Real**: Pusher (WebSocket como servicio)
- **Autenticación**: JWT (JSON Web Tokens)

---

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

- [Node.js](https://nodejs.org/) (v18 o superior)
- [PHP](https://www.php.net/) (v8.0 o superior)
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/) (v8.0 o superior)
- Cuenta gratuita en [Pusher](https://pusher.com/)

---

## 🛠️ Instalación y Configuración

### 1️⃣ Configurar Base de Datos

```bash
# Accede a MySQL
mysql -u root -p

# Ejecuta el script SQL
source database/schema.sql

# O copia y pega el contenido desde MySQL Workbench
```

### 2️⃣ Configurar Backend (PHP)

```bash
# Navega a la carpeta del backend
cd api

# Instala dependencias de Composer
composer install

# Copia el archivo de variables de entorno
copy .env.example .env

# Edita el archivo .env con tus credenciales:
# - Configura DB_HOST, DB_NAME, DB_USER, DB_PASS
# - Agrega tus credenciales de Pusher desde https://dashboard.pusher.com/
# - Genera un JWT_SECRET seguro (puedes usar: php -r "echo bin2hex(random_bytes(32));" )
```

### 3️⃣ Configurar Frontend (React)

```bash
# Navega a la carpeta del frontend
cd frontend

# Instala dependencias de NPM
npm install

# Copia el archivo de variables de entorno
copy .env.example .env

# Edita el archivo .env:
# - VITE_PUSHER_APP_KEY: tu App Key de Pusher
# - VITE_PUSHER_CLUSTER: tu cluster de Pusher (ej: eu, us2, ap1)
```

### 4️⃣ Obtener Credenciales de Pusher

1. Ve a [Pusher Dashboard](https://dashboard.pusher.com/)
2. Crea una nueva App o usa una existente
3. En la sección "App Keys", encontrarás:
   - **app_id**: para el backend (.env de PHP)
   - **key**: para frontend y backend
   - **secret**: solo para el backend
   - **cluster**: para frontend y backend

---

## ▶️ Ejecutar la Aplicación

### Backend PHP

```bash
# Desde la carpeta /api
cd api
php -S localhost:8000
```

Tu API estará corriendo en: **http://localhost:8000**

### Frontend React

```bash
# Desde la carpeta /frontend (en otra terminal)
cd frontend
npm run dev
```

Tu aplicación React estará en: **http://localhost:5173**

---

## 🧪 Probar la Aplicación

### Usuarios de Prueba

El script SQL incluye 3 usuarios de prueba:

| Usuario | Email | Contraseña |
|---------|-------|-----------|
| alice | alice@example.com | password123 |
| bob | bob@example.com | password123 |
| charlie | charlie@example.com | password123 |

### Flujo de Prueba

1. **Abre dos ventanas del navegador** (o una normal + una incógnito)
2. **Ventana 1**: Inicia sesión con `alice@example.com`
3. **Ventana 2**: Inicia sesión con `bob@example.com`
4. **Envía mensajes** desde cualquier ventana
5. **Verifica** que los mensajes aparezcan **instantáneamente** en ambas ventanas

---

## 📁 Estructura del Proyecto

```
chat_avanzado_con_react/
├── frontend/              # Aplicación React + Vite
│   ├── src/
│   │   ├── components/    # Componentes de React
│   │   ├── services/      # API y Pusher clients
│   │   ├── context/       # Context API para estado global
│   │   └── styles/        # SASS files
│   └── package.json
│
├── api/                   # Backend PHP
│   ├── config/            # Configuraciones (DB, JWT, Pusher)
│   ├── controllers/       # Lógica de negocio
│   ├── models/            # Modelos de datos
│   ├── middleware/        # Auth middleware
│   └── composer.json
│
└── database/
    └── schema.sql         # Script de base de datos
```

---

## 🔐 Seguridad Implementada

✅ Contraseñas hasheadas con `password_hash()` (bcrypt)  
✅ Autenticación con JWT tokens  
✅ Prepared statements (PDO) para prevenir SQL Injection  
✅ Validación de inputs en backend  
✅ CORS configurado correctamente  
✅ Tokens con tiempo de expiración

---

## 📚 Próximos Pasos

Ahora que tienes el esqueleto funcionando, puedes:

1. ✅ Crear los controladores PHP (AuthController, MessageController)
2. ✅ Construir componentes React (Login, Chat, MessageList)
3. ✅ Implementar la lógica de Pusher para tiempo real
4. ✅ Diseñar la UI con SASS (colores, gradientes, animaciones)
5. ✅ Agregar features avanzados (typing indicators, read receipts, etc.)

---

## 🆘 Solución de Problemas

### Error: "Connection refused" en MySQL
- Verifica que MySQL esté corriendo: `mysql --version`
- Comprueba las credenciales en `api/.env`

### Error: "Pusher authentication failed"
- Verifica que las credenciales de Pusher sean correctas
- Asegúrate de usar el mismo `app_key` en frontend y backend

### Mensajes no llegan en tiempo real
- Abre la consola del navegador (F12) y busca errores de Pusher
- Verifica que el backend esté disparando eventos correctamente

---

## 📝 Licencia

Este proyecto es de código abierto para fines educativos.

---

**¡Disfruta construyendo tu aplicación de chat! 🚀**
