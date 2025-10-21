# Tu Mercado Sena (Desktop)

## Estructura

- **main.py:** primer script ejecutado, lanza el programa
- **requeriments.txt:** lista las librerias necesarias para Python
- **config.py:** configuración de credenciales, rutas, DB
- **core:** carpeta con la conección al backend en la nube
-    **api_client.py:** manejar peticiones HTTP a la API y verifica seguridad
-    **services.py:** abstrae llamadas, devolviendo los datos listos para su uso
-    **utils.py:** herramientas diversas, por ejemplo, redimensionar imágen
- **ui:** carpeta con toda la interfaz de usuario frontend
-    **main_window.py:** ventana principal que contiene a todos los widgets y dialogos
-    **dialogs:** carpeta con login, configuración, etc
-    **widgets:** carpeta con clases para estructuras que se llaman en la ventana principal
-    **resources:** iconos, imágenes, sonidos, etc
- **data:** archivos locales coo imágenes descargadas, etc
- **tests:** para pruebas unitarias
