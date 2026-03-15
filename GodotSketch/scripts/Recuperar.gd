extends Panel

func _ready():
	$LinCorreo.text = ""

func _on_BtnLogin_pressed():
	get_parent().Cambio("Login")

func _on_BtnRecuperar_pressed():
	var mail = $LinCorreo.text
	$LinCorreo.text = ""
	if mail.count("@soysena") != 1:
		$Mensaje.text = "el correo debe ser del tipo: nombre@soysena, truco: ponga javier@soysena"
		$Reloj.start()
	else:
		$Mensaje.text = "se ha enviado un correo de recuperación con la nueva contraseña, truco: ponga 123"
		$Reloj.start()

func _on_Reloj_timeout():
	$Mensaje.text = ""
