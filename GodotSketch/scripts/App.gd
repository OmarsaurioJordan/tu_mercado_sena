extends Node

# ind de usuario que ingreso
var login = -1

# imagen, nombre
var categorias = [
	[preload("res://sprites/alimento.png"), "Alimentos"], # 0
	[preload("res://sprites/ropa.png"), "Vestimenta"], # 1
	[preload("res://sprites/papeleria.png"), "Papelería"], # 2
	[preload("res://sprites/herramienta.png"), "Herramientas"], # 3
	[preload("res://sprites/cosmetico.png"), "Cosméticos"], # 4
	[preload("res://sprites/deporte.png"), "Deportivos"], # 5
	[preload("res://sprites/dispositivo.png"), "Dispositivos"], # 6
	[preload("res://sprites/servicio.png"), "Servicio"], # 7
	[preload("res://sprites/otro.png"), "Otro"], # 8
]

# imagen, correo, nombre, link, descripcion
var usuario = [
	[preload("res://avatares/ashly.png"), "ashly", "Ashly Usama", # 0
		"https://www.youtube.com/watch?v=zfNB-m4-SGw",
		"..."],
	[preload("res://avatares/darling.png"), "darling", "Darling Angulo", # 1
		"https://www.online-python.com/",
		"hola, me gusta esta aplicación, yo trabajé en ella"],
	[preload("res://avatares/erick.png"), "erick", "Erick Calderón", # 2
		"https://www.youtube.com/watch?v=kTlv5_Bs8aw",
		"bien bien XD"],
	[preload("res://avatares/felipe.png"), "felipe", "Felipe Ocampo", # 3
		"https://www.youtube.com/watch?v=xkTmIizMEQM",
		"viva el Sena"],
	[preload("res://avatares/floro.png"), "floro", "Floro Rojas", # 4
		"https://www.mercadolibre.com.co/",
		"estudiante de software"],
	[preload("res://avatares/freddy.png"), "freddy", "Freddy Ushija", # 5
		"https://www.youtube.com/watch?v=mNbnV3aN3KA",
		"aún no sé qué vender :v"],
	[preload("res://avatares/gerardo.png"), "gerardo", "Gerardo Giraldo", # 6
		"https://www.youtube.com/watch?v=bg-d2O0kWo4",
		"recuerden la normativa del Sena, si venden droga los están sacando"],
	[preload("res://avatares/heidy.png"), "heidy", "Heidy Hurtado", # 7
		"https://www.youtube.com/watch?v=887lax8LDDg",
		"todo obra para bien, aunque usted no lo crea"],
	[preload("res://avatares/janier.png"), "janier", "Janier Delacruz", # 8
		"https://www.youtube.com/watch?v=XQOpeJ8YHrE",
		"estas iniciativas son muy buenas, usted no sabe todo lo que hay"],
	[preload("res://avatares/javier.png"), "javier", "Javier Barela", # 9
		"https://www.youtube.com/watch?v=JtUmD5IA25M",
		"uy le tengo lo que busca, escribame que vendo lo que sea"],
	[preload("res://avatares/jean.png"), "jean", "Jean Carlos", # 10
		"https://www.instagram.com/",
		"esta página está llena de trolls, pero está increíble"],
	[preload("res://avatares/jefferson.png"), "jefferson", "Jefferson Angulo", # 11
		"https://chatgpt.com/",
		"solo cosas nuevas, soy el mejor vendedor del Sena dios mio"],
	[preload("res://avatares/johan.png"), "johan", "Johan Arsiniegas", # 12
		"https://www.youtube.com/watch?v=ZxzDE90Mg38",
		"viva el sena, mejor que el ejército"],
	[preload("res://avatares/juanseb.png"), "juanseb", "JuanSeb Ocampo", # 13
		"https://www.google.com.co/",
		"jajaja no sé que escribir aquí"],
	[preload("res://avatares/juliana.png"), "juliana", "Juliana Rojas", # 14
		"https://www.youtube.com/watch?v=p9ZdeARKTzE",
		"estoy emprendiendo con venta de celulares, cualuier cosa escribe"],
	[preload("res://avatares/kelly.png"), "kelly", "Kelly Zanclemente", # 15
		"https://www.youtube.com/watch?v=R1TSiB9OuVM",
		"vendo principalmente postres, hechos con recetas de mi abuela"],
	[preload("res://avatares/kevin.png"), "kevin", "Kevin Restrepo", # 16
		"https://www.youtube.com/watch?v=y9LlnLTH87U",
		"mano, compre, vea que así movemos la economía mano"],
	[preload("res://avatares/omar.png"), "omar", "Omar Jordan", # 17
		"https://omwekiatl.itch.io/",
		"desarrollo videojuegos en tiempo libre, eso es todo"],
	[preload("res://avatares/pablo.png"), "pablo", "Pablo Vocero", # 18
		"https://www.youtube.com/watch?v=nK-fhA25j3A",
		"soy vocero del ADSO24 y estoy aquí para ver si es que esto sirve"],
	[preload("res://avatares/valery.png"), "valery", "Valery Aldana", # 19
		"https://www.youtube.com/watch?v=MetXyUFY3Ts",
		"ay nooo estas clases están muy duras jajaja, vea cómpreme"]
]
var online = []

