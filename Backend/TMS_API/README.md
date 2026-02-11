# API para Tu Mercado Sena - Desktop

## General

El archivo **config.php** tiene las credenciales de la DB y un bool llamdo **debug** para activar o desactivar la verificación de token, el token se verifica llamando al script **validation()** cuando se requiera esa funcionalidad, dicho script recibe **"admin_email"** y **"admin_token"** para hacer la comprobación de sesion, de no pasarse la misma, llama a **exit**, abortando la ejecución

## Usuarios

**api/usuarios/admin_login**
input:
- m
result:
- m
