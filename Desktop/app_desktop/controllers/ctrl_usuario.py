import requests
from core.app_config import (
    API_LIMIT_ITEMS, DEFAULT_INFO, API_BASE_URL
)
from models.usuario import Usuario

class CtrlUsuario:

    def __init__(self):
        self.limpiar()
    
    def limpiar(self):
        self.usuarios_busqueda = []
        self.usuarios = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    def api_usuario(self, id=0):
        params = {"id": id}
        response = requests.get(API_BASE_URL + "usuarios/get.php", params=params)
        if response.status_code == 200:
            usr = Usuario.from_json(response.json())
            self.add_usuarios([usr], False)
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
                    usuarios.append(Usuario.from_json(item))
                if len(usuarios) > 0:
                    self.cursor_busqueda["cursor_fecha"] = usuarios[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = usuarios[-1].id
                    self.add_usuarios(usuarios, True)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return usuarios
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def add_usuarios(self, usuarios=[], from_busqueda=True):
        for usr in usuarios:
            self.set_in_list(self.usuarios, usr, True)
            if from_busqueda:
                self.set_in_list(self.usuarios_busqueda, usr, False)

    def get_usuario(self, id=0):
        for usr in self.usuarios:
            if usr.id == id:
                return usr
        for usr in self.usuarios_busqueda:
            if usr.id == id:
                return usr
        return self.api_usuario(id)
    
    def get_usuarios(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            filtros = self.cursor_busqueda["filtros"]
        else:
            self.cursor_busqueda["filtros"] = filtros
        self.api_usuarios(filtros)
        return self.usuarios_busqueda

    def set_in_list(self, lista=[], value=None, reemplazar=True):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                if reemplazar:
                    lista[i] = value
                return True
        lista.append(value)
        return False

    def get_master_info(self):
        descripcion = ""
        response = requests.get(API_BASE_URL + "usuarios/master_info.php")
        if response.status_code == 200:
            descripcion = response.json().get('descripcion')
        if descripcion == "":
            return DEFAULT_INFO
        return descripcion

    def admin_login(self, correo="", password=""):
        params = {"correo": correo, "password": password}
        response = requests.get(API_BASE_URL + "usuarios/admin_login.php", params=params)
        data = response.json()
        if response.status_code == 200:
            return {
                "token": data.get('token'),
                "id": int(data.get('id')),
                "error": ""
            }
        elif response.status_code == 404:
            return {
                "token": "",
                "id": 0,
                "error": data.get('error')
            }
        return {"token": "", "error": ""}
