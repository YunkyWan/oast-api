# OAST Importadores – API (Laravel)

API REST para la gestión de importadores del Organismo Autónomo de Servicios Tributarios (OAST).  
Este backend proporciona los servicios de autenticación, gestión de usuarios y operaciones sobre los datos de importadores, integrándose con un frontend desarrollado en Vue 3.

---

## 1. Descripción del proyecto

Este backend forma parte del Trabajo Final de Máster y tiene como objetivo modernizar la gestión de importadores del OAST, sustituyendo una aplicación legacy por una solución basada en arquitecturas y tecnologías actuales.

Principales responsabilidades de la API:

- Autenticación de usuarios mediante **Laravel Sanctum**.
- Gestión de **usuarios** y sus **roles** (`admin` y `consultor`).
- Exposición de endpoints para la gestión de **importadores**.
- Validación de datos, seguridad básica y soporte a una SPA en Vue 3.

---

## 2. Tecnologías utilizadas

- **PHP 8.2**
- **Laravel 11**
- **Laravel Sanctum** (autenticación)
- **MySQL / MariaDB** (base de datos)
- **Composer** (gestión de dependencias)
- **Git + GitHub** (control de versiones)

---

## 3. Funcionalidades implementadas

### 3.1. Autenticación

- Login y logout de usuarios mediante cookies de sesión.
- Protección de rutas con el middleware `auth:sanctum`.
- Endpoint `/api/user` que devuelve el usuario autenticado junto con sus roles.

### 3.2. Gestión de usuarios (solo admin)

- Listado de usuarios.
- Creación de nuevos usuarios con rol asignado (`admin` o `consultor`).
- Edición de datos básicos y cambio de contraseña.
- Eliminación de usuarios.
- Roles gestionados mediante tablas `roles` y `role_user` (relación many-to-many).

### 3.3. Gestión de importadores

- Listado de importadores.
- Consulta de detalle de un importador.
- Creación de nuevos importadores mediante formulario en el frontend.
- Integración con la tabla legada `importad` respetando su estructura original.

---

## 4. Requisitos previos

- PHP 8.2 o superior
- Composer 2.x
- MySQL / MariaDB
- (Opcional) XAMPP 3.3.0 o similar
- Git

---

## 5. Instalación

1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/YunkyWan/oast-api.git
   cd oast-api

2. **Instalar dependencias PHP**
    ```bash
    composer install


3. **Crear el archivo de entorno**
    ```bash
    cp .env.example .env


4. **Configurar el archivo .env**

    Ajustar, como mínimo, estas variables:

    ```bash
    APP_NAME=OAST
    APP_URL=http://localhost:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=oast_importadores
    DB_USERNAME=root
    DB_PASSWORD=

    FRONTEND_URL=http://localhost:5173
    SESSION_DRIVER=cookie
    SESSION_DOMAIN=localhost
    SESSION_SECURE_COOKIE=false
    SESSION_SAME_SITE=lax

    SANCTUM_STATEFUL_DOMAINS=localhost:5173


5. **Generar la clave de la aplicación**
    ```bash
    php artisan key:generate


6. **Crear la base de datos**

    En MySQL / phpMyAdmin:
    ```bash
    CREATE DATABASE oast_importadores
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;


7. **Importar la tabla legada importad**

    Utilizar el script SQL proporcionado (importad.sql) para crear la tabla original de importadores.

8. **Ejecutar migraciones y seeders**
    ```bash
    php artisan migrate
    php artisan db:seed
    ```
    Los seeders crean:

    - Usuario administrador: admin@oast.local / 123456

    - Roles admin y consultor

    - Relación del usuario admin con el rol admin.

---

## 6. Puesta en marcha

Iniciar el servidor de desarrollo:

    php artisan serve


Por defecto, la API quedará disponible en:

http://localhost:8000

---

## 7. Endpoints principales
### 7.1. Autenticación

GET /sanctum/csrf-cookie
Inicializa la cookie CSRF para la SPA.

POST /login
Cuerpo: { "email": "admin@oast.local", "password": "123456" }
Inicia sesión y establece las cookies de sesión.

POST /logout
Cierra la sesión.

GET /api/user
Devuelve el usuario autenticado y sus roles.

### 7.2. Importadores

Todas estas rutas requieren autenticación (auth:sanctum).

GET /api/importadores
Lista de importadores.

GET /api/importadores/{id}
Detalle de un importador.

PUT /api/importadores/{id}
Modifica un importador (datos enviados desde el formulario del frontend).

POST /api/importadores
Crea un importador nuevo (datos enviados desde el formulario del frontend).

### 7.3. Usuarios (solo admin)

Protegidas por auth:sanctum + middleware admin.

GET /api/usuarios
Lista de usuarios con su rol principal.

POST /api/usuarios
Crea un nuevo usuario y lo asigna a un rol.

PUT /api/usuarios/{user}
Actualiza los datos del usuario (incluyendo rol).

DELETE /api/usuarios/{user}
Elimina un usuario.

---

## 8. Arquitectura y decisiones técnicas

- Laravel 11 por su madurez, ecosistema y rapidez de desarrollo.

- Sanctum para autenticación segura con SPA sin necesidad de JWT.

- Uso de roles mediante tablas roles y role_user, manteniendo una relación many-to-many flexible.

- Respeto a la estructura original de la tabla importad para facilitar la integración con sistemas existentes del OAST.

Estas decisiones se describen y justifican con mayor detalle en la memoria del TFM (capítulos de materiales, métodos y análisis de alternativas).

---

## 9. Pruebas 

El backend incorpora un conjunto de pruebas automatizadas con PHPUnit que cubren las funcionalidades esenciales del sistema. Estas pruebas validan tanto la seguridad como la correcta operación de la API REST, garantizando estabilidad y evitando regresiones durante el desarrollo.

Las pruebas implementadas incluyen:

- <b>Autenticación:</b> verificación de login, logout y protección de rutas (Sanctum).

- <b>Registro, verificación de correo y recuperación de contraseña:</b> validación completa del flujo de alta y mantenimiento de usuarios.

- <b>CRUD de usuarios (solo administradores):</b> creación, edición, eliminación y control de permisos.

- <b>CRUD de importadores:</b> creación con generación automática de CLAVIM, validaciones, actualización y borrado controlado por roles.

- <b>Validación de respuestas JSON y códigos de estado</b> en todos los endpoints.

Además, se han utilizado colecciones de Postman para pruebas manuales de la API durante el desarrollo.

Estas pruebas aportan una capa adicional de fiabilidad al sistema, asegurando que los componentes críticos funcionan correctamente y que la seguridad se mantiene de forma consistente.

---
