import requests
from PySide6.QtCore import QRunnable, Slot, Signal, QObject
from PySide6.QtGui import QPixmap
from io import BytesIO
from services.image_utils import circular_pixmap
from core.app_config import (TIME_OUT)

class ImageWorkerSignals(QObject):
    finished = Signal(QPixmap)

class ImageWorker(QRunnable):
    def __init__(self, url="", is_avatar=True):
        super().__init__()
        self.url = url
        self.is_avatar = is_avatar
        self.signals = ImageWorkerSignals()

    @Slot()
    def run(self):
        try:
            r = requests.get(self.url, timeout=TIME_OUT)
            if not r.ok:
                raise Exception("HTTP error")
            img = QPixmap()
            if not img.loadFromData(r.content):
                raise Exception("load error")
            if self.is_avatar:
                circle = circular_pixmap(img, 128)
                self.signals.finished.emit(circle)
            else:
                self.signals.finished.emit(img)
        except:
            pass
