from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtGui import QDesktopServices
from PySide6.QtCore import QUrl
from core.app_config import VERSION
from components.boton import Boton

class InfoMenus(QWidget):

    def __init__(self, widget=None, show_salir=True):
        super().__init__()

        manager = QApplication.instance().property("controls")
        ctrlUsuario = manager.get_usuarios()

        version = QLabel("v" + VERSION)
        info = ctrlUsuario.get_master_info()
        descripcion = QLabel(info["descripcion"])

        layIzq = QVBoxLayout()
        layIzq.addWidget(version)
        layIzq.addStretch()
        layIzq.addWidget(descripcion)

        btnWeb = Boton("Página\nWeb", "logo", 48)
        btnWeb.clicked.connect(self.open_link)
        self.link = info["link"]

        if show_salir:
            btnSalir = Boton("   Salir", "logout", 20)
            btnSalir.clicked.connect(self.logout)

        layDer = QVBoxLayout()
        if show_salir:
            layDer.addWidget(btnSalir)
        layDer.addStretch()
        layDer.addWidget(btnWeb)

        if widget is None:
            widget = QLabel()

        layFondo = QHBoxLayout()
        layFondo.addLayout(layIzq)
        layFondo.addStretch()
        layFondo.addWidget(widget)
        layFondo.addStretch()
        layFondo.addLayout(layDer)
        layFondo.setContentsMargins(20, 20, 20, 20)

        self.setLayout(layFondo)

    def logout(self):
        print("InfoMenus: logout")
        manager = QApplication.instance().property("manager")
        manager.set_login()

    def open_link(self):
        print("InfoMenus: open_link " + self.link)
        QDesktopServices.openUrl(QUrl(self.link))
