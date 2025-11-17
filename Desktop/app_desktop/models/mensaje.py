class Mensaje:
    def __init__(self, id, es_comprador, remitente_id, chat_id, mensaje, fecha_registro, es_imagen):
        self.id = id
        self.es_comprador = es_comprador
        self.remitente_id = remitente_id
        self.chat_id = chat_id
        self.mensaje = mensaje
        self.fecha_registro = fecha_registro
        self.es_imagen = es_imagen

    @staticmethod
    def from_json(data):
        return Mensaje(
            id=data.get('id'),
            es_comprador=data.get('es_comprador'),
            remitente_id=data.get('remitente_id'),
            chat_id=data.get('chat_id'),
            mensaje=data.get('mensaje'),
            fecha_registro=data.get('fecha_registro'),
            es_imagen=data.get('es_imagen')
        )
