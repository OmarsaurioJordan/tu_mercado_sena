extends Panel

func _ready():
	$LinPassworsAnt.text = ""
	$LinPassworsNew1.text = ""
	$LinPassworsNew2.text = ""

func _on_BtnVolver_pressed():
	get_parent().Cambio("Opciones")

func _on_BtnLogin_pressed():
	var ant = $LinPassworsAnt.text
	var nueva = $LinPassworsNew1.text
	var nuevi = $LinPassworsNew2.text
	$LinPassworsAnt.text = ""
	$LinPassworsNew1.text = ""
	$LinPassworsNew2.text = ""
	if ant != "123":
		$Mensaje.text = "la contraseña es incorrecta, inténtalo de nuevo"
	elif nueva != nuevi or nueva == "":
		$Mensaje.text = "la nueva contraseña es inválida o no coincide con su confirmación"
	else:
		$Mensaje.text = "muy bien, has logrado cambiar la contraseña, nota: sigue siendo 123"
	$Reloj.start()

func _on_Reloj_timeout():
	$Mensaje.text = ""
