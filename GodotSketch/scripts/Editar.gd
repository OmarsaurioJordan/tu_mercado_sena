extends Panel

var caras = [
	preload("res://avatares/carafea.png"),
	preload("res://avatares/carafeo.png")
]

func setPerfil():
	var raiz = get_parent()
	var ind = raiz.login
	$Foto.texture = raiz.usuario[ind][0]
	$LinNombre.text = raiz.usuario[ind][2]
	$LinDescripcion.text = raiz.usuario[ind][4]
	$LinLink.text = raiz.MedioLink(raiz.usuario[ind][3])

func _on_BtnVolver_pressed():
	get_parent().Cambio("Usuario")

func _on_BtnGuardar_pressed():
	var raiz = get_parent()
	var ind = raiz.login
	raiz.usuario[ind][0] = $Foto.texture
	raiz.usuario[ind][2] = $LinNombre.text
	raiz.usuario[ind][4] = $LinDescripcion.text
	raiz.usuario[ind][3] = $LinLink.text
	raiz.Cambio("Usuario")

func _on_BtnFoto_pressed():
	if $Foto.texture != caras[0] and $Foto.texture != caras[1]:
		if randf() < 0.5:
			$Foto.texture = caras[0]
		else:
			$Foto.texture = caras[1]
	elif $Foto.texture == caras[0]:
		$Foto.texture = caras[1]
	else:
		$Foto.texture = caras[0]
