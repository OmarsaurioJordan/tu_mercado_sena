import requests
from PySide6.QtCore import QRunnable, Signal, QObject
from core.app_config import API_BASE_URL, TIME_OUT

class NotifiWorkerSignals(QObject):
    finished = Signal(int, int) # tot denuncias, tot PQRSs

class NotifiWorker(QRunnable):
    def __init__(self):
        super().__init__()
        self.signals = NotifiWorkerSignals()

    def run(self):
        try:
            response = requests.get(API_BASE_URL + "/tools/get_help.php", timeout=TIME_OUT)
            if response.status_code == 200:
                data = response.json()[0]
                self.signals.finished.emit(data["denuncias"], data["pqrss"])
            else:
                self.signals.finished.emit(-1, -1)
        except Exception as e:
            self.signals.finished.emit(-1, -1)
