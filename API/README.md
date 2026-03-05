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

**Configuración para que Laravel no requiera de una tabla sesiones y/o cache en la BD en el archivo .env**
```ENV
SESSION_DRIVER=file
```

```ENV
CACHE_STORE=file
```


**Configuración para incluir el puerto para asegurar que las urls de las imagenes sean accesibles**
```ENV
APP_URL=http://127.0.0.1:8000
```

Configuración del servicio de mails (Configurar solo si se va comprobar que el correo se envio de manera exitosa a tu correo):
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

Configuración de JWT
```ENV
AUTH_GUARD=api
JWT_TTL=1440          # 24 horas en minutos
JWT_REFRESH_TTL=20160 # 2 semanas en minutos
JWT_ALGO=HS256
JWT_BLACKLIST_ENABLED=true
```

Configurar para que el sistema permita correos de gmail en el archivo .env:

```env
ALLOW_GMAIL=true
```

**👁️OJO** Los correos gmail no pueden subir avatares, subir imagenes en los chats

**⚠️Importante**


1️⃣ Poner en los headers lo siguiente:  

**Accept: application/json**


2️⃣ Hacer las migraciones de las tablas usando este comando en la terminal teniendo la base de datos ya creada: 

👁️ **OJO**

El siguiente comando borra todos los registros que tengas en la base de datos que configuraste, si la base de datos tiene registros en las tablas hacer copia de seguridad

```CMD
php artisan migrate:fresh
```

**Configurar e instalar Framework intervention Image para subir imagenes**

1️⃣ En el cmd poner el siguiente comando para instalarlo
```CMD
composer require intervention/image-laravel
```

2️⃣ Configurar la extensión para que laravel la pueda usar
```CMD
php artisan vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"
```

3️⃣ En la configuración de php.ini (Desde Xammp, activar apache, config, php.ini) decomentar la siguiente linea:

Comentada
**;extension=gd**

Descomentada
**extension=gd**

4️⃣Ejecutar el siguiente comando para crear un enlace simbiotico de las imagenes. Esto es necesario para que la carpeta publica pueda acceder a los archivos subidos.

```CMD
php artisan storage:link
```


🔓 RUTAS PÚBLICAS

1️⃣ Registro de usuario

Método: POST
Ruta: http://localhost:8000/api/auth/iniciar-registro

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
 "cuenta_id": 1
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
      "link": "https://instagram.com/julian.https",
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

**🔒 RUTAS PROTEGIDAS**

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

4️⃣ Editar Perfil usuario

Método: POST

ruta: Ruta: http://localhost:8000/api/editar-perfil/{usuarioId}

**usuarioId = Usuario que quiere cambiar sus datos**

**👁️IMPORTANTE**

Enviar petición usando **_method: PATCH** para que se envie los datos correctamente

Ejemplo de uso:

```JSON
{
  "_method": "PATCH",
  "imagen": "Nueva_foto",
  "nickname": "Nuevo Nickname",
  "descripcion": "Nueva_descripcioón",
  "link": "Nueva red social"
  "notifica_push": true,
  "notifica_correo": false
}
```

**⚠️Nota:** Se pueden enviar todos los datos o se pueden mandar uno, no tendra ninguna afectación al momento de actualizar los datos

Respuesta:

```JSON
{
  "status": "success",
  "message": "Perfil actualizado correctamente.",
  "data": {
    "id": 3,
    "cuenta_id": 3,
    "nickname": "Marcos228",
    "imagen": "C:\\xampp\\tmp\\php79B0.tmp",
    "descripcion": "Estudiante de desarrollo",
    "link": "https://instagram.com/julian.https",
    "rol_id": 1,
    "estado_id": 1,
    "fecha_reciente": "2026-02-28 14:40:33",
    "notifica_push": true,
    "notifica_correo": false
  }
}
```

**Modulo de Bloqueados**

**1️⃣Bloquear Usuario**

Método: **POST**

👁️ **usuario_id = Id del usuario que se desea bloquear**

Ruta: **http://127.0.0.1:8000/api/bloqueados/{usuario_id}**

**Respuesta**


```Json
{
  "success": true,
  "message": "Usuarios bloqueados",
  "data": [
    {
      "id": 3,
      "bloqueador_id": 2,
      "bloqueado_id": 1,
      "usuario_bloqueado": {
        "id": 1,
        "nickname": "Nickname",
      }
    }
  ]
}
```



