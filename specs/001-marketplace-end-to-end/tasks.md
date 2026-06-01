# Tasks — 001-marketplace-end-to-end

Cada tarea es atómica, testeable y verificable. Marca `[x]` al completar.

## Backend

- [x] T1. Consolidar migraciones: `users` con `rol`, `productos` con `tipo/imagen`, `mensajes` con FKs y CASCADE.
- [x] T2. Mover el admin a `AdminUserSeeder` (idempotente con `updateOrCreate`).
- [x] T3. Añadir `rol` al `#[Fillable]` de `User`.
- [x] T4. Añadir trait `HasFactory` a `Producto` y crear `ProductoFactory` con estados.
- [ ] T5. (Opcional) Crear middleware `EnsureAdmin` y reemplazar `verificarAdmin()` privado.
- [ ] T6. (Opcional) Migrar queries crudas de `ChatController` a query bindings parametrizados.

## Tests (Pest)

- [x] T7. `phpunit.xml` con SQLite `:memory:`.
- [x] T8. `tests/Pest.php` con `RefreshDatabase` para Feature.
- [x] T9. `AuthTest`: registro, login, redirect dashboard, bloqueo admin.
- [x] T10. `ProductoTest`: solo aprobados en home, RBAC de edit/destroy, aprobar/rechazar.
- [x] T11. `ChatTest`: creación de conversación inicial, envío de mensaje, validación, end-to-end.
- [x] T12. `ProductoModelTest`: `imagen_url` placeholder vs storage, relación `user`.

## SpecKit

- [x] T13. `specs/001-marketplace-end-to-end/spec.md`.
- [x] T14. `specs/001-marketplace-end-to-end/plan.md`.
- [x] T15. `specs/001-marketplace-end-to-end/tasks.md` (este archivo).

## Verificación previa al test

- [ ] T16. `php artisan migrate:fresh --seed` en local MySQL.
- [ ] T17. `php artisan storage:link`.
- [ ] T18. `php artisan test --testsuite=Feature` (todos verdes).
- [ ] T19. Smoke manual: registro → publicar → aprobar como admin → chatear.

## Comandos de referencia

```bash
# Resetear BD y sembrar admin
php artisan migrate:fresh --seed

# Crear symlink para imágenes
php artisan storage:link

# Correr suite completa
php artisan test

# Solo un archivo
php artisan test tests/Feature/ChatTest.php

# Solo un test por nombre
php artisan test --filter "end-to-end"
```

## Credenciales por defecto

- Admin: `admin@uts.edu.co` / `admin123` (creado por seeder)
- Otros usuarios: crear por `/register` o `User::factory()`
