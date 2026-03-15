import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import (
    API_LIMIT_ITEMS, API_BASE_URL, TIME_OUT
)
from models.mensaje import Mensaje

class CtrlMensajeSignal(QObject):
    hubo_cambio = Signal(int) # id mensaje

class CtrlMensaje:

    def __init__(self):
        self.mensaje_signal = CtrlMensajeSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        print("CtrlMensaje: limpiar")
        if not solo_busqueda:
            self.mensajes = []
        self.mensajes_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de mensajes

    def api_mensaje(self, id=0):
        print("CtrlMensaje: api_mensaje-init")
        params = {"id": id}
        response = requests.get(API_BASE_URL + "mensajes", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlMensaje: api_mensaje-ok")
            data = response.json()
            mensaje = self.new_mensaje(data[0])
            self.add_mensajes([mensaje], False)
            self.mensaje_signal.hubo_cambio.emit(mensaje.id)
            return mensaje
        return None

    def api_mensajes(self, filtros={}):
        print("CtrlMensaje: api_mensajes-init")
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "mensajes", params=filtros, timeout=TIME_OUT)
            mensajes = []
            if response.status_code == 200:
                print("CtrlMensaje: api_mensajes-ok")
                data = response.json()
                for item in data:
                    mensaje = self.new_mensaje(item)
                    mensajes.append(mensaje)
                if len(mensajes) > 0:
                    self.cursor_busqueda["cursor_fecha"] = mensajes[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = mensajes[-1].id
                    self.add_mensajes(mensajes, True)
                    self.mensaje_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return mensajes
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            print("CtrlMensaje: do_busqueda-rebusqueda")
            filtros = self.cursor_busqueda["filtros"]
        else:
            print("CtrlMensaje: do_busqueda-busqueda")
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_mensajes(filtros)

    # administracion de agregacion de mensajes

    def add_mensajes(self, mensajes=[], from_busqueda=True):
        for mensaje in mensajes:
            self.set_in_list(self.mensajes, mensaje)
            if from_busqueda:
                self.set_in_list(self.mensajes_busqueda, mensaje)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de mensajes

    def get_mensaje(self, id=0):
        for mensaje in self.mensajes:
            if mensaje.id == id:
                return mensaje
        for mensaje in self.mensajes_busqueda:
            if mensaje.id == id:
                return mensaje
        return self.api_mensaje(id)
    
    def get_busqueda(self):
        return self.mensajes_busqueda

    # metodos de apoyo
    
    def set_image(self, id=0):
        self.mensaje_signal.hubo_cambio.emit(id)
    
    def new_mensaje(self, data_json):
        mensaje = Mensaje.from_json(data_json)
        mensaje.img_signal.ok_image.connect(self.set_image)
        mensaje.load_image()
        return mensaje
