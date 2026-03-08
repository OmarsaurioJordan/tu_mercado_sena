class Chat:
    def __init__(self, id, comprador_id, vendedor_id, producto_id, estado_id, precio, cantidad, calificacion, comentario, fecha_venta, comprador_name, producto_name, vendedor_name):
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
        self.comprador_name = comprador_name
        self.producto_name = producto_name
        self.vendedor_name = vendedor_name

    @staticmethod
    def from_json(data):
        return Chat(
            id = int(data.get('id')),
            comprador_id = int(data.get('comprador_id')),
            vendedor_id = int(data.get('vendedor_id')),
            producto_id = int(data.get('producto_id')),
            estado_id = int(data.get('estado_id')),
            precio = float(data.get('precio')),
            cantidad = int(data.get('cantidad')),
            calificacion = int(data.get('calificacion')),
            comentario = data.get('comentario'),
            fecha_venta = data.get('fecha_venta'),
            comprador_name = data.get('comprador_name'),
            producto_name = data.get('producto_name'),
            vendedor_name = data.get('vendedor_name')
        )
