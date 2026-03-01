from PySide6.QtWidgets import (
    QWidget, QTabWidget, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt
from components.scroll import Scroll
from components.buscador import Buscador
from ui.result_busqueda import ResultBusqueda
from ui.usuario_body import UsuarioBody
from ui.usuario_filter import UsuarioFilter
from ui.producto_body import ProductoBody
from ui.producto_filter import ProductoFilter
from ui.pqrs_body import PqrsBody
from ui.pqrs_filter import PqrsFilter
from ui.denuncia_body import DenunciaBody
from ui.denuncia_filter import DenunciaFilter

class ToolsWidget(QWidget):

    def __init__(self):
        super().__init__()

        manager = QApplication.instance().property("controls")
        self.ctrlUsuario = manager.get_usuarios()
        self.ctrlProducto = manager.get_productos()
        self.ctrlPqrs = manager.get_pqrss()
        self.ctrlDenuncia = manager.get_denuncias()

        tabsA = QTabWidget()
        productoBody = ProductoBody()
        tabsA.addTab(Scroll(productoBody), "Producto")
        pqrsBody = PqrsBody()
        tabsA.addTab(Scroll(pqrsBody), "PQRS")
        denunciaBody = DenunciaBody()
        tabsA.addTab(Scroll(denunciaBody), "Denuncia")
        tabsA.addTab(Scroll(), "Chat")
        tabsA.addTab(Scroll(), "Auditoría")

        tabsB = QTabWidget()
        usuarioBody = UsuarioBody()
        tabsB.addTab(Scroll(usuarioBody), "Usuario")
        tabsB.addTab(Scroll(), "Catálogo")
        tabsB.addTab(Scroll(), "Papelera")
        tabsB.addTab(Scroll(), "Historial")

        self.tabs = [tabsA, tabsB]

        # conectar otras fichas
        denunciaBody.card_usuario_clic.connect(
            lambda user_id: self.buscarUsuario(user_id, usuarioBody)
        )
        denunciaBody.card_producto_clic.connect(
            lambda prod_id: self.buscarProducto(prod_id, productoBody)
        )

        tabsFind = QTabWidget()
        # estructura de la busqueda de usuarios
        usuarioFilter = UsuarioFilter()
        usuarioBusqueda = ResultBusqueda("usuarios")
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
        productoFilter = ProductoFilter()
        productoBusqueda = ResultBusqueda("productos")
        tabsFind.addTab(Buscador(productoFilter, productoBusqueda), "Productos")
        productoFilter.clicAplicar.connect(
            lambda filtros: self.buscarProductos(filtros, productoBusqueda, productoBody)
        )
        productoBusqueda.scroll_at_end.connect(
            lambda: self.rebuscarProductos()
        )
        productoBusqueda.card_clic.connect(
            lambda prod_id: self.buscarProducto(prod_id, productoBody)
        )
        # estructura de la busqueda de PQRSs
        pqrsFilter = PqrsFilter()
        pqrsBusqueda = ResultBusqueda("pqrss")
        tabsFind.addTab(Buscador(pqrsFilter, pqrsBusqueda), "PQRSs")
        pqrsFilter.clicAplicar.connect(
            lambda filtros: self.buscarPqrss(filtros, pqrsBusqueda, pqrsBody)
        )
        pqrsBusqueda.scroll_at_end.connect(
            lambda: self.rebuscarPqrss()
        )
        pqrsBusqueda.card_clic.connect(
            lambda pqrs_id: self.buscarPqrs(pqrs_id, pqrsBody)
        )
        # estructura de la busqueda de denuncias
        denunciaFilter = DenunciaFilter()
        denunciaBusqueda = ResultBusqueda("denuncias")
        tabsFind.addTab(Buscador(denunciaFilter, denunciaBusqueda), "Denuncias")
        denunciaFilter.clicAplicar.connect(
            lambda filtros: self.buscarDenuncias(filtros, denunciaBusqueda, denunciaBody)
        )
        denunciaBusqueda.scroll_at_end.connect(
            lambda: self.rebuscarDenuncias()
        )
        denunciaBusqueda.card_clic.connect(
            lambda den_id: self.buscarDenuncia(den_id, denunciaBody)
        )
        # estructura de la busqueda de chats
        tabsFind.addTab(Buscador(), "Chats")
        # estructura de la busqueda de auditorias
        tabsFind.addTab(Buscador(), "Auditorías")

        # sombreado de fichas seleccionadas
        usuarioBody.cambioData.connect(usuarioBusqueda.set_sombrear)
        productoBody.cambioData.connect(productoBusqueda.set_sombrear)
        pqrsBody.cambioData.connect(pqrsBusqueda.set_sombrear)
        denunciaBody.cambioData.connect(denunciaBusqueda.set_sombrear)

        # cuando cambia usuario
        usuarioBody.cambioData.connect(productoBody.set_is_seleccionado)
        usuarioBody.cambioData.connect(pqrsBody.set_is_seleccionado)
        usuarioBody.cambioData.connect(denunciaBody.set_is_seleccionado)
        # cuando cambia producto
        productoBody.cambioData.connect(usuarioBody.set_from_producto)
        productoBody.cambioData.connect(denunciaBody.set_is_producto)
        # cuando cambia PQRS
        pqrsBody.cambioData.connect(usuarioBody.set_from_pqrs)

        # colocar todo en layout principal
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

    def select_tab(self, target=""):
        print(f"ToolsWidget: select_tab {target}")
        for tabs in (self.tabs[0], self.tabs[1]):
            for idx in range(tabs.count()):
                text = tabs.tabText(idx)
                if text == target:
                    tabs.setCurrentIndex(idx)
                    return True
        return False

    def buscarUsuarios(self, filtros, widgetResultado, widgetReset):
        print("ToolsWidget: buscarUsuarios")
        widgetResultado.eliminar_items()
        widgetReset.resetData()
        self.ctrlUsuario.do_busqueda(filtros=filtros)
    
    def rebuscarUsuarios(self):
        print("ToolsWidget: rebuscarUsuarios")
        self.ctrlUsuario.do_busqueda(rebusqueda=True)

    def buscarUsuario(self, user_id, widgetResultado):
        print(f"ToolsWidget {user_id}: buscarUsuario")
        usuario = self.ctrlUsuario.get_usuario(user_id)
        widgetResultado.setData(usuario)
        self.select_tab("Usuario")
    
    def buscarProductos(self, filtros, widgetResultado, widgetReset):
        print("ToolsWidget: buscarProductos")
        widgetResultado.eliminar_items()
        widgetReset.resetData()
        self.ctrlProducto.do_busqueda(filtros=filtros)
    
    def rebuscarProductos(self):
        print("ToolsWidget: rebuscarProductos")
        self.ctrlProducto.do_busqueda(rebusqueda=True)

    def buscarProducto(self, prod_id, widgetResultado):
        print(f"ToolsWidget {prod_id}: buscarProducto")
        producto = self.ctrlProducto.get_producto(prod_id)
        widgetResultado.setData(producto)
        self.select_tab("Producto")
    
    def buscarPqrss(self, filtros, widgetResultado, widgetReset):
        print("ToolsWidget: buscarPqrss")
        widgetResultado.eliminar_items()
        widgetReset.resetData()
        self.ctrlPqrs.do_busqueda(filtros=filtros)
    
    def rebuscarPqrss(self):
        print("ToolsWidget: rebuscarPqrss")
        self.ctrlPqrs.do_busqueda(rebusqueda=True)

    def buscarPqrs(self, pqrs_id, widgetResultado):
        print(f"ToolsWidget {pqrs_id}: buscarPqrs")
        pqrs = self.ctrlPqrs.get_pqrs(pqrs_id)
        widgetResultado.setData(pqrs)
        self.select_tab("PQRS")

    def buscarDenuncias(self, filtros, widgetResultado, widgetReset):
        print("ToolsWidget: buscarDenuncias")
        widgetResultado.eliminar_items()
        widgetReset.resetData()
        self.ctrlDenuncia.do_busqueda(filtros=filtros)
    
    def rebuscarDenuncias(self):
        print("ToolsWidget: rebuscarDenuncias")
        self.ctrlDenuncia.do_busqueda(rebusqueda=True)

    def buscarDenuncia(self, den_id, widgetResultado):
        print(f"ToolsWidget {den_id}: buscarDenuncia")
        denuncia = self.ctrlDenuncia.get_denuncia(den_id)
        widgetResultado.setData(denuncia)
        self.select_tab("Denuncia")
