from PySide6.QtWidgets import (
    QFrame, QWidget, QVBoxLayout, QHBoxLayout, QLabel
)
from PySide6.QtCore import Qt, Signal
from PySide6.QtGui import QPixmap

class UsuarioCard(QFrame):
    card_clic = Signal(int)

    def __init__(self, usuario):
        super().__init__()
        self.id = usuario.id

        self.estado_color = {
            1: "#e6e5e5", # activo
            2: "#D2EDF8", # invisible
            3: "#999898", # eliminado
            4: "#f7d9ac" # bloqueado
        }.get(usuario.estado_id, "#f88eef") # error
        self.setPulsado()

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setMaximumWidth(500)

        self.avatar = QLabel()
        self.avatar.setScaledContents(True)
        self.avatar.setFixedSize(48, 48)
        self.set_image(QPixmap("assets/sprites/avatar.png"))
        usuario.img_signal.ok_image.connect(self.set_image)

        nombre = QLabel(usuario.nombre)
        nombre.setWordWrap(True)
        font = nombre.font()
        font.setBold(True)
        nombre.setFont(font)
        nombre.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        correo = QLabel(usuario.correo.split("@")[0])
        correo.setWordWrap(True)
        correo.setStyleSheet("color: #777777;")
        correo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layNombreCorreo = QVBoxLayout()
        layNombreCorreo.addWidget(nombre)
        layNombreCorreo.addWidget(correo)

        if usuario.rol_id != 3:
            rol = QLabel("M" if usuario.rol_id == 1 else "A")
            font = rol.font()
            font.setPointSize(8)
            rol.setFont(font)
            layRol = QVBoxLayout()
            layRol.addWidget(rol)
            layRol.addWidget(QLabel())
            layRol.addWidget(QLabel())

        layHorizontal = QHBoxLayout()
        layHorizontal.addLayout(layNombreCorreo)
        layHorizontal.addWidget(self.avatar)
        if usuario.rol_id != 3:
            layHorizontal.addLayout(layRol)
        self.setLayout(layHorizontal)

    def setPulsado(self, is_pulsado=False):
        if is_pulsado:
            self.setStyleSheet(f"""
                UsuarioCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                UsuarioCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        self.card_clic.emit(self.id)
        super().mousePressEvent(event)

    def set_image(self, image):
        self.avatar.setPixmap(image)
