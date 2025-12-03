from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import QUrl
from core.app_config import (
    VERSION, WEB_LINK
)
from components.boton import Boton

class InfoMenus(QWidget):

    def __init__(self, widget=None, show_salir=True):
        super().__init__()

        manager = QApplication.instance().property("controls")
        ctrlUsuario = manager.get_usuarios()

        version = QLabel("v" + VERSION)
        descripcion = QLabel(ctrlUsuario.get_master_info())

        layIzq = QVBoxLayout()
        layIzq.addWidget(version)
        layIzq.addStretch()
        layIzq.addWidget(descripcion)

        btnWeb = Boton("Página\nWeb", "logo", 48)
        btnWeb.clicked.connect(lambda: QDesktopServices.openUrl(QUrl(WEB_LINK)))

        if show_salir:
            btnSalir = Boton("   Salir", "logout", 20)
            btnSalir.clicked.connect(self.logout)

        layDer = QVBoxLayout()
        if show_salir:
            layDer.addWidget(btnSalir)
        layDer.addStretch()
        layDer.addWidget(btnWeb)

        if widget == None:
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
        manager = QApplication.instance().property("manager")
        manager.set_login()
