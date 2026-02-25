# Sistema de Facturación (Billing CRUD)

Proyecto full stack - Facturación CRUD
Dev: Kevin Luciano
Permite la gestión de empresas, clientes y facturas con partidas dinámicas, incluyendo exportación en PDF.

---

## 1. Descripción General

Este sistema implementa un CRUD completo para facturación utilizando:

- Frontend: Vue 3 + Vite + Pinia
- Backend: Laravel (API REST)
- Base de datos: MySQL
- Contenerización: Docker + Docker Compose
- Exportación: PDF

El objetivo es demostrar:

- Arquitectura limpia
- Buenas prácticas en Laravel
- Manejo correcto de estados HTTP
- Validaciones
- Dockerización completa del entorno

---

## 2. Características Principales

### 2.1 Empresas (Emisores)

- Crear, listar, actualizar y eliminar empresas
- Soporte para:
  - Persona Física
  - Persona Moral
- Validación de RFC
- Relación con facturas

### 2.2 Clientes (Receptores)

- CRUD completo
- Cambio dinámico de formulario según:
  - Persona Física
  - Persona Moral
- Validación condicional de campos
- Relación con facturas

### 2.3 Facturas

- Crear facturas con múltiples partidas
- Cálculo automático de:
  - Subtotal
  - Impuestos
  - Total
- Estados disponibles:
  - DRAFT
  - ISSUED
  - PAID
  - CANCELED
- Filtros por:
  - Empresa
  - Cliente
  - Estado
  - Rango de fechas
- Eliminación lógica (opcional si se usa SoftDeletes)

### 2.4 Exportaciones

- Exportar factura individual en PDF
- Exportar múltiples facturas en PDF

---

## 3. Arquitectura del Proyecto

### Backend (Laravel)

app/
├── Http/
│ ├── Controllers/Api/
│ ├── Requests/
│ ├── Resources/
├── Models/
├── Services/
│ └── Invoice/

### Frontend (Vue 3)

Principios aplicados:

- Manejo de estado con Pinia
- Manejo centralizado de llamadas API
- Formularios dinámicos según tipo de cliente/empresa

### Base de Datos: MySQL

## 5. Acceso

- **Backend API:** http://localhost:8000  
- **Frontend:** http://localhost:5173 (tras montar el proyecto Vue; ver abajo)

### Base de datos MySQL

- Host: localhost  
- Puerto: 3306  
- Base de datos: factura_crud  
- Usuario: factura_user  
- Contraseña: factura_pass  
- Root password: root  

### Login (API y frontend)

La API usa **Laravel Sanctum** (token Bearer). Usuario de prueba creado por el seeder:

- **Email:** test@example.com  
- **Contraseña:** password  

Endpoints de auth: `POST /api/login`, `POST /api/register`, `POST /api/logout`, `GET /api/user`. El resto de rutas requieren cabecera `Authorization: Bearer <token>`.

---

## 6. Montar el frontend (Vue 3 + Login)

Pasos detallados para crear el proyecto Vue, configurar axios, Pinia, router y la pantalla de login están en **[FRONTEND_SETUP.md](./FRONTEND_SETUP.md)**.

Resumen rápido:

1. En la raíz: `npm create vue@latest frontend` → elegir Vue Router y Pinia.
2. `cd frontend` → `npm install` → `npm install axios`.
3. Crear `frontend/.env` con `VITE_API_URL=http://localhost:8000/api`.
4. Crear cliente axios (con interceptor del token), store de auth (Pinia), rutas con protección y vista de login según la guía.
5. Probar con test@example.com / password.
