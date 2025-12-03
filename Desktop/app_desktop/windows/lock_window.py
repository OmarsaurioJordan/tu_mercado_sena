from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, QLabel
)
from PySide6.QtCore import Qt
from ui.header_layout import HeaderLayout
from components.txt_edit import TxtEdit
from components.boton import Boton
from ui.info_menus import InfoMenus

class LockWindow(QMainWindow):

    def __init__(self):
        super().__init__()
        self.setWindowTitle("TuMercadoSena-Desktop")
        self.resize(800, 600)

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
        central = QWidget()
        central.setLayout(header)
        self.setCentralWidget(central)

    def ingresar(self):
        pass
