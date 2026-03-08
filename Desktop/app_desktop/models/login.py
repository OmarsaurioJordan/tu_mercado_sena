class Login:
    def __init__(self, id, usuario_id, ip_direccion, informacion, fecha_registro, nickname, email, dias):
        self.id = id
        self.usuario_id = usuario_id
        self.ip_direccion = ip_direccion
        self.informacion = informacion
        self.fecha_registro = fecha_registro
        self.nickname = nickname
        self.email = email
        self.dias = dias

    @staticmethod
    def from_json(data):
        return Login(
            id = int(data.get('id')),
            usuario_id = int(data.get('usuario_id')),
            ip_direccion = data.get('ip_direccion'),
            informacion = data.get('informacion'),
            fecha_registro = data.get('fecha_registro'),
            nickname = data.get('nickname'),
            email = data.get('email'),
            dias = int(data.get('dias'))
        )
