# Ruta Transporte - API de Gestión de Rutas

Sistema de gestión y rastreo de rutas para empresa de transporte.

## Requisitos

- PHP ^8.2
- Composer
- MySQL 8.0+ / MariaDB 10.4+
- Extensión PHP: `pdo_mysql`, `mysqli`

## Instalación

```bash
composer install
cp .env .env  # o .env.example a .env si es necesario
php artisan key:generate
```

Configurar base de datos en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ruta_transporte
DB_USERNAME=root
DB_PASSWORD=
```

Ejecutar migraciones y seeders:

```bash
php artisan migrate:fresh --seed
```

## Endpoints API

### Autenticación (pública)

#### `POST /api/login`

Inicia sesión y devuelve un token de Sanctum.

**Body:**
```json
{
    "email": "admin@rutatransporte.com",
    "password": "password"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Inicio de sesión exitoso",
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "Admin Principal",
        "email": "admin@rutatransporte.com",
        "role": "Administrador"
    }
}
```

**Error (422):**
```json
{
    "message": "Las credenciales proporcionadas son incorrectas.",
    "errors": { "email": ["..."] }
}
```

---

### Autenticados (requieren `Authorization: Bearer <token>`)

#### `POST /api/logout`

Cierra la sesión y revoca el token actual.

**Respuesta (200):**
```json
{
    "message": "Sesión cerrada exitosamente"
}
```

#### `GET /api/me`

Obtiene los datos del usuario autenticado.

**Respuesta (200):**
```json
{
    "user": {
        "id": 1,
        "name": "Admin Principal",
        "email": "admin@rutatransporte.com",
        "role": "Administrador"
    }
}
```

---

## Seeders

| Usuario | Email | Rol | Contraseña |
|---|---|---|---|
| Admin Principal | admin@rutatransporte.com | Administrador | password |
| Carlos López | carlos@rutatransporte.com | Chofer | password |
| María García | maria@rutatransporte.com | Chofer | password |

## Pruebas

```bash
php artisan test --testsuite=Feature --filter=AuthTest
```

## Estructura del proyecto

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           └── AuthController.php
├── Models/
│   ├── Role.php
│   └── User.php
database/
├── factories/
│   ├── RoleFactory.php
│   └── UserFactory.php
├── migrations/
│   ├── ...create_users_table.php
│   ├── ...create_roles_table.php
│   └── ...add_role_id_to_users_table.php
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── RoleSeeder.php
│   └── UserSeeder.php
routes/
└── api.php
tests/
└── Feature/
    └── AuthTest.php
```
