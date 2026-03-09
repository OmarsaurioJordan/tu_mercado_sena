from PySide6.QtCore import (QTimer, Signal, QObject, QThread)
from PySide6.QtWidgets import (QApplication)
from controllers.ctrl_data import CtrlData
from controllers.ctrl_usuario import CtrlUsuario
from controllers.ctrl_producto import CtrlProducto
from controllers.ctrl_pqrs import CtrlPqrs
from controllers.ctrl_denuncia import CtrlDenuncia
from controllers.ctrl_mensaje import CtrlMensaje
from controllers.ctrl_auditoria import CtrlAuditoria
from controllers.ctrl_login import CtrlLogin
from controllers.ctrl_chat import CtrlChat
from services.notifi_worker import NotifiWorker
from core.app_config import TIME_NOTIFI

class ControllersManager(QObject):
    signal_notifi = Signal(int, int) # tot denuncias, tot PQRSs
    signal_tools_cambio = Signal(str) # nombre de la pestanna a mostrar en Tools
    signal_filter_notifi = Signal(bool) # True PQRS, False denuncia

    def __init__(self):
        super().__init__()
        self.data = CtrlData()
        self.usuarios = CtrlUsuario()
        self.productos = CtrlProducto()
        self.pqrss = CtrlPqrs()
        self.denuncias = CtrlDenuncia()
        self.mensajes = CtrlMensaje()
        self.chats = CtrlChat()
        self.logins = CtrlLogin()
        self.auditorias = CtrlAuditoria()
        self.catalogo = CtrlProducto()
        self.dialogo = CtrlMensaje()

        self.timer_notifi = QTimer()
        self.timer_notifi.timeout.connect(self.api_notificaciones)
        self.timer_notifi.start(TIME_NOTIFI * 1000)

    def api_notificaciones(self):
        manager = QApplication.instance().property("manager")
        if not manager.is_login():
            return
        self.thread = QThread()
        self.worker = NotifiWorker()
        self.worker.moveToThread(self.thread)
        self.thread.started.connect(self.worker.run)
        self.worker.finished.connect(self.signal_notifi)
        self.worker.finished.connect(self.thread.quit)
        self.worker.finished.connect(self.worker.deleteLater)
        self.thread.finished.connect(self.thread.deleteLater)
        self.thread.start()

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

    def get_mensajes(self):
        return self.mensajes

    def get_chats(self):
        return self.chats
    
    def get_logins(self):
        return self.logins
    
    def get_auditorias(self):
        return self.auditorias

    def get_catalogo(self):
        return self.catalogo
    
    def get_dialogo(self):
        return self.dialogo

    def limpiar(self):
        print("ControllersManager: limpiar")
        self.usuarios.limpiar()
        self.productos.limpiar()
        self.pqrss.limpiar()
        self.denuncias.limpiar()
        self.mensajes.limpiar()
        self.chats.limpiar()
        self.logins.limpiar()
        self.auditorias.limpiar()
        self.catalogo.limpiar()
        self.dialogo.limpiar()
