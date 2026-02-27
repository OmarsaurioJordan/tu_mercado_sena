extends Panel

var procedencia = "Main"
var vendedor = -1

func setPerfil(ind):
	var raiz = get_parent()
	vendedor = ind
	$Foto.texture = raiz.usuario[ind][0]
	$Nombre.text = raiz.usuario[ind][2]
	$Descripcion.text = raiz.usuario[ind][4]
	$BtnLink.text = raiz.MedioLink(raiz.usuario[ind][3])
	$Online.visible = raiz.online[ind]
	setCalifica()
	setFavorito()

func _on_BtnVolver_pressed():
	get_parent().Cambio(procedencia)

func setCalifica(suma=false):
	var raiz = get_parent()
	var txt = "....."
	var valor = 0
	for i in range(len(raiz.califica)):
		if raiz.califica[i][0] == vendedor and\
				raiz.califica[i][1] == raiz.login:
			if suma:
				raiz.califica[i][2] += 1
				if raiz.califica[i][2] > 5:
					raiz.califica.remove(i)
					suma = false
					break
			valor = raiz.califica[i][2]
			if valor == 0:
				txt = "ooooo"
			elif valor == 1:
				txt = "xoooo"
			elif valor == 2:
				txt = "xxooo"
			elif valor == 3:
				txt = "xxxoo"
			elif valor == 4:
				txt = "xxxxo"
			else: # 5
				txt = "xxxxx"
			break
	if txt == "....." and suma:
		# [calificado, calificador, valor 0-5]
		raiz.califica.append([vendedor, raiz.login, 1])
		txt = "xoooo"
	$BtnCalificar.text = "califica\n" + txt
	$Estrellas.text = raiz.getCalifica(vendedor)

func setFavorito():
	var raiz = get_parent()
	$BtnFavorito.text = "Fav\nO"
	for i in range(len(raiz.favorito)):
		# [favorito, votante]
		if raiz.favorito[i][1] == raiz.login and\
				raiz.favorito[i][0] == vendedor:
			$BtnFavorito.text = "Fav\nX"
			break

func _on_BtnCalificar_pressed():
	setCalifica(true)

func _on_BtnLink_pressed():
	if $BtnLink.text != "":
		OS.shell_open($BtnLink.text)

func _on_BtnFavorito_pressed():
	var raiz = get_parent()
	for i in range(len(raiz.favorito)):
		# [favorito, votante]
		if raiz.favorito[i][1] == raiz.login and\
				raiz.favorito[i][0] == vendedor:
			$BtnFavorito.text = "Fav\nO"
			raiz.favorito.remove(i)
			return null
	raiz.favorito.append([vendedor, raiz.login])
	$BtnFavorito.text = "Fav\nX"

func _on_BtnBloquear_pressed():
	get_parent().Bloquear(vendedor)
	get_parent().Cambio("Main")

func _on_BtnChat_pressed():
	get_parent().Cambio("Chat")
	get_parent().get_node("Chat").LoadChat(vendedor, -1)

func _on_BtnCatalogo_pressed():
	get_parent().Cambio("Catalogo")
	get_parent().get_node("Catalogo").Actualizar(vendedor)