**2️⃣Ver usuarios bloqueados**
Método: **GET**

Ruta: **http://127.0.0.1:8000/api/bloqueados**

**Respuesta**

```Json
{
  "success": true,
  "message": "Usuarios bloqueados",
  "data": [
    {
      "id": 3,
      "bloqueador_id": 2,
      "bloqueado_id": 1,
      "usuario_bloqueado": {
        "id": 1,
        "nickname": "XXXXXXXXX",
      }
    }
  ]
}
```



**3️⃣Desbloquear usuario**

Método: **DELETE**

Ruta: **http://127.0.0.1:8000/api/bloqueados/{bloqueado_id}**

**Respuesta**

```JSON
{
  "success": true,
  "message": "Usuario desbloqueado exitosamente."
}
```

**Modulo productos**
**Importante para la parte de las fotos, aún esta en prueba**

👁️**OJO**


**Se debe de ejecutar el siguiente comando para crear un enlace simbiotico de las imagenes. Esto es necesario para que la carpeta publica pueda acceder a los archivos subidos.**
```CMD
php artisan storage:link
```


**1️⃣ Listar productos**

Método: *GET*

Ruta: **http://127.0.0.1:8000/api/productos**

**Respuesta**

```JSON
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Laptop HP",
      "descripcion": "Laptop con 16GB de RAM",
      "precio": 2500000,
      "disponibles": 5
    }
  ]
}
```

**2️⃣ Buscar productos**

Método: *GET*

Ruta: **http://127.0.0.1:8000/api/productos/buscar**

Ejemplo de uso: ***http://127.0.0.1:8000/api/productos/buscar/?q=laptop**

**Respuesta**

```JSON
{
  "success": true,
  "data": [
    {
      "id": 2,
      "nombre": "Laptop Lenovo",
      "precio": 2100000
    }
  ]
}

```
**3️⃣ Obtener producto por ID**

Método: *GET*

Ruta: **http://127.0.0.1:8000/api/productos/{id}**

Ejemplo de uso: ***http://127.0.0.1:8000/api/productos/buscar/?q=laptop**

**Respuesta**

```JSON
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Monitor LG",
    "descripcion": "Monitor 24 pulgadas",
    "precio": 800000,
    "disponibles": 3
  }
}
```

**4️⃣ Obtener productos de un vendedor**

Método: *GET*

Ruta: **http://127.0.0.1:8000/api/productos/vendedor/{vendedorId}**


**Respuesta**

```JSON
{
  "success": true,
  "data": [
    {
      "id": 3,
      "nombre": "Teclado Mecánico",
      "precio": 180000
    }
  ]
}
```

**5️⃣ Crear producto**

Método: *POST*

Ruta: **http://127.0.0.1:8000/api/productos**

**Ejemplo de uso: **

```JSON
{
  "nombre": "Mouse Gamer",
  "descripcion": "Mouse RGB con 7 botones",
  "subcategoria_id": 4,
  "integridad_id": 1,
  "precio": 120000,
  "disponibles": 10
}
```

**Respuesta**

```JSON
{
  "success": true,
  "message": "Producto creado correctamente"
}

```

**6️⃣ Actualizar producto**

Método: *PUT o PATCH*

Ruta: **http://127.0.0.1:8000/api/productos/{id}**

**Ejemplo de uso: **

```JSON
{
  "precio": 100000,
  "disponibles": 8
}

```
**⚠️Nota:** Se pueden eviar uno o varios campos, solo se actualizarán los enviados.

**Respuesta**

```JSON
{
  "success": true,
  "message": "Producto actualizado correctamente"
}

```

**7️⃣ Eliminar producto**

Método: **DELETE**

Ruta: **http://127.0.0.1:8000/api/productos/{id}**

**Respuesta**

```JSON
{
  "success": true,
  "message": "Producto eliminado correctamente"
}

```

**8️⃣ Cambiar estado del producto**

Método: *PATCH*

Ruta: **http://127.0.0.1:8000/api/productos/{id}/estado**

**Ejemplo de uso: **

```JSON
{
  "estado_id": 2
}

```

**Respuesta**

```JSON
{
  "success": true,
  "message": "Estado del producto actualizado"
}

```

**9️⃣ Obtener mis productos**

Método: **GET**

Ruta: **http://127.0.0.1:8000/api/mis-productos**

**Respuesta**

```JSON
{
  "success": true,
  "data": [
    {
      "id": 5,
      "nombre": "Audífonos Bluetooth",
      "precio": 150000
    }
  ]
}

```

