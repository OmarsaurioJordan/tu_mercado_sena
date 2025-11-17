from PySide6.QtWidgets import (
    QWidget, QTabWidget, QScrollArea, QHBoxLayout, QLabel
)
from PySide6.QtCore import Qt
from ui.usuario_body import UsuarioBody
from ui.usuario_filter import Usuariofilter
from ui.usuario_busqueda import UsuarioBusqueda

class ToolsWidget(QWidget):

    def __init__(self):
        super().__init__()

        tabsFind = QTabWidget()
        tabsFind.addTab(self.busqueda(Usuariofilter(), UsuarioBusqueda()), "Usuarios")
        tabsFind.addTab(self.busqueda(), "Productos")
        tabsFind.addTab(self.busqueda(), "PQRSs")
        tabsFind.addTab(self.busqueda(), "Denuncias")
        tabsFind.addTab(self.busqueda(), "Chats")
        tabsFind.addTab(self.busqueda(), "Auditorías")

        tabsA = QTabWidget()
        tabsA.addTab(self.scroll(), "Producto")
        tabsA.addTab(self.scroll(), "PQRS")
        tabsA.addTab(self.scroll(), "Denuncia")
        tabsA.addTab(self.scroll(), "Chat")
        tabsA.addTab(self.scroll(), "Auditoría")

        tabsB = QTabWidget()
        tabsB.addTab(self.scroll(UsuarioBody()), "Usuario")
        tabsB.addTab(self.scroll(), "Catálogo")
        tabsB.addTab(self.scroll(), "Papelera")
        tabsB.addTab(self.scroll(), "Historial")

        layFondoTres = QHBoxLayout()
        layFondoTres.setSpacing(10)
        layFondoTres.setContentsMargins(10, 10, 10, 10)
        layFondoTres.addWidget(tabsFind)
        layFondoTres.addWidget(tabsA)
        layFondoTres.addWidget(tabsB)
        layFondoTres.setStretch(0, 6)
        layFondoTres.setStretch(1, 3)
        layFondoTres.setStretch(2, 3)
        self.setLayout(layFondoTres)
    
    def scroll(self, widget=None):
        scroll = QScrollArea()
        scroll.setWidgetResizable(True)
        scroll.setVerticalScrollBarPolicy(Qt.ScrollBarAsNeeded)
        scroll.setHorizontalScrollBarPolicy(Qt.ScrollBarAlwaysOff)
        if widget == None:
            scroll.setWidget(QLabel())
        else:
            scroll.setWidget(widget)
        result = QWidget()
        layMargen = QHBoxLayout(result)
        layMargen.setContentsMargins(10, 10, 10, 10)
        layMargen.addWidget(scroll)
        return result
    
    def busqueda(self, widgetFiltro=None, widgetResultado=None):
        widgetBusqueda = QWidget()
        layBusqueda = QHBoxLayout(widgetBusqueda)
        layBusqueda.setSpacing(10)
        layBusqueda.setContentsMargins(0, 0, 0, 0)
        if widgetFiltro == None:
            widgetFiltro = QLabel()
        if widgetResultado == None:
            widgetResultado = QLabel()
        layBusqueda.addWidget(widgetFiltro, 1)
        layBusqueda.addWidget(self.scroll(widgetResultado), 4)
        return widgetBusqueda
