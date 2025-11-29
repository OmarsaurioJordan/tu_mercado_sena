class Pqrs:
    def __init__(self, id, remitente_id, mensaje, motivo, estado_id, fecha_registro):
        self.id = id
        self.remitente_id = remitente_id
        self.mensaje = mensaje
        self.motivo = motivo
        self.estado_id = estado_id
        self.fecha_registro = fecha_registro

    @staticmethod
    def from_json(data):
        return Pqrs(
            id=data.get('id'),
            remitente_id=data.get('remitente_id'),
            mensaje=data.get('mensaje'),
            motivo=data.get('motivo'),
            estado_id=data.get('estado_id'),
            fecha_registro=data.get('fecha_registro')
        )
