extends Panel

var procedencia = "Main"
var productin = 0
var filtrado = []

func _ready():
	Actualizar()

func Actualizar():
	var raiz = get_parent()
	filtrado = []
	for p in range(len(raiz.producto)):
		if raiz.producto[p][2] == raiz.login:
			filtrado.append(p)
	filtrado.shuffle()
	setProducto()

func setProducto(mover=0):
	if len(filtrado) == 0:
		productin = 0
		for i in range(1, 10):
			setFicha(get_node("Producto" + str(i)), -1)
	else:
		productin = clamp(productin + mover * 3,
			0, max(0, len(filtrado) - 8))
		for i in range(9):
			if productin + i < len(filtrado):
				setFicha(get_node("Producto" + str(i + 1)), productin + i)
			else:
				setFicha(get_node("Producto" + str(i + 1)), -1)

func setFicha(ficha, indFiltrado):
	var raiz = get_parent()
	if indFiltrado == -1:
		ficha.visible = false
	else:
		var i = filtrado[indFiltrado]
		ficha.get_node("Img").texture = raiz.producto[i][0]
		ficha.get_node("Nombre").text = raiz.producto[i][3]
		ficha.get_node("Precio").text = "$" + str(raiz.producto[i][5])
		ficha.visible = true

func _on_BtnVolver_pressed():
	get_parent().Cambio(procedencia)

func _on_BtnUp_pressed():
	setProducto(-1)

func _on_BtnDown_pressed():
	setProducto(1)

func GoProducto(indFiltrado):
	var raiz = get_parent()
	if indFiltrado != -1:
		var i = filtrado[productin + indFiltrado]
		raiz.Cambio("Mio")
		raiz.get_node("Mio").setProducto(i)

func _on_BtnProducto1_pressed():
	GoProducto(0)

func _on_BtnProducto2_pressed():
	GoProducto(1)

func _on_BtnProducto3_pressed():
	GoProducto(2)

func _on_BtnProducto4_pressed():
	GoProducto(3)

func _on_BtnProducto5_pressed():
	GoProducto(4)

func _on_BtnProducto6_pressed():
	GoProducto(5)

func _on_BtnProducto7_pressed():
	GoProducto(6)

func _on_BtnProducto8_pressed():
	GoProducto(7)

func _on_BtnProducto9_pressed():
	GoProducto(8)