**Modulo Chats y Mensajes**

1️⃣**Iniciar Chat con vendedor**

**Método**: POST

**Ruta**: http://127.0.0.1:8000/api/productos/{producto_id}/chats

**Restricciones**


✖️ Que el vendedor tenga bloqueado al comprador o viceversa.

✖️ Que el vendedor intente crear un chat consigo mismo.

✅ Mostrar la información para la interfaz de detalles del chat.


**Salida**

```Json
{
"status":"success",
"message":"Chat iniciado correctamente",
"data": {
    "id":2,"comprador_id":3,
    "producto": {
        "id":1,
        "nombre":"Mouse Gamer",
        "imagen":null,
        "vendedor": {
            "id":1,
            "nickname":"julian1223",
            "imagen":"usuarios/1/698a9969b97db.webp"
        }    
    },
    "estado_id":1,
    "visto_comprador":false,
    "visto_vendedor":false,
    "mensajes":[],
    "cantidad":null,
    "calificacion":null,
    "comentario":null,
    "fecha_venta":null,
    "paginacion":[]
    }
}
```

2️⃣**Enviar mensaje al vendedor**

**Restricciones**

✖️ Que el vendedor tenga bloqueado al comprador o viceversa.

✖️ Que la imagen no pase de 5MB.


**Método**: POST

**Ruta**: http://127.0.0.1:8000/api/chats/{chat_id}/mensajes

**Entrada**


```Json
{
    "mensaje": "Soy el comprador"
}    
```

o en un formulario, con el nombre o id **imagen** para mandar una imagen.


⚠️ Cuando el comprador envia el mensaje, el **visto_comprador** queda en true, y el **visto_vendedor** queda en false y viceversa. Al momento que el vendedor o comprador vea los detalles del chat (Ruta más abajo) el visto_vendedor o visto_comprador cambia a true.

**Salida comprador**

```Json
{
  "status": "success",
  "chat_detalle": {
    "id": 2,
    "comprador_id": 3,
    "producto": {
      "id": 1,
      "nombre": "Mouse Gamer",
      "imagen": null,
      "vendedor": {
        "id": 1,
        "nickname": "julian1223",
        "imagen": "usuarios/1/698a9969b97db.webp"
      }
    },
    "estado_id": 1,
    "visto_comprador": true,
    "visto_vendedor": false,
    "mensajes": [
      {
        "id": 4,
        "mensaje": "Soy el comprador",
        "es_comprador": true,
        "imagen": null,
        "fecha_registro": "2026-02-11 21:59:05"
      }
    ],
    "cantidad": 1,
    "paginacion": {
      "total": 1,
      "pagina_actual": 1,
      "siguiente_pagina": null,
      "pagina_anterior": null
    },
    "calificacion": null,
    "comentario": null,
    "fecha_venta": null
  },
  "nuevo_mensaje": {
    "mensaje": "Soy el comprador",
    "imagen": "",
    "chat_id": 2,
    "es_comprador": true,
    "fecha_registro": "2026-02-12T02:59:05.000000Z",
    "id": 4
  }
}
```

**Salida vendedor**


```Json
{
  "status": "success",
  "chat_detalle": {
    "id": 2,
    "comprador_id": 3,
    "producto": {
      "id": 1,
      "nombre": "Mouse Gamer",
      "imagen": null,
      "vendedor": {
        "id": 1,
        "nickname": "julian1223",
        "imagen": "usuarios/1/698a9969b97db.webp"
      }
    },
    "estado_id": 1,
    "visto_comprador": false,
    "visto_vendedor": true,
    "mensajes": [
      {
        "id": 5,
        "mensaje": "Soy vendedor",
        "es_comprador": false,
        "imagen": null,
        "fecha_registro": "2026-02-11 22:46:35"
      },
      {
        "id": 4,
        "mensaje": "Soy el comprador",
        "es_comprador": true,
        "imagen": null,
        "fecha_registro": "2026-02-11 21:59:05"
      }
    ],
    "cantidad": 1,
    "paginacion": {
      "total": 2,
      "pagina_actual": 1,
      "siguiente_pagina": null,
      "pagina_anterior": null
    },
    "calificacion": null,
    "comentario": null,
    "fecha_venta": null
  },
  "nuevo_mensaje": {
    "mensaje": "Soy vendedor",
    "imagen": "",
    "chat_id": 2,
    "es_comprador": false,
    "fecha_registro": "2026-02-12T03:46:35.000000Z",
    "id": 5
  }
}
```

