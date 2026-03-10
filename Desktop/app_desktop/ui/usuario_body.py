from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QGroupBox, QTextEdit, QApplication, QMessageBox
)
from PySide6.QtCore import Qt, Signal
from PySide6.QtGui import QPixmap
from core.app_config import (DOMINIO_EMAIL, MSJ_MAX, DB_NOTIFI_MSJ_MAX)
from components.selector import Selector
from components.boton import Boton
from components.alerta import Alerta
from core.session import Session

class UsuarioBody(QWidget):
    cambioData = Signal(int) # id usuario

    def __init__(self):
        super().__init__()
        self.id = 0
        self.catalogo = None
        self.chats = None
        self.papelera = None

        self.ctrlCatalogo = QApplication.instance().property("controls").get_catalogo()
        self.ctrlChat = QApplication.instance().property("controls").get_chats()
        self.ctrlPapelera = QApplication.instance().property("controls").get_papelera()

        ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
        ctrlUsuario.usuario_signal.hubo_cambio.connect(self.actualizar)

        ctrlData = QApplication.instance().property("controls").get_data()

        self.imagen = QLabel(self)
        self.imagen.setPixmap(QPixmap("assets/sprites/avatar.png"))
        self.imagen.setScaledContents(True)
        self.imagen.setFixedSize(192, 192)
        self.imagen.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.nickname = QLabel("", self)
        self.nickname.setWordWrap(True)
        font = self.nickname.font()
        font.setBold(True)
        font.setPointSize(20)
        self.nickname.setFont(font)
        self.nickname.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.email = QLabel("", self)
        self.email.setWordWrap(True)
        self.email.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        laySelectores = QHBoxLayout()
        self.sel_rol = Selector(ctrlData.get_roles_basicos(),
            "rol...", "Rol", 0, "usuario_rol")
        laySelectores.addWidget(self.sel_rol)
        laySelectores.addSpacing(10)
        self.sel_estado = Selector(ctrlData.get_estados_basicos(),
            "estado...", "Estado", 0, "usuario_estado")
        laySelectores.addWidget(self.sel_estado)

        self.link = QLabel("", self)
        self.link.setWordWrap(True)
        self.link.setStyleSheet("color: #777777;")
        self.link.setOpenExternalLinks(True)
        self.link.setTextFormat(Qt.RichText)
        self.link.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.descripcion = QLabel("", self)
        self.descripcion.setWordWrap(True)
        self.descripcion.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        layFechas = QHBoxLayout()
        self.registro = self.labelFechas("Registro")
        layFechas.addWidget(self.registro)
        layFechas.addSpacing(10)
        self.edicion = self.labelFechas("Edición")
        layFechas.addWidget(self.edicion)
        layFechas.addSpacing(10)
        self.actividad = self.labelFechas("Actividad")
        layFechas.addWidget(self.actividad)

        groupRecuperacion = QGroupBox("Recuperación")
        layRecup = QVBoxLayout()
        self.email_recup = QLineEdit()
        self.email_recup.setAlignment(Qt.AlignCenter)
        self.email_recup.setPlaceholderText("email_usuario" + DOMINIO_EMAIL)
        layRecup.addWidget(self.email_recup)
        layRecup.addSpacing(10)
        self.btnRecup = Boton("Recuperar clave")
        self.btnRecup.clicked.connect(self.enviaRecuperacion)
        layRecup.addWidget(self.btnRecup)
        groupRecuperacion.setLayout(layRecup)

        groupMensaje = QGroupBox("Mensaje")
        layMsj = QVBoxLayout()
        self.mensaje = QTextEdit()
        self.mensaje.setAlignment(Qt.AlignJustify)
        self.mensaje.setPlaceholderText("escribe un texto que será enviado al email del usuario, se agregarán automáticamente un pie de página con información del administrador remitente y contexto a Tu Mercado Sena")
        self.mensaje.textChanged.connect(self.limitar_mensaje)
        layMsj.addWidget(self.mensaje)
        layMsj.addSpacing(10)
        self.btnMensaje = Boton("Enviar Mensaje")
        self.btnMensaje.clicked.connect(self.enviarMensaje)
        layMsj.addWidget(self.btnMensaje)
        groupMensaje.setLayout(layMsj)
        self.texto_msg = ""

        layVertical = QVBoxLayout()
        layVertical.addSpacing(10)
        layVertical.addWidget(self.imagen, alignment=Qt.AlignCenter)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.nickname)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.email)
        layVertical.addSpacing(10)
        layVertical.addLayout(laySelectores)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.link)
        layVertical.addSpacing(20)
        layVertical.addWidget(self.descripcion)
        layVertical.addSpacing(20)
        layVertical.addLayout(layFechas)
        layVertical.addSpacing(20)
        layVertical.addWidget(groupRecuperacion)
        layVertical.addSpacing(20)
        layVertical.addWidget(groupMensaje)
        layVertical.addStretch()
        self.setLayout(layVertical)
        self.resetData()
    
    def labelFechas(self, texto=""):
        label = QLabel(texto)
        label.setStyleSheet("color: #777777;")
        label.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        return label
    
    def buscarCatalogo(self):
        print("UsuarioBody: buscarCatalogo")
        filtros = { "vendedor_id": self.id }
        self.ctrlCatalogo.do_busqueda(filtros=filtros)
    
    def buscarChats(self):
        print("UsuarioBody: buscarChats")
        filtros = { "comprador_id": self.id, "vendedor_id": self.id }
        self.ctrlChat.do_busqueda(filtros=filtros)
    
    def buscarPapelera(self):
        print("UsuarioBody: buscarPapelera")
        filtros = { "usuario_id": self.id }
        self.ctrlPapelera.do_busqueda(filtros=filtros)

    def enviaRecuperacion(self):
        self.email_recup.setText("")
        Alerta("Información", "Esta funcionalidad aún no está disponible", 2)

    def resetData(self):
        print(f"UsuarioBody {self.id}: resetData")
        self.id = 0
        self.email.setText("*** email ***")
        self.nickname.setText("*** ??? ***")
        self.descripcion.setText("*** descripción vacía ***")
        self.link.setText("*** link ***")
        self.registro.setText("Registro")
        self.edicion.setText("Edición")
        self.actividad.setText("Actividad")
        self.mensaje.setText("")
        self.email_recup.setText("")
        self.sel_rol.set_index(0)
        self.sel_estado.set_index(0)
        self.sel_rol.set_ente_id(0)
        self.sel_estado.set_ente_id(0)
        self.imagen.setPixmap(QPixmap("assets/sprites/avatar.png"))
        # eliminar estructuras internas
        if self.catalogo:
            self.ctrlCatalogo.limpiar()
            self.catalogo.eliminar_items()
        if self.chats:
            self.ctrlChat.limpiar()
            self.chats.eliminar_items()
        if self.papelera:
            self.ctrlPapelera.limpiar()
            self.papelera.eliminar_items()
        # llamar a la signal
        self.cambioData.emit(0)

    def setData(self, usuario):
        if usuario is None:
            self.resetData()
            return
        print(f"UsuarioBody {usuario.id}: setData")
        self.id = usuario.id
        self.email.setText(usuario.email)
        self.nickname.setText(usuario.nickname)
        self.cambioData.emit(usuario.id)
        if usuario.descripcion == "":
            self.descripcion.setText("*** descripción vacía ***")
        else:
            self.descripcion.setText(usuario.descripcion)
        if usuario.link == "":
            self.link.setText("*** link ***")
        else:
            self.link.setText(f'<a href="{usuario.link}">{usuario.link}</a>')
        self.registro.setText("Registro\n" + usuario.fecha_registro.replace(" ", "\n"))
        self.edicion.setText("Edición\n" + usuario.fecha_actualiza.replace(" ", "\n"))
        self.actividad.setText("Actividad\n" + usuario.fecha_reciente.replace(" ", "\n"))
        self.sel_rol.set_index_from_data(usuario.rol_id)
        self.sel_estado.set_index_from_data(usuario.estado_id)
        self.sel_rol.set_ente_id(usuario.id)
        self.sel_estado.set_ente_id(usuario.id)
        self.imagen.setPixmap(usuario.img_pix)
        self.mensaje.setText("")
        self.email_recup.setText("")
        # crear estructuras internas
        if self.catalogo:
            self.ctrlCatalogo.limpiar()
            self.catalogo.eliminar_items()
            self.buscarCatalogo()
        if self.chats:
            self.ctrlChat.limpiar()
            self.chats.eliminar_items()
            self.buscarChats()
        if self.papelera:
            self.ctrlPapelera.limpiar()
            self.papelera.eliminar_items()
            self.buscarPapelera()

    def actualizar(self, id=0):
        if self.id == 0 or id != self.id:
            return
        print(f"UsuarioBody {id}: actualizar")
        ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
        self.setData(ctrlUsuario.get_usuario(id))
    
    def set_sombrear_catalogo(self, item_id=0):
        if self.catalogo:
            self.catalogo.set_sombrear(item_id)
    
    def set_sombrear_chats(self, item_id=0):
        if self.chats:
            self.chats.set_sombrear(item_id)
    
    def set_sombrear_papelera(self, item_id=0):
        if self.papelera:
            self.papelera.set_sombrear(item_id)

    def set_from_producto(self, producto_id=0):
        if producto_id != 0:
            print(f"UsuarioBody {self.id} - {producto_id}: set_from_producto")
            ctrlProducto = QApplication.instance().property("controls").get_productos()
            producto = ctrlProducto.get_producto(producto_id)
            if producto is not None:
                if self.id != producto.vendedor_id:
                    ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
                    usuario = ctrlUsuario.get_usuario(producto.vendedor_id)
                    self.setData(usuario)
    
    def set_from_pqrs(self, pqrs_id=0):
        if pqrs_id != 0:
            print(f"UsuarioBody {self.id} - {pqrs_id}: set_from_pqrs")
            ctrlPqrs = QApplication.instance().property("controls").get_pqrss()
            pqrs = ctrlPqrs.get_pqrs(pqrs_id)
            if pqrs is not None:
                if self.id != pqrs.usuario_id:
                    ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
                    usuario = ctrlUsuario.get_usuario(pqrs.usuario_id)
                    self.setData(usuario)
    
    def set_from_denuncia(self, denu_id=0):
        if denu_id != 0:
            print(f"UsuarioBody {self.id} - {denu_id}: set_from_denuncia")
            ctrlPqrs = QApplication.instance().property("controls").get_denuncias()
            denuncia = ctrlPqrs.get_denuncia(denu_id)
            if denuncia is not None:
                if self.id != denuncia.denunciante_id and self.id != denuncia.usuario_id:
                    ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
                    usuario = ctrlUsuario.get_usuario(denuncia.denunciante_id)
                    self.setData(usuario)
    
    def set_from_chat(self, chat_id=0):
        if chat_id != 0:
            print(f"UsuarioBody {self.id} - {chat_id}: set_from_chat")
            ctrlChat = QApplication.instance().property("controls").get_chats()
            chat = ctrlChat.get_chat(chat_id)
            if chat is not None:
                if self.id != chat.comprador_id and self.id != chat.vendedor_id:
                    ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
                    usuario = ctrlUsuario.get_usuario(chat.comprador_id)
                    self.setData(usuario)

    def enviarMensaje(self):
        self.texto_msg = self.mensaje.toPlainText()
        if self.texto_msg != "" and self.id != 0:
            ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
            ses = Session()
            admindata = ses.get_login()
            admin = ctrlUsuario.get_usuario(admindata["id"])
            admin_info = "Usuario Administrador - email_administrativo" + DOMINIO_EMAIL
            if admin is not None:
                admin_info = admin.nickname + " - " + admin.email
            self.texto_msg += "\n\n*** Tu Mercado Sena ***\n\nEste es un mensaje administrativo enviado por: " + admin_info
            resp = QMessageBox.question(self, "Confirmación", "¿Desea enviar el mensaje al usuario?")
            if resp == QMessageBox.Yes:
                print("UsuarioBody: enviarMensaje")
                self.mensaje.setText("")
                if len(self.texto_msg) > DB_NOTIFI_MSJ_MAX:
                    self.texto_msg = self.texto_msg[:DB_NOTIFI_MSJ_MAX]
                ctrlUsuario.send_message(self.id, self.texto_msg, 10) # msj administrativo

    def limitar_mensaje(self):
        text = self.mensaje.toPlainText()
        if len(text) > MSJ_MAX:
            self.mensaje.blockSignals(True)
            self.mensaje.setPlainText(text[:MSJ_MAX])
            self.mensaje.blockSignals(False)
            cursor = self.mensaje.textCursor()
            cursor.movePosition(cursor.End)
            self.mensaje.setTextCursor(cursor)