from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QPushButton
)
from PySide6.QtCore import Qt, QSize
from PySide6.QtGui import QPixmap, QIcon
from components.usuario_card import UsuarioCard
from components.boton import Boton

class HeaderLayout(QVBoxLayout):

    def __init__(self, widget=None):
        super().__init__()

        image = QPixmap("assets/sprites/logo.png")
        logo = QLabel()
        logo.setPixmap(image)
        logo.setScaledContents(True)
        logo.setFixedSize(64, 64)

        titulo = QLabel("Tu Mercado Sena")
        font = titulo.font()
        font.setPointSize(24)
        font.setBold(True)
        titulo.setFont(font)
        titulo.setAlignment(
            Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignVCenter
        )

        btnMenu = Boton("MENÚ", "menu")
        
        notifica_pqrs = Boton("PQRS: 0","bell")
        notifica_denuncias = Boton("Denuncias: 0", "bell")

        header = QWidget()
        layHorizontal = QHBoxLayout(header)
        layHorizontal.addWidget(logo)
        layHorizontal.addSpacing(10)
        layHorizontal.addWidget(titulo)
        layHorizontal.addSpacing(10)
        layHorizontal.addStretch()
        layHorizontal.addWidget(btnMenu)
        layHorizontal.addSpacing(10)
        layHorizontal.addStretch()
        layHorizontal.addWidget(notifica_pqrs)
        layHorizontal.addSpacing(10)
        layHorizontal.addWidget(notifica_denuncias)
        layHorizontal.addSpacing(10)
        layHorizontal.addStretch()
        layHorizontal.addWidget(UsuarioCard(0, "Nombre de Usuario", "correo_usuario@sena.edu.co", 2, 1))
        self.addWidget(header)
        
        if widget == None:
            self.addWidget(QLabel())
        else:
            self.addWidget(widget)
