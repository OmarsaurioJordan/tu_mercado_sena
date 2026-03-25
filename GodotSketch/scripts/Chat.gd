extends Panel

var tipos = [
	preload("res://scenes/ImgL.tscn"),
	preload("res://scenes/ImgR.tscn"),
	preload("res://scenes/TxtL.tscn"),
	preload("res://scenes/TxtR.tscn")
]
var chatMax = 0.0
var chatLen = 0.0
var msjs = []
var mivendedor = -1
var miproducto = -1
var separa = 114

var palabrotas = [
	"hola", "más vos", "pues no", "ninguno", "uno", "dos", "tres", "jajaja",
	"queseso", "re mal", "todos", "damelo", "uy no", "jijiji", "¡eso!", "¿ah?",
	"que", "no", "si", "de una", "pelmazo", "es mentira", "me gusta", "bien",
	"está fresco", "creo", "bueno", "mañana", "!ay!", "ma$&%do", "ya", "dinero",
	"hoy", "rápido", "urgente", "entonces", "traígalo", "nuevo", "de segunda",
	"tramposo", "dañado", "eso creo", "revíselo", "bancolombia", "nequi","viejo",
	"en salomia", "JAJAJA", "XD", ":v", ":3", "gratis", "regalado", "lo llevo",
	"cuando", "lunes", "martes", "miercoles", "okey", "¿cómo?",
	"jueves", "viernes", "sabado", "domingo", "¡no!", "¡si!", "llévelo",
	"al amanecer", "mitad", "al mes", "al año", "el", "la", "repito",
	"un día", "dos días", "tres días", "un mes", "eso", "esa", "venga",
	"un año", "hoy mismo", "será mañana", "diferente", "por", "con", "sin",
	"musiquita", "esperemos", "con paciencia", "para yá", "para",
	"un momento", "unos días", "prontamente", "enseguida", "qué pereza",
	"metodo", "pago", "tarjeta", "celular", "sos loco", "me parece",
	"cuanto", "plata", "viaje", "banco", "falso", "verdadero",
	"vale", "cuesta", "costoso", "caro", "rancio", "buenísimo",
	"amor", "honey", "cariño", "love", "piltrafa", "pésimo"
]

# cada item en mensajes es un chat, consta de:
# [vendedor, comprador, producto, fechaultima, []]
# dentro estan los chats, cada uno contiene:
# [isVendedor, data]
# data="" si Img

func _ready():
	chatMax = $Msjs.rect_position.y
	for t in $Msjs.get_children():
		t.queue_free()
	
	for p in range(len(palabrotas)):
		for r in range(len(palabrotas)):
			if p == r:
				continue
			if palabrotas[p] == palabrotas[r]:
				print(palabrotas[p])
				break

func _process(_delta):
	if Input.is_action_just_pressed("key_enter"):
		if not Input.is_action_pressed("key_shift"):
			_on_BtnSend_pressed()
	if Input.is_action_just_pressed("key_scroll_up"):
		$Msjs.rect_position.y = min(chatMax, $Msjs.rect_position.y + separa / 2)
	if Input.is_action_just_pressed("key_scroll_down"):
		$Msjs.rect_position.y = max(chatMax - chatLen + min(chatLen, separa * 6),
			$Msjs.rect_position.y - separa / 2)

func LoadChat(vendedor, producto, comprador=null):
	# producto puede ser -1
	var raiz = get_parent()
	if comprador == null:
		comprador = raiz.login
	mivendedor = vendedor
	miproducto = producto
	for t in $Msjs.get_children():
		t.queue_free()
	chatLen = 0.0
	$LinMsj.text = ""
	raiz.newChat(vendedor, miproducto, comprador)
	for m in range(len(raiz.mensajes)):
		if raiz.mensajes[m][0] == vendedor and\
				raiz.mensajes[m][1] == comprador:
			miproducto = raiz.mensajes[m][2]
			# poner informacion de encabezado
			$ImgUsr.texture = raiz.usuario[raiz.mensajes[m][0]][0]
			$Online.visible = raiz.online[raiz.mensajes[m][0]]
			$Nombre.text = raiz.usuario[raiz.mensajes[m][0]][2]
			$Fecha.text = raiz.mensajes[m][3]
			if miproducto == -1:
				$ImgPro.visible = false
			else:
				$ImgPro.texture = raiz.producto[raiz.mensajes[m][2]][0]
				$ImgPro/Producto.text = raiz.producto[raiz.mensajes[m][2]][3]
				$ImgPro.visible = true
			# cargar msjs
			for c in raiz.mensajes[m][4]:
				if c[0]:
					if c[1] == "":
						addMsj(0)
					else:
						addMsj(2, c[1])
				else:
					if c[1] == "":
						addMsj(1)
					else:
						addMsj(3, c[1])
			break

