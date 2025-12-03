from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, QApplication
)
from ui.header_layout import HeaderLayout
from components.boton import Boton
from ui.info_menus import InfoMenus

class MenuWindow(QMainWindow):

    def __init__(self):
        super().__init__()
        self.setWindowTitle("TuMercadoSena-Desktop")
        self.resize(800, 600)

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
        central = QWidget()
        central.setLayout(header)
        self.setCentralWidget(central)
    
    def cambiaPagina(self, pagina=""):
        manager = QApplication.instance().property("manager")
        manager.change_tool(pagina)
