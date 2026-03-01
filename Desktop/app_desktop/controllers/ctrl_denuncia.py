import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import (
    API_LIMIT_ITEMS, API_BASE_URL, TIME_OUT
)
from models.denuncia import Denuncia
from core.session import Session

class CtrlDenunciaSignal(QObject):
    hubo_cambio = Signal(int)

class CtrlDenuncia:

    def __init__(self):
        self.denuncia_signal = CtrlDenunciaSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        print("CtrlDenuncia: limpiar")
        if not solo_busqueda:
            self.denuncias = []
        self.denuncias_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de denuncias

    def api_denuncia(self, id=0):
        print("CtrlDenuncia: api_denuncia-init")
        params = {"id": id}
        response = requests.get(API_BASE_URL + "denuncias", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlDenuncia: api_denuncia-ok")
            data = response.json()
            denuncia = self.new_denuncia(data[0])
            self.add_denuncias([denuncia], False)
            self.denuncia_signal.hubo_cambio.emit(denuncia.id)
            return denuncia
        return None

    def api_denuncias(self, filtros={}):
        print("CtrlDenuncia: api_denuncias-init")
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "denuncias", params=filtros, timeout=TIME_OUT)
            denuncias = []
            if response.status_code == 200:
                print("CtrlDenuncia: api_denuncias-ok")
                data = response.json()
                for item in data:
                    denuncia = self.new_denuncia(item)
                    denuncias.append(denuncia)
                if len(denuncias) > 0:
                    self.cursor_busqueda["cursor_fecha"] = denuncias[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = denuncias[-1].id
                    self.add_denuncias(denuncias, True)
                    self.denuncia_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return denuncias
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            print("CtrlDenuncia: do_busqueda-rebusqueda")
            filtros = self.cursor_busqueda["filtros"]
        else:
            print("CtrlDenuncia: do_busqueda-busqueda")
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_denuncias(filtros)

    # administracion de agregacion de denuncias

    def add_denuncias(self, denuncias=[], from_busqueda=True):
        for denuncia in denuncias:
            self.set_in_list(self.denuncias, denuncia)
            if from_busqueda:
                self.set_in_list(self.denuncias_busqueda, denuncia)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de denuncias

    def get_denuncia(self, id=0):
        for denuncia in self.denuncias:
            if denuncia.id == id:
                return denuncia
        for denuncia in self.denuncias_busqueda:
            if denuncia.id == id:
                return denuncia
        return self.api_denuncia(id)
    
    def get_busqueda(self):
        return self.denuncias_busqueda

    # llamadas a la API para modificar denuncias
    
    def set_estado(self, id=0, estado_id=0):
        print("CtrlDenuncia: set_estado-init")
        ses = Session()
        admindata = ses.get_login()
        params = {"id": id, "estado": estado_id,
            "admin_email": admindata["email"], "admin_token": admindata["token"]
        }
        response = requests.get(API_BASE_URL + "denuncias/set_estado.php", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlDenuncia: set_estado-ok")
            res = response.json()["Ok"] == "1"
            if res:
                self.api_denuncia(id)
            return res
        return False
    
    def set_motivo(self, id=0, motivo=0):
        print("CtrlDenuncia: set_motivo-init")
        return False

    # metodos de apoyo
    
    def new_denuncia(self, data_json):
        denuncia = Denuncia.from_json(data_json)
        return denuncia
