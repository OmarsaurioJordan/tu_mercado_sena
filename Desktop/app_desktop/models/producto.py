class Producto:
    def __init__(self, id, nickname, con_imagen, categoria, subcategoria, integridad, vendedor_id, estado_id, descripcion, precio, disponibles, fecha_registro, fecha_actualiza):
        self.id = id
        self.nickname = nickname
        self.con_imagen = con_imagen
        self.categoria = categoria
        self.subcategoria = subcategoria
        self.integridad = integridad
        self.vendedor_id = vendedor_id
        self.estado_id = estado_id
        self.descripcion = descripcion
        self.precio = precio
        self.disponibles = disponibles
        self.fecha_registro = fecha_registro
        self.fecha_actualiza = fecha_actualiza

    @staticmethod
    def from_json(data):
        return Producto(
            id=data.get('id'),
            nickname=data.get('nickname'),
            con_imagen=data.get('con_imagen'),
            categoria=data.get('categoria'),
            subcategoria=data.get('subcategoria'),
            integridad=data.get('integridad'),
            vendedor_id=data.get('vendedor_id'),
            estado_id=data.get('estado_id'),
            descripcion=data.get('descripcion'),
            precio=data.get('precio'),
            disponibles=data.get('disponibles'),
            fecha_registro=data.get('fecha_registro'),
            fecha_actualiza=data.get('fecha_actualiza')
        )
