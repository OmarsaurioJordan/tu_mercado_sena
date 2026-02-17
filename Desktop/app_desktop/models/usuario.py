from PySide6.QtCore import QThreadPool, Signal, QObject
from PySide6.QtGui import QPixmap
from services.load_image import ImageWorker
from core.app_config import IMAGE_USER_LINK

class UsuarioSignal(QObject):
    ok_image = Signal(int)

class Usuario:
    
    def __init__(self, id, email, rol_id, nickname, imagen, descripcion, link, estado_id, fecha_registro, fecha_actualiza, fecha_reciente):
        self.id = id
        self.email = email
        self.rol_id = rol_id
        self.nickname = nickname
        self.descripcion = descripcion
        self.link = link
        self.estado_id = estado_id
        self.fecha_registro = fecha_registro
        self.fecha_actualiza = fecha_actualiza
        self.fecha_reciente = fecha_reciente
        # carga de imagenes
        self.imagen = imagen
        self.img_signal = UsuarioSignal()
        self.img_pix = QPixmap("assets/sprites/avatar.png")
        self.is_img_load = False
        self.worker = None

    @staticmethod
    def from_json(data):
        return Usuario(
            id = int(data.get('id')),
            email = data.get('email'),
            rol_id = int(data.get('rol_id')),
            nickname = data.get('nickname'),
            imagen = data.get('imagen'),
            descripcion = data.get('descripcion'),
            link = data.get('link'),
            estado_id = int(data.get('estado_id')),
            fecha_registro = data.get('fecha_registro'),
            fecha_actualiza = data.get('fecha_actualiza'),
            fecha_reciente = data.get('fecha_reciente')
        )

    def load_image(self):
        if not self.imagen:
            self.worker = ImageWorker("", True)
            QThreadPool.globalInstance().start(self.worker)
            return
        url = IMAGE_USER_LINK + self.imagen
        self.worker = ImageWorker(url, True)
        self.worker.signals.finished.connect(self.set_image)
        QThreadPool.globalInstance().start(self.worker)

    def set_image(self, image):
        self.img_pix = image
        self.is_img_load = True
        self.img_signal.ok_image.emit(self.id)
