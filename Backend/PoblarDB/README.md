# Poblador de DB para Tu Mercado Sena

Este código Python se encarga de crear datos sintéticos aleatorios, aproximados a lo que generarían los usuarios, esto para hacer test de estrés al sistema, es en parte una sencilla simulación de comportamiento según los casos de uso

En `db_sql.py` puede cambiar las credenciales de acceso a la DB, por ejemplo si quiere conectar al servidor XAMPP.

El archivo `engine.py` es el main o archivo principal, desde donde se lanza el sistema, allí hay un set de constantes al inicio que se usan para controlar todo el comportamiento aleatorio del sistema, por ejemplo, puede especificar cuántos usuarios crear.

El sistema simula también a usuarios con acciónes ilegales o de trolleo, por lo que pueden aparecer palabras inadecuadas en algunos perfiles o productos, estos son los casos que en realidad aparecerán y con los cuales se debe lidiar desde la administración. Si coloca prob_lacra y prob_troll a 0 en `engine.py` eliminará esos comportamientos.

El archivo `Tareas.txt` tiene un listado de las cosas que están pendientes por programar.

## Requiere:

- `pip install mysql-connector-python` librería para SQL

- `pip install pillow` librería para manejar imágenes

## Pruebas

- las carpetas `img_usuarios`, `img_productos`, `img_mensajes`, `img_backup`, `img_test` contendrán imágenes de prueba para los usuarios, productos, chats y backup (papelera), esto último es para cuando un usuario reemplaza o elimina una imágen, la anterior queda almacenada en backup, solo los administradores pueden ver el historial
