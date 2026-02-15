from PySide6.QtWidgets import (
    QWidget, QHBoxLayout, QLabel
)
from components.scroll import Scroll

class Buscador(QWidget):

    def __init__(self, widgetFiltro=None, widgetResultado=None):
        super().__init__()
        layBusqueda = QHBoxLayout()
        layBusqueda.setSpacing(10)
        layBusqueda.setContentsMargins(0, 0, 0, 0)
        if widgetFiltro is None:
            widgetFiltro = QLabel()
        if widgetResultado is None:
            widgetResultado = QLabel()
        layBusqueda.addWidget(widgetFiltro, 1)
        self.scroll = Scroll(widgetResultado)
        layBusqueda.addWidget(self.scroll, 4)
        self.setLayout(layBusqueda)
