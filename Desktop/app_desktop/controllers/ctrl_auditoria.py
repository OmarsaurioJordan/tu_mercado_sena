import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import (
    API_LIMIT_ITEMS, API_BASE_URL, TIME_OUT
)
from models.auditoria import Auditoria
from core.session import Session

class CtrlAuditoriaSignal(QObject):
    hubo_cambio = Signal(int) # id auditoria

class CtrlAuditoria:

    def __init__(self):
        self.auditoria_signal = CtrlAuditoriaSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        print("CtrlAuditoria: limpiar")
        if not solo_busqueda:
            self.auditorias = []
        self.auditorias_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de auditorias

    def api_auditoria(self, id=0):
        print("CtrlAuditoria: api_auditoria-init")
        params = {"id": id}
        response = requests.get(API_BASE_URL + "auditorias", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlAuditoria: api_auditoria-ok")
            data = response.json()
            auditoria = self.new_auditoria(data[0])
            self.add_auditorias([auditoria], False)
            self.auditoria_signal.hubo_cambio.emit(auditoria.id)
            return auditoria
        return None

    def api_auditorias(self, filtros={}):
        print("CtrlAuditoria: api_auditorias-init")
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "auditorias", params=filtros, timeout=TIME_OUT)
            auditorias = []
            if response.status_code == 200:
                print("CtrlAuditoria: api_auditorias-ok")
                data = response.json()
                for item in data:
                    auditoria = self.new_auditoria(item)
                    auditorias.append(auditoria)
                if len(auditorias) > 0:
                    self.cursor_busqueda["cursor_fecha"] = auditorias[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = auditorias[-1].id
                    self.add_auditorias(auditorias, True)
                    self.auditoria_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return auditorias
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            print("CtrlAuditoria: do_busqueda-rebusqueda")
            filtros = self.cursor_busqueda["filtros"]
        else:
            print("CtrlAuditoria: do_busqueda-busqueda")
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_auditorias(filtros)

    # administracion de agregacion de auditorias

    def add_auditorias(self, auditorias=[], from_busqueda=True):
        for auditoria in auditorias:
            self.set_in_list(self.auditorias, auditoria)
            if from_busqueda:
                self.set_in_list(self.auditorias_busqueda, auditoria)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de auditorias

    def get_auditoria(self, id=0):
        for auditoria in self.auditorias:
            if auditoria.id == id:
                return auditoria
        for auditoria in self.auditorias_busqueda:
            if auditoria.id == id:
                return auditoria
        return self.api_auditoria(id)
    
    def get_busqueda(self):
        return self.auditorias_busqueda

    # metodos de apoyo
    
    def new_auditoria(self, data_json):
        auditoria = Auditoria.from_json(data_json)
        return auditoria
