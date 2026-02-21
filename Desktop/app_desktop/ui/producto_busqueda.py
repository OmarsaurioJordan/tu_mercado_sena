from PySide6.QtWidgets import (
    QListView, QListWidget, QListWidgetItem, QAbstractItemView, QApplication
)
from PySide6.QtGui import (
    QStandardItemModel, QStandardItem
)
from PySide6.QtCore import QSize, Signal
from components.producto_card import ProductoCard

class ProductoBusqueda(QListWidget):
    scroll_at_end = Signal()
    card_clic = Signal(int)

    def __init__(self):
        super().__init__()

        ctrlProducto = QApplication.instance().property("controls").get_productos()
        ctrlProducto.producto_signal.hubo_cambio.connect(self.actualizar)

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

    def eliminar_productos(self):
        self.clear()

    def actualizar(self, id=0):
        ctrlProducto = QApplication.instance().property("controls").get_productos()
        if id == 0:
            productos = ctrlProducto.get_busqueda()
            self.agregar_productos(productos)
        else:
            for i in range(self.count()):
                item = self.item(i)
                ficha = self.itemWidget(item)
                if ficha.id == id:
                    ficha.setData(ctrlProducto.get_producto(id))
                    break
    
    def agregar_productos(self, productos):
        ids = self.obtener_ids()
        for prd in productos:
            if prd.id in ids:
                continue
            ids.add(prd.id)
            ficha = ProductoCard(prd)
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
    
    def _click_event(self, prod_id):
        self.card_clic.emit(prod_id)
    
    def set_sombrear(self, prod_id=0):
        for i in range(self.count()):
            item = self.item(i)
            widget = self.itemWidget(item)
            widget.setPulsado(widget.id == prod_id)
