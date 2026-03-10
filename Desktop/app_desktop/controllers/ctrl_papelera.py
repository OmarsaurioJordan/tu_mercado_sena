import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import (
    API_LIMIT_ITEMS, API_BASE_URL, TIME_OUT
)
from models.papelera import Papelera

class CtrlPapeleraSignal(QObject):
    hubo_cambio = Signal(int) # id papelera

class CtrlPapelera:

    def __init__(self):
        self.papelera_signal = CtrlPapeleraSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        print("CtrlPapelera: limpiar")
        if not solo_busqueda:
            self.papeleras = []
        self.papeleras_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de papeleras

    def api_papelera(self, id=0):
        print("CtrlPapelera: api_papelera-init")
        params = {"id": id}
        response = requests.get(API_BASE_URL + "papelera", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlPapelera: api_papelera-ok")
            data = response.json()
            papelera = self.new_papelera(data[0])
            self.add_papeleras([papelera], False)
            self.papelera_signal.hubo_cambio.emit(papelera.id)
            return papelera
        return None

    def api_papeleras(self, filtros={}):
        print("CtrlPapelera: api_papeleras-init")
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "papelera", params=filtros, timeout=TIME_OUT)
            papeleras = []
            if response.status_code == 200:
                print("CtrlPapelera: api_papeleras-ok")
                data = response.json()
                for item in data:
                    papelera = self.new_papelera(item)
                    papeleras.append(papelera)
                if len(papeleras) > 0:
                    self.cursor_busqueda["cursor_fecha"] = papeleras[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = papeleras[-1].id
                    self.add_papeleras(papeleras, True)
                    self.papelera_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return papeleras
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            print("CtrlPapelera: do_busqueda-rebusqueda")
            filtros = self.cursor_busqueda["filtros"]
        else:
            print("CtrlPapelera: do_busqueda-busqueda")
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_papeleras(filtros)

    # administracion de agregacion de papeleras

    def add_papeleras(self, papeleras=[], from_busqueda=True):
        for papelera in papeleras:
            self.set_in_list(self.papeleras, papelera)
            if from_busqueda:
                self.set_in_list(self.papeleras_busqueda, papelera)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de papeleras

    def get_papelera(self, id=0):
        for papelera in self.papeleras:
            if papelera.id == id:
                return papelera
        for papelera in self.papeleras_busqueda:
            if papelera.id == id:
                return papelera
        return self.api_papelera(id)
    
    def get_busqueda(self):
        return self.papeleras_busqueda

    # metodos de apoyo
    
    def set_image(self, id=0):
        self.papelera_signal.hubo_cambio.emit(id)
    
    def new_papelera(self, data_json):
        papelera = Papelera.from_json(data_json)
        papelera.img_signal.ok_image.connect(self.set_image)
        papelera.load_image()
        return papelera
