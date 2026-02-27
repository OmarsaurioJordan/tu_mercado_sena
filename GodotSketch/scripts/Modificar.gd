extends Panel

var calidad = 0
var categoria = 0
var producto = -1

func setProducto(ind):
	var raiz = get_parent()
	producto = ind
	categoria = raiz.producto[ind][1]
	$ImgCat.texture = raiz.categorias[categoria][0]
	$ImgCat/TxtCat.text = raiz.categorias[categoria][1]
	$Foto.texture = raiz.producto[ind][0]
	$LinNombre.text = raiz.producto[ind][3]
	calidad = raiz.producto[ind][4]
	$BtnCalidad.text = raiz.calidad[calidad]
	$LinDescripcion.text = raiz.producto[ind][7]
	$LinCosto.value = raiz.producto[ind][5]
	$LinDisponible.value = raiz.producto[ind][6]
	$BtnFoto.visible = false
	$BtnLimpiar.visible = true

func _on_BtnCalidad_pressed():
	var raiz = get_parent()
	calidad += 1
	if calidad >= len(raiz.calidad):
		calidad = 0
	$BtnCalidad.text = raiz.calidad[calidad]

func _on_BtnVolver_pressed():
	get_parent().Cambio("Mio")

func _on_BtnGuardar_pressed():
	if $LinNombre.text == "" or $LinDescripcion.text == "" or\
			$Foto.texture == null:
		$Mensaje.text = "falta información!"
		$Reloj.start()
	else:
		var raiz = get_parent()
		raiz.upVenta(producto,
			$Foto.texture, categoria, $LinNombre.text, calidad, $LinCosto.value,
			$LinDisponible.value, $LinDescripcion.text
		)
		raiz.Cambio("Mio")
		raiz.get_node("Mio").setProducto(producto)

func _on_BtnFoto_pressed():
	$BtnFoto.visible = false
	$BtnLimpiar.visible = true
	$Foto.texture = load("res://fotos/cajin.jpg")

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
