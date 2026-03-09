from PySide6.QtWidgets import (
    QWidget, QTabWidget, QHBoxLayout, QApplication
)
from components.scroll import Scroll
from components.buscador import Buscador
from ui.result_busqueda import ResultBusqueda
from ui.usuario_body import UsuarioBody
from ui.login_filter import LoginFilter
from ui.auditoria_filter import AuditoriaFilter

class AuditoriasWidget(QWidget):

    def __init__(self):
        super().__init__()

        manager = QApplication.instance().property("controls")
        self.ctrlUsuario = manager.get_usuarios()
        self.ctrlAuditoria = manager.get_auditorias()
        self.ctrlLogins = manager.get_logins()

        tabs = QTabWidget()
        usuarioBody = UsuarioBody()
        tabs.addTab(Scroll(usuarioBody), "Usuario")

        tabsFind = QTabWidget()
        # estructura de la busqueda de auditorias
        auditoriaFilter = AuditoriaFilter()
        auditoriaBusqueda = ResultBusqueda("auditorias")
        tabsFind.addTab(Buscador(auditoriaFilter, auditoriaBusqueda), "Auditorías")
        auditoriaFilter.clicAplicar.connect(
            lambda filtros: self.buscarAuditorias(filtros, auditoriaBusqueda, usuarioBody)
        )
        auditoriaBusqueda.scroll_at_end.connect(
            lambda: self.rebuscarAuditorias()
        )
        auditoriaBusqueda.card_clic.connect(
            lambda user_id: self.buscarUsuario(user_id, usuarioBody)
        )
        # estructura de la busqueda de productos
        loginFilter = LoginFilter()
        loginBusqueda = ResultBusqueda("logins")
        tabsFind.addTab(Buscador(loginFilter, loginBusqueda), "Logins")
        loginFilter.clicAplicar.connect(
            lambda filtros: self.buscarLogins(filtros, loginBusqueda, usuarioBody)
        )
        loginBusqueda.scroll_at_end.connect(
            lambda: self.rebuscarLogins()
        )
        loginBusqueda.card_clic.connect(
            lambda user_id: self.buscarUsuario(user_id, usuarioBody)
        )

        # sombreado de fichas seleccionadas
        usuarioBody.cambioData.connect(auditoriaBusqueda.set_sombrear)
        usuarioBody.cambioData.connect(loginBusqueda.set_sombrear)

        # colocar todo en layout principal
        layFondoTres = QHBoxLayout()
        layFondoTres.setSpacing(10)
        layFondoTres.setContentsMargins(10, 10, 10, 10)
        layFondoTres.addWidget(tabsFind)
        layFondoTres.addWidget(tabs)
        layFondoTres.setStretch(0, 3)
        layFondoTres.setStretch(1, 1)
        self.setLayout(layFondoTres)

    # usuarios

    def buscarUsuario(self, user_id, widgetResultado):
        print(f"AuditoriasWidget {user_id}: buscarUsuario")
        usuario = self.ctrlUsuario.get_usuario(user_id)
        widgetResultado.setData(usuario)
        self.select_tab("Usuario")
    
    # auditorias

    def buscarAuditorias(self, filtros, widgetResultado, widgetReset):
        print("AuditoriasWidget: buscarAuditorias")
        widgetResultado.eliminar_items()
        widgetReset.resetData()
        self.ctrlAuditoria.do_busqueda(filtros=filtros)
    
    def rebuscarAuditorias(self):
        print("AuditoriasWidget: rebuscarAuditorias")
        self.ctrlAuditoria.do_busqueda(rebusqueda=True)
    
    # logins

    def buscarLogins(self, filtros, widgetResultado, widgetReset):
        print("AuditoriasWidget: buscarLogins")
        widgetResultado.eliminar_items()
        widgetReset.resetData()
        self.ctrlLogins.do_busqueda(filtros=filtros)
    
    def rebuscarLogins(self):
        print("AuditoriasWidget: rebuscarLogins")
        self.ctrlLogins.do_busqueda(rebusqueda=True)
