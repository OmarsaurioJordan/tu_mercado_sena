from controllers.ctrl_data import CtrlData
from controllers.ctrl_usuario import CtrlUsuario
from controllers.ctrl_producto import CtrlProducto

class ControllersManager:

    def __init__(self):
        self.data = CtrlData()
        self.usuarios = CtrlUsuario()
        self.productos = CtrlProducto()

    def get_data(self):
        return self.data

    def get_usuarios(self):
        return self.usuarios
    
    def get_productos(self):
        return self.productos

    def limpiar(self):
        self.usuarios.limpiar()
        self.productos.limpiar()
