class Pqrs:
    def __init__(self, id, usuario_id, mensaje, motivo_id, estado_id, fecha_registro, usuario_name, email, dias):
        self.id = id
        self.usuario_id = usuario_id
        self.mensaje = mensaje
        self.motivo_id = motivo_id
        self.estado_id = estado_id
        self.fecha_registro = fecha_registro
        self.usuario_name = usuario_name
        self.email = email
        self.dias = dias

    @staticmethod
    def from_json(data):
        return Pqrs(
            id = data.get('id'),
            usuario_id = int(data.get('usuario_id')),
            mensaje = data.get('mensaje'),
            motivo_id = int(data.get('motivo_id')),
            estado_id = int(data.get('estado_id')),
            fecha_registro = data.get('fecha_registro'),
            usuario_name = data.get('nickname'),
            email = data.get('email'),
            dias = int(data.get('dias', 0))
        )