func addMsj(tipo, txt=""):
	msjs.append(tipos[tipo].instance())
	$Msjs.add_child(msjs[-1])
	var c = 0
	if txt != "":
		msjs[-1].get_child(0).text = txt
		c = msjs[-1].get_child(0).get_line_count()
	msjs[-1].rect_position.x = 0
	msjs[-1].rect_position.y = chatLen
	if tipo >= 2:
		chatLen += (separa / 2) * c
	else:
		chatLen += separa * 2

func setMsj(vendedor, isNPC, txt=""):
	var raiz = get_parent()
	raiz.setMsj(vendedor, isNPC, txt)
	if isNPC:
		var ok = $Msjs.rect_position.y == chatMax - chatLen + min(chatLen, separa * 6)
		if txt == "":
			addMsj(0)
		else:
			addMsj(2, txt)
		if ok:
			$Msjs.rect_position.y = chatMax - chatLen + min(chatLen, separa * 6)
	else:
		if txt == "":
			addMsj(1)
		else:
			addMsj(3, txt)
		$Msjs.rect_position.y = chatMax - chatLen + min(chatLen, separa * 6)
	$Fecha.text = "ahora"
	if txt != "" and not isNPC:
		$LinMsj.text = ""

func _on_BtnSend_pressed():
	var txt = $LinMsj.text
	if txt != "":
		if txt.substr(txt.length() - 1, 1) == "\n":
			txt = txt.substr(0, txt.length() - 1)
	if txt != "":
		setMsj(mivendedor, false, txt)
		setIA()

func _on_BtnImg_pressed():
	setMsj(mivendedor, false)
	setIA()

func setIA():
	if get_parent().online[mivendedor]:
		$RelojIA.start(rand_range(3, 9))

func _on_BtnOk_pressed():
	if miproducto != -1:
		var raiz = get_parent()
		for m in range(len(raiz.mensajes)):
			if raiz.mensajes[m][0] == mivendedor or\
					raiz.mensajes[m][1] == mivendedor:
				raiz.get_node("Transaccion").Actualizar(m, miproducto)
				raiz.Cambio("Transaccion")
				break
	else:
		$Reloj.start()
		$Mensaje.visible = true

func _on_BtnUp_pressed():
	$Msjs.rect_position.y = min(chatMax, $Msjs.rect_position.y + separa / 2)

func _on_BtnDown_pressed():
	$Msjs.rect_position.y = max(chatMax - chatLen + min(chatLen, separa * 6),
		$Msjs.rect_position.y - separa / 2)

func _on_BtnVolver_pressed():
	get_parent().Cambio("Mensajes")

func _on_BtnUsr_pressed():
	get_parent().Cambio("Perfil")
	get_parent().get_node("Perfil").setPerfil(mivendedor)

func _on_BtnPro_pressed():
	if miproducto != -1:
		get_parent().Cambio("Producto")
		get_parent().get_node("Producto").setProducto(miproducto)

func MensajeAuto(isIA):
	if randf() < 0.05:
		setMsj(mivendedor, isIA)
	else:
		var txt = ""
		var tot = rand_range(4, 60)
		while txt.length() < tot:
			txt += palabrotas[randi() % len(palabrotas)]
			if randf() < 0.25:
				txt += ", "
			else:
				txt += " "
		setMsj(mivendedor, isIA, txt)

func _on_RelojIA_timeout():
	MensajeAuto(true)
	if randf() < 0.2:
		setIA()

func _on_Reloj_timeout():
	$Mensaje.visible = false
