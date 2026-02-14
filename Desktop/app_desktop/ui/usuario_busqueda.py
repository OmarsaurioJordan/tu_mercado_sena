from PySide6.QtWidgets import (
    QListView, QListWidget, QListWidgetItem, QAbstractItemView, QApplication
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

        ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
        ctrlUsuario.usuario_signal.hubo_cambio.connect(self.actualizar)

        self.setViewMode(QListWidget.IconMode)
        self.setResizeMode(QListWidget.Adjust)
        self.setSpacing(10)
        self.setWrapping(True)
        self.setFlow(QListWidget.LeftToRight)
        self.setDragEnabled(False)
        self.setDragDropMode(QAbstractItemView.NoDragDrop)
        self.verticalScrollBar().valueChanged.connect(self._check_scroll)
    
    # signal cuando llega al fondo del scroll

    def _check_scroll(self, value):
        scroll = self.verticalScrollBar()
        if value == scroll.maximum():
            self.scroll_at_end.emit()

    # CRUD de fichas

    def eliminar_usuarios(self):
        self.clear()

    def actualizar(self, id=0):
        ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
        if id == 0:
            usuarios = ctrlUsuario.get_busqueda()
            self.agregar_usuarios(usuarios)
        else:
            for i in range(self.count()):
                item = self.item(i)
                ficha = self.itemWidget(item)
                if ficha.id == id:
                    ficha.setData(ctrlUsuario.get_usuario(id))
                    break
    
    def agregar_usuarios(self, usuarios):
        ids = self.obtener_ids()
        for usr in usuarios:
            if usr.id in ids:
                continue
            ids.add(usr.id)
            ficha = UsuarioCard(usr)
            ficha.card_clic.connect(self._click_event)
            item = QListWidgetItem()
            item.setSizeHint(ficha.sizeHint())
            self.addItem(item)
            self.setItemWidget(item, ficha)

    def obtener_ids(self):
        ids = set()
        for i in range(self.count()):
            item = self.item(i)
            ficha = self.itemWidget(item)
            ids.add(ficha.id)
        return ids
    
    # pulsacion de fichas
    
    def _click_event(self, user_id):
        self.card_clic.emit(user_id)
        self.set_sombrear(user_id)
    
    def set_sombrear(self, user_id=0):
        for i in range(self.count()):
            item = self.item(i)
            widget = self.itemWidget(item)
            widget.setPulsado(widget.id == user_id)
