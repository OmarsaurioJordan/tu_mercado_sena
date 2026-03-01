import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import (
    API_LIMIT_ITEMS, API_BASE_URL, TIME_OUT
)
from models.pqrs import Pqrs
from core.session import Session

class CtrlPqrsSignal(QObject):
    hubo_cambio = Signal(int)

class CtrlPqrs:

    def __init__(self):
        self.pqrs_signal = CtrlPqrsSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        print("CtrlPqrs: limpiar")
        if not solo_busqueda:
            self.pqrss = []
        self.pqrss_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de PQRSs

    def api_pqrs(self, id=0):
        print("CtrlPqrs: api_pqrs-init")
        params = {"id": id}
        response = requests.get(API_BASE_URL + "pqrss", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlPqrs: api_pqrs-ok")
            data = response.json()
            pqrs = self.new_pqrs(data[0])
            self.add_pqrss([pqrs], False)
            self.pqrs_signal.hubo_cambio.emit(pqrs.id)
            return pqrs
        return None

    def api_pqrss(self, filtros={}):
        print("CtrlPqrs: api_pqrss-init")
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "pqrss", params=filtros, timeout=TIME_OUT)
            pqrss = []
            if response.status_code == 200:
                print("CtrlPqrs: api_pqrss-ok")
                data = response.json()
                for item in data:
                    pqrs = self.new_pqrs(item)
                    pqrss.append(pqrs)
                if len(pqrss) > 0:
                    self.cursor_busqueda["cursor_fecha"] = pqrss[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = pqrss[-1].id
                    self.add_pqrss(pqrss, True)
                    self.pqrs_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return pqrss
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            print("CtrlPqrs: do_busqueda-rebusqueda")
            filtros = self.cursor_busqueda["filtros"]
        else:
            print("CtrlPqrs: do_busqueda-busqueda")
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_pqrss(filtros)

    # administracion de agregacion de PQRSs

    def add_pqrss(self, pqrss=[], from_busqueda=True):
        for pqrs in pqrss:
            self.set_in_list(self.pqrss, pqrs)
            if from_busqueda:
                self.set_in_list(self.pqrss_busqueda, pqrs)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de PQRSs

    def get_pqrs(self, id=0):
        for pqrs in self.pqrss:
            if pqrs.id == id:
                return pqrs
        for pqrs in self.pqrss_busqueda:
            if pqrs.id == id:
                return pqrs
        return self.api_pqrs(id)
    
    def get_busqueda(self):
        return self.pqrss_busqueda

    # llamadas a la API para modificar PQRSs
    
    def set_estado(self, id=0, estado_id=0):
        print("CtrlPqrs: set_estado-init")
        ses = Session()
        admindata = ses.get_login()
        params = {"id": id, "estado": estado_id,
            "admin_email": admindata["email"], "admin_token": admindata["token"]
        }
        response = requests.get(API_BASE_URL + "pqrss/set_estado.php", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlPqrs: set_estado-ok")
            res = response.json()["Ok"] == "1"
            if res:
                self.api_pqrs(id)
            return res
        return False
    
    def set_motivo(self, id=0, motivo=0):
        print("CtrlPqrs: set_motivo-init")
        return False

    # metodos de apoyo
    
    def new_pqrs(self, data_json):
        pqrs = Pqrs.from_json(data_json)
        return pqrs
