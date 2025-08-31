# Poblador de DB para Tu Mercado Sena

Este código Python se encarga de crear datos sintéticos aleatorios, aproximados a lo que generarían los usuarios, esto para hacer test de carga al sistema, es en parte una sencilla simulación de comportamiento según los casos de uso

El archivo `Tareas.txt` tiene un listado de las cosas que están pendientes por programar

El archivo `engine.py` es el main o archivo principal, desde donde se lanza el sistema, allí hay un set de constantes al inicio que se usan para controlar todo el comportamiento aleatorio del sistema, por ejemplo, puede especificar cuántos usuarios crear.

El sistema simula también a usuarios con acciónes ilegales o de trolleo, por lo que pueden aparecer palabras inadecuadas en algunos perfiles o productos, estos son los casos que en realidad aparecerán y con los cuales se debe lidiar desde la administración. Si coloca prob_lacra y prob_troll a 0 en `engine.py` eliminará esos comportamientos.

## Requiere:

- `pip install mysql-connector-python` librería para SQL

- `pip install pillow` librería para manejar imágenes

## Pruebas

- https://avatar.iran.liara.run/public/1 reemplazar el 1 por un id de avatar de usuario, hay de 1 a 100 avatares gratis en esa API

- las carpetas `img_productos`, `img_mensajes`, `img_backup`, `img_test` contendrán imágenes de prueba para los productos, chats y backup, esto último es para cuando un usuario reemplaza o elimina una imágen, la anterior queda almacenada en backup, solo los administradores pueden ver el historial
