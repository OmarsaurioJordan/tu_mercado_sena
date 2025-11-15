from PySide6.QtWidgets import (
    QFrame, QWidget, QVBoxLayout, QHBoxLayout, QLabel
)
from PySide6.QtCore import Qt
from PySide6.QtGui import QPixmap

class UsuarioCard(QFrame):

    def __init__(self, usr_id, usr_nombre, usr_correo, usr_rol, usr_estado):
        super().__init__()
        self.id = usr_id

        estado_color = {
            1: "#e6e5e5", # activo
            2: "#D2EDF8", # invisible
            3: "#999898", # eliminado
            4: "#f7d9ac" # bloqueado
        }.get(usr_estado, "#f88eef") # error

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setMaximumWidth(500)
        self.setStyleSheet(f"""
            UsuarioCard {{
                background-color: {estado_color};
                border: 1px solid #cccccc;
                border-radius: 10px;
            }}
        """)

        image = QPixmap("assets/sprites/avatar.png")
        avatar = QLabel()
        avatar.setPixmap(image)
        avatar.setScaledContents(True)
        avatar.setFixedSize(48, 48)

        nombre = QLabel(usr_nombre)
        nombre.setWordWrap(True)
        font = nombre.font()
        font.setBold(True)
        nombre.setFont(font)
        nombre.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        correo = QLabel(usr_correo.split("@")[0])
        correo.setWordWrap(True)
        correo.setStyleSheet("color: #777777;")
        correo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layNombreCorreo = QVBoxLayout()
        layNombreCorreo.addWidget(nombre)
        layNombreCorreo.addWidget(correo)

        if usr_rol != 3:
            rol = QLabel("M" if usr_rol == 1 else "A")
            font = rol.font()
            font.setPointSize(8)
            rol.setFont(font)
            layRol = QVBoxLayout()
            layRol.addWidget(rol)
            layRol.addWidget(QLabel())
            layRol.addWidget(QLabel())

        layHorizontal = QHBoxLayout()
        layHorizontal.addLayout(layNombreCorreo)
        layHorizontal.addWidget(avatar)
        if usr_rol != 3:
            layHorizontal.addLayout(layRol)
        self.setLayout(layHorizontal)
