import requests
from PySide6.QtCore import QRunnable, Slot, Signal, QObject
from PySide6.QtGui import QImage
from core.app_config import (TIME_OUT)

class ImageWorkerSignals(QObject):
    finished = Signal(QImage)

class ImageWorker(QRunnable):
    def __init__(self, url=""):
        super().__init__()
        self.url = url
        self.signals = ImageWorkerSignals()

    @Slot()
    def run(self):
        try:
            r = requests.get(self.url, timeout=TIME_OUT)
            if not r.ok:
                raise Exception("HTTP error")
            img = QImage()
            if not img.loadFromData(r.content):
                raise Exception("load error")
            self.signals.finished.emit(img)
        except:
            pass
