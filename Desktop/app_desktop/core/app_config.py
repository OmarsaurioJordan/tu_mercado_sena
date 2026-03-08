# constantes globales de configuracion
# la version del sfowtare
VERSION = "1.0.0"
# url para el servidor oficial, tambien local para pruebas
API_BASE_URL = "http://localhost/TMS_API/"
#API_BASE_URL = "https://omwekiatl.xyz/TMS_API/"
# limite de carga de items por consulta a la API
API_LIMIT_ITEMS = 50
# los email oficiales deben terminar asi
DOMINIO_EMAIL = "@soy.sena.edu.co"
# poner a true para iniciar directamtente logeado
DEBUG_NO_LOGIN = True
# texto que se muestra cuando no hay descripcion de master, y link a TMS server
DEFAULT_INFO = "Información de contacto:\n***Admin Name***\ntel: 333666\nmail: master@soy.sena.edu.co\ndir: cra 1 # 12-69\nlugar: edif 328B"
DEFAUL_LINK = "https://omwekiatl.xyz/Prestamo/"
# url donde estan alojadas las imagenes de usuarios
IMAGE_USER_LINK = "http://localhost/Prestamo/freddy/Frontend/uploads/usuarios/"
#IMAGE_USER_LINK = "https://omwekiatl.xyz/Prestamo/ensano_chat/uploads/usuarios/"
# url donde estan alojadas las imagenes de productos
IMAGE_PROD_LINK = "http://localhost/Prestamo/freddy/Frontend/uploads/productos/"
#IMAGE_PROD_LINK = "https://omwekiatl.xyz/Prestamo/ensano_chat/uploads/productos/"
# url donde estan alojadas las imagenes de mensajes
IMAGE_CHAT_LINK = "http://localhost/Prestamo/freddy/Frontend/uploads/mensajes/"
#IMAGE_CHAT_LINK = "https://omwekiatl.xyz/Prestamo/ensano_chat/uploads/mensajes/"
# url donde estan alojadas las imagenes de papelera
IMAGE_PAPELERA_LINK = "http://localhost/Prestamo/freddy/Frontend/uploads/papalera/"
#IMAGE_PAPELERA_LINK = "https://omwekiatl.xyz/Prestamo/ensano_chat/uploads/papalera/"
# precio maximo de la aplicacion
PRECIO_MAX = 10000000
# tiempo para esperar una solicitud al servidor (segundos)
TIME_OUT = 15
# temporizacion para buscar notificaciones (segundos)
TIME_NOTIFI = 5
# talla maxima del mensaje para enviar a un usuario
MSJ_MAX = 155
# talla efectiva que soporta la DB, MSJ_MAX + churumbelas
DB_NOTIFI_MSJ_MAX = 255
# cantidad de puntos que se le puede poner a un producto comprado
CALIFICACION_MAX = 5
