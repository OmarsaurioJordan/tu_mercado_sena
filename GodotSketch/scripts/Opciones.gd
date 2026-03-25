extends Panel

func _on_BtnPassword_pressed():
	get_parent().Cambio("Repassword")

func _on_BtnCerrar_pressed():
	var raiz = get_parent()
	raiz.login = -1
	raiz.guardar()
	get_parent().Cambio("Login")

func _on_BtnVolver_pressed():
	get_parent().Cambio("Main")

func _on_BtnBloqueados_pressed():
	get_parent().Cambio("Bloqueados")

func _on_BtnAyuda_pressed():
	get_parent().Cambio("Ayuda")
