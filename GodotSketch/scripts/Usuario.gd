extends Panel

func setPerfil():
	var raiz = get_parent()
	var ind = raiz.login
	$Foto.texture = raiz.usuario[ind][0]
	$Nombre.text = raiz.usuario[ind][2]
	$Descripcion.text = raiz.usuario[ind][4]
	$BtnLink.text = raiz.MedioLink(raiz.usuario[ind][3])
	$Estrellas.text = raiz.getCalifica(ind)
	$Favoritos.text = raiz.getFavoritos(ind)

func _on_BtnVolver_pressed():
	get_parent().Cambio("Main")

func _on_BtnLink_pressed():
	if $BtnLink.text != "":
		OS.shell_open($BtnLink.text)

func _on_BtnEditar_pressed():
	get_parent().Cambio("Editar")

func _on_BtnChats_pressed():
	get_parent().Cambio("Mensajes")
	get_parent().get_node("Mensajes").procedencia = "Usuario"

func _on_BtnCatalogo_pressed():
	get_parent().Cambio("Ventas")
	get_parent().get_node("Ventas").Actualizar()

func _on_BtnHistorial_pressed():
	get_parent().Cambio("Historial")
	get_parent().get_node("Historial").procedencia = "Usuario"
