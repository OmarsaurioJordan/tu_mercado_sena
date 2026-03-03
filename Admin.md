# Administración del Proyecto

en este documento se organizan los equipos de trabajo y se explica para qué sirve cada carpeta y archivo del repositorio

## Grupos

cada grupo tiene un **líder** y una **carpeta** asignada en este repositorio, cada líder es responsable de esa carpeta, de hacer trabajar a su grupo y se encarga de hacer lo necesario para que esa parte del proyecto marche bien, aún así esta estructura no es inamovible, personas de un grupo pueden prestar apoyo a otro

### API

se encargan de administrar la Database mediante una API en Laravel, que se conecta a los 3 entornos: web, móvil y desktop, este grupo es de gran importancia para estructurar todo el modelo de información / negocio del sistema

- **Brahian Alexander Cortes Ceballos**
- Erik Santiago Castellanoz Erazo
- Kevin Andrés Barona Barreiro

### Database

acá se diseña la DB como modelo relacional transaccional, hay un algoritmo para poblarla y hacer test de estrés, finalmente la DB guía la creación de la API y se reflejan los cambios mutuamente

- miembros de API + Desktop

### Desktop

aplicación de escritorio con propósitos administrativos para el sitio web, debe funcionar en Windows y se desarrolla en Python con su versión de Qt creator

- **Omar Jordán Jordán**

### Design

se encargan de definir íconos, logotipos, isotipos, paletas de colores, reglas de UX, tipografías, narrativa para comunicar al usuario, crean los mockups, mapas de navegabilidad y sketchs de las interfaces, este grupo trabaja muy de la mano con los 3 entornos

- **Jean Carlos Araujo Fori**
- Brandon Steven Rios Galvis
- Valery Yesenia Claros Omen

### Documentación

encargados de crear el documento de diseño donde se explica el análisis, los requerimientos, casos de uso, diagramas UML, diagramas de DB, normas ISO aplicadas, mockups, pruebas y resultados, este grupo administra el diseño del software y guía la documentación, pero entre todos los grupos aportan por ejemplo, diagramas UML, cada uno desde su área, acá lo que hacen es recopilar, organizar y dirigir

- **Heidy Jissel Calderón Estrella**
- Ashley Catalina Usama Alarcón
- Darling Angulo Castillo

### GodotSketch

contiene los primeros ensayos en el motor de videojuegos Godot, de cómo luciría la aplicación móvil, es una simulación interactiva, allí hallará una imágen con las interfaces y en este link puede ponerla en marcha (contraseña adso24): https://omwekiatl.itch.io/marketsenademo login: johan@soysena - 123

- miembros de Desktop

### Marketing

desarrollo de imágenes promocionales, videos proocionales y todo lo que gira en torno al producto sin ser parte interna del sistema

- miembors de Design

### Móvil

se desarrolla la versión App para Android del sitio, soporta las funciones suficientes para que un usuario no administrador pueda ver productos, chatear y publicar productos, se utiza React Native con Expo

- **Juan Manuel Rondón Gomez**
- Juan Sebastián Granobles Osorio
- Javier Varela Valencia

### Web

entorno web, un aplicativo para navegador y responsivo para móvil, acá se lleva a cabo la mayor puesta en marcha de los requerimientos, el sistema debe cumplir con todo lo necesario para satisfacer a un usuario no administrador

- **José Freddy Reyes Granja**
- Daniel Alejandro Ramos Espinosa
- Johan Estiven Torres Escobar
