# Feature Spec: Marketplace UTS — Flujo end-to-end

**ID:** 001-marketplace-end-to-end
**Estado:** Draft
**Fecha:** 2026-05-23
**Owner:** Juan Otero

---

## 1. Resumen

UNI Market es un marketplace cerrado para la comunidad UTS donde estudiantes pueden:
publicar productos o servicios, los administradores los aprueban, y otros estudiantes
pueden contactar al vendedor mediante un chat interno tipo WhatsApp Web.

## 2. Objetivos (success criteria)

- Un estudiante puede registrarse con email/contraseña y queda con rol `estudiante`.
- Un estudiante autenticado puede publicar un producto/servicio; queda en estado `pendiente`.
- Un administrador puede `aprobar` o `rechazar` cada publicación pendiente.
- Solo los productos `aprobado` aparecen en la home pública.
- Un comprador autenticado puede abrir un chat asociado a un producto y a su vendedor.
- Las conversaciones del usuario se listan ordenadas por última actividad.

## 3. Actores

| Actor          | Capacidades                                                              |
|----------------|--------------------------------------------------------------------------|
| Visitante      | Ver home con productos aprobados, registrarse, iniciar sesión.           |
| Estudiante     | Publicar, editar y eliminar sus productos. Chatear con otros vendedores. |
| Administrador  | Aprobar/rechazar productos, eliminar publicaciones, ver todos los users. |

## 4. Historias de usuario

### US-1 — Registro
> Como visitante, quiero registrarme con nombre, email y contraseña para poder publicar y chatear.
- **Aceptación:** tras registro queda autenticado y redirigido a `/dashboard`; su rol es `estudiante`.

### US-2 — Publicación
> Como estudiante, quiero publicar un producto con nombre, tipo, especificación, descripción, precio, contacto e imagen opcional.
- **Aceptación:** el producto queda `pendiente` y aparece en `/mis-productos`, no en `/`.

### US-3 — Moderación
> Como administrador, quiero aprobar o rechazar las publicaciones pendientes.
- **Aceptación:** `/aprobar/{id}` y `/rechazar/{id}` cambian el estado; bloqueado para no-admin.

### US-4 — Home pública
> Como visitante, quiero ver solo productos aprobados, filtrables por tipo.
- **Aceptación:** la consulta `Producto::where('estado','aprobado')` alimenta `/`.

### US-5 — Chat
> Como comprador autenticado, quiero abrir una conversación con el vendedor de un producto y enviarle mensajes.
- **Aceptación:** la primera apertura crea un mensaje inicial; `chat.store` valida `receptor_id`, `producto_id`, `mensaje`.

### US-6 — Listado de chats
> Como usuario, quiero ver mis conversaciones agrupadas por contraparte, con el último mensaje y un timestamp relativo.

## 5. Reglas de negocio

- Solo el propietario puede editar/eliminar su producto (`abort(403)` en caso contrario).
- Solo `rol == admin` accede a `/admin` y rutas de moderación.
- Imagen opcional (max 2 MB, jpg/png/jpg/gif); placeholder si no hay.
- `mensaje` máx. 2000 caracteres.

## 6. Fuera de alcance (no goals)

- Notificaciones por email/push.
- Pagos en línea.
- WebSockets / mensajería en tiempo real (la UI actualiza al refrescar).
- Calificaciones o reputación de vendedores.

## 7. Riesgos

- `ChatController` usa raw queries con interpolación de `$userId` — está acotado (cast a `int`) pero conviene migrar a query bindings.
- No hay rate limit en `chat.store` ni en `guardar`.
- Subida de imágenes no valida dimensiones (solo MIME y tamaño).

## 8. Métricas

- Tiempo medio para aprobar una publicación: < 24h (manual).
- % de publicaciones aprobadas vs rechazadas (admin dashboard).
