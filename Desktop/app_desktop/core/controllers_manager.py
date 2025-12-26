from controllers.ctrl_usuario import CtrlUsuario

class ControllersManager:

    def __init__(self):
        self.usuarios = CtrlUsuario()

    def get_usuarios(self):
        return self.usuarios

    def limpiar(self):
        self.usuarios.limpiar()
