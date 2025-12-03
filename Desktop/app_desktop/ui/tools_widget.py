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
        # comienza a instanciar las diferentes pestannas de filtros mas listas
        usuarioFilter = Usuariofilter()
        usuarioBusqueda = UsuarioBusqueda()
        tabsFind.addTab(Buscador(usuarioFilter, usuarioBusqueda), "Usuarios")
        usuarioBusqueda.scroll_at_end.connect(
            lambda: self.rebuscarUsuarios(usuarioBusqueda)
        )
        usuarioBusqueda.card_clic.connect(
            lambda user_id: self.buscarUsuario(user_id, usuarioBody)
        )
        usuarioFilter.clicAplicar.connect(
            lambda filtros: self.buscarUsuarios(filtros, usuarioBusqueda)
        )
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

    def buscarUsuarios(self, filtros, widgetResultado):
        self.ctrlUsuario.limpiar()
        usuarios = self.ctrlUsuario.get_usuarios(filtros=filtros)
        widgetResultado.eliminar_usuarios()
        widgetResultado.cargar_usuarios(usuarios)
    
    def rebuscarUsuarios(self, widgetResultado):
        usuarios = self.ctrlUsuario.get_usuarios(rebusqueda=True)
        widgetResultado.cargar_usuarios(usuarios)

    def buscarUsuario(self, user_id, widgetResultado):
        usuario = self.ctrlUsuario.get_usuario(user_id)
        widgetResultado.actualiza(usuario)
