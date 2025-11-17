class Denuncia:
    def __init__(self, id, denunciante_id, producto_id, usuario_id, chat_id, motivo, estado_id, fecha_registro):
        self.id = id
        self.denunciante_id = denunciante_id
        self.producto_id = producto_id
        self.usuario_id = usuario_id
        self.chat_id = chat_id
        self.motivo = motivo
        self.estado_id = estado_id
        self.fecha_registro = fecha_registro

    @staticmethod
    def from_json(data):
        return Denuncia(
            id=data.get('id'),
            denunciante_id=data.get('denunciante_id'),
            producto_id=data.get('producto_id'),
            usuario_id=data.get('usuario_id'),
            chat_id=data.get('chat_id'),
            motivo=data.get('motivo'),
            estado_id=data.get('estado_id'),
            fecha_registro=data.get('fecha_registro')
        )
