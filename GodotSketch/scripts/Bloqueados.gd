extends Panel

var lista = []
var listin = 0

func _ready():
	setBloqueados()

func setBloqueados():
	var raiz = get_parent()
	lista = []
	for i in range(len(raiz.usuario)):
		# imagen, correo, nombre, link, descripcion
		if raiz.isBloqueado(i):
			lista.append(i)
	setTodo()

func setTodo(mover=0):
	if len(lista) == 0:
		listin = 0
		for i in range(1, 7):
			setFicha(get_node("User" + str(i)), -1)
	else:
		listin = clamp(listin + mover,
			0, max(0, len(lista) - 6))
		for i in range(6):
			if listin + i < len(lista):
				setFicha(get_node("User" + str(i + 1)), listin + i)
			else:
				setFicha(get_node("User" + str(i + 1)), -1)

func setFicha(ficha, indLista):
	var raiz = get_parent()
	if indLista == -1:
		ficha.visible = false
	else:
		var i = lista[indLista]
		ficha.get_node("Img").texture = raiz.usuario[i][0]
		ficha.get_node("Nombre").text = raiz.usuario[i][2]
		var d = raiz.isDenunciado(i)
		ficha.get_node("BtnDenun" + ficha.name.replace("User", "")).disabled = d
		ficha.get_node("BtnDesbloq" + ficha.name.replace("User", "")).disabled = d
		ficha.visible = true

func _on_BtnVolver_pressed():
	get_parent().Cambio("Opciones")

func _on_BtnDown_pressed():
	setTodo(-1)

func _on_BtnUp_pressed():
	setTodo(1)

func Desbloquear(indLista):
	get_parent().Desbloquear(lista[indLista])
	setBloqueados()

func Denunciar(indLista):
	get_parent().Denunciar(lista[indLista])
	setBloqueados()

func _on_BtnDenun1_pressed():
	Denunciar(listin)

func _on_BtnDesbloq1_pressed():
	Desbloquear(listin)

func _on_BtnDenun2_pressed():
	Denunciar(listin + 1)

func _on_BtnDesbloq2_pressed():
	Desbloquear(listin + 1)

func _on_BtnDenun3_pressed():
	Denunciar(listin + 2)

func _on_BtnDesbloq3_pressed():
	Desbloquear(listin + 2)

func _on_BtnDenun4_pressed():
	Denunciar(listin + 3)

func _on_BtnDesbloq4_pressed():
	Desbloquear(listin + 3)

func _on_BtnDenun5_pressed():
	Denunciar(listin + 4)

func _on_BtnDesbloq5_pressed():
	Desbloquear(listin + 4)

func _on_BtnDenun6_pressed():
	Denunciar(listin + 5)

func _on_BtnDesbloq6_pressed():
	Desbloquear(listin + 5)
