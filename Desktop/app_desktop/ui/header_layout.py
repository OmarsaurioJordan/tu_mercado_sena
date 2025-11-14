from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QPushButton
)
from PySide6.QtCore import Qt
from PySide6.QtGui import QPixmap
from components.usuario_card import UsuarioCard

class HeaderLayout(QVBoxLayout):

    def __init__(self, widget=None, hasMenuBtn=True):
        super().__init__()

        image = QPixmap("assets/sprites/logo.png")
        logo = QLabel()
        logo.setPixmap(image)
        logo.setScaledContents(True)
        logo.setFixedSize(48, 48)

        titulo = QLabel("Tu Mercado Sena")
        font = titulo.font()
        font.setPointSize(24)
        font.setBold(True)
        titulo.setFont(font)
        titulo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        if hasMenuBtn:
            btnMenu = QPushButton("...")

        header = QWidget()
        layHorizontal = QHBoxLayout(header)
        layHorizontal.addWidget(logo)
        layHorizontal.addWidget(titulo)
        layHorizontal.addStretch()
        if hasMenuBtn:
            layHorizontal.addWidget(btnMenu)
        else:
            layHorizontal.addWidget(QLabel())
        layHorizontal.addStretch()
        # notificaciones
        layHorizontal.addStretch()
        layHorizontal.addWidget(UsuarioCard(0, "Rodolfo Perea", "rodolfito@sena.edu.co", 1, 4))
        self.addWidget(header)
        
        if widget == None:
            self.addWidget(QLabel())
        else:
            self.addWidget(widget)
