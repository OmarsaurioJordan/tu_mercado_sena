class Usuario:
    def __init__(self, id, correo, rol_id, nombre, avatar, descripcion, link, estado_id, fecha_registro, fecha_actualiza, fecha_reciente):
        self.id = id
        self.correo = correo
        self.rol_id = rol_id
        self.nombre = nombre
        self.avatar = avatar
        self.descripcion = descripcion
        self.link = link
        self.estado_id = estado_id
        self.fecha_registro = fecha_registro
        self.fecha_actualiza = fecha_actualiza
        self.fecha_reciente = fecha_reciente

    @staticmethod
    def from_json(data):
        return Usuario(
            id=int(data.get('id')),
            correo=data.get('correo'),
            rol_id=int(data.get('rol_id')),
            nombre=data.get('nombre'),
            avatar=int(data.get('avatar')),
            descripcion=data.get('descripcion'),
            link=data.get('link'),
            estado_id=int(data.get('estado_id')),
            fecha_registro=data.get('fecha_registro'),
            fecha_actualiza=data.get('fecha_actualiza'),
            fecha_reciente=data.get('fecha_reciente')
        )
