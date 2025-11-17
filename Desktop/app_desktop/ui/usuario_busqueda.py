from PySide6.QtWidgets import (
    QListView, QListWidget, QListWidgetItem
)
from PySide6.QtGui import (
    QStandardItemModel, QStandardItem
)
from PySide6.QtCore import QSize 
from components.usuario_card import UsuarioCard

class UsuarioBusqueda(QListWidget):
    def __init__(self):
        super().__init__()
        self.setViewMode(QListWidget.IconMode)
        self.setResizeMode(QListWidget.Adjust)
        self.setSpacing(10)
        self.setWrapping(True)
        self.setFlow(QListWidget.LeftToRight)
        #self.setMovement(QListWidget.Static)

        for i in range(1000):
            card = UsuarioCard(i + 1, "Nombre de Usuario", "correo_usuario@sena.edu.co", 3, 1)
            item = QListWidgetItem()
            item.setSizeHint(card.sizeHint())
            self.addItem(item)
            self.setItemWidget(item, card)
