from PySide6.QtWidgets import (
    QWidget, QTabWidget, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt
from components.scroll import Scroll
from components.buscador import Buscador
from ui.usuario_body import UsuarioBody
from ui.usuario_filter import Usuariofilter
from ui.usuario_busqueda import UsuarioBusqueda

class ToolsWidget(QWidget):

    def __init__(self):
        super().__init__()

        manager = QApplication.instance().property("controls")
        self.ctrlUsuario = manager.get_usuarios()

        tabsA = QTabWidget()
        tabsA.addTab(Scroll(), "Producto")
        tabsA.addTab(Scroll(), "PQRS")
        tabsA.addTab(Scroll(), "Denuncia")
        tabsA.addTab(Scroll(), "Chat")
        tabsA.addTab(Scroll(), "Auditoría")

        tabsB = QTabWidget()
        usuarioBody = UsuarioBody()
        tabsB.addTab(Scroll(usuarioBody), "Usuario")
        tabsB.addTab(Scroll(), "Catálogo")
        tabsB.addTab(Scroll(), "Papelera")
        tabsB.addTab(Scroll(), "Historial")

        tabsFind = QTabWidget()
        # estructura de la busqueda de usuarios
        usuarioFilter = Usuariofilter()
        usuarioBusqueda = UsuarioBusqueda()
        tabsFind.addTab(Buscador(usuarioFilter, usuarioBusqueda), "Usuarios")
        usuarioFilter.clicAplicar.connect(
            lambda filtros: self.buscarUsuarios(filtros, usuarioBusqueda, usuarioBody)
        )
        usuarioBusqueda.scroll_at_end.connect(
            lambda: self.rebuscarUsuarios()
        )
        usuarioBusqueda.card_clic.connect(
            lambda user_id: self.buscarUsuario(user_id, usuarioBody)
        )
        # estructura de la busqueda de productos
        tabsFind.addTab(Buscador(), "Productos")
        tabsFind.addTab(Buscador(), "PQRSs")
        tabsFind.addTab(Buscador(), "Denuncias")
        tabsFind.addTab(Buscador(), "Chats")
        tabsFind.addTab(Buscador(), "Auditorías")

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

    def buscarUsuarios(self, filtros, widgetResultado, widgetReset):
        widgetResultado.eliminar_usuarios()
        widgetReset.resetData()
        self.ctrlUsuario.do_busqueda(filtros=filtros)
    
    def rebuscarUsuarios(self):
        self.ctrlUsuario.do_busqueda(rebusqueda=True)

    def buscarUsuario(self, user_id, widgetResultado):
        usuario = self.ctrlUsuario.get_usuario(user_id)
        widgetResultado.setData(usuario)
