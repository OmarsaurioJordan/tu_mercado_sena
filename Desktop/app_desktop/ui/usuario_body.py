from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QGroupBox, QTextEdit
)
from PySide6.QtCore import Qt
from PySide6.QtGui import QPixmap
from core.app_config import DOMINIO_EMAIL
from components.selector import Selector
from components.boton import Boton
from components.txt_edit import TxtEdit

class UsuarioBody(QWidget):
    def __init__(self):
        super().__init__()
        self.id = -1

        self.imagen = QLabel()
        image = QPixmap("assets/sprites/avatar.png")
        self.imagen.setPixmap(image)
        self.imagen.setScaledContents(True)
        self.imagen.setFixedSize(128, 128)
        self.imagen.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.nickname = QLabel("*** ??? ***")
        self.nickname.setWordWrap(True)
        font = self.nickname.font()
        font.setBold(True)
        font.setPointSize(20)
        self.nickname.setFont(font)
        self.nickname.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.email = QLabel("*** email ***")
        self.email.setWordWrap(True)
        self.email.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        laySelectores = QHBoxLayout()
        self.sel_rol = Selector([
            "Prosumer", "Admin"
        ], "rol...", "Rol", 0, "usuario_rol")
        laySelectores.addWidget(self.sel_rol)
        laySelectores.addSpacing(10)
        self.sel_estado = Selector([
            "Activo", "Invisible", "Eliminado", "Bloqueado"
        ], "estado...", "Estado", 0, "usuario_estado")
        laySelectores.addWidget(self.sel_estado)

        self.link = QLabel("*** link ***")
        self.link.setWordWrap(True)
        self.link.setStyleSheet("color: #777777;")
        self.link.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.descripcion = QLabel("*** descripción vacía ***")
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
        layRecup.addWidget(self.btnRecup)
        groupRecuperacion.setLayout(layRecup)

        groupMensaje = QGroupBox("Mensaje")
        layMsj = QVBoxLayout()
        self.mensaje = QTextEdit()
        self.mensaje.setAlignment(Qt.AlignJustify)
        self.mensaje.setPlaceholderText("escribe un texto que será enviado al email del usuario, se agregarán automáticamente cabecera y pie de página con saludo e información del administrador remitente")
        layMsj.addWidget(self.mensaje)
        layMsj.addSpacing(10)
        self.btnMensaje = Boton("Enviar Mensaje")
        layMsj.addWidget(self.btnMensaje)
        groupMensaje.setLayout(layMsj)

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
    
    def labelFechas(self, texto=""):
        label = QLabel(texto)
        label.setStyleSheet("color: #777777;")
        label.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        return label

    def actualiza(self, usuario):
        self.id = usuario.id
        self.email.setText(usuario.email)
        self.nickname.setText(usuario.nickname)
        if usuario.descripcion == "":
            self.descripcion.setText("*** descripción vacía ***")
        else:
            self.descripcion.setText(usuario.descripcion)
        if usuario.link == "":
            self.link.setText("*** link ***")
        else:
            self.link.setText(usuario.link)
        self.registro.setText("Registro\n" + usuario.fecha_registro.replace(" ", "\n"))
        self.edicion.setText("Edición\n" + usuario.fecha_actualiza.replace(" ", "\n"))
        self.actividad.setText("Actividad\n" + usuario.fecha_reciente.replace(" ", "\n"))
        self.sel_rol.set_index(0 if usuario.rol_id == 1 else 1)
        self.sel_estado.set_index(usuario.estado_id - 1)
        self.sel_rol.set_ente_id(usuario.id)
        self.sel_estado.set_ente_id(usuario.id)
        self.set_image(usuario.img_pix)

    def set_image(self, image):
        self.imagen.setPixmap(image)
