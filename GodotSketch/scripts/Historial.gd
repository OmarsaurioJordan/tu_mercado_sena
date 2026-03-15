extends Panel

var procedencia = "Main"

func _on_BtnVolver_pressed():
	get_parent().Cambio(procedencia)
