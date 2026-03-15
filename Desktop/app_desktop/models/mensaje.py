from PySide6.QtCore import QThreadPool, Signal, QObject
from PySide6.QtGui import QPixmap
from services.load_image import ImageWorker
from core.app_config import IMAGE_CHAT_LINK

class MensajeSignal(QObject):
    ok_image = Signal(int) # id mensaje

class Mensaje:
    
    def __init__(self, id, es_comprador, chat_id, mensaje, imagen, fecha_registro, estado_id):
        self.id = id
        self.es_comprador = es_comprador
        self.chat_id = chat_id
        self.mensaje = mensaje
        self.fecha_registro = fecha_registro
        self.estado_id = estado_id
        # carga de imagenes
        self.imagen = imagen
        self.img_signal = MensajeSignal()
        self.img_pix = QPixmap("assets/sprites/img_null.png")
        self.is_img_load = False
        self.worker = None

    @staticmethod
    def from_json(data):
        return Mensaje(
            id = int(data.get('id')),
            es_comprador = int(data.get('es_comprador')),
            imagen = data.get('imagen'),
            mensaje = data.get('mensaje'),
            chat_id = int(data.get('chat_id')),
            fecha_registro = data.get('fecha_registro'),
            estado_id = int(data.get('estado_id'))
        )

    def load_image(self):
        if not self.imagen:
            self.worker = ImageWorker("")
            QThreadPool.globalInstance().start(self.worker)
            return
        url = IMAGE_CHAT_LINK + self.imagen
        self.worker = ImageWorker(url)
        self.worker.signals.finished.connect(self.set_image)
        QThreadPool.globalInstance().start(self.worker)

    def set_image(self, image):
        if not image.isNull():
            pix = QPixmap.fromImage(image)
        else:
            pix = QPixmap("assets/sprites/img_null.png")
        self.img_pix = pix
        self.is_img_load = True
        self.img_signal.ok_image.emit(self.id)