3️⃣ **Ver lista de chats activos**

**Método**: GET

**Ruta**: http://127.0.0.1:8000/api/chats

**Salida vista comprador**

```Json
[
  {
    "id": 2,
    "usuario": {
      "id": 1,
      "nickname": "julian1223",
      "imagen": "usuarios/1/698a9969b97db.webp"
    },
    "visto_comprador": true,
    "visto_vendedor": false,
    "ultimoMensajeTexto": "Soy el comprador",
    "fechaUltimoMensaje": "2026-02-11 21:59:05"
  }
]
```

**Vista Vendedor**

```Json
[
  {
    "id": 2,
    "usuario": {
      "id": 3,
      "nickname": "Marcos228",
      "imagen": "usuarios/3/698a9f3bd455f.webp"
    },
    "visto_comprador": true,
    "visto_vendedor": false,
    "ultimoMensajeTexto": "Soy el comprador",
    "fechaUltimoMensaje": "2026-02-11 21:59:05"
  }
]
```

4️⃣ **Ver detalles de un chat**

**Método**: GET

**Ruta**: http://127.0.0.1:8000/api/chats/{chat_id}

**Salida vista comprador**

```Json
{
  "status": "success",
  "data": {
    "id": 2,
    "comprador_id": 3,
    "producto": {
      "id": 1,
      "nombre": "Mouse Gamer",
      "imagen": null,
      "vendedor": {
        "id": 1,
        "nickname": "julian1223",
        "imagen": "usuarios/1/698a9969b97db.webp"
      }
    },
    "estado_id": 1,
    "visto_comprador": true,
    "visto_vendedor": false,
    "mensajes": [
      {
        "id": 4,
        "mensaje": "Soy el comprador",
        "es_comprador": true,
        "imagen": null,
        "fecha_registro": "2026-02-11 21:59:05"
      }
    ],
    "cantidad": 1,
    "calificacion": null,
    "comentario": null,
    "fecha_venta": null,
    "paginacion": {
      "total": 1,
      "pagina_actual": 1,
      "siguiente_pagina": null,
      "pagina_anterior": null
    }
  }
}
```

**Vista salida vendedor**

```Json
{
  "status": "success",
  "data": {
    "id": 2,
    "comprador_id": 3,
    "producto": {
      "id": 1,
      "nombre": "Mouse Gamer",
      "imagen": null,
      "vendedor": {
        "id": 1,
        "nickname": "julian1223",
        "imagen": "usuarios/1/698a9969b97db.webp"
      }
    },
    "estado_id": 1,
    "visto_comprador": true,
    "visto_vendedor": true,
    "mensajes": [
      {
        "id": 4,
        "mensaje": "Soy el comprador",
        "es_comprador": true,
        "imagen": null,
        "fecha_registro": "2026-02-11 21:59:05"
      }
    ],
    "cantidad": 1,
    "calificacion": null,
    "comentario": null,
    "fecha_venta": null,
    "paginacion": {
      "total": 1,
      "pagina_actual": 1,
      "siguiente_pagina": null,
      "pagina_anterior": null
    }
  }
}
```

5️⃣ **Borrar Mensaje**

**Restricciones**

✖️ Solo el autor puede borrar el mensaje

**Método**: DELETE

**Ruta**: http://127.0.0.1:8000/api/mensajes/{mensaje_id}

```Json
{
  "status": "success",
  "message": "Mensaje eliminado correctamente"
}
```

**⚠️ Cuando un usuario trata de borrar el mensaje de otro usuario**

```Json
{
  "status": "error",
  "message": "Acceso denegado: No tienes permisos para realizar esta acción."
}
```

6️⃣ **Borrar Chat**

**Método**: DELETE

**Ruta**: http://127.0.0.1:8000/api/chats/{chat_id}

⚠️ El chat no se borra, solo cambia de estado, haciendo que no aparezca en la lista de chats activos, siendo invisible para el usuario que lo borro pero visible para la otra parte.

**Comprador borra el chat**


```Json
{
  "status": "success",
  "message": "Chat eliminado correctamente"
}
```

**Cuando comprador usa la ruta para visualizar la lista de chats activos**

❕Array vacio porque solo tenia un chat activo


```Json
[]
```

**Cuando vendedor usa la ruta para visualizar la lista de chats activos**

