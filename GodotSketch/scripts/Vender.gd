extends Panel

var calidad = 0
var categoria = 0

func Limpiar():
	$LinNombre.text = ""
	$LinDescripcion.text = ""
	$LinCosto.value = 0
	$LinDisponible.value = 1
	_on_BtnLimpiar_pressed()

func _on_BtnCalidad_pressed():
	var raiz = get_parent()
	calidad += 1
	if calidad >= len(raiz.calidad):
		calidad = 0
	$BtnCalidad.text = raiz.calidad[calidad]

func _on_BtnVolver_pressed():
	get_parent().Cambio("Main")

func _on_BtnVender_pressed():
	if $LinNombre.text == "" or $LinDescripcion.text == "" or\
			$Foto.texture == null:
		$Mensaje.text = "falta información!"
		$Reloj.start()
	else:
		var raiz = get_parent()
		var prod = raiz.setVenta(
			$Foto.texture, categoria, $LinNombre.text, calidad, $LinCosto.value,
			$LinDisponible.value, $LinDescripcion.text
		)
		raiz.Cambio("Mio")
		raiz.get_node("Mio").setProducto(prod)

func _on_BtnFoto_pressed():
	$BtnFoto.visible = false
	$BtnLimpiar.visible = true
	$Foto.texture = load("res://fotos/caja.jpg")

func _on_BtnLimpiar_pressed():
	$BtnFoto.visible = true
	$BtnLimpiar.visible = false
	$Foto.texture = null

func _on_BtnCategoria_pressed():
	var raiz = get_parent()
	categoria += 1
	if categoria >= len(raiz.categorias):
		categoria = 0
	$ImgCat.texture = raiz.categorias[categoria][0]
	$ImgCat/TxtCat.text = raiz.categorias[categoria][1]

func _on_Reloj_timeout():
	$Mensaje.text = ""
