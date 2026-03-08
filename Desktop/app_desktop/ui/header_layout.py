from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt
from PySide6.QtGui import QPixmap
from core.app_config import DOMINIO_EMAIL
from components.usuario_card import UsuarioCard
from components.boton import Boton
from models.usuario import Usuario
from core.session import Session

class HeaderLayout(QVBoxLayout):

    def __init__(self, widget=None, con_btn_menu=True):
        super().__init__()

        manager = QApplication.instance().property("controls")
        ctrlUsuario = manager.get_usuarios()
        admin_id = Session().get_login()["id"]
        if admin_id == 0:
            admin = self.usuario_debug()
        else:
            admin = ctrlUsuario.get_usuario(admin_id)
            if admin is None:
                admin_id = 0
                admin = self.usuario_debug()
        
        manager.signal_notifi.connect(self.on_notifi)

        image = QPixmap("assets/sprites/logo.png")
        logo = QLabel()
        logo.setPixmap(image)
        logo.setScaledContents(True)
        logo.setFixedSize(64, 64)

        titulo = QLabel("Tu Mercado Sena")
        font = titulo.font()
        font.setPointSize(24)
        font.setBold(True)
        titulo.setFont(font)
        titulo.setAlignment(
            Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignVCenter
        )

        if con_btn_menu:
            btnMenu = Boton("MENÚ", "menu")
            btnMenu.clicked.connect(lambda: self.cambiaPagina("menu"))
        else:
            btnMenu = QLabel()
        
        self.notifica_pqrss = Boton("PQRS: 0","bell")
        self.notifica_pqrss.clicked.connect(self.find_pqrss)
        self.notifica_denuncias = Boton("Denuncias: 0", "bell")
        self.notifica_denuncias.clicked.connect(self.find_denuncias)
        self.hay_busqueda = [False, False]

        header = QWidget()
        layHorizontal = QHBoxLayout(header)
        layHorizontal.addWidget(logo)
        layHorizontal.addSpacing(10)
        layHorizontal.addWidget(titulo)
        layHorizontal.addSpacing(10)
        layHorizontal.addStretch()
        layHorizontal.addWidget(btnMenu)
        layHorizontal.addSpacing(10)
        layHorizontal.addStretch()
        layHorizontal.addWidget(self.notifica_pqrss)
        layHorizontal.addSpacing(10)
        layHorizontal.addWidget(self.notifica_denuncias)
        layHorizontal.addSpacing(10)
        layHorizontal.addStretch()
        layHorizontal.addWidget(UsuarioCard(admin))
        self.addWidget(header)
        
        if widget is None:
            self.addWidget(QLabel())
        else:
            self.addWidget(widget)

    def usuario_debug(self):
        print("HeaderLayout: usuario_debug")
        return Usuario(0, "email_administrativo" + DOMINIO_EMAIL, 3, "Usuario Administrador", 0, "", "", 1, "", "", "")

    def cambiaPagina(self, pagina=""):
        print("HeaderLayout: cambiaPagina")
        manager = QApplication.instance().property("manager")
        manager.change_tool(pagina)

    def on_notifi(self, denuncias, pqrss):
        self.notifica_denuncias.set_text("Denuncias: " + str(denuncias))
        self.notifica_pqrss.set_text("PQRS: " + str(pqrss))
        ant = self.hay_busqueda
        if (not ant[0] and denuncias > 0) or (not ant[1] and pqrss > 0):
            QApplication.instance().property("sound_notifi").play()
        self.hay_busqueda = [denuncias > 0, pqrss > 0]

    def find_pqrss(self):
        if self.hay_busqueda[1]:
            self.cambiaPagina("tools")
            manager = QApplication.instance().property("controls")
            manager.signal_tools_cambio.emit("PQRSs")
            manager.signal_filter_notifi.emit(True)
    
    def find_denuncias(self):
        if self.hay_busqueda[0]:
            self.cambiaPagina("tools")
            manager = QApplication.instance().property("controls")
            manager.signal_tools_cambio.emit("Denuncias")
            manager.signal_filter_notifi.emit(False)