```Json
[
  {
    "id": 2,
    "usuario": {
      "id": 3,
      "nickname": "Marcos228",
      "imagen": "usuarios/3/698a9f3bd455f.webp"
    },
    "visto_comprador": false,
    "visto_vendedor": true,
    "ultimoMensajeTexto": "Soy vendedor",
    "fechaUltimoMensaje": "2026-02-11 22:46:35"
  }
]
```

⚠️ Si una de las partes que no ha borrado el chat, envia un mensaje luego que la otra la borro, vuelve activar el chat para la parte que lo borro.

**MODULO DE TRANSFERENCIA 🚧🚧🚧**

**1️⃣Iniciar proceso de compraventa**

RUTA: **http://127.0.0.1:8000/api/chats/{chat_id}/iniciar-compraventas**

MÉTODO: **PATCH**

**Restricciones**

✖️ Solo el vendedor puede iniciar el proceso.

✖️ El chat debe estar activo.

**Datos a enviar**

```Json
{
    "cantidad": 1,
    "precio": 70000
}
```

**Respuesta**

```Json
{
    "success": true,
    "message": "Proceso iniciado, espera la confirmación del comprador"
}
```

**2️⃣Terminar proceso de compraventa**

RUTA: **http://127.0.0.1:8000/api/chats/{chat_id}/terminar-compraventas**

MÉTODO: **PATCH**

**Restricciones**

✖️ Solo el comprador puede terminar el proceso.

✖️ El chat debe estar en esperando (estado_id = 6).

**Si el comprador acepta, el estado del chat cambia a vendido**

**Datos a enviar**

```Json
{
    "confirmacion": true,
    "comentario": "Buenos auriculares",
    "calificacion": 4
}
```

**Respuesta**

```Json
{
    "success": true,
    "message": "Venta concretada con exito"
}
```

**Si el comprador rechaza, el estado del chat cambia a activo**

```Json
{
    "confirmacion": false,
}
```

**Respuesta**

```Json
{
    "success": true,
    "message": "Proceso cancelado"
}
```

**3️⃣Iniciar proceso de devolución**

RUTA: **http://127.0.0.1:8000/api/chats/{chat_id}/iniciar-devoluciones**

MÉTODO: **PATCH**

**Restricciones**

✖️ Solo el comprador puede iniciar el proceso.

✖️ El estado del chat debe estar en vendido (estado_id = 5).

**Salida Json**

```Json
{
    "success": true,
    "message": "Proceso de devolución iniciado, espera la confirmación del vendedor"
}
```


**4️⃣Terminar proceso de devolución**

RUTA: **http://127.0.0.1:8000/api/chats/{chat_id}/terminar-devoluciones**

MÉTODO: **PATCH**

**Restricciones**

✖️ Solo el vendedor puede terminar el proceso.

✖️ El estado del chat debe estar en devolviendo (estado_id = 7).

✖️ Si ha pasado más de 3 días desde el inicio de devolución el estado del chat vuelve a vendido

**Salida Json**

```Json
{
    "success": true,
    "message": "Devolución registrada con exito"
}
```

**5️⃣Mostrar Estados para transferencias**

**👁️OJO** Método para obtener los estados de las transferencias. Util para hacer el input de tipo checkbox dinamico, a la par con la base de datos.

RUTA: **http://127.0.0.1:8000/api/estados**

MÉTODO: **GET**

**Salida Json**

```Json
[
  {
    "id": 1,
    "nombre": "activo",
    "descripcion": "Cuando funciona con completa normalidad"
  },
  {
    "id": 5,
    "nombre": "vendido",
    "descripcion": "aplicado a un chat cuando se hizo la transacción"
  },
  {
    "id": 6,
    "nombre": "esperando",
    "descripcion": "la transacción del chat espera el visto bueno del comprador"
  },
  {
    "id": 7,
    "nombre": "devolviendo",
    "descripcion": "el historial abre una solicitud de devolución, a espera de respuesta del vendedor"
  },
  {
    "id": 8,
    "nombre": "devuelto",
    "descripcion": "el chat finalizó con una transacción que fué cancelada"
  }
]
```



**6️⃣Mostrar lista de transferencias**

RUTA: **http://127.0.0.1:8000/api/transferencias**

MÉTODO: **POST**

**👁️OJO** Solo se muestra uno porque solo hay uno registro porque el usuario solo tiene una transferencia

**RESTRICCIONES**

