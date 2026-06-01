# Plan de implementación — 001-marketplace-end-to-end

> Plan de cómo la spec se mapea al código actual y qué cambios técnicos hacemos.

## Stack

- Laravel 13, PHP 8.3
- MySQL en dev (`marketplace`), SQLite `:memory:` en tests
- Pest 4 + pest-plugin-laravel
- Tailwind + Vite para UI
- Sesiones en BD (`SESSION_DRIVER=database`)

## Esquema de datos (consolidado)

```
users
  id, name, email (uniq), email_verified_at, password,
  rol ENUM('admin','estudiante') DEFAULT 'estudiante',
  remember_token, timestamps

productos
  id, nombre, tipo ENUM('producto','servicio'), especificacion?, imagen?,
  descripcion, precio DECIMAL(10,2), contacto,
  estado ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  user_id FK users.id ON DELETE CASCADE, timestamps

mensajes
  id, emisor_id FK users.id, receptor_id FK users.id,
  producto_id FK productos.id, mensaje TEXT, timestamps
  INDEX (emisor_id, receptor_id, producto_id)
```

Tres tablas de auth/cache/queue se mantienen del scaffolding de Laravel.

## Routing

- Públicas: `/`, `/login`, `/register`, `/forgot-password`, `/reset-password`.
- Auth: `/dashboard`, `/crear`, `/guardar`, `/mis-productos`, `/producto/{id}/{editar,actualizar}`, `DELETE /producto/{id}`.
- Auth+admin: `/admin`, `/aprobar/{id}`, `/rechazar/{id}`, `DELETE /admin/producto/{id}`.
- Chat (auth): `GET /chats`, `GET /chat?receptor_id&producto_id`, `POST /chat/mensaje`.

## Componentes

- `ProductoController` — CRUD + filtro por estado en `index()`.
- `AdminController` — moderación. `verificarAdmin()` privado, idealmente futura migración a middleware `EnsureAdmin`.
- `ChatController` — Listado y vista de conversación; queries directas a `mensajes`.
- Vistas en `resources/views/{productos,admin,chat,auth,profile,layouts,components}`.

## Decisiones técnicas

1. **Migraciones consolidadas:** colapsamos las 9 originales en 5 (auth+cache+queue del scaffold, productos, mensajes). El admin se mueve a un seeder (`AdminUserSeeder`).
2. **`rol` en User#fillable** vía atributo PHP `#[Fillable]` para permitir factories y seeders.
3. **`ProductoFactory`** con estados `aprobado()`, `rechazado()`, `servicio()`.
4. **Sin MensajeModel:** el chat funciona con `DB::table('mensajes')`. No vale la pena un Eloquent Model para el alcance actual.
5. **Tests sobre SQLite in-memory** vía `phpunit.xml` para que `php artisan test` sea instantáneo.

## Riesgos conocidos

- `ChatController::buildConversations()` usa `groupByRaw` y `selectRaw` con interpolación — los IDs son enteros casteados, pero conviene migrar a query bindings con `?` antes de producción.
- El `enum` en MySQL para `rol` y `estado` no es portable; si más adelante se mete PostgreSQL, sustituir por `string` + check.

## Cómo correr

```bash
php artisan migrate:fresh --seed   # esquema + admin user
php artisan storage:link           # imágenes públicas
php artisan test                   # corre Pest contra SQLite in-memory
composer dev                       # serve + queue + pail + vite
```
