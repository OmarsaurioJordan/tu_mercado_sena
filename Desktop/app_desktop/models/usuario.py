from PySide6.QtCore import QThreadPool, Signal, QObject
from PySide6.QtGui import QPixmap
from services.load_image import ImageWorker
from core.app_config import IMAGE_USER_LINK

class UsuarioSignal(QObject):
    ok_image = Signal(QPixmap)

class Usuario:
    def __init__(self, id, email, rol_id, nickname, imagen, descripcion, link, estado_id, fecha_registro, fecha_actualiza, fecha_reciente):
        self.id = id
        self.email = email
        self.rol_id = rol_id
        self.nickname = nickname
        self.imagen = imagen
        self.descripcion = descripcion
        self.link = link
        self.estado_id = estado_id
        self.fecha_registro = fecha_registro
        self.fecha_actualiza = fecha_actualiza
        self.fecha_reciente = fecha_reciente
        self.img_signal = UsuarioSignal()
        self.img_pix = QPixmap("assets/sprites/avatar.png")

    @staticmethod
    def from_json(data):
        usr = Usuario(
            id=int(data.get('id')),
            email=data.get('email'),
            rol_id=int(data.get('rol_id')),
            nickname=data.get('nickname'),
            imagen=data.get('imagen'),
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
        if self.imagen == "":
            return
        url = IMAGE_USER_LINK + self.imagen
        worker = ImageWorker(url, True)
        worker.signals.finished.connect(self.set_image)
        threadpool = QThreadPool.globalInstance()
        threadpool.start(worker)

    def set_image(self, image):
        self.img_pix = image
        self.img_signal.ok_image.emit(image)
