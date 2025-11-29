class Chat:
    def __init__(self, id, comprador_id, vendedor_id, producto_id, estado_id, precio, cantidad, calificacion, comentario, fecha_venta):
        self.id = id
        self.comprador_id = comprador_id
        self.vendedor_id = vendedor_id
        self.producto_id = producto_id
        self.estado_id = estado_id
        self.precio = precio
        self.cantidad = cantidad
        self.calificacion = calificacion
        self.comentario = comentario
        self.fecha_venta = fecha_venta

    @staticmethod
    def from_json(data):
        return Chat(
            id=data.get('id'),
            comprador_id=data.get('comprador_id'),
            vendedor_id=data.get('vendedor_id'),
            producto_id=data.get('producto_id'),
            estado_id=data.get('estado_id'),
            precio=data.get('precio'),
            cantidad=data.get('cantidad'),
            calificacion=data.get('calificacion'),
            comentario=data.get('comentario'),
            fecha_venta=data.get('fecha_venta')
        )
