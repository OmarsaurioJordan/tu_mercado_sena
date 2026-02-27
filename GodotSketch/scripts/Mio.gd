extends Panel

var producto = -1

func setProducto(ind):
	var raiz = get_parent()
	producto = ind
	$Foto.texture = raiz.producto[ind][0]
	$Nombre.text = raiz.producto[ind][3]
	$Calidad.text = raiz.calidad[raiz.producto[ind][4]]
	$Descripcion.text = raiz.producto[ind][7]
	$Costo.text = "valor: $" + str(raiz.producto[ind][5])
	$Disponible.text = "disponibles: " + str(raiz.producto[ind][6])
	$Vistas.text = "vistas: " + str(raiz.vistas[ind])

func _on_BtnVolver_pressed():
	get_parent().Cambio("Ventas")

func _on_BtnEliminar_pressed():
	var raiz = get_parent()
	raiz.producto.remove(producto)
	raiz.vistas.remove(producto)
	raiz.get_node("Ventas").Actualizar()
	raiz.Cambio("Ventas")

func _on_BtnEditar_pressed():
	get_parent().Cambio("Modificar")
	get_parent().get_node("Modificar").setProducto(producto)
