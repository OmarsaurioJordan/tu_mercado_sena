extends Panel

var procedencia = "Main"
var producto = -1
var vendedor = -1

func setProducto(ind):
	var raiz = get_parent()
	producto = ind
	$Foto.texture = raiz.producto[ind][0]
	$Nombre.text = raiz.producto[ind][3]
	$Calidad.text = raiz.calidad[raiz.producto[ind][4]]
	$Descripcion.text = raiz.producto[ind][7]
	$Costo.text = "valor: $" + str(raiz.producto[ind][5])
	$Disponible.text = "disponibles: " + str(raiz.producto[ind][6])
	vendedor = raiz.producto[ind][2]
	$FotoVende.texture = raiz.usuario[vendedor][0]
	$Online.visible = raiz.online[vendedor]
	$NombreVend.text = raiz.usuario[vendedor][2]
	$Estrellas.text = raiz.getCalifica(vendedor)

func _on_BtnVolver_pressed():
	get_parent().Cambio(procedencia)

func _on_BtnFoto_pressed():
	get_parent().Cambio("Perfil")
	get_parent().get_node("Perfil").setPerfil(vendedor)
	get_parent().get_node("Perfil").procedencia = "Producto"

func _on_BtnChat_pressed():
	get_parent().Cambio("Chat")
	get_parent().get_node("Chat").LoadChat(vendedor, producto)

func _on_BtnCatalogo_pressed():
	get_parent().Cambio("Catalogo")
	get_parent().get_node("Catalogo").Actualizar(vendedor)
