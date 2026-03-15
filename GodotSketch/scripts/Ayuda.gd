extends Panel

func _ready():
	$Mensaje.visible = false

func Limpiar():
	$LinDescripcion.text = ""
	for b in range(1, 5):
		get_node("Btn" + str(b)).disabled = false

func _on_BtnMail_pressed():
	if $LinDescripcion.text != "":
		Limpiar()
		$Mensaje.visible = true
		$Reloj.start()

func _on_BtnVolver_pressed():
	get_parent().Cambio("Opciones")

func BtnPoner(bb):
	for b in range(1, 5):
		get_node("Btn" + str(b)).disabled = false
	get_node("Btn" + str(bb)).disabled = true

func _on_Btn1_pressed():
	BtnPoner(1)

func _on_Btn2_pressed():
	BtnPoner(2)

func _on_Btn3_pressed():
	BtnPoner(3)

func _on_Btn4_pressed():
	BtnPoner(4)

func _on_Reloj_timeout():
	$Mensaje.visible = false
