extends Panel

var categoria = 0
var productin = 0
var produmas = [-1, -1, -1] # R, L, N
var filtrado = []
var vendedor = 0
var vendemas = [-1, -1, -1] # R, L, N
var vendedores = [] # [ind, items, valmin]

func _ready():
	categoria = randi() % len(get_parent().categorias)
	setCategoria()

func setCategoria(mover=0):
	# mover -1, 0, 1
	var raiz = get_parent()
	categoria += mover
	if categoria >= len(raiz.categorias):
		categoria = 0
	elif categoria < 0:
		categoria = len(raiz.categorias) - 1
	$ImgCat.texture = raiz.categorias[categoria][0]
	$TxtCat.text = raiz.categorias[categoria][1]
	var i = categoria + 1
	if i >= len(raiz.categorias):
		i = 0
	$ImgCatR.texture = raiz.categorias[i][0]
	$TxtCatR.text = raiz.categorias[i][1]
	i = categoria - 1
	if i < 0:
		i = len(raiz.categorias) - 1
	$ImgCatL.texture = raiz.categorias[i][0]
	$TxtCatL.text = raiz.categorias[i][1]
	Filtrar()
	buscarVendedores()

func Filtrar():
	var raiz = get_parent()
	filtrado = []
	for p in range(len(raiz.producto)):
		if raiz.producto[p][2] == raiz.login:
			continue
		if raiz.isBloqueado(raiz.producto[p][2]):
			continue
		if raiz.producto[p][1] == categoria:
			filtrado.append(p)
	filtrado.shuffle()
	productin = 0
	setProducto()

func buscarVendedores():
	var raiz = get_parent()
	var auxvend = []
	vendedores = []
	var pp = -1
	for u in range(len(raiz.usuario)):
		if u == raiz.login:
			continue
		for p in range(len(raiz.producto)):
			if raiz.producto[p][1] != categoria:
				continue
			if raiz.producto[p][2] == u:
				if raiz.isBloqueado(raiz.producto[p][2]):
					continue
				if u in auxvend:
					pp = auxvend.find(u)
					vendedores[pp][1] += 1
					vendedores[pp][2] =\
						min(vendedores[pp][2], raiz.producto[p][5])
				else:
					auxvend.append(u)
					vendedores.append([u, 1, raiz.producto[p][5]])
	vendedores.shuffle()
	vendedor = 0
	setVendedor()

func setVendedor(mover=0):
	# mover -1, 0, 1
	if len(vendedores) == 0:
		vendemas = [-1, -1, -1]
		setPerfil($User, -1)
		setPerfil($UserL, -1)
		setPerfil($UserR, -1)
	else:
		vendedor += mover
		if vendedor >= len(vendedores):
			vendedor = 0
		elif vendedor < 0:
			vendedor = len(vendedores) - 1
		var hist = []
		setPerfil($User, vendedor)
		vendemas[2] = vendedor
		hist.append(vendedor)
		var i = vendedor + 1
		if i >= len(vendedores):
			i = 0
		if not i in hist:
			setPerfil($UserR, i)
			vendemas[0] = i
			hist.append(i)
		else:
			setPerfil($UserR, -1)
			vendemas[0] = -1
		i = vendedor - 1
		if i < 0:
			i = len(vendedores) - 1
		if not i in hist:
			setPerfil($UserL, i)
			vendemas[1] = i
		else:
			setPerfil($UserL, -1)
			vendemas[1] = -1

func setProducto(mover=0):
	# mover -1, 0, 1
	if len(filtrado) == 0:
		produmas = [-1, -1, -1]
		setFicha($Producto, -1)
		setFicha($ProductoL, -1)
		setFicha($ProductoR, -1)
	else:
		productin += mover
		if productin >= len(filtrado):
			productin = 0
		elif productin < 0:
			productin = len(filtrado) - 1
		var hist = []
		setFicha($Producto, productin)
		produmas[2] = productin
		hist.append(productin)
		var i = productin + 1
		if i >= len(filtrado):
			i = 0
		if not i in hist:
			setFicha($ProductoR, i)
			produmas[0] = i
			hist.append(i)
		else:
			setFicha($ProductoR, -1)
			produmas[0] = -1
		i = productin - 1
		if i < 0:
			i = len(filtrado) - 1
		if not i in hist:
			setFicha($ProductoL, i)
			produmas[1] = i
		else:
			setFicha($ProductoL, -1)
			produmas[1] = -1

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

func _on_BtnCatL_pressed():
	setCategoria(-1)

func _on_BtnCatR_pressed():
	setCategoria(1)

func _on_BtnRandL_pressed():
	setProducto(-1)

func _on_BtnRandR_pressed():
	setProducto(1)

func goProducto(indFiltrado):
	var raiz = get_parent()
	if indFiltrado != -1:
		var i = filtrado[indFiltrado]
		raiz.Cambio("Producto")
		raiz.get_node("Producto").setProducto(i)
		raiz.get_node("Producto").procedencia = "Main"

func _on_BtnProducto_pressed():
	goProducto(produmas[2])

func _on_BtnProductoL_pressed():
	goProducto(produmas[1])

func _on_BtnProductoR_pressed():
	goProducto(produmas[0])

func _on_BtnCatLm_pressed():
	setCategoria(-1)

func _on_BtnCatRm_pressed():
	setCategoria(1)

func _on_BtnConfig_pressed():
	get_parent().Cambio("Opciones")

func _on_BtnPerfil_pressed():
	get_parent().Cambio("Usuario")

func goPerfil(indVendedor):
	var raiz = get_parent()
	if indVendedor != -1:
		var i = vendedores[indVendedor][0]
		raiz.Cambio("Perfil")
		raiz.get_node("Perfil").setPerfil(i)
		raiz.get_node("Perfil").procedencia = "Main"

func _on_BtnUsr_pressed():
	goPerfil(vendemas[2])

func _on_BtnUsrL_pressed():
	goPerfil(vendemas[1])

func _on_BtnUsrR_pressed():
	goPerfil(vendemas[0])

func _on_BtnPerfilL_pressed():
	setVendedor(-1)

func _on_BtnPerfilR_pressed():
	setVendedor(1)

func _on_BtnVenta_pressed():
	get_parent().Cambio("Vender")

func _on_BtnCatalogo_pressed():
	get_parent().Cambio("Ventas")
	get_parent().get_node("Ventas").Actualizar()

func _on_BtnFiltro_pressed():
	get_parent().Cambio("Filtro")

func _on_BtnChats_pressed():
	get_parent().Cambio("Mensajes")
	get_parent().get_node("Mensajes").procedencia = "Main"

func _on_BtnFavoritos_pressed():
	get_parent().Cambio("Favoritos")

func _on_BtnHistorial_pressed():
	get_parent().Cambio("Historial")
	get_parent().get_node("Historial").procedencia = "Main"
