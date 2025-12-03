from PySide6.QtWidgets import (
    QMainWindow, QWidget, QVBoxLayout, QHBoxLayout
)
from ui.header_layout import HeaderLayout
from components.boton import Boton
from ui.info_menus import InfoMenus

class LockWindow(QMainWindow):

    def __init__(self):
        super().__init__()
        self.setWindowTitle("TuMercadoSena-Desktop")
        self.resize(800, 600)


        layVert = QVBoxLayout()

        menu = QWidget()
        menu.setLayout(layVert)
        contenido = InfoMenus(menu)

        header = HeaderLayout(contenido, False)
        central = QWidget()
        central.setLayout(header)
        self.setCentralWidget(central)
