# UNIMARKET UTS

Sistema de marketplace universitario desarrollado en Laravel.

## Funcionalidades

- Registro y login
- Publicación de productos
- Panel de administración

## Configuración

- Base de datos recomendada: MySQL
- Variables de entorno por defecto: ver [.env.example](.env.example)
- Antes de ejecutar migraciones, crea la base de datos `marketplace`
- Para pruebas, crea la base de datos `marketplace_test`

## Usuario administrador

- La migración [database/migrations/2026_05_01_000000_create_admin_user.php](database/migrations/2026_05_01_000000_create_admin_user.php) crea o actualiza el usuario `admin@uts.edu.co`
- Contraseña: `admin123`
