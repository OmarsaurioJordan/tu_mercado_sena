from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QPushButton, QApplication
)
from PySide6.QtCore import Qt, QSize
from PySide6.QtGui import QPixmap, QIcon
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
        
        notifica_pqrss = Boton("PQRS: 0","bell")
        notifica_denuncias = Boton("Denuncias: 0", "bell")

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
        layHorizontal.addWidget(notifica_pqrss)
        layHorizontal.addSpacing(10)
        layHorizontal.addWidget(notifica_denuncias)
        layHorizontal.addSpacing(10)
        layHorizontal.addStretch()
        layHorizontal.addWidget(UsuarioCard(admin))
        self.addWidget(header)
        
        if widget is None:
            self.addWidget(QLabel())
        else:
            self.addWidget(widget)

    def usuario_debug(self):
        return Usuario(0, "email_administrativo@sena.edu.co", 3, "Usuario Administrador", 0, "", "", 1, "", "", "")

    def cambiaPagina(self, pagina=""):
        manager = QApplication.instance().property("manager")
        manager.change_tool(pagina)
