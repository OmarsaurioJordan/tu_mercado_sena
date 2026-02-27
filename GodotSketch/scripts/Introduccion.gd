extends Panel

func _on_BtnWeb_pressed():
	OS.shell_open("https://zajuna.sena.edu.co/")

func _on_BtnIngreso_pressed():
	get_parent().Cambio("Login")

func _on_BtnPlay_pressed():
	if $Video.is_playing():
		$Video.paused = not $Video.paused
	else:
		$Video.play()
