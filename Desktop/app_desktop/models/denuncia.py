class Denuncia:
    def __init__(self, id, denunciante_id, producto_id, usuario_id, chat_id, motivo_id, estado_id, fecha_registro, denunciante_name, producto_name, usuario_name, email, dias):
        self.id = id
        self.denunciante_id = denunciante_id
        self.producto_id = producto_id
        self.usuario_id = usuario_id
        self.chat_id = chat_id
        self.motivo_id = motivo_id
        self.estado_id = estado_id
        self.fecha_registro = fecha_registro
        self.denunciante_name = denunciante_name,
        self.usuario_name = usuario_name
        self.producto_name = producto_name
        self.email = email
        self.dias = dias
        self.tipo = ""

    @staticmethod
    def from_json(data):
        res = Denuncia(
            id = int(data.get('id')),
            denunciante_id = int(data.get('denunciante_id')),
            usuario_id = int(data.get("usuario_id"))
            producto_id = int(data['producto_id']) if data.get('producto_id') is not None else 0,
            chat_id = int(data['chat_id']) if data.get('chat_id') is not None else 0,
            motivo_id = int(data.get('motivo_id')),
            estado_id = int(data.get('estado_id')),
            fecha_registro = data.get('fecha_registro'),
            denunciante_name = data.get('denunciante'),
            usuario_name = data.get('usuario'),
            producto_name = data.get('producto') or "",
            email = data.get('email'),
            dias = int(data.get('dias', 0))
        )
        res.tipo = "Chat" if res.chat_id != 0 else ("Producto" if res.producto_id != 0 else "Usuario")
        return res