✖️ Si el usuario no tiene transferencias (chats activos, esperando respuesta del vendedor, vendidos, devolviendo producto y producto devuelto) mostrara un mensaje diciendo que no tiene transferencias

**Salida Json**

```Json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "producto": {
        "id": 1,
        "nombre": "Mouse Gamer",
        "imagen": null
      },
      "usuario": {
        "id": 1,
        "nickname": "julian1223",
        "avatar": "usuarios/1/698a9969b97db.webp"
      },
      "estado": {
        "id": 8,
        "nombre": "devuelto"
      },
      "cantidad": 1,
      "precio": 70000,
      "calificacion": 4,
      "comentario": "4",
      "fecha_venta": "2026-02-15T19:55:53.000000Z"
    }
  ]
}
```

**7️⃣Filtrar las transferencias**

RUTA: **http://127.0.0.1:8000/api/transferencias-filtros?estados[]=1&estados[]=6**

**OJO👁️** Los estados[]=1 o estados[]=6 son solo de ejemplo, mostrando unicamente los chats con estados activos o esperando pero pueden cambiar según las transferencias que marque el usuario. Pueden ser estados[]=7 para mostrar los chats con estado devolviendo

MÉTODO: **POST**

**👁️OJO** Solo se muestra uno porque solo hay uno registro porque el usuario solo tiene una transferencia

**RESTRICCIONES**

✖️ Si el usuario no tiene transferencias (chats activos, esperando respuesta del vendedor, vendidos, devolviendo producto y producto devuelto) mostrara un mensaje diciendo que no tiene transferencias.

✖️ Deben enviarse al menos un parametro de consulta ejemplo: **?estados[]=1** 


**Salidas Json**

Si el usuario consulta una transferencias que no tiene hecha

```Json
{
  "success": true,
  "data": "No tienes tranferencias hechas"
}
```

Si el usuario marca varios transferencias para su consulta. Ejemplo **?estados[]=1&estados[]=8** mostrara solo la que tiene activa.

```Json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "producto": {
        "id": 1,
        "nombre": "Mouse Gamer",
        "imagen": null
      },
      "usuario": {
        "id": 1,
        "nickname": "julian1223",
        "avatar": "usuarios/1/698a9969b97db.webp"
      },
      "estado": {
        "id": 8,
        "nombre": "devuelto"
      },
      "cantidad": 1,
      "precio": 70000,
      "calificacion": 4,
      "comentario": "4",
      "fecha_venta": "2026-02-15T19:55:53.000000Z"
    }
  ]
}
```


**Favoritos**


**1️⃣Ver usuarios favoritos**

RUTA: **http://127.0.0.1:8000/api/favoritos**

MÉTODO: **GET**

**Restricciones**


**Salida Json**

```Json
{
    "success": true,
    "message": "Favoritos obtenidos correctamente.",
    "favoritos": [
        {
            "id": 6,
            "votante_id": 1,
            "usuario_votado": {
                "id": 2,
                "nickname": "XXXXXX",
                "imagen": null
            }
        }
    ]
}
```

**2️⃣Agregar usuario a favoritos**

**👁️OJO**

El usuarioId corresponde al id del usuario que se quiere agregar


RUTA: **http://127.0.0.1:8000/api/favoritos/{usuarioId}**

MÉTODO: **POST**

**RESTRICCIONES**

✖️ Si el usuario ya esta en la lista, saldra una excepción.

**Salida Json**

```Json
{
    "status": "error",
    "type": "BusinessException",
    "message": "El usuario ya se encuentra en favoritos."
}
```

**Salida Json**

```Json
{
    "success": true,
    "usuarioAgregado": {
        "id": 7,
        "votante_id": 1,
        "usuario_votado": {
            "id": 2,
            "nickname": "XXXXXXXXX",
            "imagen": null
        }
    }
}
```


**3️⃣Eliminar usuario a favoritos**

**👁️OJO**

El usuarioId corresponde al id del usuario que se quiere eliminar


RUTA: **http://127.0.0.1:8000/api/favoritos/{usuarioId}**

MÉTODO: **DELETE**

**Salida Json**

```Json
{
    "success": true,
    "message": "Usuario eliminado de favoritos exitosamente."
}
```



**Código	Significado**

200	Operación exitosa

201	Registro completado

401	Token inválido / no autenticado

422	Error de validación

500	Error interno del servidor

La parte de fotos sigue en prueba (Con exactitud puede que funcione) Mirar LOGS, falta probar con postman
