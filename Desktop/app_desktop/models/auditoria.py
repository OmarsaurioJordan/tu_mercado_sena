class Auditoria:
    def __init__(self, id, administrador_id, suceso, descripcion, fecha_registro):
        self.id = id
        self.administrador_id = administrador_id
        self.suceso = suceso
        self.descripcion = descripcion
        self.fecha_registro = fecha_registro

    @staticmethod
    def from_json(data):
        return Auditoria(
            id=data.get('id'),
            administrador_id=data.get('administrador_id'),
            suceso=data.get('suceso'),
            descripcion=data.get('descripcion'),
            fecha_registro=data.get('fecha_registro')
        )
