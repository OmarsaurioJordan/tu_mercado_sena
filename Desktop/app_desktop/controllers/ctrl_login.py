import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import API_LIMIT_ITEMS, API_BASE_URL, TIME_OUT
from models.login import Login

class CtrlLoginSignal(QObject):
    hubo_cambio = Signal(int) # id login

class CtrlLogin:

    def __init__(self):
        self.login_signal = CtrlLoginSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        print("CtrlLogin: limpiar")
        if not solo_busqueda:
            self.logins = []
        self.logins_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de logins

    def api_login(self, id=0):
        print("CtrlLogin: api_login-init")
        params = {"id": id}
        response = requests.get(API_BASE_URL + "logins", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlLogin: api_login-ok")
            data = response.json()
            login = self.new_login(data[0])
            self.add_logins([login], False)
            self.login_signal.hubo_cambio.emit(login.id)
            return login
        return None

    def api_logins(self, filtros={}):
        print("CtrlLogin: api_logins-init")
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "logins", params=filtros, timeout=TIME_OUT)
            logins = []
            if response.status_code == 200:
                print("CtrlLogin: api_logins-ok")
                data = response.json()
                for item in data:
                    login = self.new_login(item)
                    logins.append(login)
                if len(logins) > 0:
                    self.cursor_busqueda["cursor_fecha"] = logins[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = logins[-1].id
                    self.add_logins(logins, True)
                    self.login_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return logins
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            print("CtrlLogin: do_busqueda-rebusqueda")
            filtros = self.cursor_busqueda["filtros"]
        else:
            print("CtrlLogin: do_busqueda-busqueda")
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_logins(filtros)

    # administracion de agregacion de logins

    def add_logins(self, logins=[], from_busqueda=True):
        for login in logins:
            self.set_in_list(self.logins, login)
            if from_busqueda:
                self.set_in_list(self.logins_busqueda, login)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de logins

    def get_login(self, id=0):
        for login in self.logins:
            if login.id == id:
                return login
        for login in self.logins_busqueda:
            if login.id == id:
                return login
        return self.api_login(id)
    
    def get_busqueda(self):
        return self.logins_busqueda

    # metodos de apoyo
    
    def new_login(self, data_json):
        login = Login.from_json(data_json)
        return login
