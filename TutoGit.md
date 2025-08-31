# Tutorial Git y GitHub

en principio Git es un sistema de control de versiónes y repositorios a nivel local (en su computadora), y GitHub es lo mismo pero a nivel de nube, un servicio web de control de versiónes y repositorios, existen otros sistemas de este tipo además de Git y GitHub

¿por qué usarlo? cuando usamos el almacenamiento del computador o carpetas Drive / Dropbox / Onedrive aunque algunos sistema manejan versiónes de archivos, es más fácil perder información al eliminar o sobre-escribir archivos, además se dificulta distinguir qué archivo pertenece a cada versión de todo un proyecto, termina la persona guardando varias copias del proyceto lo que ocupa mucho espacio

luego, los sistemas de control de versiónes como Git permiten el trabajo en equipo, comparando diferencias entre archivos, así como crear versiónes "paralelas" del proyecto y almacenar todo en un espacio comprimido

más adelante en el documento hay un glosario de términos

## Primeros Pasos

vaya al sitio web de Git y descargue el instalador para su computadora, al abrir la línea de comandos puede verificar si Git está instalado con:

> git --version

luego cree una cuenta en el sitio web de GitHub, puede acceder con su cuenta de Google, una cuenta GitHub consta de: nombre de usuario, correo y contraseña

cuando esté en su computadora haciendo los comandos push por primera vez, le pedirá sus credenciales de inicio de sesión en GitHub, puede guardarlas a nivel global en su computadora para que no las pida más

puedes guardar usuario y correo previamente así, necesario para hacer commits

> git config --global user.name "tu_nombre"  
> git config --global user.email "tu_correo"

## Crear un Proyecto Nuevo en GitHub

en GitHub está la opción de crear repositorio, repositorios -> nuevo, colocamos nombre y descripción, si va a ser público o privado, y elegimos algunos archivos como .gitignore y README.md

una vez en la página del repositorio, obtendrémos el link para clonarlo

abrimos la consola en alguna ubicación, con este comando creará una carpeta ahí dentro con el proyecto descargado de la nube e inicializado en el sistema Git, opcionalmente le damos el nombre de la carpeta, sino, será el nombre del repositorio

> git clone link_repositorio opc_carpeta_name

## Subir un Proyecto Local Existente a GitHub

primero seguimos los pasos para crear un repositorio en GitHub, al hacerlo la web nos mostrará unos comandos, vamos a desglozarlos aquí

primero abres la terminal en la carpeta raíz de tu proyecto creado en la computadora, el primer comando inicializa un repositorio Git en dicha carpeta

luego add y commit crean un punto de guardado, checkpoint (se describirá más adelante), first_commit es un mensaje a tu gusto que describa ese checkpoint

el branch -M lo que hace es renombrar la rama principal como main, porque Git por defecto la llama "master" y GitHub la llama "main" así que para evitar confusiónes

después remote add, allí vas a pegar el link de tu repositorio de GitHub, para que el local sepa a dónde está su orígen

finalmente el push sube los cambios del local a la nube, esto solo se puede hacer si tienes credenciales o permisos con ese repositorio en Github

> git init  
> git add .  
> git commit -m "first_commit"  
> git branch -M main  
> git remote add origin link_repositorio  
> git push -u origin main

## Crear una Nueva Rama 

esto crea una rama llamada rama_nueva a partir de la rama en la cuál estés actualmente, y te pone en ella como la actual

luego actualiza los cambios en la nube, lo que creará allá la rama_nueva

> git checkout -b rama_nueva  
> git push origin rama_nueva

## Moverse Entre Ramas y Obtener Información

el checkout permite posicionarse en una rama específica, si el proyecto no tiene más ramas, la principal suele llamarse main

luego el branch muestra todas las ramas locales que existen y dice en cuál estás actualmente, es para obtener información, el -a hace lo mismo pero teniendo en cuenta también todas las ramas del repositorio en la nube

> git checkout rama_name  
> git branch  
> git branch -a

## Ciclo Básico de Guardado de Commits

primero se dice que todos los archivos serán agregados al próximo commit, digamos, están en mira para ser procesados y evaluados por Git

lego el commit crea en efecto el "checkpoint" o punto de guardado, se pone un mensaje alusivo a qué se hizo o qué hay guardado o una descripción

finalmente se suben los datos del commit a la nube, para guardarlos en GitHub, rama_name suele llamarse main (si no has creado otras ramas)

> git add .  
> git commit -m "mensaje"  
> git push origin rama_name

## Fusionar Ramas

pararse en la rama que recibirá los cambios, luego traer los cambios desde la otra, el sistema mostrará qué archivos entran en conflicto, estos conflictos deben resolverse manualmente, a criterio de análisis, finalmente se guardan los cambios con el commit y push

> git checkout rama_receptora  
> git merge rama_donadora  
> // si hay conflictos resolverlos  
> // hacer el guardado del commit (como siempre)

## Actualizar Local Desde la Nube

usualmente rama_name es main si no has creado más ramas, esto internaemente hace dos cosas: descarga los datos desde el repositorio en GitHub y los mezcla "merge" en la rama en la que te encuentras, por lo que podría haber conflictos que resolver

