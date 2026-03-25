import random
from datetime import datetime, timedelta
from usuario import Usuario
from producto import Producto
from db_sql import Conector
from img_gen import MakeImg

# configuracion del sistema
total_usuarios = 1000 # recomendable 1000+
year_ini = 2024 # year para iniciar el sistema
month_ini = 1 # mes para iniciar el sistema, 1 es enero
dominiomail = "soy.sena.edu.co" # terminacion luego del usuarioname@
password = "$2y$10$gl68EE0OBsVO8JX.r9k/Tu2BDUWj3qqirrd9L6f0w5N8rKbErqsKS" # 123456
prob_tener_link = 0.15 # para que compartan su link a redes sociales
prob_notifi = 0.25 # recibir correos, notifi push y usar datos
prob_descripcion = 0.666 # prob de tener una descripcion en el perfil
prob_superdescripcion = 0.333 # prob que la descripcion sea larga
prob_troll = 1 / 5 # 1/5 acciones troll: chat, producto, perfil, pqrs, denuncia
prob_lacra = 1 / 3 # 1/3 acciones lacra: chat, producto, perfil
prob_hitos = [0.1, 0.8] # prob gran pausa vs prob continuacion corta
prob_conimagen = 0.95 # que un producto o usuario posea foto
max_imagenes = 5 # cantidad de imagenes maxima por producto
integridades = [0.6, 0.3, 0.08, 0.02] # nuevo, usado, reparado, reciclable, suman 100%
tipo_usr_porc = { # probabilidad existir, deben sumar 100%
    "master": 0,
    "admin": 0.01,
    "comprador": 0.4,
    "curioso": 0.3,
    "lacra": 0.04,
    "troll": 0.08,
    "vendeuno": 0.07,
    "vendeall": 0.1
}
tipo_usr_vende = { # probabilidad vender algo
    "master": 0,
    "admin": 0,
    "comprador": 0.03,
    "curioso": 0.01,
    "lacra": 0.05,
    "troll": 0.1,
    "vendeuno": 0.05,
    "vendeall": 0.2
}
tipo_usr_compra = { # probabilidad comprar algo
    "master": 0,
    "admin": 0,
    "comprador": 0.2,
    "curioso": 0.01,
    "lacra": 0.01,
    "troll": 0.01,
    "vendeuno": 0.05,
    "vendeall": 0.03
}
tipo_usr_ver = { # probabilidad ver algo
    "master": 0.01,
    "admin": 0.05,
    "comprador": 0.15,
    "curioso": 0.25,
    "lacra": 0.05,
    "troll": 0.2,
    "vendeuno": 0.1,
    "vendeall": 0.03
}
tipo_usr_chatok = { # probabilidad la compra salga bien
    "master": 0,
    "admin": 0,
    "comprador": 0.07,
    "curioso": 0.15,
    "lacra": 0.03,
    "troll": 0.01,
    "vendeuno": 0.1,
    "vendeall": 0.2
}
sena_desktop_info = "Tel: 6668069\nmail: master@ayudeme.com\nSalomia calle 1-20\nPepe Collazos Admin" # esto se vera en las portadas como info de contacto

# estructuras para los agentes
usuarios = []
productos = []

def limpiar_db():
    Conector.run_sql("DELETE FROM `favoritos`")
    Conector.run_sql("DELETE FROM `bloqueados`")
    Conector.run_sql("DELETE FROM `vistos`")
    Conector.run_sql("DELETE FROM `auditorias`")
    Conector.run_sql("DELETE FROM `notificaciones`")
    Conector.run_sql("DELETE FROM `pqrs`")
    Conector.run_sql("DELETE FROM `denuncias`")
    Conector.run_sql("DELETE FROM `mensajes`")
    Conector.run_sql("DELETE FROM `chats`")
    Conector.run_sql("DELETE FROM `productos`")
    Conector.run_sql("DELETE FROM `fotos`")
    Conector.run_sql("DELETE FROM `usuarios`")
    Conector.run_sql("DELETE FROM `cuentas`")
    Conector.run_sql("DELETE FROM `papelera`")
    Conector.run_sql("DELETE FROM `login_ip`")
    Conector.run_sql("DELETE FROM `tokens_de_sesion`")
    MakeImg.clear_all()

def get_hitos(inicial_dt):
    global prob_hitos
    # retorna array con fechas timestamp en las que se haran acciones
    ini_dt = datetime.fromtimestamp(inicial_dt)
    fin_dt = datetime.now()
    fechas = []
    while True:
        fechas.append(ini_dt.timestamp())
        r = random.random()
        if r < prob_hitos[0]: # gran salto sin actividad
            paso = timedelta(hours=random.randint(100 * 24, 300 * 24))
        elif r < prob_hitos[1]: # actividad continua
            paso = timedelta(hours=random.randint(1, 3 * 24))
        else: # mediano salto de actividad
            paso = timedelta(hours=random.randint(10 * 24, 30 * 24))
        ini_dt += paso
        if ini_dt >= fin_dt:
            break
    return fechas

