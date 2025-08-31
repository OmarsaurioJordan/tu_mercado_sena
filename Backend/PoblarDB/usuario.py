import random
from db_sql import Conector

class Usuario:
    
    def __init__(self, correo, password, rol, con_link, fecha,
            noti_correo, noti_push, uso_datos, lvl_descripcion, tipo, go_troll, go_lacra, dt):
        self.correo = correo
        self.password = password
        self.rol_id = rol
        self.nombre = self.newNombre()
        self.avatar = self.newAvatar()
        self.tipo = tipo # master, admin, comprador, curioso, lacra, troll, vendeuno, vendeall
        self.descripcion = self.newDescripcion(lvl_descripcion, go_troll, go_lacra)
        self.link = self.newLink() if con_link else ""
        self.estado_id = "1"
        self.notifica_correo = noti_correo
        self.notifica_push = noti_push
        self.uso_datos = uso_datos
        self.fecha_registro = fecha
        self.fecha_actualiza = fecha
        self.fecha_reciente = fecha
        self.registro_dt = dt
        self.id = self.sendSQL()
    
    def sendSQL(self):
        id = Conector.run_sql("INSERT INTO `correos` (`correo`, `clave`, `fecha_mail`) VALUES (%s, '', %s)", (self.correo, self.fecha_registro))
        if id == -1:
            return id
        return Conector.run_sql("INSERT INTO `usuarios` (`correo_id`, `password`, `rol_id`, `nombre`, `avatar`, `descripcion`, `link`, `estado_id`, `notifica_correo`, `notifica_push`, `uso_datos`, `fecha_registro`, `fecha_actualiza`, `fecha_reciente`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", (id, self.password, self.rol_id, self.nombre, self.avatar, self.descripcion, self.link, self.estado_id, self.notifica_correo, self.notifica_push, self.uso_datos, self.fecha_registro, self.fecha_actualiza, self.fecha_reciente))

    def newNombre(self):
        nombresH = [
            "Aurelio", "Armando", "Andy", "Alexander", "Alejandro",
            "Ariel", "Adam", "Adolfo", "Alberto", "Antonio",
            "Boris", "Brandon", "Brayan", "Bruce", "Bart",
            "Carlos", "Camilo", "Christian", "Cesar", "Cory",
            "Cain", "Charly", "Chuck", "Conan", "Clark",
            "Daniel", "Dante", "Dario", "Denis", "Dustin",
            "Ernesto", "Esteban", "Erick", "Emilio", "Efrain",
            "Francisco", "Federico", "Fabio", "Fernando", "Felipe",
            "Gabriel", "Galvin", "German", "Gonzalo", "Gaston",
            "Hector", "Humberto", "Herbert", "Hugo", "Harry",
            "Ignacio", "Izidro", "Ismael", "Ivan", "Isaac",
            "Jose", "John", "Juan", "Jeremias", "Jean",
            "Kevin", "Kenner", "Kenny", "Klaus", "Karin",
            "Luis", "Leonardo", "Lucas", "Leandro", "Leo",
            "Mateo", "Mauricio", "Manuel", "Marcos", "Martin",
            "Michael", "Marino", "Matias", "Merlin", "Max",
            "Norman", "Nicolas", "Nestor", "Nelson", "Nacho",
            "Omar", "Oswaldo", "Oliver", "Orion", "Ovidio",
            "Pablo", "Paco", "Pancho", "Paul", "Pedro",
            "Ramon", "Remi", "Ragnar", "Ramiro", "Rafael",
            "Santiago", "Samuel", "Sebastian", "Stephen", "Salvador",
            "Tomas", "Tadeo", "Teodoro", "Teofilo", "Tulio",
            "Victor", "Vicente", "Vladimir", "Ventura", "Van",
            "Wilfredo", "William", "Waldo", "Wally", "Walter",
            "Xander", "Yael", "Zamir", "Querubin", "Ulises"
        ]
        nombresM = [
            "Angy", "Angela", "Alejandra", "Amanda", "Andrea",
            "Ana", "Adriana", "Allison", "Aurora", "Agata",
            "Bonnie", "Bianca", "Barbara", "Bety", "Brenda",
            "Cony", "Camila", "Carolina", "Celeste", "Cindy",
            "Celia", "Catherine", "Cristina", "Coral", "Claudia",
            "Darian", "Diana", "Dafne", "Dora", "Delia",
            "Estefany", "Eliza", "Emily", "Elena", "Esmeralda",
            "Frida", "Florencia", "Fiona", "Fabiola", "Fatima",
            "Guisela", "Gema", "Gloria", "Gabriela", "Ginna",
            "Homura", "Helena", "Hilda", "Heidy", "Harley",
            "Ingrid", "Ines", "Isabella", "Irene", "Iris",
            "Jackie", "Jazmin", "Jade", "Jenny", "Jessica",
            "Karen", "Kimi", "Kala", "Keiko", "Kyoko",
            "Luisa", "Luz", "Luna", "Laura", "Lorena",
            "Maria", "Martha", "Maribel", "Melisa", "Marcela",
            "Marisol", "Melanie", "Miranda", "Monica", "Margarita",
            "Nora", "Nancy", "Nereida", "Natalia", "Nadia",
            "Olivia", "Olga", "Oriana", "Ofelia", "Ovidia",
            "Patricia", "Paula", "Paloma", "Paola", "Priscila",
            "Regina", "Rebeca", "Rocio", "Rosa", "Rubi",
            "Samanta", "Sara", "Susana", "Sonia", "Selina",
            "Tania", "Tamara", "Tatiana", "Teresa", "Trixie",
            "Victoria", "Valentina", "Valery", "Vanessa", "Vania",
            "Wanda", "Wendy", "Willow", "Winny", "Wynona",
            "Ximena", "Yolanda", "Zaira", "Quira", "Ursula"
        ]
        apellidos = [
            "Acosta", "Aguilar", "Aguirre", "Alonso", "Andrade",
            "Báez", "Ballesteros", "Barrios", "Benítez", "Blanco",
            "Cabrera", "Calderón", "Campos", "Cano", "Carrillo",
            "Delgado", "Díaz", "Domínguez", "Duarte", "Durán",
            "Escalante", "Escobar", "Espinosa", "Estrada", "Echeverría",
            "Fernández", "Figueroa", "Flores", "Fonseca", "Franco",
            "Gálvez", "García", "Gil", "Gómez", "Guerrero",
            "Hernández", "Herrera", "Huerta", "Hurtado", "Huertas",
            "Ibáñez", "Iglesias", "Ibarra", "Izquierdo", "Islas",
            "Jáuregui", "Jiménez", "Juárez", "Jaramillo", "Jordán",
            "Lara", "León", "López", "Luján", "Lozano",
            "Maldonado", "Márquez", "Martínez", "Medina", "Molina",
            "Navarro", "Nieto", "Núñez", "Novoa", "Naranjo",
            "Olivares", "Orozco", "Ortega", "Ortiz", "Oviedo",
            "Pacheco", "Padilla", "Palma", "Pérez", "Ponce",
            "Quevedo", "Quintero", "Quiroga", "Quintana", "Quispe",
            "Ramírez", "Ramos", "Reyes", "Ríos", "Romero",
            "Salazar", "Salinas", "Sánchez", "Sandoval", "Soto",
            "Téllez", "Torres", "Trejo", "Trujillo", "Tovar",
            "Urbina", "Ureña", "Uribe", "Ulloa", "Urdiales",
            "Valdés", "Valencia", "Valenzuela", "Vásquez", "Vega",
            "King", "Walker", "Xu", "Yosa", "Werner",
            "Zambrano", "Zapata", "Zavala", "Zúñiga", "Zárate"
        ]
        genero = nombresH if random.random() < 0.5 else nombresM
        return random.choice(genero) + " " + random.choice(apellidos) + " " + random.choice(apellidos)
    
    def newDescripcion(self, lvl_descripcion, go_troll=False, go_lacra=False):
        limite1 = random.random() * (lvl_descripcion / 2)
        limite2 = random.random() * (lvl_descripcion / 2)
        limite = round(512 * max(limite1, limite2))
        gustos = [
            [
                "adoro", "amo", "me gusta", "me encanta", "me dedico a",
                "odio", "detesto", "me incomoda", "reprocho", "me disgusta",
                "admiro", "quiero", "quisiera", "yo hacía", "me gustaba"
            ],
            [
                "el fútbol", "la natación", "senderismo", "escalar", "dormir",
                "beber", "dibujar", "gritar", "tejer", "comer", "los chistes",
                "la medicina", "la lectura", "leer", "trasquilar", "cabalgar"
            ]
        ]
        emociones = [
            [
                "estoy", "soy", "me siento", "pienso", "creo que",
                "reitero que", "hoy ando", "ayer andaba", "ando algo", "tengo",
                "yo", "nomás un", "quiero ser", "analizo que", "me pesa"
            ],
            [
                "la depresión", "la ansiedad", "el estrés", "felíz", "locura",
                "positivo", "pesimista", "hiperactividad", "emo", "impulsivo",
                "care palo", "serio", "bufón", "algo chistoso", "idiota"
            ]
        ]
        tareas = [
            [
                "me dedico a", "trabajo en", "estudio", "mi oficio es", "soy de",
                "quiero", "pues", "mi vocación", "estudiante de", "mi ficha",
                "no pude con", "uy", "antes fuí", "yo estudiaba", "jamás sería"
            ],
            [
                "computación", "programar", "escribir", "estudiar", "entrenador",
                "empaquetar", "barrer", "vigilar", "archivar", "analizar datos",
                "domar alces", "administración", "profesor", "enseñar", "secretariado"
            ]
        ]
        palabras = [gustos, emociones, tareas]
        if self.tipo == "lacra" and go_lacra:
            palabras.append([
                [
                    "venga por", "le doy", "yo vendo", "quiere", "buscas",
                    "quiero", "necesito", "compro", "busco", "me urge"
                ],
                [
                    "420", "hierba", "cilantro", "vaper", "cannabis",
                    "cuchillo", "droga", "salchicha", "webcam", "xxx"
                ]
            ])
        if self.tipo == "troll" and go_troll:
            palabras.append([
                [
                    "qué ves", "qué le importa", "no joda", "lárguese", "lalala",
                    "pienséle", "qué mira", "ah vos", "enga le digo", "soy o sos"
                ],
                [
                    "fuck", "boludo", "niche", "panguano", "roñoso",
                    "goofy", "tonto", "stupid", "piruja", "lámpara"
                ]
            ])
        descrip = ""
        while True:
            p = random.choice(palabras)
            frase = random.choice(p[0]) + " " + random.choice(p[1])
            if len(descrip) + len(frase) + 3 > limite:
                break
            descrip += ("" if descrip == "" else " y ") + frase
        return descrip
    
    def newMensaje(slef, go_troll=False, go_lacra=False):
        limite = max(12, round(pow(random.random(), 3) * 512))
        palabras = [
            "jajaja", "uy no", "cómo así", "Si", "No", "pues bueno", "no quiero",
            "nooo", "de una", "no jodas", "qué va", "el color", "malito", "funciona",
            "loco sos", "pues mano", "si pilla", "gratis", "lo compro", "lo vendo",
            "es bueno", "para", "el", "la", "entonces", "qué", "XD", ":v", ":)", "y",
            "bonito", "barato", "está bien", "ayshhh", "dámelo", "quiero", "jeje",
            "pues", "ajá", "es que", "dale papu", "intercambio", "más vos", "hola"
        ]
        if self.tipo == "lacra" and go_lacra:
            palabras.extend([
                "cilantro", "cannabis", "hierba", "420", "droga",
                "xxx", "prosti", "webcam", "arma", "cuchillo"
            ])
        if self.tipo == "troll" and go_troll:
            palabras.extend([
                "fuck", "tonto", "stupid", "goofy", "lalala",
                "panguano", "niche", "piruja", "roñosos", "lámparas"
            ])
        msj = ""
        while True:
            p = random.choice(palabras)
            if len(msj) + len(p) + 1 > limite:
                break
            msj += ("" if msj == "" else " ") + p
        return msj

    def newLink(self):
        links = [
            "https://www.google.com",
            "https://www.wikipedia.org",
            "https://www.youtube.com",
            "https://www.amazon.com",
            "https://www.reddit.com",
            "https://www.nytimes.com",
            "https://www.bbc.com",
            "https://www.cnn.com",
            "https://www.stackoverflow.com",
            "https://www.github.com",
            "https://www.medium.com",
            "https://www.quora.com",
            "https://www.imdb.com",
            "https://www.nationalgeographic.com",
            "https://www.khanacademy.org",
            "https://www.coursera.org",
            "https://www.duolingo.com",
            "https://www.artstation.com",
            "https://www.deviantart.com",
            "https://www.behance.net",
            "https://www.dribbble.com",
            "https://www.etsy.com",
            "https://www.amazon.com",
            "https://www.mercadolibre.com",
            "https://www.alibaba.com",
            "https://www.ebay.com",
            "https://www.bandcamp.com",
            "https://www.soundcloud.com",
            "https://www.pixiv.net",
            "https://www.fiverr.com",
            "https://www.upwork.com",
            "https://www.freelancer.com",
            "https://unity.com",
            "https://godotengine.org",
            "https://gamemaker.io",
            "https://www.rpgmakerweb.com",
            "https://www.unrealengine.com",
            "https://defold.com",
            "https://www.construct.net",
            "https://www.cryengine.com",
            "https://gdevelop.io",
            "https://www.torque3d.org"
        ]
        return random.choice(links)
    
    def newAvatar(self):
        return str(random.randint(1, 100))
