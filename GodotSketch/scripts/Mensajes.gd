extends Panel

var procedencia = "Main"
var lista = []
var listin = 0

func _on_BtnVolver_pressed():
	get_parent().Cambio(procedencia)

func Actualizar():
	var raiz = get_parent()
	lista = []
	# cada item en mensajes es un chat, consta de:
	# [vendedor, comprador, producto, fechaultima, []]
	# dentro estan los chats, cada uno contiene:
	# [isVendedor, data]
	# data="" si Img
	for i in range(len(raiz.mensajes)):
		if raiz.mensajes[i][0] == raiz.login or\
				raiz.mensajes[i][1] == raiz.login:
			lista.append(i)
	setTodo()

func setTodo(mover=0):
	if len(lista) == 0:
		listin = 0
		for i in range(1, 8):
			setFicha(get_node("User" + str(i)), -1)
	else:
		listin = clamp(listin + mover,
			0, max(0, len(lista) - 7))
		for i in range(7):
			if listin + i < len(lista):
				setFicha(get_node("User" + str(i + 1)), listin + i)
			else:
				setFicha(get_node("User" + str(i + 1)), -1)

func setFicha(ficha, indLista):
	var raiz = get_parent()
	if indLista == -1:
		ficha.visible = false
	else:
		var m = lista[indLista]
		var i = raiz.mensajes[m][0]
		if i == raiz.login:
			i = raiz.mensajes[m][1]
		ficha.get_node("Img").texture = raiz.usuario[i][0]
		ficha.get_node("Nombre").text = raiz.usuario[i][2]
		ficha.get_node("Fecha").text = raiz.mensajes[m][3]
		ficha.get_node("Online").visible = raiz.online[i]
		var s = "> "
		if len(raiz.mensajes[m][4]) == 0:
			ficha.get_node("Chat").text = "..."
		else:
			if raiz.mensajes[m][4][-1][0]:
				if raiz.mensajes[m][0] == raiz.login:
					s = "tú: "
			else:
				if raiz.mensajes[m][1] == raiz.login:
					s = "tú: "
			ficha.get_node("Chat").text = s + raiz.mensajes[m][4][-1][1]
		ficha.visible = true

func goChat(indLista):
	var raiz = get_parent()
	var i = lista[indLista]
	var vnd = raiz.mensajes[i][0]
	var cmp = raiz.mensajes[i][1]
	raiz.Cambio("Chat")
	raiz.get_node("Chat").LoadChat(vnd, -1, cmp)

func _on_Btn1_pressed():
	goChat(listin)

func _on_Btn2_pressed():
	goChat(listin + 1)

func _on_Btn3_pressed():
	goChat(listin + 2)

func _on_Btn4_pressed():
	goChat(listin + 3)

func _on_Btn5_pressed():
	goChat(listin + 4)

func _on_Btn6_pressed():
	goChat(listin + 5)

func _on_Btn7_pressed():
	goChat(listin + 6)

func _on_BtnUp_pressed():
	setTodo(1)

func _on_BtnDown_pressed():
	setTodo(-1)
