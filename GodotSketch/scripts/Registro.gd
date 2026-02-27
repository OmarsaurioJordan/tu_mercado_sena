extends Panel

var mails = []

func _ready():
	$LinCorreo.text = ""
	$LinNombre.text = ""

func _on_BtnLogin_pressed():
	get_parent().Cambio("Login")

func _on_BtnRegistro_pressed():
	var raiz = get_parent()
	var mail = $LinCorreo.text
	$LinCorreo.text = ""
	$LinNombre.text = ""
	if mail.count("@soysena") != 1:
		$Mensaje.text = "el correo debe ser del tipo: nombre@soysena, truco: ponga javier@soysena"
		$Reloj.start()
	else:
		for u in range(len(raiz.usuario)):
			if raiz.usuario[u][1] + "@soysena" == mail:
				$Mensaje.text = "el correo ya está registrado"
				$Reloj.start()
				return null
		if mail in mails:
			$Mensaje.text = "ya se ha enviado el correo, por favor revíselo, nota: es mentira, esto es un demo"
		else:
			$Mensaje.text = "se ha enviado un correo con la contraseña autoasignada, nota: es mentira, esto es un demo"
			mails.append(mail)
		$Reloj.start()

func _on_Reloj_timeout():
	$Mensaje.text = ""

func _on_BtnDatos_pressed():
	get_parent().Cambio("Legal")
