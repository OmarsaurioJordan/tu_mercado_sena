import requests
from PySide6.QtCore import QThreadPool, Signal, QObject
from core.app_config import API_BASE_URL, TIME_OUT
from services.data_loader import DataLoaderWorker

class CtrlDataSignals(QObject):
    data_loaded = Signal()

class CtrlData:

    # inicializacion

    def __init__(self):
        self.data_signals = CtrlDataSignals()
        self.limpiar()
    
    def limpiar(self):
        print("CtrlData: limpiar")
        self.hay_data = False
        self.motivos = None
        self.sucesos = None
        self.subcategorias = None
        self.roles = None
        self.integridad = None
        self.estados = None
        self.categorias = None
        self._load_data_async()
    
    def _load_data_async(self):
        print("CtrlData: _load_data_async")
        self.thread_pool = QThreadPool.globalInstance()
        self.worker = DataLoaderWorker()
        self.worker.signals.finished.connect(self._on_data_loaded)
        self.thread_pool.start(self.worker)
    
    def _on_data_loaded(self, data):
        print("CtrlData: _on_data_loaded")
        self.motivos = data.get("motivos")
        self.sucesos = data.get("sucesos")
        self.subcategorias = data.get("subcategorias")
        self.roles = data.get("roles")
        self.integridad = data.get("integridad")
        self.estados = data.get("estados")
        self.categorias = data.get("categorias")
        self.hay_data = True
        self.data_signals.data_loaded.emit()
    
    # obtener diferentes estructuras de informacion y pedirlos a la API si acaso faltan

    def get_data(self, tabla=""):
        match tabla:
            case "motivos":
                if not self.motivos:
                    self.motivos = self.api_data("motivos")
                return self.motivos
            case "sucesos":
                if not self.sucesos:
                    self.sucesos = self.api_data("sucesos")
                return self.sucesos
            case "subcategorias":
                if not self.subcategorias:
                    self.subcategorias = self.api_data("subcategorias")
                return self.subcategorias
            case "roles":
                if not self.roles:
                    self.roles = self.api_data("roles")
                return self.roles
            case "integridad":
                if not self.integridad:
                    self.integridad = self.api_data("integridad")
                return self.integridad
            case "estados":
                if not self.estados:
                    self.estados = self.api_data("estados")
                return self.estados
            case "categorias":
                if not self.categorias:
                    self.categorias = self.api_data("categorias")
                return self.categorias
        return None

    def api_data(self, tabla=""):
        print(f"CtrlData: api_data-{tabla}-init")
        params = {"tabla": tabla}
        response = requests.get(API_BASE_URL + "tools/get_data.php", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print(f"CtrlData: api_data-{tabla}-ok")
            data = response.json()
            return data
        return None

    # obtencion de informacion para selectores, array de duplas str, int

    def get_to_selector(self, tabla=""):
        res = []
        data = self.get_data(tabla)
        if not data:
            return [["???", -1]]
        for dt in data:
            res.append([dt["nombre"].capitalize(), dt["id"]])
        return res

    def get_row(self, tabla="", id=0):
        data = self.get_data(tabla)
        if not data:
            return None
        for dt in data:
            if dt["id"] == id:
                return dt
        return None
    
    # obtencion de informacion mas especifica, casos puntuales, formato selector

    def get_motivos(self, tipo=""):
        res = []
        motivos = self.get_data("motivos")
        if not motivos:
            return [["???", -1]]
        for mot in motivos:
            if mot["extra"] == tipo:
                res.append([mot["nombre"].capitalize(), mot["id"]])
        return res

    def get_estados_basicos(self):
        res = []
        estados = self.get_data("estados")
        if not estados:
            return [["???", -1]]
        for est in estados:
            if est["nombre"] in ["activo", "invisible", "eliminado", "bloqueado", "denunciado"]:
                res.append([est["nombre"].capitalize(), est["id"]])
        return res
    
    def get_estados_resueltos(self):
        res = []
        estados = self.get_data("estados")
        if not estados:
            return [["???", -1]]
        for est in estados:
            if est["nombre"] in ["activo", "resuelto"]:
                res.append([est["nombre"].capitalize(), est["id"]])
        return res

    def get_estados_chats(self):
        res = []
        estados = self.get_data("estados")
        if not estados:
            return [["???", -1]]
        for est in estados:
            if est["nombre"] in ["activo", "vendido", "esperando", "devolviendo", "devuelto", "censurado", "chat_eliminado_vendedor", "chat_eliminado_comprador", "chat_eliminado_ambos"]:
                match est["id"]:
                    case 12:
                        est["nombre"] = "elim.Vend."
                    case 13:
                        est["nombre"] = "elim.Comp."
                    case 14:
                        est["nombre"] = "eliminado"
                res.append([est["nombre"].capitalize(), est["id"]])
        return res
    
    def get_roles_basicos(self):
        res = []
        roles = self.get_data("roles")
        if not roles:
            return [["???", -1]]
        for rol in roles:
            if rol["nombre"] in ["prosumer", "administrador"]:
                res.append([rol["nombre"].capitalize(), rol["id"]])
        return res

    # obtencion de informacion referente a categorias y subcategorias

    def get_subcategorias(self, categoria_id=0):
        subcategorias = self.get_data("subcategorias")
        if subcategorias:
            subcat = []
            for sub in subcategorias:
                if sub["extra"] == categoria_id:
                    subcat.append(sub)
            return subcat
        return []
    
    def get_subcategorias_to_selector(self, categoria_id=0):
        res = []
        subcategorias = self.get_subcategorias(categoria_id)
        if len(subcategorias) == 0:
            return [["???", -1]]
        for sub in subcategorias:
            res.append([sub["nombre"].capitalize(), sub["id"]])
        return res

    # obtener info de analitica

    def api_informacion(self):
        print(f"CtrlData: api_informacion-init")
        response = requests.get(API_BASE_URL + "tools/informacion.php", timeout=TIME_OUT)
        if response.status_code == 200:
            print(f"CtrlData: api_informacion-ok")
            data = response.json()
            return data
        return None
