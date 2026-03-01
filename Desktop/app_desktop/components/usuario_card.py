from PySide6.QtWidgets import (
    QFrame, QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt, Signal
from PySide6.QtGui import QPixmap

class UsuarioCard(QFrame):
    card_clic = Signal(int)

    def __init__(self, usuario, parent=None):
        super().__init__(parent)
        self.id = usuario.id
        self.miItem = None

        ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
        ctrlUsuario.usuario_signal.hubo_cambio.connect(self.actualizar)

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setMaximumWidth(500)

        self.imagen = QLabel(self)
        self.imagen.setScaledContents(True)
        self.imagen.setFixedSize(48, 48)
        self.imagen.setPixmap(QPixmap("assets/sprites/avatar.png"))

        self.nickname = QLabel("", self)
        self.nickname.setWordWrap(True)
        font = self.nickname.font()
        font.setBold(True)
        self.nickname.setFont(font)
        self.nickname.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.email = QLabel("", self)
        self.email.setWordWrap(True)
        self.email.setStyleSheet("color: #777777;")
        self.email.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layNicknameEmail = QVBoxLayout()
        layNicknameEmail.addWidget(self.nickname)
        layNicknameEmail.addWidget(self.email)

        self.rol = QLabel("", self)
        font = self.rol.font()
        font.setPointSize(8)
        self.rol.setFont(font)
        layRol = QVBoxLayout()
        layRol.addWidget(self.rol)
        layRol.addWidget(QLabel("", self))
        layRol.addWidget(QLabel("", self))

        layHorizontal = QHBoxLayout()
        layHorizontal.addLayout(layNicknameEmail)
        layHorizontal.addWidget(self.imagen)
        layHorizontal.addLayout(layRol)
        self.setLayout(layHorizontal)
        self.setData(usuario)

    def setData(self, usuario):
        if usuario is None:
            return
        print(f"UsuarioCard {usuario.id}: setData")
        self.rol.setText("M" if usuario.rol_id == 3 else ("A" if usuario.rol_id == 2 else ""))
        self.imagen.setPixmap(usuario.img_pix)
        self.nickname.setText(usuario.nickname)
        self.email.setText(usuario.email.split("@")[0])
        self.estado_color = {
            1: "#e6e5e5", # activo
            2: "#d2edf8", # invisible
            3: "#B9B9B9", # eliminado
            4: "#f7d9ac", # bloqueado
            10: "#f4f7ac" # denunciado
        }.get(usuario.estado_id, "#f88eef") # error
        self.setPulsado()

    def actualizar(self, id=0):
        if id != 0 and id == self.id:
            print(f"UsuarioCard {id}: actualizar")
            ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
            self.setData(ctrlUsuario.get_usuario(id))

    def setPulsado(self, is_pulsado=False):
        print(f"UsuarioCard {self.id}: setPulsado")
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
        self.adjustSize()
        if self.miItem is not None:
            self.miItem.setSizeHint(self.sizeHint())

    def mousePressEvent(self, event):
        print(f"UsuarioCard {self.id}: mousePressEvent")
        self.card_clic.emit(self.id)
        super().mousePressEvent(event)
