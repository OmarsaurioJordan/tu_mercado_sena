extends Panel

var miProducto = -1
var michat = -1

func Actualizar(indChat, indProd):
	var raiz = get_parent()
	michat = indChat
	miProducto = indProd
	var u = raiz.mensajes[indChat][0]
	var tipo = "Venta"
	if u == raiz.login:
		u = raiz.mensajes[indChat][1]
		tipo = "Compra"
	$Nombre.text = raiz.usuario[u][2]
	$FotoVende.texture = raiz.usuario[u][0]
	$Item.text = raiz.producto[miProducto][3]
	$Foto.texture = raiz.producto[miProducto][0]
	$Costo.text = "Valor de " + tipo

func _on_BtnVolver_pressed():
	get_parent().Cambio("Chat")

func _on_BtnBorrar_pressed():
	var raiz = get_parent()
	raiz.deleteChat(raiz.login)
	raiz.Cambio("Mensajes")

func _on_BtnOk_pressed():
	var raiz = get_parent()
	raiz.setHistorial(michat, $LinCosto.value, miProducto)
	raiz.deleteChat(raiz.login)
	raiz.Cambio("Historial")
