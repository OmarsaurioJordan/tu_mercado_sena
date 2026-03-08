from PySide6.QtCore import QThreadPool, Signal, QObject
from PySide6.QtGui import QPixmap
from services.load_image import ImageWorker
from core.app_config import IMAGE_PAPELERA_LINK

class PapeleraSignal(QObject):
    ok_image = Signal(int) # id papelera

class Papelera:
    def __init__(self, id, usuario_id, mensaje, imagen, fecha_registro):
        self.id = id
        self.usuario_id = usuario_id
        self.mensaje = mensaje
        self.fecha_registro = fecha_registro
        # carga de imagenes
        self.imagen = imagen
        self.img_signal = PapeleraSignal()
        self.img_pix = QPixmap("assets/sprites/img_null.png")
        self.is_img_load = False
        self.worker = None

    @staticmethod
    def from_json(data):
        return Papelera(
            id = int(data.get('id')),
            usuario_id = int(data.get('usuario_id')),
            mensaje = data.get('mensaje'),
            imagen = data.get('imagen'),
            fecha_registro = data.get('fecha_registro')
        )

    def load_image(self):
        if not self.imagen:
            self.worker = ImageWorker("")
            QThreadPool.globalInstance().start(self.worker)
            return
        url = IMAGE_PAPELERA_LINK + self.imagen
        self.worker = ImageWorker(url)
        self.worker.signals.finished.connect(self.set_image)
        QThreadPool.globalInstance().start(self.worker)

    def set_image(self, image):
        if not image.isNull():
            pix = QPixmap.fromImage(image)
        else:
            pix = QPixmap()
        self.img_pix = pix
        self.is_img_load = True
        self.img_signal.ok_image.emit(self.id)
