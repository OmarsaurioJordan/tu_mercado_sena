import random
from db_sql import Conector

class Producto:
    
    def __init__(self, con_imagen, categos, integridad, vendedor,
            tipo, go_troll, go_lacra, fecha, dt):
        self.con_imagen = con_imagen
        self.subcategoria_id = categos[0]
        self.subname = categos[1]
        self.catname = categos[2]
        self.tipo = tipo # bad, joda, all, uno
        self.integridad_id = integridad
        self.vendedor_id = vendedor.id
        self.nombre = self.newNombre(go_troll, go_lacra)
        self.estado_id = "1"
        self.descripcion = self.newDescripcion(go_troll, go_lacra)
        self.precio = self.newPrecio()
        self.disponibles = self.newDisponibles()
        self.fecha_registro = fecha
        self.fecha_actualiza = fecha
        self.registro_dt = dt
        self.id = self.sendSQL()
 
    def sendSQL(self):
        return Conector.run_sql("INSERT INTO `productos` (`nombre`, `con_imagen`, `subcategoria_id`, `integridad_id`, `vendedor_id`, `estado_id`, `descripcion`, `precio`, `disponibles`, `fecha_registro`, `fecha_actualiza`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", (self.nombre, self.con_imagen, self.subcategoria_id, self.integridad_id, self.vendedor_id, self.estado_id, self.descripcion, self.precio, self.disponibles, self.fecha_registro, self.fecha_actualiza))

    def newNombre(self, go_troll=False, go_lacra=False):
        n = ""
        if self.tipo == "bad" and go_lacra:
            n = random.choice([
                    "cilantro", "cannabis", "hierba", "droga",
                    "navaja", "chuchillos", "webcam", "xxx",
                    "vaper", "nalgas", "abortos", "ladrillo",
                ])
        elif self.tipo == "joda" and go_troll:
            n = random.choice([
                    "me vendo", "tu tío", "jajaja", "lol",
                    ":v", "mi japi", "trolazo", "no leas",
                    "vota por mí", "te amo Luz", "spam", "tu vieja",
                ])
        else:
            match self.catname:
                case "vestimenta":
                    n = random.choice([
                        "trapos", "pantalón", "camisa", "blusa",
                        "sandalias", "bóxer", "tanga", "gorra",
                        "bermuda", "chaleco", "botas", "zapatos",
                    ])
                case "alimento":
                    n = random.choice([
                        "arrozito", "carnita", "espagueti", "lechuga",
                        "caramelos", "agua", "pollito", "fresas",
                        "fruta", "bebida", "postre", "helado",
                    ])
                case "papelería":
                    n = random.choice([
                        "cuaderno", "lápiz", "bolígrafo", "grapadora",
                        "papel", "ega", "borrador", "tinta",
                        "colores", "libro", "fotocopia", "folio",
                    ])
                case "herramienta":
                    n = random.choice([
                        "tijeras", "taladro", "pulidora", "mazo",
                        "alicate", "puntillas", "brocas", "martillo",
                        "taches", "bastón", "leds", "conos",
                    ])
                case "cosmético":
                    n = random.choice([
                        "sombra", "pestañas", "peluca", "labial",
                        "talco", "espejito", "rubor", "pestañina",
                        "condón", "tampónes", "moñas", "pulsera",
                    ])
                case "deportivo":
                    n = random.choice([
                        "balón", "skateboard", "guayos", "soga",
                        "conitos", "snorkel", "patines", "mancuerna",
                        "silbato", "raqueta", "pelota", "pesas",
                    ])
                case "dispositivo":
                    n = random.choice([
                        "celular", "laptop", "computadora", "pantalla",
                        "audífonos", "micrófono", "teclado", "mouse",
                        "parlante", "nokia", "iphone", "USB",
                    ])
                case "servicio":
                    n = random.choice([
                        "fotocopias", "redacción", "tesis", "programación",
                        "baile", "entrenamiento", "cocina", "diseño",
                        "psicología", "tarot", "lavandería", "inglés",
                    ])
                case "social":
                    n = random.choice([
                        "amistad", "confesiónes", "charla", "filosofar",
                        "política", "pareja", "pasear", "fiesta",
                        "feministas", "lgbti", "apoyo", "viajar",
                    ])
                case "mobiliario":
                    n = random.choice([
                        "asiento", "mesa", "sillón", "nevera",
                        "cesta", "canasto", "cama", "estufa",
                        "cortinas", "lavadora", "ventilador", "colchón",
                    ])
                case "vehículo":
                    n = random.choice([
                        "automóvil", "moto", "scooter", "bicicleta",
                        "camioneta", "jeep", "eléctrica", "tarjeta MIO",
                        "bujía", "tacómetro", "aceite", "llanta",
                    ])
                case "mascota":
                    n = random.choice([
                        "perro", "cachorro", "gato", "peces",
                        "collar", "pienso", "reptiles", "aves",
                        "correa", "manta", "tapete", "bozal",
                    ])
                case "otro":
                    n = random.choice([
                        "transformer", "juguete", "legos", "Netflix",
                        "dildo", "chatarra", "diamante", "Minecraft"
                    ])
                case _:
                    n = "???"
        n += " " + random.choice([
            "blanco", "gris", "negro", "fucsia", "morado",
            "verde", "rojo", "azul", "amarillo", "café",
            "naranja", "rosado", "caqui", "plateado", "dorado",
            "traslúcido", "clarito", "azulado", "rojizo", "limón"
        ])
        n += " " + random.choice([
            "buenísimo", "increíble", "gangazo", "bueno", "barato",
            "chévere", "ezquisito", "limpio", "magnífico", "poderoso",
            "común", "sencillo", "simple", "insípido", "rancio",
            "reparado", "sucio", "pasable", "útil", "cómprelo",
            "duro", "esponjoso", "suave", "sabroso", "gratis"
        ])
        return n

    def newDescripcion(self, go_troll=False, go_lacra=False):
        limite1 = random.random()
        limite2 = random.random()
        limite = round(512 * max(limite1, limite2))
        palabras = [
            "jajaja", "uy no", "cómo así", "Si", "No", "pues bueno", "no quiero",
            "nooo", "de una", "no jodas", "qué va", "el color", "malito", "funciona",
            "loco sos", "pues mano", "si pilla", "gratis", "lo compro", "lo vendo",
            "es bueno", "para", "el", "la", "entonces", "qué", "XD", ":v", ":)", "y",
            "bonito", "barato", "está bien", "ayshhh", "dámelo", "quiero", "jeje",
            "pues", "ajá", "es que", "dale papu", "intercambio", "más vos", "hola"
        ]
        if self.tipo == "bad" and go_lacra:
            palabras.extend([
                "cilantro", "cannabis", "hierba", "420", "droga",
                "xxx", "prosti", "webcam", "arma", "cuchillo"
            ])
        if self.tipo == "joda" and go_troll:
            palabras.extend([
                "fuck", "tonto", "stupid", "goofy", "lalala",
                "panguano", "niche", "piruja", "roñosos", "lámparas"
            ])
        descrip = ""
        while True:
            p = random.choice(palabras)
            if len(descrip) + len(p) + 1 > limite:
                break
            descrip += ("" if descrip == "" else " ") + p
        return descrip

    def newPrecio(self, go_troll=False, go_lacra=False):
        p = 0
        if self.tipo == "bad" and go_lacra:
            p = random.randint(0, 100)
        elif self.tipo == "joda" and go_troll:
            p = random.randint(0, 1000000)
        else:
            match self.catname:
                case "vestimenta":
                    p = random.randint(100, 800)
                case "alimento":
                    p = random.randint(20, 300)
                case "papelería":
                    p = random.randint(10, 500)
                case "herramienta":
                    p = random.randint(200, 2000)
                case "cosmético":
                    p = random.randint(50, 400)
                case "deportivo":
                    p = random.randint(200, 2000)
                case "dispositivo":
                    p = random.randint(500, 10000)
                case "servicio":
                    p = random.randint(100, 1000)
                case "social":
                    p = 0
                case "mobiliario":
                    p = random.randint(1000, 7000)
                case "vehículo":
                    p = random.randint(8000, 200000)
                case "mascota":
                    p = random.randint(0, 1000)
                case "otro":
                    p = random.randint(0, 1000)
                case _:
                    p = 0
        return str(p * 100)
    
    def newDisponibles(self):
        match self.tipo:
            case "bad":
                return str(random.randint(1, 10))
            case "joda":
                return str(random.randint(1, 100000))
            case "uno":
                return "1"
            case "all":
                if self.precio == "0":
                    return str(random.randint(2, 50))
                tot = 1
                freno = random.randint(2, 50)
                r = pow(random.random(), 3)
                limite = round(300 + r * 10000) * 100
                precio = float(self.precio)
                while tot * precio < limite:
                    tot += 1
                    freno -= 1
                    if freno <= 0:
                        break
                return str(tot)
            case _:
                return "1"
