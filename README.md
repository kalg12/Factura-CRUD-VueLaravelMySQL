# Sistema de Facturación (Billing CRUD)

Proyecto full stack para gestión de empresas, clientes y facturas con exportación a PDF y CSV. Incluye login con Laravel Sanctum y formularios dinámicos según tipo de persona (física/moral).

**Desarrollador:** Kevin Luciano

---

## 1. Descripción general

| Capa        | Tecnología                          |
|------------|--------------------------------------|
| Frontend   | Vue 3, Vite, TypeScript, Pinia, Vue Router, Axios |
| Backend    | Laravel 12, API REST                 |
| Base de datos | MySQL 8                             |
| Infra      | Docker + Docker Compose              |

**Objetivos del proyecto:**

- CRUD completo de empresas, clientes y facturas (con partidas).
- Autenticación por token (Bearer) con Sanctum.
- Formularios dinámicos según persona física / persona moral.
- Exportación de facturas seleccionadas en PDF y CSV.
- Buenas prácticas: validación (FormRequest), recursos API (Resource), manejo de estados y errores en el frontend.

---

## 2. Características

### 2.1 Autenticación

- **Login** y **registro** de usuario.
- Rutas protegidas: solo con token válido se accede a empresas, clientes y facturas.
- Usuario de prueba (creado por seeder): `test@example.com` / `password`.

### 2.2 Empresas

- Listado paginado, alta, edición y eliminación.
- Tipo de persona: **Persona física** o **Persona moral** (etiqueta dinámica: “Nombre completo” / “Razón social”).
- Campos: nombre, RFC (único), email, teléfono, domicilio.

### 2.3 Clientes

- CRUD completo con paginación.
- Tipo de persona: física o moral; formulario con misma lógica de etiquetas.
- **Empresa opcional** (relación con empresas).
- Campos: nombre, RFC, email, teléfono, domicilio.

### 2.4 Facturas

- Listado paginado con empresa, cliente, fecha, total y estado.
- **Selección múltiple** (checkboxes) para exportar.
- Alta y edición con:
  - Empresa y cliente (obligatorios).
  - Fecha, estado (DRAFT, SENT, PAID, CANCELLED), moneda.
  - **Partidas dinámicas**: descripción, cantidad, precio unitario; subtotal, IVA (16 %) y total calculados en tiempo real.
- Eliminación con confirmación.

### 2.5 Exportaciones

- **PDF**: una o varias facturas seleccionadas (varias en un mismo PDF multipágina).
- **CSV**: facturas seleccionadas; columnas: ID, fecha, estado, empresa, cliente, subtotal, IVA, total, moneda.

---

## 3. Estructura del proyecto

```
factura-crud/
├── backend/                 # Laravel (API)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/   # Auth, Company, Client, Invoice
│   │   │   ├── Requests/          # Store/Update FormRequests
│   │   │   └── Resources/         # Company, Client, Invoice, InvoiceItem
│   │   └── Models/
│   ├── database/migrations/
│   ├── database/seeders/          # FacturaSeeder (empresas, clientes, facturas)
│   ├── resources/views/invoices/  # Blade para PDF
│   ├── routes/api.php
│   ├── .env
│   └── Dockerfile
├── frontend/                # Vue 3 SPA
│   ├── src/
│   │   ├── api/            # client (axios), companies, clients, invoices
│   │   ├── layouts/        # MainLayout (menú)
│   │   ├── router/         # Rutas + guard auth
│   │   ├── stores/         # auth (Pinia)
│   │   ├── types/          # TypeScript (User, Company, Client, Invoice, etc.)
│   │   └── views/          # Login, Home, Companies, Clients, Invoices
│   ├── .env                # VITE_API_URL
│   └── package.json
├── docker-compose.yml      # app (Laravel), db (MySQL)
├── README.md
└── FRONTEND_SETUP.md       # Guía detallada frontend + login
```

---

## 4. Requisitos

- **Docker** y **Docker Compose** (para backend + MySQL).
- **Node.js** 18+ y **npm** (para el frontend).

---

## 5. Cómo ejecutar

### 5.1 Backend (API + MySQL)

En la raíz del proyecto:

```bash
docker compose up -d
```

- API: **http://localhost:8000**
- MySQL: puerto **3306** (credenciales abajo).

Si es la primera vez o cambiaste migraciones/seeders:

```bash
docker exec factura_app php artisan migrate --force
docker exec factura_app php artisan db:seed --force
```

### 5.2 Frontend

```bash
cd frontend
npm install
npm run dev
```

- App: **http://localhost:5173**

Crea o revisa `frontend/.env`:

```env
VITE_API_URL=http://localhost:8000/api
```

### 5.3 Probar el flujo

1. Abre http://localhost:5173.
2. Inicia sesión con **test@example.com** / **password**.
3. Usa el menú: **Empresas**, **Clientes**, **Facturas**.
4. En Facturas, selecciona filas y usa **Descargar PDF** o **Descargar CSV**.

---

## 6. Acceso a servicios

| Servicio   | URL / datos |
|-----------|-------------|
| Backend API | http://localhost:8000 |
| Frontend    | http://localhost:5173 |
| MySQL       | Host: `localhost`, Puerto: `3306` |

### Base de datos MySQL

| Parámetro   | Valor          |
|------------|----------------|
| Base de datos | `factura_crud` |
| Usuario    | `factura_user` |
| Contraseña | `factura_pass` |
| Root       | `root`         |

### API (Laravel Sanctum)

- **Login:** `POST /api/login` — body: `{ "email", "password" }` → devuelve `token` y `user`.
- **Registro:** `POST /api/register` — body: `{ "name", "email", "password", "password_confirmation" }`.
- **Usuario actual:** `GET /api/user` — cabecera: `Authorization: Bearer <token>`.
- **Logout:** `POST /api/logout` — cabecera: `Authorization: Bearer <token>`.

El resto de endpoints (companies, clients, invoices, export) requieren la cabecera `Authorization: Bearer <token>`.

---

## 7. Documentación adicional

- **[FRONTEND_SETUP.md](./FRONTEND_SETUP.md)** — Pasos para montar el proyecto Vue desde cero (axios, Pinia, router, login) y configuración de CORS/entorno.