# nuevo, segunda, dannado
var calidad = ["(N)", "(S)", "(D)"]

# imagen, categoria, vendedor, nombre, calidad, valor, disponible, descripcion
var producto = [
	[preload("res://fotos/boxer.jpg"), 1, 10,
		"Boxers bien buenos", 1, 2000, 1,
		"están un poco usados pero vea, usted les da dos lavada, les puntea un poquito y sale"],
	[preload("res://fotos/camisena.jpg"), 1, 15,
		"Camisa del sena", 1, 22000, 1,
		"muchachos en vista de que me pasaré a otra institución, y no sé qué hacer con esta camisa, la publico por si a alguien le interesa"],
	[preload("res://fotos/casco.jpg"), 1, 11,
		"Casco de moto nuevo", 0, 100000, 1,
		"este casco es buenísimo, vea eso usted se puede dar de cabeza contra el Mio, se lo garantizo"],
	[preload("res://fotos/celular.jpg"), 6, 16,
		"Celular en buen estado", 1, 140000, 1,
		"hola, recién cambié mi celular y estoy buscando vender este, usted sabe mano, conseuir una platica ahí con eso"],
	[preload("res://fotos/celunuevo.jpg"), 6, 14,
		"Iphone recién comprado", 0, 720000, 2,
		"está nuevo, recién traido de San Andresito, garantizados"],
	[preload("res://fotos/chaqueta.jpg"), 1, 18,
		"Chaqueta negra", 1, 45000, 1,
		"no tiene huecos ni nada, está en buen estado pero es de segunda"],
	[preload("res://fotos/cilantro.jpg"), 0, 11,
		"Cilantro para la sopa", 0, 4200, 100,
		"vea, para que le ponga al almuerzo del Sena, ahí me escribe y cuadramos, para el almuerzo"],
	[preload("res://fotos/cuaderno.jpg"), 2, 9,
		"Cuaderno de mi hermana", 1, 1000, 1,
		"le quité el cuadreno a mi hermana jajaja está todo rayado pero le quedan unas hojas"],
	[preload("res://fotos/entrenamiento.jpg"), 7, 3,
		"Entrenamiento funcional", 0, 18000, 3,
		"entrenamiento izquiotibial, lateral y anaerobio, se dan instrucciónes en el gimnasio del Sena"],
	[preload("res://fotos/gamedev.png"), 7, 17,
		"Prototipos digitales", 0, 72000, 5,
		"hago demos de software, multimedia, videojuegos, páginas o cosas así, en Godot"],
	[preload("res://fotos/gato.jpg"), 8, 19,
		"Gatitos cachorros", 0, 0, 3,
		"la gata de mi tía tuvo unos gatos y ayyy no sabemos a quién darlos en adopción, son bien bonitos"],
	[preload("res://fotos/gorra.jpg"), 1, 2,
		"Gorra para pesca", 0, 55000, 1,
		"una gorra, ahí pueden verla, es esa misma"],
	[preload("res://fotos/guantes.jpg"), 5, 18,
		"Guantes de box", 1, 46000, 1,
		"mano, la situación está dura, yo ya colgué los guayos y ahora los guantes, ni me pidan rebaja"],
	[preload("res://fotos/labiales.jpg"), 4, 7,
		"Labiales de varios colores", 0, 21000, 20,
		"hola comunidad Sena, estoy vendiendo labiales para financiar mis estudios, preguntenme por tonos"],
	[preload("res://fotos/lapiceros.jpg"), 2, 6,
		"Lapiceros sencillos", 0, 1000, 40,
		"ustedes creen que yo necesito vender lapiceros? estoy revisando, cerciorando qué es lo que hacen aquí"],
	[preload("res://fotos/medias.jpg"), 1, 18,
		"Medias variopintas", 0, 5000, 25,
		"se le tienen las medias para el hijo, la madre, el abuelo, la amante, vea de todos los colores"],
	[preload("res://fotos/nintendo.jpg"), 6, 16,
		"Nintendo 64", 1, 67000, 1,
		"ey pues me tocó calir de esto, mano muy triste mano, espero lo cuiden bien, prende con unos golpesitos"],
	[preload("res://fotos/nokia.jpg"), 6, 9,
		"Nokia a prueba de todo", 1, 91000, 1,
		"quiere un celular que no se dañe al caer, vea este Nokia como nuevo, está intacto"],
	[preload("res://fotos/pan.jpg"), 0, 9,
		"Pan que mordí anoche", 1, 500, 1,
		"un pan, pues está medio manoseado y mordido, pero relájado, eso todavía es comida jajaja"],
	[preload("res://fotos/postre.jpg"), 0, 15,
		"Postres de milo", 0, 3000, 11,
		"me pueden encargar postres por el chat, yo los suelo hacer cada lunes y viernes, escríban"],
	[preload("res://fotos/skateboard.jpg"), 5, 16,
		"Skateboard sin uso", 0, 62000, 1,
		"la compré y ay, nunca aprendí a montar eso, es que mano no tengo tiempo, pero si me sirve la platica"],
	[preload("res://fotos/vestido.jpg"), 1, 0,
		"Vestido kawai otaku", 1, 59000, 1,
		"un vestido, puedo llevarlo para probar en el baño, está en buen estado"],
	[preload("res://fotos/zapatos.jpg"), 1, 9,
		"Zapatos de mi tío", 2, 7000, 1,
		"el loco este se muriooo man de fumar, y pues no sabemos que hacer con esa ropa"],
	[preload("res://fotos/zapatico.jpg"), 1, 18,
		"Un zapato solo", 0, 69000, 1,
		"vendo un solo zapato, nuevo, hecho para deporte de alto rendimiento, el otro se me perdió"]
]

