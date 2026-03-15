# Resumen del Frontend – Qué hay y qué falta

## 1. Lo que hay hoy

### Estructura del Frontend
- **Auth:** login, register, logout, welcome, forgot_password, reset_password, verificar_registro.
- **Productos:** index (listado), producto (detalle), publicar, editar_producto, eliminar_producto, mis_productos.
- **Chat:** chat (conversación), mis_chats, contactar (iniciar chat), chats.php.
- **Perfil:** perfil, perfil_publico, vendedor, bloqueados, bloqueado, favoritos, historial.
- **Soporte:** contacto, politicas, pqrs.
- **Admin:** ver_denuncias.
- **API PHP (Frontend/api):** ~23 endpoints que replican lógica (productos, chats, mensajes, bloqueos, favoritos, reportes, etc.).

### Autenticación actual
- **Login / Register / Logout:** 100 % PHP (formularios POST, `config.php`, `getDBConnection()`, sesión en `$_SESSION`).
- **Recuperar contraseña:** Solo flujo PHP de 2 pasos (correo → nueva contraseña), sin código por email.
- No se usa JWT ni los endpoints de Laravel para auth.

### Consumo de API
- **api-config.js:** `USE_LARAVEL: false`. Hay mapeo de nombres PHP → Laravel pero no se usa porque la bandera está en false.
- **script.js:** Usa `getApiUrl(endpoint)` que devuelve `window.BASE_URL + endpoint` (no usa `getApiUrlForFrontend`). Todas las llamadas van a **Frontend/api** (PHP).
- **Páginas PHP:** Login, register, contactar, publicar, editar, forgot_password, etc. usan directamente la BD vía `config.php` y `getDBConnection()`.

### Funcionalidades que sí están
- Listado y detalle de productos, publicar/editar/eliminar producto.
- Chats: listar, ver mensajes, enviar mensaje, enviar imagen, eliminar chat.
- Bloqueos: listar bloqueados, bloquear/desbloquear (vía PHP o según versión, algún fetch).
- Favoritos, reportar producto, denunciar usuario.
- Confirmación de venta y devoluciones (solicitar/responder) vía Frontend/api.
- Perfil, editar perfil, avatar, historial de transacciones.
- PQRS, políticas, contacto.
- Admin: ver denuncias.
- Cierre automático de chats (config + cron/script).

---

## 2. Lo que falta (API Laravel)

Para que el front **consuma la API Laravel** en lugar de (o además de) PHP:

### 2.1 Configuración y enrutado
- **Activar Laravel en el front:** Poner `USE_LARAVEL: true` en `js/api-config.js` y asegurar que la URL sea la correcta (ej. `http://127.0.0.1:8000/api/` si el servidor Laravel está en ese puerto).
- **Unificar rutas en JS:** Que `script.js` use una función tipo `getApiUrlForFrontend(endpoint)` que, según `USE_LARAVEL`, devuelva la URL de Laravel o la de Frontend/api (con mapeo de nombres y, si aplica, IDs en la ruta).
- **Headers y método:** En llamadas con Laravel, enviar `Accept: application/json`, `Content-Type: application/json` cuando toque, y `Authorization: Bearer {token}` en rutas protegidas.

### 2.2 Auth
- **Login:** Sustituir (o complementar) el POST a `login.php` por un `fetch` a `POST /api/auth/login` con `email`, `password`, `device_name`; guardar el token (ej. en `localStorage`) y redirigir.
- **Registro:** Si Laravel usa flujo con código por email:
  - Paso 1: `POST /api/auth/iniciar-registro` → enviar código.
  - Paso 2: `POST /api/auth/register` con `cuenta_id`, `clave`, `datosEncriptados`, `device_name` → completar registro y guardar token.
- **Logout:** Llamar `POST /api/auth/logout` con el token y limpiar sesión/token en el front.
- **Recuperar contraseña:** Consumir los 3 endpoints de Laravel:
  - `POST /api/auth/recuperar-contrasena/validar-correo`
  - `POST /api/auth/recuperar-contrasena/validar-clave-recuperacion`
  - `PATCH /api/auth/recuperar-contrasena/reestablecer-contrasena`  
  Flujo en la página: correo → código de 6 caracteres → nueva contraseña (y redirección al login).

### 2.3 Sesión y token
- Tras login/registro por Laravel, guardar el JWT (ej. `localStorage.setItem('api_token', data.token)`).
- En todas las peticiones a rutas protegidas, enviar el header `Authorization: Bearer {token}`.
- Opcional: refresco de token con `POST /api/auth/refresh` cuando falte poco para expirar.
- Decidir si se mantiene sesión PHP en paralelo (por ejemplo para compatibilidad con páginas que aún usan `isLoggedIn()` por servidor) o se migra todo a “sesión = token válido”.

