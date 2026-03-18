class Auditoria:
    def __init__(self, id, administrador_id, suceso_id, descripcion, fecha_registro, nickname, email, dias, rol_id):
        self.id = id
        self.administrador_id = administrador_id
        self.suceso_id = suceso_id
        self.descripcion = descripcion
        self.fecha_registro = fecha_registro
        self.nickname = nickname
        self.email = email
        self.dias = dias
        self.rol_id = rol_id

    @staticmethod
    def from_json(data):
        return Auditoria(
            id = int(data.get('id')),
            administrador_id = int(data.get('administrador_id')),
            suceso_id = int(data.get('suceso_id')),
            descripcion = data.get('descripcion'),
            fecha_registro = data.get('fecha_registro'),
            nickname = data.get('nickname'),
            email = data.get('email'),
            dias = int(data.get('dias') or 0),
            rol_id = int(data.get('rol_id'))
        )