> git pull origin rama_name

## Recordar Rama de Push y Pull

al usar el -u Git recordará la rama elegida, entonces la próxima vez solo es suficiente con llamar a push sin parámetros, como en la segunda línea

lo mismo aplica al hacer el pull

> git push -u origin rama_name  
> git push
> 
> git pull -u origin rama_name  
> git pull

## Crear Punto Estable para Release

cada tanto, se crea una versión estable de un proyecto, tag_name suele ser v1.0, v1.4-ext, v218, etc, de esta forma luego desde GitHub se puede crear un release, por ejemplo, subiendo un ejecutable .exe que los usuarios pueden descargar sin tener que tocar el código fuente

> git tag tag_name  
> git push origin tag_name

## Crear un Fork

esto se hace desde la interfaz gráfica de GitHub, donde veas un repositorio, puedes encontrar tanto su link para clonarlo como el botón de Fork para hacer una copia del repositorio en tu propio GitHub, esto para más adelante proponer cambios (pull request) al repositorio original

en tu fork de GitHub puedes actualizar cambios desde el original hacia tu fork, y proponer las PR, mismas que deben ser aprobadas por el propietario quién además deberá resolver conflictos de archivos

## Glosario

- **repositorio:** es un proyecto como tal, digamos que es como la carpte raíz donde todo se guarda, pero tiene además opciónes de configuración, como por ejemplo: ser público o privado en GitHub
- **rama (branch):** versión "paralela" del proyecto, usualmente la rama principal se llama main (a veces master), todas las ramas cohexisten en un mismo repositorio
- **commit:** es como un "checkpoint" o punto de guardado del proyecto, suele ir acompañado de un mensaje descriptivo
- **push:** subir a la nube las actualizaciónes hechas en el repositorio local
- **pull:** obtener en el repositorio local las actualizaciónes hechas en la nube
- **clone:** clonar un proyecto significa descargar los archivos de la nube para hacer un repositorio local
- **nube:** Internet, en este contexto refiere al repositorio en GitHub
- **release:** es un lanzamiento, software finalizado listo para ser usado
- **fork:** es como un clone pero de una cuenta GitHub a otra, se le hace un fork al repositorio de otro usuario y queda copiado en tu cuenta
- **pull request (PR):** es una solicitud para integrar cambios usualmente desde un fork al repositorio original, de este modo un proyecto puede recibir colaboración, pero su admin debe aceptar cambios y resolver conflictos

## .gitignore

usualmente presente en los proyectos, es un archivo de texto sin nombre (solo extensión), hecho para que Git sepa qué archivos o carpetas debe evitar subir al GitHub y evitar formar parte del sistema de versiónes, por ejemplo, para evitar que un pesado ejecutable .exe ocupe espacio en el repositorio, o evitar que una carpeta con cientos de resultados de un algoritmo se guarden como parte del proyecto (son resultados no estructura), puede haber varios .gitignore en diferentes carpetas, cada uno afecta desde su posición raíz hacia adelante

> #comentario
> 
> #ignora todos los archivos .txt el * es un comodín, cualquier nombre  
> *.txt
> 
> #ignora a data, no ignora cosas/data pues / al inicio especifica raíz (donde está el mismo .gitignore ahí debe estar data para ser ignorado)  
> /data
> 
> #el ! hace que el archivo sea incluído forzosamente, así haya un *.txt en .gitignore, y el hola.txt puede estar en cualquier subcarpeta  
> !hola.txt
> 
> #acá si se incluye forsozamente el archivo solo si está en el nodo raíz  
> !/cnfg.txt
> 
> #ignora los archivos dentro de algo, da igual dónde esté algo, ej: cosas/algo/  
> algo/
> 
> #ignora por ej: cosas/doc/hi.txt pero no ignorará a doc/lib/hi.txt  
> doc/*.txt
> 
> #ignora cualquier .pdf en el directorio doc dentro de cualquiera de sus subdirectorios  
> doc/**/*.pdf

## README.md

los proyectos y las exportaciónes de software siempre suelen llevar este archivo, ya que es un abrebocas informativo de lo que el proyecto o software trata, brinda ayuda sobre cómo instalar, soporte técnico, etc, puede haber más de estos archivos en las subcarpetas para explicar módulos específicos, pero siempre debe ir uno en el directorio raíz, GitHub lo precargará al ver un repositorio en la web

## Notas

Git crea en la carpeta local del proyecto una carpeta oculta .git, no la toque, destruirla eliminará el repositorio Git y deberá crearlo de nuevo, en esa carpeta Git guarda todo, todo el control de versiónes y ramas

los archivos no se eliminan, cuando usted borra algo y actualiza el commit, pareciera que se han eliminado, pero quedan guardados en .git y en GitHub respectivamente, por si quiere "volver atrás" y ver una versión previa del proyecto, por eso debe ser muy cuidadoso con lo que hace, por ejemplo, no hacer que un commit arrastre basura de pesados archivos innecesarios (bueno si hay comandos para deshacer commits, pero la idea es no tener que usarlos)
