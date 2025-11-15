from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QGroupBox, QTextEdit
)
from PySide6.QtCore import Qt
from PySide6.QtGui import QPixmap
from components.selector import Selector
from components.boton import Boton
from components.txt_edit import TxtEdit

class UsuarioBody(QWidget):
    def __init__(self):
        super().__init__()
        self.id = -1

        self.avatar = QLabel()
        image = QPixmap("assets/sprites/avatar.png")
        self.avatar.setPixmap(image)
        self.avatar.setScaledContents(True)
        self.avatar.setFixedSize(128, 128)
        self.avatar.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.nombre = QLabel("Nombre de Usuario")
        self.nombre.setWordWrap(True)
        font = self.nombre.font()
        font.setBold(True)
        font.setPointSize(20)
        self.nombre.setFont(font)
        self.nombre.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.correo = QLabel("correo_usuario@sena.edu.co")
        self.correo.setWordWrap(True)
        self.correo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        laySelectores = QHBoxLayout()
        self.sel_rol = Selector([
            "Prosumer", "Admin"
        ], "rol...", "Rol", 0)
        laySelectores.addWidget(self.sel_rol)
        laySelectores.addSpacing(10)
        self.sel_estado = Selector([
            "Activo", "Invisible", "Eliminado", "Bloqueado"
        ], "estado...", "Estado", 0)
        laySelectores.addWidget(self.sel_estado)

        self.link = QLabel("http://link_red_social_usuario.com")
        self.link.setWordWrap(True)
        self.link.setStyleSheet("color: #777777;")
        self.link.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.descripcion = QLabel(
            "información adicional, un párrafo con lo que el usuario quiera poner"
        )
        self.descripcion.setWordWrap(True)
        self.descripcion.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        layFechas = QHBoxLayout()
        self.registro = self.labelFechas("Registro\ndd/mm/yyyy")
        layFechas.addWidget(self.registro)
        layFechas.addSpacing(10)
        self.edicion = self.labelFechas("Edición\ndd/mm/yyyy")
        layFechas.addWidget(self.edicion)
        layFechas.addSpacing(10)
        self.actividad = self.labelFechas("Actividad\ndd/mm/yyyy")
        layFechas.addWidget(self.actividad)

        groupRecuperacion = QGroupBox("Recuperación")
        layRecup = QVBoxLayout()
        self.correo_recup = QLineEdit()
        self.correo_recup.setAlignment(Qt.AlignCenter)
        self.correo_recup.setPlaceholderText("correo_usuario@sena.edu.co")
        layRecup.addWidget(self.correo_recup)
        layRecup.addSpacing(10)
        self.btnRecup = Boton("Recuperar clave")
        layRecup.addWidget(self.btnRecup)
        groupRecuperacion.setLayout(layRecup)

        groupMensaje = QGroupBox("Mensaje")
        layMsj = QVBoxLayout()
        self.mensaje = QTextEdit()
        self.mensaje.setAlignment(Qt.AlignJustify)
        self.mensaje.setPlaceholderText("escribe un texto que será enviado al correo del usuario, se agregarán automáticamente cabecera y pie de página con saludo e información del administrador remitente")
        layMsj.addWidget(self.mensaje)
        layMsj.addSpacing(10)
        self.btnMensaje = Boton("Enviar Mensaje")
        layMsj.addWidget(self.btnMensaje)
        groupMensaje.setLayout(layMsj)

        layVertical = QVBoxLayout()
        layVertical.addSpacing(10)
        layVertical.addWidget(self.avatar, alignment=Qt.AlignCenter)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.nombre)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.correo)
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

    def actualize(self, usr_id):
        self.id = usr_id
