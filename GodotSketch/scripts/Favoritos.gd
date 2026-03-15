extends Panel

var huella = ""
var vendedor = 0
var vendedores = [] # [ind, items, valmin]

func buscarVendedores():
	var raiz = get_parent()
	if huella == raiz.getHuella():
		return null
	huella = raiz.getHuella()
	vendedores = []
	# [favorito, votante]
	for u in range(len(raiz.favorito)):
		if raiz.favorito[u][1] != raiz.login:
			continue
		if raiz.isBloqueado(raiz.favorito[u][0]):
			continue
		vendedores.append([raiz.favorito[u][0], 0, 1000000])
		# imagen, categoria, vendedor, nombre, calidad, valor, disponible, descripcion
		for p in range(len(raiz.producto)):
			if raiz.producto[p][2] == raiz.favorito[u][0]:
				vendedores[-1][1] += 1
				vendedores[-1][2] = min(vendedores[-1][2], raiz.producto[p][5])
	vendedores.shuffle()
	vendedor = 0
	setVendedor()

func setVendedor(mover=0):
	# mover -1, 0, 1
	if len(vendedores) == 0:
		vendedor = 0
		for i in range(1, 10):
			setPerfil(get_node("User" + str(i)), -1)
	else:
		vendedor = clamp(vendedor + mover * 3,
			0, max(0, len(vendedores) - 8))
		for i in range(9):
			if vendedor + i < len(vendedores):
				setPerfil(get_node("User" + str(i + 1)), vendedor + i)
			else:
				setPerfil(get_node("User" + str(i + 1)), -1)

func setPerfil(ficha, indVendedor):
	var raiz = get_parent()
	if indVendedor == -1:
		ficha.visible = false
	else:
		var i = vendedores[indVendedor][0]
		ficha.get_node("Img").texture = raiz.usuario[i][0]
		var nn = raiz.usuario[i][2]
		ficha.get_node("Nombre").text = nn.split(" ")[0]
		ficha.get_node("Info").text = str(vendedores[indVendedor][1]) +\
			" ítems\n$" + str(vendedores[indVendedor][2])
		ficha.get_node("Online").visible = raiz.online[i]
		ficha.visible = true

func _on_BtnUp_pressed():
	setVendedor(-1)

func _on_BtnDown_pressed():
	setVendedor(1)

func _on_BtnVolver_pressed():
	get_parent().Cambio("Main")

func GoProducto(indFiltrado):
	var raiz = get_parent()
	if indFiltrado != -1:
		var i = vendedores[vendedor + indFiltrado][0]
		raiz.Cambio("Perfil")
		raiz.get_node("Perfil").setPerfil(i)
		raiz.get_node("Perfil").procedencia = "Favoritos"

func _on_BtnUsr9_pressed():
	GoProducto(8)

func _on_BtnUsr8_pressed():
	GoProducto(7)

func _on_BtnUsr7_pressed():
	GoProducto(6)

func _on_BtnUsr6_pressed():
	GoProducto(5)

func _on_BtnUsr5_pressed():
	GoProducto(4)

func _on_BtnUsr4_pressed():
	GoProducto(3)

func _on_BtnUsr3_pressed():
	GoProducto(2)

func _on_BtnUsr2_pressed():
	GoProducto(1)

func _on_BtnUsr1_pressed():
	GoProducto(0)
