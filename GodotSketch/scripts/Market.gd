extends Panel

var productin = 0
var filtrado = []

func _process(_delta):
	if Input.is_action_just_pressed("key_scroll_up"):
		setProducto(1)
	if Input.is_action_just_pressed("key_scroll_down"):
		setProducto(-1)

func Actualizar(categoria, costoMin, costoMax, calidad, calificacion, online):
	var raiz = get_parent()
	filtrado = []
	# imagen, categoria, vendedor, nombre, calidad, valor, disponible, descripcion
	for p in range(len(raiz.producto)):
		if raiz.producto[p][2] == raiz.login:
			continue
		if raiz.isBloqueado(raiz.producto[p][2]):
			continue
		if raiz.producto[p][1] != categoria:
			continue
		if raiz.producto[p][5] < costoMin or raiz.producto[p][5] > costoMax:
			continue
		# 0:N 1:S 2:D 3:NS 4:all
		if calidad != 4:
			if calidad == 3:
				if raiz.producto[p][4] == 2:
					continue
			elif calidad != raiz.producto[p][4]:
				continue
		if raiz.getNota(raiz.producto[p][2])[1] < calificacion:
			continue
		if online:
			if not raiz.online[raiz.producto[p][2]]:
				continue
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
		ficha.get_node("Online").visible = raiz.online[raiz.producto[i][2]]
		ficha.visible = true

func _on_BtnVolver_pressed():
	get_parent().Cambio("Filtro")

func _on_BtnUp_pressed():
	setProducto(-1)

func _on_BtnDown_pressed():
	setProducto(1)

func GoProducto(indFiltrado):
	var raiz = get_parent()
	if indFiltrado != -1:
		var i = filtrado[indFiltrado]
		raiz.Cambio("Producto")
		raiz.get_node("Producto").setProducto(i)
		raiz.get_node("Producto").procedencia = "Market"

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