# [calificado, calificador, valor 0-5]
var califica = []

# [favorito, votante]
var favorito = []

# igual de largo que producto, con el num de vistas de cada uno
var vistas = []

# [bloqueado, porquien, denunciado]
var bloqueados = []

# cada item en mensajes es un chat, consta de:
# [vendedor, comprador, producto, fechaultima, []]
# dentro estan los chats, cada uno contiene:
# [isVendedor, data]
# data="" si Img
var mensajes = []

func _init():
	randomize()
	Calificaciones()
	var tot = len(usuario)
	for _i in range(len(producto)):
		vistas.append(randi() % tot)
	for _i in range(len(usuario)):
		online.append(randf() < 0.666)

func _ready():
	cargar()
	if login == -1:
		Cambio("Login")
	else:
		Cambio("Main")

func Cambio(nameHoja):
	get_node("Introduccion").get_node("Video").stop()
	var hoja = get_node(nameHoja)
	if hoja != null:
		var posHoja = hoja.rect_position
		for h in get_children():
			h.rect_position -= posHoja
			if h.rect_position.x == 0 and h.rect_position.y == 0:
				match h.name:
					"Editar":
						h.setPerfil()
					"Usuario":
						h.setPerfil()
					"Vender":
						h.Limpiar()
					"Ayuda":
						h.Limpiar()
					"Bloqueados":
						h.setBloqueados()
					"Mensajes":
						h.Actualizar()
					"Favoritos":
						h.buscarVendedores()

func isBloqueado(ind):
	for i in range(len(bloqueados)):
		# [bloqueado, porquien, denunciado]
		if bloqueados[i][0] == ind and bloqueados[i][1] == login:
			return true
	return false

func isDenunciado(ind):
	for i in range(len(bloqueados)):
		# [bloqueado, porquien, denunciado]
		if bloqueados[i][0] == ind and bloqueados[i][1] == login:
			return bloqueados[i][2]
	return false

func Bloquear(ind):
	if not isBloqueado(ind):
		# [bloqueado, porquien, denunciado]
		bloqueados.append([ind, login, false])
		get_node("Main").setCategoria()

func Desbloquear(ind):
	for i in range(len(bloqueados)):
		if bloqueados[i][0] == ind and bloqueados[i][1] == login:
			bloqueados.remove(i)
			get_node("Main").setCategoria()
			break

func Denunciar(ind):
	for i in range(len(bloqueados)):
		if bloqueados[i][0] == ind and bloqueados[i][1] == login:
			bloqueados[i][2] = true
			break