### 2.4 Productos
- Listado: que la llamada que hoy va a `api/productos.php` pueda ir a `GET /api/productos` (con query params que Laravel acepte).
- Crear: que publicar use `POST /api/productos` con el body que espere Laravel (nombre, descripcion, subcategoria_id, integridad_id, precio, disponibles, etc.).
- Editar / eliminar: `PATCH` y `DELETE` a `/api/productos/{id}`.
- Cambio de estado (visible/oculto): `PATCH /api/productos/{id}/estado` con `estado_id`.
- Mis productos: `GET /api/mis-productos` (protegido).
- Adaptar las respuestas de Laravel al formato que espera el front (ej. paginación, nombres de campos).

### 2.5 Chats y mensajes
- Listar chats: que la llamada actual vaya a `GET /api/chats` y se adapte la respuesta (ej. array de chats con la estructura que use el front).
- Mensajes de un chat: `GET /api/chats/{id}` y mapear la respuesta a la que espera el front (ej. `messages` o `mensajes`).
- Enviar mensaje: `POST /api/chats/{chat_id}/mensajes` con `mensaje` (y opcional imagen según documentación).
- Eliminar chat: `DELETE /api/chats/{id}` (sin body).
- Iniciar chat: en `contactar.php` (o equivalente), en lugar de crear el chat por PHP, llamar `POST /api/productos/{producto_id}/chats` y redirigir al chat devuelto.
- Borrar mensaje (si el front lo usa): `DELETE /api/mensajes/{id}`.

### 2.6 Bloqueados
- Listar: `GET /api/bloqueados`.
- Bloquear: `POST /api/bloqueados/{usuario_id}`.
- Desbloquear: `DELETE /api/bloqueados/{bloqueado_id}`.
- Que las pantallas de bloqueados y las acciones desde perfil/chat usen estas rutas cuando `USE_LARAVEL` esté activo.

### 2.7 Transferencias (compraventa / devoluciones)
- Iniciar compraventa: `PATCH /api/chats/{id}/iniciar-compraventas` (cantidad, precio).
- Terminar compraventa: `PATCH /api/chats/{id}/terminar-compraventas` (confirmacion, comentario, calificacion).
- Iniciar devolución: `PATCH /api/chats/{id}/iniciar-devoluciones`.
- Terminar devolución: `PATCH /api/chats/{id}/terminar-devoluciones`.
- Listar transferencias: `POST /api/transferencias` y, si aplica, filtros con `POST /api/transferencias-filtros`.
- Estados: `GET /api/estados` para inputs o filtros que dependan de estados.

### 2.8 Perfil
- Editar perfil: `PATCH /api/editar-perfil/{usuarioId}` con los campos que acepte Laravel (imagen, nickname, descripcion, link, notificaciones, etc.), enviando `_method: PATCH` si el backend lo requiere.

### 2.9 Lo que Laravel no tiene (o no está documentado)
- Favoritos (toggle, listar).
- Valorar perfil / valoración en perfil público.
- Reportar producto (motivo, comentario).
- Denunciar usuario (desde chat u otro).
- Silencio de chat.
- Cierre automático de chats (cron).

Eso sigue dependiendo de **Frontend/api** (PHP) o de ampliar la API Laravel.

---

## 3. Otras cosas que pueden faltar

- **Documentación:** Un solo documento (ej. `ENDPOINTS_EN_USO.md` o este resumen) que liste qué endpoint del front llama a qué ruta de Laravel o de Frontend/api.
- **Registro con código:** Si se quiere alinear con Laravel, el flujo de registro debe ser en 2 pasos (iniciar-registro → register con código); hoy el registro es en un solo paso contra PHP.
- **Base URL y CORS:** Asegurar que la API Laravel tenga CORS habilitado para el origen del front (ej. `localhost/Nueva_carpeta/Frontend`) y que en el front la base URL de la API sea la correcta según entorno (dev/producción).
- **Manejo de errores:** Mensajes unificados cuando la API devuelve 401, 422, 500 (por ejemplo redirigir al login si el token expira).
- **Tests:** Pruebas automáticas del front (o al menos de los flujos críticos: login, listado, chat) contra la API real o un mock.

---

## 4. Resumen en una frase

**Hoy el front funciona entero con PHP y Frontend/api; no consume la API Laravel.** Para que “todo” pase por Laravel hace falta: activar y enrutar bien las llamadas (getApiUrl / getApiUrlForFrontend), migrar auth a JWT y a los endpoints de Laravel (login, registro, recuperar contraseña, logout), y conectar productos, chats, mensajes, bloqueados, transferencias y editar perfil a las rutas documentadas de la API; lo que no exista en Laravel (favoritos, valorar, reportar, denunciar, silencio, cierre automático) seguirá en Frontend/api o habría que implementarlo en el backend.
