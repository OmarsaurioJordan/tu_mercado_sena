from PySide6.QtWidgets import (
    QListView, QListWidget, QListWidgetItem, QAbstractItemView
)
from PySide6.QtGui import (
    QStandardItemModel, QStandardItem
)
from PySide6.QtCore import QSize, Signal
from components.usuario_card import UsuarioCard

class UsuarioBusqueda(QListWidget):
    scroll_at_end = Signal()
    card_clic = Signal(int)

    def __init__(self):
        super().__init__()
        self.setViewMode(QListWidget.IconMode)
        self.setResizeMode(QListWidget.Adjust)
        self.setSpacing(10)
        self.setWrapping(True)
        self.setFlow(QListWidget.LeftToRight)
        self.setDragEnabled(False)
        self.setDragDropMode(QAbstractItemView.NoDragDrop)
        self.verticalScrollBar().valueChanged.connect(self._check_scroll)
        #self.setMovement(QListWidget.Static)
    
    def _check_scroll(self, value):
        scroll = self.verticalScrollBar()
        if value == scroll.maximum():
            self.scroll_at_end.emit()

    def eliminar_usuarios(self):
        self.clear()

    def cargar_usuarios(self, usuarios):
        ids = self.obtener_ids()
        for usr in usuarios:
            if usr.id in ids:
                continue
            card = UsuarioCard(usr)
            card.card_clic.connect(self._click_event)
            item = QListWidgetItem()
            item.setSizeHint(card.sizeHint())
            self.addItem(item)
            self.setItemWidget(item, card)

    def obtener_ids(self):
        ids = set()
        for i in range(self.count()):
            item = self.item(i)
            widget = self.itemWidget(item)
            ids.add(widget.id)
        return ids
    
    def set_sombrear(self, user_id=0):
        for i in range(self.count()):
            item = self.item(i)
            widget = self.itemWidget(item)
            widget.setPulsado(widget.id == user_id)
    
    def _click_event(self, user_id):
        self.card_clic.emit(user_id)
        self.set_sombrear(user_id)
