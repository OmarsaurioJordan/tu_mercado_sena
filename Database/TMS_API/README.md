# API para Tu Mercado Sena - Desktop

Autogenerado con IA de Copilot...

## Descripción general

Este repositorio alberga el conjunto de scripts PHP que conforman la API administrativa de la aplicación **Tu Mercado Sena**. La interfaz se divide en módulos lógicos que exponen recursos mediante llamadas HTTP GET. No existe lógica de escritura simultánea desde clientes públicos; las operaciones de modificación requieren credenciales administrativas.

Todas las rutas se ejecutan en el directorio raíz del proyecto (`c:\xampp\htdocs\TMS_API\`) y comienzan por subcarpetas tales como `usuarios`, `productos`, `chats`, etc. Los parámetros se transmiten mediante la cadena de consulta (`?clave=valor`). Las respuestas se devuelven en formato JSON y, en caso de error, se establece un código de estado HTTP adecuado y se incluye un objeto con la clave `error`.

### Configuración (`config.php`)

- **Conexion a la base de datos**: las variables `$host`, `$user`, `$pass` y `$db` deben apuntar a la instancia MySQL.
- **Modo debug** (`$debug = true`): cuando está activado se omiten las comprobaciones de token y de auditoría, facilitando pruebas locales. En producción debe ser `false`.
- **Funciones de apoyo**:
  - `validation()`: verifica `admin_email` y `admin_token` contra la tabla `tokens_de_sesion`; si fallan, aborta la ejecución con un error HTTP 400/404.
  - `auditar($suceso_id, $descripcion)`: registra una entrada en `auditorias` asociada al administrador actual. No realiza ninguna acción cuando `$debug` está activado.

### Parámetros comunes a las consultas

- `cursor_fecha` y `cursor_id`: implementan paginación de tipo checkpoint (desplazamiento hacia atrás en el tiempo). `cursor_fecha` toma la fecha del último elemento recibido y `cursor_id` complementa la condición cuando hay varios registros con la misma marca temporal.
- `limite`: número máximo de registros a devolver.
- `id`: request para un registro específico; suele anular los demás filtros.
- `registro_desde` / `registro_hasta`: acotan rango habitualmente sobre `fecha_registro`.
- Filtros específicos de cada módulo, documentados más abajo.
- `admin_email` y `admin_token`: credenciales necesarias para cualquier    operación que modifique datos (login, set_estado, etc.). Se obtienen mediante el endpoint de login administrativo.

Más allá de estos, cada módulo define sus propios parámetros de consulta.

---

## Módulos y endpoints

### Herramientas auxiliares (`api/tools`)

| Script | Método | Descripción | Parámetros | Respuesta principal |
|--------|--------|-------------|------------|---------------------|
| `get_data.php` | GET | Obtiene listas de tablas simples de referencia (`roles`, `categorias`, etc.) | `tabla` (roles, categorias, estados, integridad, motivos, subcategorias, sucesos) | Array de objetos con `id`, `nombre` y un campo `extra` cuyo significado depende de la tabla |
| `get_help.php` | GET | Valores contados de denuncias y PQRS activas | ninguno | `[ { "denuncias": <n>, "pqrss": <n> } ]` |
| `informacion.php` | GET | Estadísticas generales de usuarios, productos y chats | ninguno | Objeto con varios contadores según estado |
| `responder.php` | GET | Inserta una notificación para un usuario | **requeridos**: `id`, `mensaje`, `motivo_id`; además `admin_email`, `admin_token` | `{ "Ok": "1" }` o `{ "Ok": "0" }` |

También está el endpoint `api/tools/master` sin argumentos, que crea un usuario administrador si no existe ninguno aún, usar esto con precaución para debug

### Usuarios (`api/usuarios`)

#### `index.php` (consulta)
Filtra y devuelve usuarios.
**Parámetros** (todos GET opcionales salvo `id` en casos puntuales):
- `nickname` (cadena, coincidencia parcial).
- `rol_id` (1=usuario, 2=moderador; el rol 3 está excluido de las búsquedas normales).
- `estado_id` (1 activo, 2 invisible, 3 eliminado, 4 bloqueado, 10 bloqueado por denuncia; 100 y 101 representan agrupaciones). 
- `con_link`, `con_descripcion`, `con_productos` (1 para exigir que el campo no esté vacío o exista al menos un producto asociado).
- `dias_activo` (registra recientes dentro de los últimos N días)
- `registro_desde`, `registro_hasta` (filtra por fecha de registro)
- `email` (busca por correo electrónico; sobrescribe otros filtros y excluye rol 3)
- `id` (obtiene un usuario concreto)
- Paginación: `cursor_fecha`, `cursor_id`, `limite`.

**Respuesta**: lista de usuarios con campos `id`, `email`, `rol_id`, `nickname`, `imagen`, `descripcion`, `link`, `estado_id`, `fecha_registro`, `fecha_actualiza`, `fecha_reciente`.

#### `set_data.php` (actualización)
Modifica nickname, descripción, link, PIN o contraseña de un usuario.
**Parámetros requeridos**: `id` más al menos uno de (`nickname`, `descripcion`, `link`, `pin`, `password`).
Requiere `admin_email` y `admin_token`. Realiza hashing automático para la contraseña.
**Respuesta**: `{ "Ok": "1" }` en caso de éxito.

#### `set_rol.php` y `set_estado.php` (administración)
- `set_rol.php`: cambia el `rol_id` a 1 o 2. Requiere `id` y `rol`.
- `set_estado.php`: cambia el `estado_id` entre 1 y 4. Requiere `id` y `estado`.
Ambos endpoints requieren credenciales administrativas y devuelven `{ "Ok": "1" }` en caso de éxito; además registran una auditoría.


### Productos (`api/productos`)

#### `index.php` (consulta)
Opciones de filtrado:
- `nombre` (texto parcial).
- `subcategoria_id`, `categoria_id`, `integridad_id`.
- `estado_id` (misma codificación que usuarios, con agrupaciones 100/101).
- `precio_min`, `precio_max`.
- `con_descripcion` (1 para exigir descripción).
- Rangos de registro y `id` específico.
- Paginación: `cursor_fecha`, `cursor_id`, `limite`.

Respuesta: array de productos con sus metadatos y un subcampo `imagenes` que contiene una lista de URLs.

#### `set_estado.php`
Actualiza el estado de un producto (1‑4). Parámetros `id` y `estado`. Credenciales administrativas obligatorias.


### PQRS (`api/pqrss`)

#### `index.php` (consulta)
Filtra por:
- nickname del usuario quien generó la PQRS
- `motivo_id`
- `estado_id` 1=activo, 11=resuelto
- rangos de fecha, correo electrónico o `id` específico
- `limite`, `cursor_fecha`, `cursor_id`

Respuesta: objetos con información básica del PQRS y del usuario, incluyendo días transcurridos.

#### `set_estado.php`
Cambia el estado a 11 (resuelto). Requiere `id` y `estado`. Credenciales administrativas.


### Denuncias (`api/denuncias`)

#### `index.php` (consulta)
Criterios similares a los de PQRS, con adiciones:
- `motivo_id`
- `estado_id` 1=activo, 11=resuelto
- `tipo` 1=usuario, 2=producto, 3=chat (determinado según presencia de `chat_id` o `producto_id`).

Respuesta: información ampliada que incluye nombres de afectados y objetos relacionados.


### Chats (`api/chats`)

#### `index.php` (consulta)
Permite buscar por `estado_id` (1,3,5,8,9), `comprador_id`, `vendedor_id`, intervalos de `fecha_venta`, `id`, `limite` y paginación.

Respuesta: datos de la venta, calificación, nombres de comprador/vendedor, etc.

#### `set_estado.php`
Modifica el estado a uno de los valores permitidos (1,3,5,8,9). Requiere credenciales de administrador.


### Mensajes (`api/mensajes`)

Enumera los mensajes de un chat. Parámetros de filtrado:
- `palabras` (texto dentro del mensaje)
- `chat_id` obligatorio para acotar a un chat concreto (aunque no es requerido, sin él devuelve todos)
- `con_imagen` (1 para exigir imagen adjunta)
- rangos de fecha, `id`, `limite`, `cursor_fecha`, `cursor_id`.

Nota: este endpoint invoca `validation()` porque los mensajes son considerados sensibles.


### Papelera (`api/papelera`)

Recupera los elementos de la tabla `papelera` (mensajes borrados). Filtros:
- `usuario_id`
- `con_imagen` (1/0 para incluir/excluir imágenes)
- rangos de fecha, `id`, `limite`, paginación.
Requiere validación administrativa.


### Auditorías (`api/auditorias`)

Listado de registros de acciones administrativas. Filtros:
- `nickname`, `suceso_id`
- intervalo de fechas, `email`, `id`, `limite`, paginación.


### Logins (`api/logins`)

Historial de intentos de inicio de sesión. Parámetros:
- `nickname`, `email`, intervalos de fecha, `id`, `limite`, paginación.


### Rutas públicas de administración

- `admin/admin_login.php`: obtiene un token de sesión. Parámetros `email` y `password`. Devuelve `{ "token": "<jti>", "id": <usuario_id> }` en caso de autenticación exitosa.
- `admin/admin_pin.php`: verifica el PIN de un administrador. Requiere `email` y `pin`. No necesita token.
- `admin/master_info.php`: devuelve la descripción y enlace del usuario con rol "master".


## Uso típico

1. **Obtener token**: solicitar `admin/admin_login.php` con credenciales. El token (`jti`) se utiliza como `admin_token` en llamadas subsecuentes.
2. **Ejecutar consultas**: llamar a los endpoints de consulta con filtros adecuados.
3. **Modificar recursos**: incluir `admin_email` y `admin_token` en la URL y acceder a los scripts `set_*`.

> **Importante:** cuando `$debug` en `config.php` está en `true`, los métodos `validation()` y `auditar()` no realizan ninguna comprobación ni registro. Esta configuración sólo debe emplearse en entornos de prueba.

## Consideraciones adicionales

- Todas las búsquedas se retornan ordenadas por fecha descendente y, en caso de empate, por identificador.
- Los estados numéricos se documentan en tablas de referencia (`estados`, `motivos`, etc.), accesibles mediante `tools/get_data.php`.
- Los endpoints de escritura devuelven `Ok` con valor `0` o `1`; los lectores devuelven listas o un error 404 cuando no se encuentra nada.

---

Esta documentación debería abarcar el conjunto de funcionalidades disponibles en la API. Para ampliar o modificar comportamientos, consulte los archivos PHP correspondientes.

