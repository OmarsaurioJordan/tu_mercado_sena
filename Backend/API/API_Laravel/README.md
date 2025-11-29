🛒 Tu Mercado SENA - Backend API

Versión: 1.0
Framework: Laravel 12
Autenticación: JWT (Tymon JWTAuth)
Formato de respuesta: JSON
Estado: 🚧 En desarrollo (faltan rutas que serán complementadas con el tiempo)

🧭 Descripción General

El Backend de Tu Mercado SENA fue diseñado para manejar peticiones HTTP, procesarlas, interactuar con la base de datos y devolver respuestas estructuradas en formato JSON.

Sigue la arquitectura MVC y aplica el patrón Repository-Service, lo que garantiza una mejor separación de responsabilidades, escalabilidad y facilidad de mantenimiento.

**Flujo que seguira el backed**

![image alt](https://github.com/Br4h7an005/tu_mercado_sena/blob/c558675e226f56b0bfd018dce878b73e56554620/Backend/API/API_Laravel/Flujo%20Backend.jpg)

🌐 RUTAS DE LA API

⚠️ Nota: Actualmente están disponibles solo las rutas del módulo de autenticación.
Otras rutas (productos, chats, favoritos, etc.) serán añadidas progresivamente conforme avance el desarrollo.

🔓 RUTAS PÚBLICAS
1️⃣ Registro de usuario

Método: POST
Ruta: http://localhost:8000/api/auth/inicio-registro

Restricciones:

Campo	Restricción

correo_id	Solo se aceptan correos institucionales @soy.sena.edu.co

password	Mínimo 8 caracteres, debe incluir números, no estar comprometida, y coincidir con password_confirmation

nombre	Máximo 24 caracteres

descripcion	Máximo 300 caracteres

link	Debe ser una red social válida: YouTube, Instagram, Facebook, Twitter o LinkedIn

Ejemplo JSON:

```JSON
{
 "correo": "XXXXXXXXX@soy.sena.edu.co",
 "password": "XXXXXXXXX",
 "password_confirmation": "XXXXXXXX",
 "rol_id": 1, // Prosumer
 "estado_id": 1 // Activo
 "nombre": "Julian",
  "avatar": 1,
  "descripcion": "Estudiante de desarrollo",
  "link": "https://instagram.com/Julian",
  "device_name": "web"
}
```

Respuesta (201 - Created):

🔓 RUTAS PÚBLICAS
2️⃣ Completar el registro del usuario

Método: POST
Ruta: http://localhost:8000/api/auth/register

Restricciones:

""

Ejemplo JSON:

```JSON
{
  "clave": "FIVLO6" // Ejemplo,
  "datosEncriptados": "eyJpdiI6I..."
}
```

Respuesta (201 - Created):

```JSON
{
  "message": "Usuario registrado correctamente",
  "user": {
    "correo_id": 5,
    "nombre": "Julian",
    "avatar": 1,
    "descripcion": "Estudiante de desarrollo",
    "link": "https://instagram.com/Julian",
    "rol_id": 1,
    "estado_id": 1,
    "id": 3,
    "rol": {
      "id": 1,
      "nombre": "prosumer",
      "created_at": null,
      "updated_at": null
    }
  },
  "token": "Token"
  "token_type": "bearer",
  "expires_in": 86400 // tiempo de expiración del token JWT
}
```

3️⃣ Recuperar contraseña: Validar Correo

Método: POST
Ruta: http://localhost:8000/api/auth/recuperar-contrasena/validar-correo

Restricciones:

El correo debe estar en la base de datos.

Mensajes posibles:

❌ Correo no registrado en la base de datos.

❌ El correo no es institucional (soy.sena.edu.co)

Ejemplo JSON:

```JSON
{
  "correo": "bxxxxxxxx@soy.sena.edu.co"
}
```

Respuesta (200 - OK):

```JSON
{
{
  "message": "Código de recuperación enviado correctamente",
  "id_correo": 5,
  "expira_en": "2025-11-29" // 🚧 Falta mejorar este apartado 
}
}
```

4️⃣ Recuperar Contraseña: Validar Clave

Mensajes posibles:

❌ El correo es obligatorio.

❌Correo Invalido.

❌Correo no registrado en la base de datos.
            
❌Debe ingresar el código de verificación
            
❌El código debe tener 6 caracteres.


Método: POST
Ruta: http://localhost:8000/api/auth/recuperar-contrasena/validar-clave-recuperacion

Restricciones:

id_correo = Debe ingresar el id del usuario.

clave = Clave que le llega al usuario al usuario.

Ejemplo JSON:

```JSON
{
  "id_correo": 5,
  "clave": "9AM50F"
}
```

Respuesta (200 - OK):

```JSON
{
  "success": true,
  "message": "Código verificado correctamente",
  "id_usuario": 3,
}
```

5️⃣ Recuperar Contraseña: Reestablecer Contraseña

Mensajes posibles:

❌ Usuario obligatorio. // Id del usuario obligatorio

❌Usuario invalido. // Id del usuario debe ser int

❌Usuario no registrado. // Usuario no registrado en la base de datos
            
❌Nueva contraseña requerida. // Contraseña no ingresada
            
❌Contraseña invalida. // La contraseña debe ser de tipo string

❌Las contraseñas no coinciden. // La confirmación de la contraseña debe coincidir


Método: PATCH
Ruta: http://localhost:8000/api/auth/recuperar-contrasena/reestablecer-contrasena

Restricciones:

id_correo = Debe ingresar el id del usuario.

clave = Clave que le llega al usuario al usuario.

Ejemplo JSON:

```JSON
{
  "id_correo": 5,
  "clave": "9AM50F"
}
```

Respuesta (201 - OK):

```JSON
{
  "success": true,
  "message": "Contraseña reestablecida correctamente"
}
```

6️⃣ Login

Mensajes posibles:

❌El correo es obligatorio. // El correo no fue enviado 

❌Debe ser un correo válido. // El correo no tipo email (@)

❌Correo o contraseña incorrectos // El correo no existe en la base de datos 
            
❌Nueva contraseña requerida. // Contraseña no ingresada
            
❌Contraseña invalida. // La contraseña debe ser de tipo string

✅Inicio de sesión exitoso.


Método: POST
Ruta: http://localhost:8000/api/auth/login

Restricciones:

El correo es obligatorio. // El correo no fue enviado 

Debe ser un correo válido. // El correo no tipo email (@)

Correo o contraseña incorrectos // El correo no existe en la base de datos 

La contraseña es obligatoria. // Front-end no envio la contraseña

El dispositivo debe ser: web, mobile o desktop

Ejemplo JSON:

```JSON
{
  "correo": "xxxxxx@soy.sena.edu.co",
  "password": "XXXXXXX",
  "device_name": "web"
}
```

Respuesta (201 - OK):

```JSON
{
  "message": "Inicio de sesión exisoto",
  "data": {
    "user": {
      "id": 3,
      "correo_id": 5,
      "rol_id": 1,
      "nombre": "Julian",
      "avatar": 1,
      "descripcion": "Estudiante de desarrollo",
      "link": "https://instagram.com/Julian",
      "estado_id": 1,
      "notifica_correo": 1,
      "notifica_push": 1,
      "uso_datos": true,
      "fecha_registro": "2025-11-29 00:45:32",
      "fecha_actualiza": "2025-11-29 02:22:47",
      "fecha_reciente": "2025-11-29 00:45:32",
      "rol": {
        "id": 1,
        "nombre": "prosumer",
        "created_at": null,
        "updated_at": null
      }
    },
    "token": "eyJ0eXA..." // Token JWT
    "expires_in": 86400 // Tiempo de expiracion
  }
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

**register()** -> Crea usuario y genera token

**login()** -> Valida credenciales, rol, estado y dispositivo

**logout()** -> Cierra sesión (actual o global)

**refresh()** -> Refresca token JWT

**getCurrentUser()** -> Retorna usuario autenticado

**isRecentlyActive()** -> Comprueba actividad reciente

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

**create()** → Crea usuario, hashea contraseña y asigna rol/estado.

**findByEmail()** / findById() → Búsqueda directa.

**updateLastActivity()** → Actualiza fecha de actividad.

**invalidateAllTokens()** → Cierra sesión global.

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
