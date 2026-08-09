# QR Tambo — Sistema de Asistencia por QR

Sistema de asistencia para clases de baile mediante códigos QR. Arquitectura desacoplada: **frontend** y **backend** son proyectos independientes que se comunican mediante una **API REST**.

## Estructura del proyecto

```
/
├── frontend/                # SPA React (Vite) — el frontend activo
│   ├── src/                 # Código fuente React (páginas, layouts, stores, api)
│   │   ├── api/client.js    # Cliente HTTP (axios) con sesión por cookie
│   │   ├── stores/          # Estado global (Zustand): auth, toasts
│   │   ├── layouts/         # Layouts Admin y Bailarín
│   │   ├── pages/           # Login, Register, Registro QR, Admin, Bailarín
│   │   └── styles/          # Design system (tema oscuro premium + glassmorphism)
│   ├── public/              # Estáticos copiados al build (logo, .htaccess)
│   ├── dist/                # Build de producción (generado con npm run build)
│   ├── legacy-php/          # Frontend PHP anterior (archivado, sigue funcional)
│   ├── index.html
│   ├── package.json
│   └── vite.config.js
├── backend/                 # API REST PHP
│   ├── api/                 # Front controller de la API REST (index.php + .htaccess)
│   ├── routes/api.php       # Mapa centralizado de rutas REST
│   ├── controllers/         # Controladores REST
│   ├── services/            # Lógica de negocio
│   ├── models/              # Acceso a datos (SQL)
│   ├── support/             # Router, Auth, Http, helpers
│   ├── config/              # Configuración y bootstrap
│   ├── database/schema.sql  # Esquema de la base de datos
│   ├── setup.php            # Instalación de la BD + admin por defecto
│   └── migrate_passwords.php # Migración de contraseñas a bcrypt
└── README.md
```

## Comunicación Frontend ↔ Backend

El frontend **no accede a datos directamente**: toda comunicación se realiza mediante la API REST en `backend/api/`.

- El cliente HTTP (`frontend/src/api/client.js`) usa `axios` con `withCredentials` (cookie de sesión PHP compartida) y `baseURL` desde `VITE_API_BASE`.
  - Desarrollo (`vite`): `VITE_API_BASE=/backend/api`, el dev server hace proxy a Apache.
  - Producción (`npm run build`): `VITE_API_BASE=/qr_tambo/backend/api`.
- La sesión es compartida: el login se hace por `POST /backend/api/auth/login` y la cookie `PHPSESSID` persiste; `GET /auth/me` restaura la sesión al cargar la SPA.

Endpoints disponibles:

| Método | Recurso | Descripción |
|--------|---------|-------------|
| `POST` | `/api/auth/login` | Iniciar sesión |
| `POST` | `/api/auth/register` | Registrar bailarín |
| `POST` | `/api/auth/logout` | Cerrar sesión |
| `GET` | `/api/auth/me` | Usuario de la sesión actual |
| `POST` | `/api/auth/cambiar-password` | Cambiar contraseña |
| `GET/POST` | `/api/clases` | Listar / crear clases |
| `PUT/DELETE` | `/api/clases/{id}` | Actualizar / eliminar clase |
| `GET/POST` | `/api/horarios` | Listar / crear horarios |
| `GET` | `/api/horarios/clases-por-dia?dia={n}` | Clases de un día (generación de QR) |
| `PUT/DELETE` | `/api/horarios/{id}` | Actualizar / eliminar horario |
| `GET/POST` | `/api/asistencia` | Listar / consultar asistencias |
| `POST` | `/api/asistencia/registrar` | Registrar asistencia desde QR |
| `GET` | `/api/asistencia/reporte-mensual` | Reporte mensual |
| `GET/PUT/DELETE` | `/api/usuarios` y `/api/usuarios/{id}` | Gestión de usuarios |

## Instalación (local XAMPP)

1. Colocá la carpeta `qr_tambo` en `C:\xampp\htdocs\`.
2. Configurá las credenciales de MySQL en `backend/config/config.php` (o copiá desde `backend/config/config.example.php`).
3. Iniciá Apache y MySQL.
4. Ejecutá en el navegador: `http://localhost/qr_tambo/backend/setup.php`
   - Crea las tablas y usuarios admin por defecto (cédula: `admin`, contraseña: `admin123`; cédula: `admin2`, contraseña: `admin123`).
5. Frontend React:

   ```bash
   cd frontend
   npm install
   npm run dev      # desarrollo: http://localhost:5173
   npm run build    # producción: genera frontend/dist
   ```

6. Producción (Apache): el build queda en `http://localhost/qr_tambo/frontend/dist/`
   - El `.htaccess` incluido reescribe las rutas SPA a `index.html`.
   - El backend queda disponible en `http://localhost/qr_tambo/backend/api/...`

## Notas

- Las sesiones de PHP se guardan en `backend/sessions/` y los logs en `backend/logs/` (ambos ignorados por Git).
- `backend/config/config.php` contiene credenciales reales y **no se sube al repositorio**.
- El frontend PHP anterior se conserva funcional en `frontend/legacy-php/`.
