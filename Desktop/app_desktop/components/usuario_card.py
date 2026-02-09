from PySide6.QtWidgets import (
    QFrame, QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt, Signal
from PySide6.QtGui import QPixmap

class UsuarioCard(QFrame):
    card_clic = Signal(int)

    def __init__(self, usuario):
        super().__init__()
        self.id = usuario.id

        ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
        ctrlUsuario.usuario_signal.hubo_cambio.connect(self.actualizar)

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setMaximumWidth(500)

        self.imagen = QLabel()
        self.imagen.setScaledContents(True)
        self.imagen.setFixedSize(48, 48)
        self.imagen.setPixmap(QPixmap("assets/sprites/avatar.png"))

        self.nickname = QLabel("")
        self.nickname.setWordWrap(True)
        font = self.nickname.font()
        font.setBold(True)
        self.nickname.setFont(font)
        self.nickname.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.email = QLabel("")
        self.email.setWordWrap(True)
        self.email.setStyleSheet("color: #777777;")
        self.email.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layNicknameEmail = QVBoxLayout()
        layNicknameEmail.addWidget(self.nickname)
        layNicknameEmail.addWidget(self.email)

        self.rol = QLabel("")
        font = self.rol.font()
        font.setPointSize(8)
        self.rol.setFont(font)
        layRol = QVBoxLayout()
        layRol.addWidget(self.rol)
        layRol.addWidget(QLabel())
        layRol.addWidget(QLabel())

        layHorizontal = QHBoxLayout()
        layHorizontal.addLayout(layNicknameEmail)
        layHorizontal.addWidget(self.imagen)
        layHorizontal.addLayout(layRol)
        self.setLayout(layHorizontal)
        self.setData(usuario)

    def setData(self, usuario):
        if usuario == None:
            return
        self.rol.setText("M" if usuario.rol_id == 3 else ("A" if usuario.rol_id == 2 else ""))
        self.imagen.setPixmap(usuario.img_pix)
        self.nickname.setText(usuario.nickname)
        self.email.setText(usuario.email.split("@")[0])
        self.estado_color = {
            1: "#e6e5e5", # activo
            2: "#D2EDF8", # invisible
            3: "#999898", # eliminado
            4: "#f7d9ac" # bloqueado
        }.get(usuario.estado_id, "#f88eef") # error
        self.setPulsado()

    def actualizar(self, id=0):
        if id == self.id:
            ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
            self.setData(ctrlUsuario.get_usuario(id))

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
