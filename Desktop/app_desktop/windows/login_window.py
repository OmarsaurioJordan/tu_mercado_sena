from PySide6.QtWidgets import (
    QMainWindow, QWidget, QHBoxLayout, QVBoxLayout, QLineEdit, QLabel, QMessageBox, QApplication
)
from PySide6.QtCore import Qt, QUrl
from PySide6.QtGui import QPixmap, QDesktopServices
from core.app_config import (
    DOMINIO_CORREO, VERSION, WEB_LINK
)
from components.txt_edit import TxtEdit
from components.boton import Boton
from controllers.ctrl_usuario import CtrlUsuario
from components.alerta import Alerta
from core.session import Session

class LoginWindow(QMainWindow):

    def __init__(self):
        super().__init__()
        self.setWindowTitle("TuMercadoSena-Desktop")
        self.resize(800, 600)

        self.ctrlUsuario = CtrlUsuario()

        version = QLabel("v" + VERSION)
        descripcion = QLabel(self.ctrlUsuario.get_master_info())

        layIzq = QVBoxLayout()
        layIzq.addWidget(version)
        layIzq.addStretch()
        layIzq.addWidget(descripcion)

        btnWeb = Boton("Página\nWeb", "logo", 48)
        btnWeb.clicked.connect(lambda: QDesktopServices.openUrl(QUrl(WEB_LINK)))

        layDer = QVBoxLayout()
        layDer.addStretch()
        layDer.addWidget(btnWeb)

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

        self.textCorreo = TxtEdit("Correo Institucional", "correo_administrador" + DOMINIO_CORREO)
        
        self.textPassword = TxtEdit("Contraseña", "******")
        self.textPassword.passwordMode()

        btnIngresar = Boton("Ingresar")
        btnIngresar.clicked.connect(self.login)

        layCentro = QVBoxLayout()
        layCentro.addSpacing(40)
        layCentro.addLayout(layLogo)
        layCentro.addSpacing(5)
        layCentro.addWidget(titulo)
        layCentro.addSpacing(5)
        layCentro.addWidget(descripcion)
        layCentro.addSpacing(10)
        layCentro.addWidget(self.textCorreo)
        layCentro.addSpacing(5)
        layCentro.addWidget(self.textPassword)
        layCentro.addSpacing(5)
        layCentro.addWidget(btnIngresar)
        layCentro.addSpacing(100)
        layCentro.addStretch()

        layFondo = QHBoxLayout()
        layFondo.addLayout(layIzq)
        layFondo.addStretch()
        layFondo.addLayout(layCentro)
        layFondo.addStretch()
        layFondo.addLayout(layDer)
        layFondo.setContentsMargins(20, 20, 20, 20)

        central = QWidget()
        central.setLayout(layFondo)
        self.setCentralWidget(central)

    def login(self):
        correo = self.textCorreo.get_value()
        password = self.textPassword.get_value()
        if correo == "" or password == "":
            Alerta("Alerta", "Ingrese sus credenciales", 1)
        else:
            result = self.ctrlUsuario.admin_login(correo, password)
            if result["token"] != "":
                manager = QApplication.instance().property("manager")
                manager.set_tools(result["token"], correo)
            elif result["error"] != "":
                Alerta("Alerta", result["error"], 1)
