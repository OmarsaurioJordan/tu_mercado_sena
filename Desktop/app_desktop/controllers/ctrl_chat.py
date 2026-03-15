import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import API_LIMIT_ITEMS, API_BASE_URL, TIME_OUT
from models.chat import Chat
from core.session import Session

class CtrlChatSignal(QObject):
    hubo_cambio = Signal(int) # id chat

class CtrlChat:

    def __init__(self):
        self.chat_signal = CtrlChatSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        print("CtrlChat: limpiar")
        if not solo_busqueda:
            self.chats = []
        self.chats_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de chats

    def api_chat(self, id=0):
        print("CtrlChat: api_chat-init")
        params = {"id": id}
        response = requests.get(API_BASE_URL + "chats", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlChat: api_chat-ok")
            data = response.json()
            chat = self.new_chat(data[0])
            self.add_chats([chat], False)
            self.chat_signal.hubo_cambio.emit(chat.id)
            return chat
        return None

    def api_chats(self, filtros={}):
        print("CtrlChat: api_chats-init")
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "chats", params=filtros, timeout=TIME_OUT)
            chats = []
            if response.status_code == 200:
                print("CtrlChat: api_chats-ok")
                data = response.json()
                for item in data:
                    chat = self.new_chat(item)
                    chats.append(chat)
                if len(chats) > 0:
                    self.cursor_busqueda["cursor_fecha"] = chats[-1].fecha_venta
                    self.cursor_busqueda["cursor_id"] = chats[-1].id
                    self.add_chats(chats, True)
                    self.chat_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return chats
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            print("CtrlChat: do_busqueda-rebusqueda")
            filtros = self.cursor_busqueda["filtros"]
        else:
            print("CtrlChat: do_busqueda-busqueda")
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_chats(filtros)

    # administracion de agregacion de chats

    def add_chats(self, chats=[], from_busqueda=True):
        for chat in chats:
            self.set_in_list(self.chats, chat)
            if from_busqueda:
                self.set_in_list(self.chats_busqueda, chat)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de chats

    def get_chat(self, id=0):
        for chat in self.chats:
            if chat.id == id:
                return chat
        for chat in self.chats_busqueda:
            if chat.id == id:
                return chat
        return self.api_chat(id)
    
    def get_busqueda(self):
        return self.chats_busqueda

    # llamadas a la API para modificar chats
    
    def set_estado(self, id=0, estado_id=0):
        print("CtrlChat: set_estado-init")
        ses = Session()
        admindata = ses.get_login()
        params = {"id": id, "estado": estado_id,
            "admin_email": admindata["email"], "admin_token": admindata["token"]
        }
        response = requests.get(API_BASE_URL + "chats/set_estado.php", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlChat: set_estado-ok")
            res = response.json()["Ok"] == "1"
            if res:
                self.api_chat(id)
            return res
        return False

    # metodos de apoyo
    
    def new_chat(self, data_json):
        chat = Chat.from_json(data_json)
        return chat
