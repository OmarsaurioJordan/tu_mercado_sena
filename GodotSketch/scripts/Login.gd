extends Panel

func _ready():
	$LinCorreo.text = ""
	$LinPassword.text = ""

func _on_BtnRegistro_pressed():
	get_parent().Cambio("Registro")

func _on_BtnLogin_pressed():
	var raiz = get_parent()
	var mail = $LinCorreo.text
	var pasw = $LinPassword.text
	$LinCorreo.text = ""
	$LinPassword.text = ""
	if mail.count("@soysena") != 1:
		$Mensaje.text = "el correo debe ser del tipo: nombre@soysena, truco: ponga javier@soysena"
		$Reloj.start()
	else:
		for u in range(len(raiz.usuario)):
			if raiz.usuario[u][1] + "@soysena" == mail:
				if pasw == "123":
					raiz.login = u
					raiz.guardar()
					get_parent().Cambio("Main")
					get_parent().get_node("Main").setCategoria()
				else:
					$Mensaje.text = "la contraseña es incorrecta, truco: ponga 123"
					$Reloj.start()
				return null
		$Mensaje.text = "el correo no está registrado"
		$Reloj.start()

func _on_BtnRecover_pressed():
	get_parent().Cambio("Recuperar")

func _on_Reloj_timeout():
	$Mensaje.text = ""

func _on_BtnInfo_pressed():
	get_parent().Cambio("Introduccion")
