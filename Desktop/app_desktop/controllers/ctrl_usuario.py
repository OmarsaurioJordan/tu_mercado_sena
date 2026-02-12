import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import (
    API_LIMIT_ITEMS, DEFAULT_INFO, API_BASE_URL
)
from models.usuario import Usuario
from core.session import Session

class CtrlUsuarioSignal(QObject):
    hubo_cambio = Signal(int)

class CtrlUsuario:

    def __init__(self):
        self.usuario_signal = CtrlUsuarioSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        if not solo_busqueda:
            self.usuarios = []
        self.usuarios_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de usuarios

    def api_usuario(self, id=0):
        params = {"id": id}
        response = requests.get(API_BASE_URL + "usuarios", params=params)
        if response.status_code == 200:
            data = response.json()
            usr = self.new_usuario(data[0])
            self.add_usuarios([usr], False)
            self.usuario_signal.hubo_cambio.emit(usr.id)
            return usr
        return None

    def api_usuarios(self, filtros={}):
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "usuarios", params=filtros)
            usuarios = []
            if response.status_code == 200:
                data = response.json()
                for item in data:
                    usr = self.new_usuario(item)
                    usuarios.append(usr)
                if len(usuarios) > 0:
                    self.cursor_busqueda["cursor_fecha"] = usuarios[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = usuarios[-1].id
                    self.add_usuarios(usuarios, True)
                    self.usuario_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return usuarios
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            filtros = self.cursor_busqueda["filtros"]
        else:
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_usuarios(filtros)

    # administracion de agregacion de usuarios

    def add_usuarios(self, usuarios=[], from_busqueda=True):
        for usr in usuarios:
            self.set_in_list(self.usuarios, usr)
            if from_busqueda:
                self.set_in_list(self.usuarios_busqueda, usr)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de usuarios

    def get_usuario(self, id=0, forzado=False):
        for usr in self.usuarios:
            if usr.id == id:
                return usr
        for usr in self.usuarios_busqueda:
            if usr.id == id:
                return usr
        if forzado:
            return self.api_usuario(id)
        return None
    
    def get_busqueda(self):
        return self.usuarios_busqueda

    # llamadas a la API para administrador

    def get_master_info(self):
        response = requests.get(API_BASE_URL + "admin/master_info.php")
        if response.status_code == 200:
            return response.json().get('descripcion')
        return DEFAULT_INFO

    def admin_login(self, email="", password=""):
        params = {"email": email, "password": password}
        response = requests.get(API_BASE_URL + "admin/admin_login.php", params=params)
        if response.status_code == 200:
            data = response.json()
            return {
                "token": data.get('token'),
                "id": int(data.get('id')),
                "error": ""
            }
        elif response.status_code == 404:
            data = response.json()
            return {
                "token": "",
                "id": 0,
                "error": data.get('error')
            }
        return {"token": "", "id": 0, "error": ""}

    def admin_pin(self, email="", pin=""):
        ses = Session()
        admindata = ses.get_login()
        params = {"email": email, "pin": pin,
            "admin_email": admindata["email"], "admin_token": admindata["token"]
        }
        response = requests.get(API_BASE_URL + "admin/admin_pin.php", params=params)
        data = response.json()
        if response.status_code == 200:
            return int(data.get('Ok')) # 0 o 1
        return 2 # error

    # llamadas a la API para modificar usuarios
    
    def set_rol(self, id=0, rol_id=0):
        ses = Session()
        admindata = ses.get_login()
        params = {"id": id, "rol": rol_id,
            "admin_email": admindata["email"], "admin_token": admindata["token"]
        }
        response = requests.get(API_BASE_URL + "usuarios/set_rol.php", params=params)
        if response.status_code == 200:
            res = response.json()["Ok"] == "1"
            if res:
                self.api_usuario(id)
            return res
        return False
    
    def set_estado(self, id=0, estado_id=0):
        ses = Session()
        admindata = ses.get_login()
        params = {"id": id, "estado": estado_id,
            "admin_email": admindata["email"], "admin_token": admindata["token"]
        }
        response = requests.get(API_BASE_URL + "usuarios/set_estado.php", params=params)
        if response.status_code == 200:
            res = response.json()["Ok"] == "1"
            if res:
                self.api_usuario(id)
            return res
        return False

    # metodos de apoyo

    def set_image(self, id=0):
        self.usuario_signal.hubo_cambio.emit(id)
    
    def new_usuario(self, data_json):
        usr = Usuario.from_json(data_json)
        usr.img_signal.ok_image.connect(self.set_image)
        return usr
