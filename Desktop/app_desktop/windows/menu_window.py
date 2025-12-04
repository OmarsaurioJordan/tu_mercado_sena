from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QApplication
)
from ui.header_layout import HeaderLayout
from components.boton import Boton
from ui.info_menus import InfoMenus

class MenuWindow(QWidget):

    def __init__(self):
        super().__init__()

        btnBloquear = Boton("  Bloquear", "candado", 20)
        btnBloquear.clicked.connect(lambda: self.cambiaPagina("lock"))

        btnStats = Boton("  Estadísticas", "estadisticas", 44)
        btnStats.clicked.connect(lambda: self.cambiaPagina("stats"))
        btnTools = Boton("  Herramientas", "tools", 44)
        btnTools.clicked.connect(lambda: self.cambiaPagina("tools"))
        btnLogins = Boton("  Ingresos", "logins", 44)
        btnLogins.clicked.connect(lambda: self.cambiaPagina("sessions"))
        btnConfig = Boton("  Configuración", "configuracion", 44)
        btnConfig.clicked.connect(lambda: self.cambiaPagina("config"))

        layVert = QVBoxLayout()
        layVert.addWidget(btnBloquear)
        layVert.addStretch()
        layVert.addWidget(btnStats)
        layVert.addWidget(btnTools)
        layVert.addWidget(btnLogins)
        layVert.addWidget(btnConfig)
        layVert.addStretch()

        menu = QWidget()
        menu.setLayout(layVert)
        contenido = InfoMenus(menu)

        header = HeaderLayout(contenido, False)
        self.setLayout(header)
    
    def cambiaPagina(self, pagina=""):
        manager = QApplication.instance().property("manager")
        manager.change_tool(pagina)
