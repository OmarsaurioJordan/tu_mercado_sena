🛒 Tu Mercado SENA - Backend API

Versión: 1.0
Framework: Laravel 12
Autenticación: JWT (Tymon JWTAuth)
Formato de respuesta: JSON
Estado: 🚧 En desarrollo (faltan rutas que serán complementadas con el tiempo)

🧭 Descripción General

El Backend de Tu Mercado SENA fue diseñado para manejar peticiones HTTP, procesarlas, interactuar con la base de datos y devolver respuestas estructuradas en formato JSON.

Sigue la arquitectura MVC y aplica el patrón Repository-Service, lo que garantiza una mejor separación de responsabilidades, escalabilidad y facilidad de mantenimiento.

🌐 RUTAS DE LA API

⚠️ Nota: Actualmente están disponibles solo las rutas del módulo de autenticación.
Otras rutas (productos, chats, favoritos, etc.) serán añadidas progresivamente conforme avance el desarrollo.

🔓 RUTAS PÚBLICAS
1️⃣ Registro de usuario

Método: POST
Ruta: http://localhost:8000/api/auth/register

Restricciones:

Campo	Restricción
correo_id	Solo se aceptan correos institucionales @sena.edu.co
password	Mínimo 8 caracteres, debe incluir números, no estar comprometida, y coincidir con password_confirmation
nombre	Máximo 24 caracteres
descripcion	Máximo 300 caracteres
link	Debe ser una red social válida: YouTube, Instagram, Facebook, Twitter o LinkedIn

Ejemplo JSON:

```JSON
{
 "correo_id": "juan.perez@sena.edu.co",
 "password": "Password123",
 "password_confirmation": "Password123",
 "nombre": "Juan Pérez",
  "avatar": 1,
  "descripcion": "Estudiante de desarrollo",
  "link": "https://instagram.com/juanperez",
  "device_name": "web"
}
```

Respuesta (201 - Created):


```JSON
{
  "user": { ... },
  "token": "xxxxx",
  "token_type": "bearer",
  "expires_in": 3600
}
```

2️⃣ Inicio de sesión

Método: POST
Ruta: http://localhost:8000/api/auth/login

Restricciones:

Correo y contraseña son obligatorios.

device_name solo puede ser: desktop, mobile o web.

Mensajes posibles:

❌ Correo o contraseña incorrectos

🚫 Esta cuenta ha sido desactivada

⚠️ No cuentas con el rol para acceder a este dispositivo
Ejemplo JSON:

```JSON
{
  "correo_id": "omar.jordan@sena.edu.co",
  "password": "omarJordan1234",
  "device_name": "desktop"
}
```

Respuesta (200 - OK):

```JSON
{
  "user": { ... },
  "token": "xxxxx",
  "token_type": "bearer",
  "expires_in": 3600
}
```

🔒 RUTAS PROTEGIDAS

Estas rutas requieren un token JWT válido en los headers:

Authorization: Bearer {token}

1️⃣ Cerrar sesión

Método: POST
Ruta: http://localhost:8000/api/auth/logout

Cuerpo opcional:

```JSON
{
  "all_devices": false
}
```

Respuesta:

```JSON
{
  "message": "Sesión cerrada correctamente"
}
```

💡 Si all_devices = true, se intentará cerrar sesión en todos los dispositivos. (En pruebas)

2️⃣ Refrescar token

Método: POST
Ruta: http://localhost:8000/api/auth/refresh

Descripción:
Renueva el token cuando le queda menos de 5 minutos antes de expirar.

Respuesta:

```JSON
{
  "message": "Token refrescado correctamente",
  "data": {
    "token": "xxxxx",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```
3️⃣ Obtener usuario autenticado

Método: GET
Ruta: http://localhost:8000/api/auth/me

Respuesta:

```JSON
{
  "user": { ... }
}
```

🧩 ESTRUCTURA Y COMPONENTES DEL CÓDIGO
📦 DTOs (Data Transfer Objects)

Los DTOs encapsulan los datos que se transfieren entre capas, evitando manipular directamente el request y garantizando validación y seguridad.

DTO	Atributos	Descripción
LoginDTO	correo_id, password, device_name	Gestiona datos de inicio de sesión
RegisterDTO	correo_id, password, nombre, avatar, descripcion, link	Gestiona datos del registro de usuario

Métodos comunes:

fromRequest() → Crea el DTO a partir del request validado.

toArray() → Devuelve los datos como arreglo.

👤 Modelo: Usuario

Define la tabla usuarios y sus propiedades.
Oculta el campo password y agrega relaciones con roles y estados.

Métodos clave:

getJWTIdentifier() → ID único del usuario para JWT

getJWTCustomClaims() → Agrega información personalizada (correo, nombre, rol, estado, avatar)

⚙️ Servicio de Autenticación (AuthService)

Centraliza la lógica de negocio de autenticación.
Cumple con el principio Single Responsibility (SOLID).

Método/Función
**register()**  	
Crea usuario y genera token
**login()**  
Valida credenciales, rol, estado y dispositivo
**logout()**  
Cierra sesión (actual o global)
**refresh()**  Refresca token JWT
**getCurrentUqser()**  Retorna usuario autenticado
**isRecentlyActive()**	Comprueba actividad reciente

🗃️ Repositorio e Interfaz
UserRepositoryInterface

Define los métodos base:

create()

findByEmail()

findById()

updateLastActivity()

exists()

invalidateAllTokens()

UserRepository

Implementa la interfaz usando Eloquent ORM:

create() → Crea usuario, hashea contraseña y asigna rol/estado.

findByEmail() / findById() → Búsqueda directa.

updateLastActivity() → Actualiza fecha de actividad.

invalidateAllTokens() → Cierra sesión global.

🧱 Middleware: ValidateJWTToken

Valida y protege las rutas que requieren autenticación.

Funciones clave:

Comprueba validez y expiración del token.

Rechaza usuarios eliminados (estado_id = 3).

Detecta tokens inválidos o expirados.

Maneja errores personalizados:

TokenExpiredException

TokenInvalidException

JWTException

🧭 Controlador: AuthController

Conecta las peticiones HTTP con el servicio AuthService.

Responsabilidades:

Recibir y validar el Request

Crear DTOs

Delegar la lógica al servicio

Devolver respuestas JSON coherentes

Códigos de respuesta:

Código	Significado
200	Operación exitosa
201	Registro completado
401	Token inválido / no autenticado
422	Error de validación
500	Error interno del servidor
🧠 Conclusión

El backend de Tu Mercado SENA está estructurado bajo principios de arquitectura limpia:
Controller → Service → Repository → Model

Esto permite mantener un código modular, escalable y de fácil mantenimiento.
