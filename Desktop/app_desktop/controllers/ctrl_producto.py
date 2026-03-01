import requests
from PySide6.QtCore import Signal, QObject
from core.app_config import (
    API_LIMIT_ITEMS, API_BASE_URL, TIME_OUT
)
from models.producto import Producto
from core.session import Session

class CtrlProductoSignal(QObject):
    hubo_cambio = Signal(int)

class CtrlProducto:

    def __init__(self):
        self.producto_signal = CtrlProductoSignal()
        self.limpiar()
    
    def limpiar(self, solo_busqueda=False):
        print("CtrlProducto: limpiar")
        if not solo_busqueda:
            self.productos = []
        self.productos_busqueda = []
        self.cursor_busqueda = {
            "cursor_fecha": "",
            "cursor_id": "",
            "finalizo": False,
            "running": False,
            "filtros": {}
        }

    # llamadas a la API para informacion de productos

    def api_producto(self, id=0):
        print("CtrlProducto: api_producto-init")
        params = {"id": id}
        response = requests.get(API_BASE_URL + "productos", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlProducto: api_producto-ok")
            data = response.json()
            prd = self.new_producto(data[0])
            self.add_productos([prd], False)
            self.producto_signal.hubo_cambio.emit(prd.id)
            return prd
        return None

    def api_productos(self, filtros={}):
        print("CtrlProducto: api_productos-init")
        if self.cursor_busqueda["finalizo"] or self.cursor_busqueda["running"]:
            return []
        self.cursor_busqueda["running"] = True
        try:
            filtros["limite"] = API_LIMIT_ITEMS
            filtros["cursor_fecha"] = self.cursor_busqueda["cursor_fecha"]
            filtros["cursor_id"] = self.cursor_busqueda["cursor_id"]
            response = requests.get(API_BASE_URL + "productos", params=filtros, timeout=TIME_OUT)
            productos = []
            if response.status_code == 200:
                print("CtrlProducto: api_productos-ok")
                data = response.json()
                for item in data:
                    prd = self.new_producto(item)
                    productos.append(prd)
                if len(productos) > 0:
                    self.cursor_busqueda["cursor_fecha"] = productos[-1].fecha_registro
                    self.cursor_busqueda["cursor_id"] = productos[-1].id
                    self.add_productos(productos, True)
                    self.producto_signal.hubo_cambio.emit(0)
                else:
                    self.cursor_busqueda["finalizo"] = True
            return productos
        finally:
            self.cursor_busqueda["running"] = False
        return []

    def do_busqueda(self, filtros={}, rebusqueda=False):
        if rebusqueda:
            print("CtrlProducto: do_busqueda-rebusqueda")
            filtros = self.cursor_busqueda["filtros"]
        else:
            print("CtrlProducto: do_busqueda-busqueda")
            self.limpiar(True)
            self.cursor_busqueda["filtros"] = filtros
        self.api_productos(filtros)

    # administracion de agregacion de productos

    def add_productos(self, productos=[], from_busqueda=True):
        for prd in productos:
            self.set_in_list(self.productos, prd)
            if from_busqueda:
                self.set_in_list(self.productos_busqueda, prd)

    def set_in_list(self, lista=[], value=None):
        for i in range(len(lista)):
            if lista[i].id == value.id:
                lista[i] = value
                return
        lista.append(value)

    # obtencion de informacion de productos

    def get_producto(self, id=0):
        for prd in self.productos:
            if prd.id == id:
                return prd
        for prd in self.productos_busqueda:
            if prd.id == id:
                return prd
        return self.api_producto(id)
    
    def get_busqueda(self):
        return self.productos_busqueda

    # llamadas a la API para modificar productos
    
    def set_estado(self, id=0, estado_id=0):
        print("CtrlProducto: set_estado-init")
        ses = Session()
        admindata = ses.get_login()
        params = {"id": id, "estado": estado_id,
            "admin_email": admindata["email"], "admin_token": admindata["token"]
        }
        response = requests.get(API_BASE_URL + "productos/set_estado.php", params=params, timeout=TIME_OUT)
        if response.status_code == 200:
            print("CtrlProducto: set_estado-ok")
            res = response.json()["Ok"] == "1"
            if res:
                self.api_producto(id)
            return res
        return False
    
    def set_integridad(self, id=0, integridad=0):
        print("CtrlProducto: set_integridad-init")
        return False
    
    def set_subcategoria(self, id=0, subcategoria=0):
        print("CtrlProducto: set_subcategoria-init")
        return False
    
    def set_categoria(self, id=0, categoria=0):
        print("CtrlProducto: set_categoria-init")
        return False

    # metodos de apoyo

    def set_image(self, id=0):
        self.producto_signal.hubo_cambio.emit(id)
    
    def new_producto(self, data_json):
        prd = Producto.from_json(data_json)
        prd.img_signal.ok_image.connect(self.set_image)
        prd.load_images(True)
        return prd