func setVenta(imagen, categoria, nombre, caliddd, valor, disponible, descripcion):
	# imagen, categoria, vendedor, nombre, calidad, valor, disponible, descripcion
	producto.append([
		#[preload("res://fotos/boxer.jpg"), 1, 10,
		#"Boxers", 1, 2000, 1,
		#"están un poco usados pero sirven"]
		imagen, categoria, login,
		nombre, caliddd, valor, disponible,
		descripcion
	])
	vistas.append(0)
	$Main.setCategoria()
	$Ventas.Actualizar()
	return len(producto) - 1

func upVenta(ind, imagen, categoria, nombre, caliddd, valor, disponible, descripcion):
	# imagen, categoria, vendedor, nombre, calidad, valor, disponible, descripcion
	producto[ind][0] = imagen
	producto[ind][1] = categoria
	producto[ind][3] = nombre
	producto[ind][4] = caliddd
	producto[ind][5] = valor
	producto[ind][6] = disponible
	producto[ind][7] = descripcion
	var tot = len(usuario)
	vistas[ind] = min(tot - 1, vistas[ind] + randi() % 3)
	$Main.setCategoria()
	$Ventas.Actualizar()

func guardar():
	var file = File.new()
	file.open("user://save.dat", file.WRITE)
	file.store_string(str(login))
	file.close()

func cargar():
	var file = File.new()
	if file.file_exists("user://save.dat"):
		file.open("user://save.dat", file.READ)
		login = int(file.get_as_text())
		file.close()

func Calificaciones():
	var juez = 0.5
	var peso = []
	for u in range(len(usuario)):
		# cada usuario tendra su probabilidad de calificar a otros
		juez = rand_range(0.1, 0.9)
		peso = [1, 2, 3, 4, 5]
		if randf() < 0.666:
			if randf() < 0.333:
				peso.append_array([1, 1, 2])
			else:
				peso.append_array([4, 5, 5])
		for c in range(len(usuario)):
			if u != c:
				# probabilidad calificarlo
				if randf() < juez:
					# [calificado, calificador, valor 0-5]
					peso.shuffle()
					califica.append([c, u, peso[0]])
					# probabilidad ponerlo como favorito
					if peso[0] >= 4 and randf() < juez:
						# [favorito, votante]
						favorito.append([c, u])

func getCalifica(ind):
	var mmm = getNota(ind)
	var tot = mmm[0]
	var valor = mmm[1]
	var txt = ".....\n0"
	if tot > 0:
		if valor == 0:
			txt = "ooooo"
		elif valor == 1:
			txt = "xoooo"
		elif valor == 2:
			txt = "xxooo"
		elif valor == 3:
			txt = "xxxoo"
		elif valor == 4:
			txt = "xxxxo"
		else: # 5
			txt = "xxxxx"
		txt += "\n" + str(tot)
	return txt

func getNota(ind):
	var valor = 0
	var tot = 0
	for i in range(len(califica)):
		# [calificado, calificador, valor 0-5]
		if califica[i][0] == ind:
			valor += califica[i][2]
			tot += 1
	valor = round(float(valor) / tot)
	return [tot, valor]

func getFavoritos(ind):
	var tot = 0
	for i in range(len(favorito)):
		# [favorito, votante]
		if favorito[i][0] == ind:
			tot += 1
	return "Fav\n" + str(tot)

func MedioLink(link, limite=32):
	if len(link) > limite:
		return link.substr(0, limite)
	return link

# cada item en mensajes es un chat, consta de:
# [vendedor, comprador, producto, fechaultima, []]
# dentro estan los chats, cada uno contiene:
# [isVendedor, data]
# data="" si Img

func newChat(vendedr, prodkt, comprador=null):
	if comprador == null:
		comprador = login
	for m in range(len(mensajes)):
		if mensajes[m][0] == vendedr and mensajes[m][1] == comprador:
			if prodkt != -1:
				mensajes[m][2] = prodkt
			return null
	mensajes.append([vendedr, comprador, prodkt, "ahora", []])

func setMsj(vendedr, isNPC, txt):
	for m in range(len(mensajes)):
		if mensajes[m][0] == vendedr and mensajes[m][1] == login:
			mensajes[m][3] = "ahora"
			mensajes[m][4].append([isNPC, txt])
			break

func deleteChat(indUsr):
	for m in range(len(mensajes)):
		if mensajes[m][0] == indUsr or mensajes[m][1] == indUsr:
			mensajes.remove(m)
			break

func setHistorial(indChat, valor, indProducto):
	pass

func getHuella():
	# [favorito, votante]
	var txt = ""
	for f in favorito:
		txt += "|" + str(f[0]) + "," + str(f[1])
	return txt
