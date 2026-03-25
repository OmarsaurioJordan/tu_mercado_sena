extends Panel

var vendedor = -1
var productin = 0
var filtrado = []

func Actualizar(vnddr):
	var raiz = get_parent()
	if vnddr == vendedor:
		return null
	vendedor = vnddr
	filtrado = []
	for p in range(len(raiz.producto)):
		if raiz.producto[p][2] == vendedor:
			filtrado.append(p)
	filtrado.shuffle()
	setProducto()
	$Usuario/Img.texture = raiz.usuario[vendedor][0]
	$Usuario/Nombre.text = raiz.usuario[vendedor][2]
	$Usuario/Estrellas.text = raiz.getCalifica(vendedor)
	$Usuario/Online.visible = raiz.online[vendedor]

func setProducto(mover=0):
	if len(filtrado) == 0:
		productin = 0
		for i in range(1, 9):
			setFicha(get_node("Producto" + str(i)), -1)
	else:
		productin = clamp(productin + mover * 2,
			0, max(0, len(filtrado) - 7))
		for i in range(8):
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
	var raiz = get_parent()
	raiz.Cambio("Perfil")
	raiz.get_node("Perfil").setPerfil(vendedor)

func _on_BtnUp_pressed():
	setProducto(-1)

func _on_BtnDown_pressed():
	setProducto(1)

func GoProducto(indFiltrado):
	var raiz = get_parent()
	if indFiltrado != -1:
		var i = filtrado[productin + indFiltrado]
		raiz.Cambio("Producto")
		raiz.get_node("Producto").setProducto(i)

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

func _on_BtnUsuario_pressed():
	_on_BtnVolver_pressed()
