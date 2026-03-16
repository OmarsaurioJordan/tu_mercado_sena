# Estado SQL vs API (tumercadosena.shop)

## Resumen

| Estado | Cantidad |
|--------|----------|
| **Ya no usan SQL** | 24+ archivos |
| **Sí usan SQL** | Solo `api/*.php` y `test_connection.php` |

---

## Ya no usan SQL (solo API)

| Archivo | Nota |
|---------|------|
| `index.php` | Categorías e integridad por API |
| `auth/login.php` | Login con `apiLogin()` |
| `auth/forgot_password.php` | Recuperar contraseña por API (3 pasos) |
| `auth/register.php` | Solo formulario API (iniciar-registro + register) |
| `auth/reset_password.php` | Sin SQL; redirige a recuperar contraseña por API |
| `productos/producto.php` | Detalle con `apiGetProducto()` y `apiGetChats()` |
| `productos/eliminar_producto.php` | Elimina con `apiEliminarProducto()` |
| `productos/publicar.php` | Publica con `apiCrearProducto()`; categorías por API |
| `config.php` | `getCurrentUser()`, `isSellerFavorite()`, imágenes: sesión/API. `getDBConnection()` sigue definida por compatibilidad. |
| `perfil/perfil.php` | `apiGetBloqueados`, `apiDesbloquearUsuario`, `apiEditarPerfil`, `apiUpdateAvatar`, API cuenta |
| `perfil/perfil_publico.php` | `apiGetPerfilPublico`, `apiGetFavoritos`, `apiGetProductosVendedor` |
| `perfil/vendedor.php` | `apiGetBloqueados`, `apiGetPerfilPublico`, `apiGetFavoritos` |
| `perfil/favoritos.php` | `apiGetFavoritosVendedores`, `apiToggleFavorito` |
| `perfil/bloqueados.php` | `apiGetBloqueados`, `apiDesbloquearUsuario` |
| `perfil/historial.php` | `apiGetHistorialVentas`, `apiGetHistorialCompras` |
| `perfil/calificar.php` | `apiGetChat` / historial, `apiCalificarChat` |
| `chat/mis_chats.php` | `apiGetChats()` |
| `chat/chat.php` | `apiGetChat`, `apiGetMensajes`, `apiMarcarVistoChat` |
| `chat/contactar.php` | `apiGetProducto`; creación de chat por front (POST API) |
| `soporte/pqrs.php` | `apiCrearPqrs`, `apiGetPqrs` |

---

## Sí usan SQL (pendientes)

### API (scripts en `/api/`)
Los scripts en `Web/api/*.php` (toggle_visibilidad, update_avatar, toggle_favorito, send_message, get_messages, etc.) siguen usando MySQL. Para dejarlos sin SQL habría que convertirlos en **proxy** hacia la API de Hostinger o que el front llame directamente a la API.

### API (scripts PHP en `/api/`)
| Archivo | Uso de SQL |
|---------|------------|
| `api/toggle_visibilidad.php` | SELECT/UPDATE productos estado |
| `api/update_avatar.php` | UPDATE usuarios avatar |
| `api/toggle_bloqueo.php` | SELECT/DELETE/INSERT bloqueados |
| `api/toggle_silencio.php` | SELECT chats, UPDATE silenciado |
| `api/toggle_favorito.php` | SELECT/DELETE/INSERT favoritos |
| `api/solicitar_confirmacion.php` | SELECT chat, INSERT mensajes |
| `api/solicitar_devolucion.php` | SELECT chat, INSERT mensajes |
| `api/responder_confirmacion.php` | SELECT chat, UPDATE fecha_venta, INSERT mensajes |
| `api/responder_devolucion.php` | SELECT chat, UPDATE estado, INSERT mensajes |
| `api/send_message.php` | SELECT chat/producto, INSERT mensajes, UPDATE visto, SELECT producto, INSERT notificaciones, SELECT cuenta, SELECT mensaje |
| `api/send_chat_image.php` | SELECT chat, INSERT mensajes, UPDATE visto |
| `api/get_messages.php` | SELECT chat, SELECT producto, SELECT mensajes, UPDATE visto |
| `api/get_chats_notificaciones.php` | SELECT chats (comprador/vendedor), SELECT mensajes |
| `api/productos.php` | SELECT productos con filtros, bloqueados, chats |
| `api/reportar_producto.php` | SELECT producto, SELECT/INSERT denuncias |
| `api/finalizar_venta.php` | SELECT chat/producto, UPDATE chats |
| `api/get_avatar.php` | SELECT usuarios avatar |
| `api/denunciar_usuario.php` | SELECT chat, CREATE TABLE, SELECT/INSERT denuncias |
| `api/eliminar_chat.php` | SELECT chat, CREATE TABLE chats_eliminados, INSERT, UPDATE estado |
| `api/cerrar_chats_automatico.php` | SELECT chats fecha_venta |
| `api/test_cierre_automatico.php` | SELECT chats, query cierres |

### Otros
| Archivo | Uso de SQL |
|---------|------------|
| `config.php` | Define `getDBConnection()` (mysqli); ya no usan SQL: getCurrentUser, isSellerFavorite, getProductImage/getProductMainImage |
| `test_connection.php` | Prueba conexión MySQL (SHOW TABLES, COUNT) |
