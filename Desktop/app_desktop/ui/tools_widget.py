from PySide6.QtWidgets import (
    QWidget, QTabWidget, QHBoxLayout, QApplication
)
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
from ui.chat_body import ChatBody
from ui.mensaje_filter import MensajeFilter

class ToolsWidget(QWidget):

    def __init__(self):
        super().__init__()

        # obtener los controladores
        manager = QApplication.instance().property("controls")
        self.ctrlUsuario = manager.get_usuarios()
        self.ctrlProducto = manager.get_productos()
        self.ctrlPqrs = manager.get_pqrss()
        self.ctrlDenuncia = manager.get_denuncias()
        self.ctrlMensaje = manager.get_mensajes()
        self.ctrlChat = manager.get_chats()
        self.ctrlCatalogo = manager.get_catalogo()
        self.ctrlPapelera = manager.get_papelera()

        # conectar la signal que cambia tabs
        manager.signal_tools_cambio.connect(self.select_tab)

        # bloque de tabs A
        tabsA = QTabWidget()
        productoBody = ProductoBody()
        tabsA.addTab(Scroll(productoBody), "Producto")
        pqrsBody = PqrsBody()
        tabsA.addTab(Scroll(pqrsBody), "PQRS")
        denunciaBody = DenunciaBody()
        tabsA.addTab(Scroll(denunciaBody), "Denuncia")
        chatBody = ChatBody()
        tabsA.addTab(Scroll(chatBody), "Chat")

        # bloque de tabs B
        tabsB = QTabWidget()
        usuarioBody = UsuarioBody()
        tabsB.addTab(Scroll(usuarioBody), "Usuario")
        usuarioBody.catalogo = ResultBusqueda("items")
        tabsB.addTab(Scroll(usuarioBody.catalogo), "Catálogo")
        usuarioBody.papelera = ResultBusqueda("papelera")
        tabsB.addTab(Scroll(usuarioBody.papelera), "Papelera")
        usuarioBody.chats = ResultBusqueda("chats")
        tabsB.addTab(Scroll(usuarioBody.chats), "Historial")

        # conectar result_buqueda internos de bodys
        usuarioBody.catalogo.scroll_at_end.connect(
            lambda: self.rebuscarCatalogo()
        )
        usuarioBody.catalogo.card_clic.connect(
            lambda prod_id: self.buscarProducto(prod_id, productoBody)
        )
        usuarioBody.chats.scroll_at_end.connect(
            lambda: self.rebuscarChats()
        )
        usuarioBody.chats.card_clic.connect(
            lambda chat_id: self.buscarChat(chat_id, chatBody)
        )
        usuarioBody.papelera.scroll_at_end.connect(
            lambda: self.rebuscarPapelera()
        )

        # conectar fichas internas de bodys
        denunciaBody.card_usuario_clic.connect(
            lambda user_id: self.buscarUsuario(user_id, usuarioBody)
        )
        denunciaBody.card_producto_clic.connect(
            lambda prod_id: self.buscarProducto(prod_id, productoBody)
        )
        denunciaBody.card_chat_clic.connect(
            lambda chat_id: self.buscarChat(chat_id, chatBody)
        )
        pqrsBody.card_clic.connect(
            lambda user_id: self.buscarUsuario(user_id, usuarioBody)
        )
        chatBody.card_usuario_clic.connect(
            lambda user_id: self.buscarUsuario(user_id, usuarioBody)
        )
        chatBody.card_producto_clic.connect(
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
        mensajeFilter = MensajeFilter()
        mensajeBusqueda = ResultBusqueda("mensajes")
        tabsFind.addTab(Buscador(mensajeFilter, mensajeBusqueda), "Chats")
        mensajeFilter.clicAplicar.connect(
            lambda filtros: self.buscarMensajes(filtros, mensajeBusqueda, chatBody)
        )
        mensajeBusqueda.scroll_at_end.connect(
            lambda: self.rebuscarMensajes()
        )
        mensajeBusqueda.card_clic.connect(
            lambda chat_id: self.buscarChat(chat_id, chatBody)
        )

        # agrupar todos los tabs
        self.tabs = [tabsA, tabsB, tabsFind]

        # sombreado de fichas seleccionadas
        usuarioBody.cambioData.connect(usuarioBusqueda.set_sombrear)
        productoBody.cambioData.connect(productoBusqueda.set_sombrear)
        productoBody.cambioData.connect(usuarioBody.set_sombrear_catalogo)
        pqrsBody.cambioData.connect(pqrsBusqueda.set_sombrear)
        denunciaBody.cambioData.connect(denunciaBusqueda.set_sombrear)
        chatBody.cambioData.connect(mensajeBusqueda.set_sombrear)
        chatBody.cambioData.connect(usuarioBody.set_sombrear_chats)

        # cuando cambia usuario
        usuarioBody.cambioData.connect(productoBody.set_is_seleccionado)
        usuarioBody.cambioData.connect(pqrsBody.set_is_seleccionado)
        usuarioBody.cambioData.connect(denunciaBody.set_is_seleccionado)
        usuarioBody.cambioData.connect(chatBody.set_is_seleccionado)
        # cuando cambia producto
        productoBody.cambioData.connect(usuarioBody.set_from_producto)
        productoBody.cambioData.connect(denunciaBody.set_is_producto)
        productoBody.cambioData.connect(chatBody.set_is_producto)
        # cuando cambia PQRS
        pqrsBody.cambioData.connect(usuarioBody.set_from_pqrs)
        # cuando cambia denuncia
        denunciaBody.cambioData.connect(usuarioBody.set_from_denuncia)
        # cuando cambia chat
        chatBody.cambioData.connect(usuarioBody.set_from_chat)

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

    # herramientas de interfaz

    def select_tab(self, target=""):
        print(f"ToolsWidget: select_tab {target}")
        for tabs in (self.tabs[0], self.tabs[1], self.tabs[2]):
            for idx in range(tabs.count()):
                text = tabs.tabText(idx)
                if text == target:
                    tabs.setCurrentIndex(idx)
                    return True
        return False

    # usuarios

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
    
    # productos

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
    
    def rebuscarCatalogo(self):
        print("ToolsWidget: rebuscarCatalogo")
        self.ctrlCatalogo.do_busqueda(rebusqueda=True)
    
    # PQRSs

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
        if pqrs is not None:
            self.ctrlUsuario.get_usuario(pqrs.usuario_id)
        widgetResultado.setData(pqrs)
        self.select_tab("PQRS")

    # denuncias

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
        if denuncia is not None:
            self.ctrlUsuario.get_usuario(denuncia.denunciante_id)
            self.ctrlUsuario.get_usuario(denuncia.usuario_id)
            if denuncia.producto_id != 0:
                self.ctrlProducto.get_producto(denuncia.producto_id)
        widgetResultado.setData(denuncia)
        self.select_tab("Denuncia")
    
    # mensajes

    def buscarMensajes(self, filtros, widgetResultado, widgetReset):
        print("ToolsWidget: buscarMensajes")
        widgetResultado.eliminar_items()
        widgetReset.resetData()
        self.ctrlMensaje.do_busqueda(filtros=filtros)
    
    def rebuscarMensajes(self):
        print("ToolsWidget: rebuscarMensajes")
        self.ctrlMensaje.do_busqueda(rebusqueda=True)

    def buscarChat(self, chat_id, widgetResultado):
        print(f"ToolsWidget {chat_id}: buscarChat")
        chat = self.ctrlChat.get_chat(chat_id)
        widgetResultado.setData(chat)
        self.select_tab("Chat")
    
    def rebuscarChats(self):
        print("ToolsWidget: rebuscarChats")
        self.ctrlChat.do_busqueda(rebusqueda=True)

    # papelera

    def rebuscarPapelera(self):
        print("ToolsWidget: rebuscarPapelera")
        self.ctrlPapelera.do_busqueda(rebusqueda=True)
