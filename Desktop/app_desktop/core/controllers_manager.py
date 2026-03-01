from controllers.ctrl_data import CtrlData
from controllers.ctrl_usuario import CtrlUsuario
from controllers.ctrl_producto import CtrlProducto
from controllers.ctrl_pqrs import CtrlPqrs
from controllers.ctrl_denuncia import CtrlDenuncia

class ControllersManager:

    def __init__(self):
        self.data = CtrlData()
        self.usuarios = CtrlUsuario()
        self.productos = CtrlProducto()
        self.pqrss = CtrlPqrs()
        self.denuncias = CtrlDenuncia()

    def get_data(self):
        return self.data

    def get_usuarios(self):
        return self.usuarios
    
    def get_productos(self):
        return self.productos
    
    def get_pqrss(self):
        return self.pqrss
    
    def get_denuncias(self):
        return self.denuncias

    def limpiar(self):
        print("ControllersManager: limpiar")
        self.usuarios.limpiar()
        self.productos.limpiar()
        self.pqrss.limpiar()
        self.denuncias.limpiar()
