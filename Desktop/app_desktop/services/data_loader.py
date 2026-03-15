import requests
from PySide6.QtCore import QObject, Signal, QRunnable
from core.app_config import API_BASE_URL, TIME_OUT

class DataLoaderWorker(QRunnable):

    class Signals(QObject):
        finished = Signal(dict) # todos los datos
    
    def __init__(self):
        super().__init__()
        self.signals = self.Signals()
    
    def run(self):
        data = {}
        tables = ["motivos", "sucesos", "subcategorias", "roles", "integridad", "estados", "categorias"]
        for tabla in tables:
            try:
                print(f"DataLoaderWorker: run-{tabla}")
                params = {"tabla": tabla}
                response = requests.get(API_BASE_URL + "tools/get_data.php", params=params, timeout=TIME_OUT)
                if response.status_code == 200:
                    data[tabla] = response.json()
                    print(f"DataLoaderWorker: ok-{tabla}")
                else:
                    print(f"DataLoaderWorker: error-{tabla}")
                    data[tabla] = None
            except Exception as e:
                print(f"DataLoaderWorker: error-{tabla}")
                data[tabla] = None
        self.signals.finished.emit(data)
