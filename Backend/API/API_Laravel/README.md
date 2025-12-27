🛒 Tu Mercado SENA - Backend API

Versión: 1.1
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

⚠️ Nota: Esta versión 1.1 se ajusto a la nueva bd con un cambio en donde se creo una tabla en donde guardara los tokens de sesion de los usuarios junto a los dispositivos.

**IMPORTANTE**


**Pasos para clonar Repositorio y configurar sus variables de entorno**

1️⃣ Clonar el repositorio usando el comando git clone (url)

2️⃣ En la dirección de carpeta ....\Backend\API\API_Laravel usar el comando
```cmd
composer install 
```
Para actualizar las dependencias

3️⃣ En la misma ventana de cmd usar el siguiente comando para generar un archivo .env
```CMD
cp .env.example .env
```

Si no funciona usar en la terminal de visual studio code

4️⃣ Generar la llave para usar comandos php artisan usando el siguiente comando:
```
php artisan key:generate
```

5️⃣Generar la jwt key para los tokens de autenticación usando este comando en la terminal
```
php artisan jwt:secret
```

6️⃣ Configurar las variables de entorno:

Configuración de la base de datos
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={nombre de la base de datos}
DB_USERNAME=root
DB_PASSWORD=
```

Configuración del servicio de mails (Configurar solo si se va comprobar que el correo se envio de manera exitosa a tu correo institucional):
```ENV
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME={Tu Correo de gmail u otro servicio}
MAIL_PASSWORD={Tu clave de aplicación de gmail o contraseña del servicio}
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS={Tu Correo de gmail u otro servicio}
MAIL_FROM_NAME="Mercado Sena"
```

Configuración de JW
```ENV
AUTH_GUARD=api
JWT_TTL=1440          # 24 horas en minutos
JWT_REFRESH_TTL=20160 # 2 semanas en minutos
JWT_ALGO=HS256
JWT_BLACKLIST_ENABLED=true
```

**⚠️Importante**

Y poner en los headers:

Accept: application/json

🔓 RUTAS PÚBLICAS
1️⃣ Registro de usuario

Método: POST
Ruta: http://localhost:8000/api/auth/inicio-registro

Restricciones:

Campo	Restricción

email:    Solo se aceptan correos institucionales @soy.sena.edu.co

password	Mínimo 8 caracteres, debe incluir números, no estar comprometida, y coincidir con password_confirmation

nombre	Máximo 24 caracteres

descripcion	Máximo 300 caracteres

link	Debe ser una red social válida: YouTube, Instagram, Facebook, Twitter o LinkedIn

Ejemplo JSON:

**rol_id: 1** = prosumer

**estado_id** = activo

```JSON
{
 "email": "xxxxxxx@soy.sena.edu.co",
 "password": "contraseña_prueba123",
 "password_confirmation": "contraseña_prueba123",
 "rol_id": 1, 
 "estado_id": 1,
 "nickname": "julian1223",
 "descripcion": "Estudiante de desarrollo",
 "link": "https://instagram.com/julian.https",
 "device_name": "web",
 "imagen": "Foto.jpg"
}
```

Respuesta (201 - Created):

```JSON
{
    "message": "Código enviado correctamente",
    "cuenta_id": 1,
    "expira_en": "2025-12-27 00:50:18",
    "datosEncriptados": "eyJpdiI6Im52VVRZTUVaaFV4UkpIc..."
}
```

2️⃣ Completar el registro del usuario

Método: POST
Ruta: http://localhost:8000/api/auth/register

Restricciones:

""

Ejemplo JSON:

```JSON
{
  "cuenta_id": 1,
  "clave": "IAO4LG",
  "datosEncriptados": "eyJpdiI...",
   "device_name": "web"
}
```

Respuesta (201 - Created):

```JSON
{
  "message": "Usuario registrado correctamente",
  "user": {
    "cuenta_id": 1,
    "nickname": "Julian1223",
    "imagen": "Foto.jpg",
    "descripcion": "Estudiante de desarrollo",
    "link": "https://instagram.com/julian.https",
    "rol_id": 1,
    "estado_id": 1,
    "fecha_actualiza": "2025-12-27 05:46:43",
    "fecha_registro": "2025-12-27 05:46:43",
    "id": 1,
    "estado": {
      "id": 1,
      "nombre": "activo"
    },
    "rol": {
      "id": 1,
      "nombre": "prosumer"
    }
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJI..",
  "token_type": "bearer",
  "expires_in": 86400
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
  "email": "bxxxxxxxx@soy.sena.edu.co"
}
```

Respuesta (200 - OK):

```JSON
{
  "message": "Código de recuperación enviado correctamente",
  "cuenta_id": 1,
  "expira_en": "2025-12-27 01:10:19"
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

cuenta_id = Debe ingresar el id del usuario.

clave = Clave que le llega al usuario al usuario.

Ejemplo JSON:

```JSON
{
  "cuenta_id": 1,
  "clave": "OYB0UE"
}
```

Respuesta (200 - OK):

```JSON
{
  "success": true,
  "message": "Código verificado correctamente",
  "cuenta_id": 1,
  "clave_verificada": true
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

 cuenta_id = Debe ingresar el id de la cuenta.

 password = La nueva contraseña del usuario.
 
 password_confirmation = Confirmación de la nueva contraseña


Ejemplo JSON:

```JSON
{
 "id_usuario": 1
 "password": "XXXXXXXXX",
 "password_confirmation": "XXXXXXXX",
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
  "email": "xxxxxxxxxx@soy.sena.edu.co",
  "password": "xxxxxxxx",
  "device_name": "web"
}
```

Respuesta (201 - OK):

```JSON
{
  "message": "Inicio de sesión exitoso",
  "data": {
    "user": {
      "id": 1,
      "cuenta_id": 1,
      "nickname": "xxxxxxxx",
      "imagen": "Foto.jpg",
      "descripcion": "Estudiante de desarrollo",
      "link": "https://instagram.com/whoIsBrahian",
      "rol_id": 1,
      "estado_id": 1,
      "fecha_registro": "2025-12-27 05:46:43",
      "fecha_actualiza": "2025-12-27 06:06:12",
      "fecha_reciente": "2025-12-27 01:06:12"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUW...",
    "token_type": "bearer",
    "expires_in": 86400
  }
}
```

🔒 RUTAS PROTEGIDAS

Estas rutas requieren un token JWT válido en los headers:

Authorization: Bearer {token}

1️⃣ Cerrar sesión

Método: POST
Ruta: http://localhost:8000/api/auth/logout

**Cuerpo opcional:**

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
  "message": "Token refrescado exitosamente",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhk3NTBhMzNjZSIsInVzdWFya...",
    "token_type": "bearer",
    "expires_in": 86400
  }
}
```
3️⃣ Obtener usuario autenticado

Método: GET
Ruta: http://localhost:8000/api/auth/me

Respuesta:

```JSON
 {
  "data": {
    "id": 1,
    "cuenta_id": 1,
    "nickname": "xxxxx",
    "imagen": "Foto.jpg",
    "descripcion": "Estudiante de desarrollo",
    "link": "https://instagram.com/xxxx",
    "rol_id": 1,
    "estado_id": 1,
    "fecha_registro": "2025-12-27 05:46:43",
    "fecha_actualiza": "2025-12-27 06:10:14",
    "fecha_reciente": "2025-12-27 01:10:14",
    "is_recently_active": true
  }
}
```


Código	Significado
200	Operación exitosa
201	Registro completado
401	Token inválido / no autenticado
422	Error de validación
500	Error interno del servidor
