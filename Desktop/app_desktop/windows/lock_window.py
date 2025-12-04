from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel
)
from PySide6.QtCore import Qt
from ui.header_layout import HeaderLayout
from components.txt_edit import TxtEdit
from components.boton import Boton
from ui.info_menus import InfoMenus

class LockWindow(QWidget):

    def __init__(self):
        super().__init__()

        titulo = QLabel("Bloqueo por PIN")
        font = titulo.font()
        font.setPointSize(20)
        font.setBold(True)
        titulo.setFont(font)
        titulo.setAlignment(Qt.AlignCenter)

        self.textPassword = TxtEdit("PIN de seguridad", "******")
        self.textPassword.passwordMode()

        btnIngresar = Boton("  Reingresar", "login", 20)
        btnIngresar.clicked.connect(self.ingresar)

        layCentro = QVBoxLayout()
        layCentro.addSpacing(40)
        layCentro.addWidget(titulo)
        layCentro.addSpacing(10)
        layCentro.addWidget(self.textPassword)
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
        pass
