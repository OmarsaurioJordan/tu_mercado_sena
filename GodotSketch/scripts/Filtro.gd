extends Panel

var categoria = 0
var calidad = 0
var calificacion = 0

func _ready():
	categoria = randi() % len(get_parent().categorias)
	calidad = len(get_parent().calidad) - 1
	_on_BtnCalidad_pressed()
	setCategoria()
	setCalifica()

func _on_BtnCalidad_pressed():
	var raiz = get_parent()
	calidad += 1
	if calidad >= len(raiz.calidad) + 2:
		calidad = 0
	if calidad == len(raiz.calidad):
		$BtnCalidad.text = "(N) + (S)"
	elif calidad == len(raiz.calidad) + 1:
		$BtnCalidad.text = "TODAS"
	else:
		match raiz.calidad[calidad]:
			"(N)":
				$BtnCalidad.text = "(N) Nuevo"
			"(S)":
				$BtnCalidad.text = "(S) de Seguna"
			"(D)":
				$BtnCalidad.text = "(D) Dañado"

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

func setCalifica(suma=false):
	if suma:
		calificacion += 1
		if calificacion > 5:
			calificacion = 0
	var txt = "....."
	if calificacion == 0:
		txt = "ooooo"
	elif calificacion == 1:
		txt = "xoooo"
	elif calificacion == 2:
		txt = "xxooo"
	elif calificacion == 3:
		txt = "xxxoo"
	elif calificacion == 4:
		txt = "xxxxo"
	else: # 5
		txt = "xxxxx"
	$BtnCalificar.text = "Calificación\nMínima\n" + txt

func _on_BtnVolver_pressed():
	get_parent().Cambio("Main")

func _on_BtnCatLm_pressed():
	setCategoria(-1)

func _on_BtnCatRm_pressed():
	setCategoria(1)

func _on_BtnCatL_pressed():
	setCategoria(-1)

func _on_BtnCatR_pressed():
	setCategoria(1)

func _on_BtnCalificar_pressed():
	setCalifica(true)

func _on_BtnBuscar_pressed():
	get_parent().Cambio("Market")
	get_parent().get_node("Market").Actualizar(
		categoria, $LinCostoMin.value, $LinCostoMax.value,
		calidad, calificacion, $BtnOnline.pressed
	)
