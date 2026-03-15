from PySide6.QtWidgets import (
    QWidget, QHBoxLayout, QVBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt
from PySide6.QtGui import QPixmap
from core.app_config import (
    DOMINIO_EMAIL
)
from ui.info_menus import InfoMenus
from components.txt_edit import TxtEdit
from components.boton import Boton
from components.alerta import Alerta

class LoginWindow(QWidget):

    def __init__(self):
        super().__init__()

        image = QPixmap("assets/sprites/logo.png")
        logo = QLabel()
        logo.setPixmap(image)
        logo.setScaledContents(True)
        logo.setFixedSize(96, 96)
        layLogo = QHBoxLayout()
        layLogo.addStretch()
        layLogo.addWidget(logo)
        layLogo.addStretch()

        titulo = QLabel("Tu Mercado Sena")
        font = titulo.font()
        font.setPointSize(32)
        font.setBold(True)
        titulo.setFont(font)
        titulo.setAlignment(Qt.AlignCenter)

        descripcion = QLabel("Sistema administrativo")
        font = descripcion.font()
        font.setPointSize(16)
        descripcion.setFont(font)
        descripcion.setAlignment(Qt.AlignCenter)

        self.textEmail = TxtEdit("Email Institucional", "email_administrador" + DOMINIO_EMAIL)
        
        self.textPassword = TxtEdit("Contraseña", "******")
        self.textPassword.passwordMode()

        btnIngresar = Boton("  Ingresar", "login", 20)
        btnIngresar.clicked.connect(self.login)

        layCentro = QVBoxLayout()
        layCentro.addSpacing(40)
        layCentro.addLayout(layLogo)
        layCentro.addSpacing(5)
        layCentro.addWidget(titulo)
        layCentro.addSpacing(5)
        layCentro.addWidget(descripcion)
        layCentro.addSpacing(10)
        layCentro.addWidget(self.textEmail)
        layCentro.addSpacing(5)
        layCentro.addWidget(self.textPassword)
        layCentro.addSpacing(5)
        layCentro.addWidget(btnIngresar)
        layCentro.addSpacing(100)
        layCentro.addStretch()

        widget = QWidget()
        widget.setLayout(layCentro)
        central = InfoMenus(widget, False)
        layCentral = QHBoxLayout()
        layCentral.addWidget(central)
        self.setLayout(layCentral)

    def login(self):
        email = self.textEmail.get_value()
        password = self.textPassword.get_value()
        if email == "" or password == "":
            Alerta("Alerta", "Ingrese sus credenciales", 1)
        else:
            manager = QApplication.instance().property("controls")
            ctrlUsuario = manager.get_usuarios()
            result = ctrlUsuario.admin_login(email, password)
            if result["token"] != "":
                manager = QApplication.instance().property("manager")
                manager.set_tools(result["token"], email, result["id"])
            elif result["error"] != "":
                Alerta("Alerta", result["error"], 1)
