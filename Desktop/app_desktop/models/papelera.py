class Papelera:
    def __init__(self, id, usuario_id, mensaje, es_imagen, fecha_registro):
        self.id = id
        self.usuario_id = usuario_id
        self.mensaje = mensaje
        self.es_imagen = es_imagen
        self.fecha_registro = fecha_registro

    @staticmethod
    def from_json(data):
        return Papelera(
            id=data.get('id'),
            usuario_id=data.get('usuario_id'),
            mensaje=data.get('mensaje'),
            es_imagen=data.get('es_imagen'),
            fecha_registro=data.get('fecha_registro')
        )
