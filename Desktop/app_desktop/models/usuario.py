from PySide6.QtCore import QThreadPool, Signal, QObject
from PySide6.QtGui import QPixmap
from services.load_image import ImageWorker
from core.app_config import IMAGE_USER_LINK

class UsuarioSignal(QObject):
    ok_image = Signal(QPixmap)

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
        self.img_signal = UsuarioSignal()
        self.imagen = QPixmap("assets/sprites/avatar.png")

    @staticmethod
    def from_json(data):
        usr = Usuario(
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
        usr.load_image()
        return usr

    def load_image(self):
        if self.avatar == 0:
            return
        url = IMAGE_USER_LINK + str(self.id) + ".jpg"
        worker = ImageWorker(url, True)
        worker.signals.finished.connect(self.set_image)
        threadpool = QThreadPool.globalInstance()
        threadpool.start(worker)

    def set_image(self, image):
        self.imagen = image
        self.img_signal.finished.emit(image)
