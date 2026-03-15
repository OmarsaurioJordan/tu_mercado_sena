# Tu Mercado Sena (Desktop)

Tutorial de PySide6:
https://www.pythonguis.com/pyside6-tutorial/

## Estructura

- **assets/** contiene material multimedia, como imágenes o sonidos
- **components/** piezas básicas de interfáz gráfica, modulares y repetitivas
- **controllers/** backend que se conecta a la API para intercambiar información con los modelos
- **core/** configuración de arranque del sistema y backend de navegación
- **models/** estructura de la base de datos en local, para la sincronización
- **services/** scripts variados de backend, para manejar cosas complejas
- **ui/** módulos compuestas por componentes, piezas de la interfaz gráfica (windows)
- **windows/** interfaces gráficas completas, páginas que conforman la aplicación
- **main.py** primer script ejecutado, lanza el programa

## Notas

- **requeriments.txt** lista las librerias necesarias para Python
- **tareas.txt** lista de cosas por hacer y anotación de bugs por corregir
- **app_config.py** ahí están las variables / constantes globales del sistema, para configurarlo
- **controllers_manager.py** administra todos los controladores de los modelos, accesible globalmente
- **window_manager.py** contiene todas las vistas / ventanas, maneja los cambios entre las mismas, accesible globalmente
- **session.py** objeto único durante la ejecución, mantiene las credenciales de sesión de usuario
- **log...** se puede ver en consola la ejecución del sistema, pulse F2 para hacer un salto de línea

## Elementos para que cada entidad pueda hacer acciónes administrativas

-   **API/** no es parte de la App, es un archivo a parte con los SQL para la DB que retorna JSON
-   **entidad.py** modelo, la estructura de la entidad, sus variables, creación a partir de JSON y carga de imágenes asincronas
-   **entidad_card.py** ficha de la entidad para ser mostrada en búsquedas o referenciada desde otros elementos de la UI
-   **entidad_body.py** información ampliada de la entidad, con acciónes administrativas, se instancia una vez en la UI
-   **entidad_filter.py** los filtros con los que se harán las búsquedad, usualmente se instancia una vez en la UI
-   **result_busqueda.py** de propósito general, muestra muchas fichas / cards resultantes de búsquedas, configurar
-   **ctrl_entidad.py** controlador, elemento maestro que maneja al modelo de la entidad, hace solicitudes a la API
-   **confirmaciones.py** para los selectores de estados, las funciónes aquí redirigen la elección al controlador
-   **controllers_manager.py** debe hacerse el llamado al controlador y establecer los métodos de acceso al mismo
-   **tools_widget.py** acá es donde se instancian la mayoría de elementos widget, y se conectan sus señales
