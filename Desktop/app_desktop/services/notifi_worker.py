import requests
from PySide6.QtCore import (QObject, Signal, Slot)
from core.app_config import (API_BASE_URL, TIME_OUT)

class NotifiWorker(QObject):
    finished = Signal(int, int) # tot denuncias, tot PQRSs

    @Slot()
    def run(self):
        try:
            response = requests.get(API_BASE_URL + "/tools/get_help.php", timeout=TIME_OUT)
            if response.status_code == 200:
                data = response.json()[0]
                self.finished.emit(data["denuncias"], data["pqrss"])
            else:
                self.finished.emit(-1, -1)
        except Exception as e:
            self.finished.emit(-1, -1)
