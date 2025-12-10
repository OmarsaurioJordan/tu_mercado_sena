from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt
from ui.header_layout import HeaderLayout
from components.txt_edit import TxtEdit
from components.boton import Boton
from ui.info_menus import InfoMenus
from components.alerta import Alerta
from core.session import Session

class LockWindow(QWidget):

    def __init__(self):
        super().__init__()

        titulo = QLabel("Bloqueo por PIN")
        font = titulo.font()
        font.setPointSize(20)
        font.setBold(True)
        titulo.setFont(font)
        titulo.setAlignment(Qt.AlignCenter)

        self.textPin = TxtEdit("PIN de seguridad", "******")
        self.textPin.passwordMode()

        btnIngresar = Boton("  Reingresar", "login", 20)
        btnIngresar.clicked.connect(self.ingresar)

        layCentro = QVBoxLayout()
        layCentro.addSpacing(40)
        layCentro.addWidget(titulo)
        layCentro.addSpacing(10)
        layCentro.addWidget(self.textPin)
        layCentro.addSpacing(5)
        layCentro.addWidget(btnIngresar)
        layCentro.addSpacing(100)
        layCentro.addStretch()

        menu = QWidget()
        menu.setLayout(layCentro)
        contenido = InfoMenus(menu)

        header = HeaderLayout(contenido, False)
        self.setLayout(header)

    def ingresar(self):
        ses = Session()
        correo = ses.get_login()["correo"]
        pin = self.textPin.get_value()
        self.textPin.set_value("")
        if correo == "":
            manager = QApplication.instance().property("manager")
            manager.set_login()
        else:
            manager = QApplication.instance().property("controls")
            ctrlUsuario = manager.get_usuarios()
            result = ctrlUsuario.admin_pin(correo, pin)
            match result:
                case 0:
                    Alerta("Alerta", "Credenciales inválidos", 1)
                case 1:
                    manager = QApplication.instance().property("manager")
                    manager.change_tool("menu")
                case 2:
                    Alerta("Alerta", "Algo salió mal", 3)