# crear a los usuarios
def crear_usuarios():
    global usuarios, total_usuarios, year_ini, month_ini, password, prob_tener_link, prob_notifi, tipo_usr_porc, prob_descripcion, prob_superdescripcion, prob_troll, prob_lacra, dominiomail, sena_desktop_info

    ini_dt = datetime(year_ini, month_ini, 1, 0, 0, 0).timestamp()
    fin_dt = datetime.now().timestamp()
    fecha = datetime.fromtimestamp(ini_dt).strftime("%Y-%m-%d %H:%M:%S")
    usuarios.append(Usuario("master@" + dominiomail, password, "3", False, True, fecha,
        True, True, True, 0, "master", 0, 0, ini_dt, sena_desktop_info))
    MakeImg.run_img(4, usuarios[-1].imagen)
    for i in range(total_usuarios):
        r = pow(random.random(), 3)
        dt = ini_dt + r * (fin_dt - ini_dt)
        fecha = datetime.fromtimestamp(dt).strftime("%Y-%m-%d %H:%M:%S")
        rol = "2" if random.random() < tipo_usr_porc["admin"] else "1"
        email = "usr" + str(i) + "@" + dominiomail
        con_link = random.random() < prob_tener_link if rol == "1" else False
        noti_correo = random.random() < prob_notifi if rol == "1" else True
        noti_push = random.random() < prob_notifi if rol == "1" else True
        uso_datos = random.random() < prob_notifi if rol == "1" else True
        lvl_descripcion = (2 if random.random() < prob_superdescripcion else 1) if random.random() < prob_descripcion else 0
        lvl_descripcion = lvl_descripcion if rol == "1" else 0
        if rol == "2":
            tipo = "admin"
        else:
            r = random.random() * (1 - tipo_usr_porc["admin"])
            if r < tipo_usr_porc["vendeall"]:
                tipo = "vendeall"
            elif r < tipo_usr_porc["vendeall"] + tipo_usr_porc["vendeuno"]:
                tipo = "vendeuno"
            elif r < tipo_usr_porc["vendeall"] + tipo_usr_porc["vendeuno"] +\
                    tipo_usr_porc["troll"]:
                tipo = "troll"
            elif r < tipo_usr_porc["vendeall"] + tipo_usr_porc["vendeuno"] +\
                    tipo_usr_porc["troll"] + tipo_usr_porc["lacra"]:
                tipo = "lacra"
            elif r < tipo_usr_porc["vendeall"] + tipo_usr_porc["vendeuno"] +\
                    tipo_usr_porc["troll"] + tipo_usr_porc["lacra"] +\
                    tipo_usr_porc["curioso"]:
                tipo = "curioso"
            else:
                tipo = "comprador"
        go_troll = random.random() < prob_troll
        go_lacra = random.random() < prob_lacra
        con_imagen = random.random() < prob_conimagen
        usuarios.append(Usuario(email, password, rol, con_link, con_imagen, fecha,
            noti_correo, noti_push, uso_datos, lvl_descripcion, tipo, go_troll, go_lacra, dt))
        if con_imagen:
            imgtipo = "lacra" if go_lacra else ("troll" if go_troll else "")
            MakeImg.run_img(4, usuarios[-1].imagen, imgtipo)

def crear_productos():
    global usuarios, productos, tipo_usr_vende, prob_conimagen, prob_troll, prob_lacra, integridades, max_imagenes

    subcts = Conector.run_sql("SELECT s.id AS id, s.nombre AS subcategoria, c.nombre AS categoria FROM subcategorias s INNER JOIN categorias c ON c.id = s.categoria_id", None, True)
    for usr in usuarios:
        hitos = get_hitos(usr.registro_dt)
        p = tipo_usr_vende[usr.tipo]
        for hit in hitos:
            if random.random() < p:
                num_imagenes = random.randint(1, max_imagenes) if random.random() < prob_conimagen else 0
                categos = random.choice(subcts)
                r = random.random()
                if r < integridades[0]:
                    integridad = 1 # nuevo
                elif r < integridades[0] + integridades[1]:
                    integridad = 2 # usado
                elif r < integridades[0] + integridades[1] + integridades[2]:
                    integridad = 3 # reparado
                else:
                    integridad = 4 # reciclable
                if usr.tipo == "lacra":
                    tipo = "bad"
                elif usr.tipo == "troll":
                    tipo = "joda"
                elif usr.tipo == "vendeall":
                    tipo = "all" if random.random() < 0.5 else "uno"
                else:
                    tipo = "uno"
                go_troll = random.random() < prob_troll
                go_lacra = random.random() < prob_lacra
                fecha = datetime.fromtimestamp(hit).strftime("%Y-%m-%d %H:%M:%S")
                productos.append(Producto(num_imagenes, categos, integridad, usr, tipo, go_troll, go_lacra, fecha, hit))
                for img in productos[-1].imagenes:
                    MakeImg.run_img(0, img, tipo)

def ver_productos():
    pass

def generar_chats():
    pass

def generar_fav_bloq():
    pass

def eliminar_productos():
    pass

def generar_pqrs_resp():
    pass

def generar_denuncias_resp():
    pass

def abandonar_usuarios():
    pass

def main():
    print("***Sist Poblador DB TuMercadoSena***")
    print("limpiando DB")
    limpiar_db()
    print("creando usuarios")
    crear_usuarios()
    print("creando productos")
    crear_productos()
    print("viendo productos")
    ver_productos()
    print("generando chats")
    generar_chats()
    print("generando fav bloq")
    generar_fav_bloq()
    print("eliminando productos")
    eliminar_productos()
    print("generando pqrs resp")
    generar_pqrs_resp()
    print("generando denuncias resp")
    generar_denuncias_resp()
    print("abandonando usuarios")
    abandonar_usuarios()
    print("***finalizado***")

main()
