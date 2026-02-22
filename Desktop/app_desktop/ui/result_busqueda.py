from PySide6.QtWidgets import (
    QListWidget, QListWidgetItem, QAbstractItemView, QApplication
)
from PySide6.QtGui import QStandardItem
from PySide6.QtCore import Signal
from components.usuario_card import UsuarioCard
from components.producto_card import ProductoCard
from components.pqrs_card import PqrsCard
from components.denuncia_card import DenunciaCard

class ResultBusqueda(QListWidget):
    scroll_at_end = Signal()
    card_clic = Signal(int)

    def __init__(self, tipo=""):
        super().__init__()
        self.tipo = tipo

        ctrl = self.get_control()
        match tipo:
            case "usuarios":
                ctrl.usuario_signal.hubo_cambio.connect(self.actualizar)
            case "productos":
                ctrl.producto_signal.hubo_cambio.connect(self.actualizar)
            case "pqrss":
                ctrl.pqrs_signal.hubo_cambio.connect(self.actualizar)
            case "denuncias":
                ctrl.denuncia_signal.hubo_cambio.connect(self.actualizar)

        if tipo in["usuarios", "productos"]:
            self.setViewMode(QListWidget.IconMode)
            self.setWrapping(True)
        else:
            self.setViewMode(QListWidget.ListMode)
            self.setWrapping(False)
        self.setResizeMode(QListWidget.Adjust)
        self.setSpacing(10)
        self.setFlow(QListWidget.LeftToRight)
        self.setDragEnabled(False)
        self.setDragDropMode(QAbstractItemView.NoDragDrop)
        self.verticalScrollBar().valueChanged.connect(self._check_scroll)
    
    def get_control(self):
        match self.tipo:
            case "usuarios":
                return QApplication.instance().property("controls").get_usuarios()
            case "productos":
                return QApplication.instance().property("controls").get_productos()
            case "pqrss":
                return QApplication.instance().property("controls").get_pqrss()
            case "denuncias":
                return QApplication.instance().property("controls").get_denuncias()
        return None

    # signal cuando llega al fondo del scroll

    def _check_scroll(self, value):
        scroll = self.verticalScrollBar()
        if value == scroll.maximum():
            self.scroll_at_end.emit()

    # CRUD de fichas

    def eliminar_items(self):
        self.clear()

    def actualizar(self, id=0):
        ctrl = self.get_control()
        if id == 0:
            items = ctrl.get_busqueda()
            self.agregar_items(items)
        else:
            for i in range(self.count()):
                item = self.item(i)
                ficha = self.itemWidget(item)
                if ficha.id == id:
                    match self.tipo:
                        case "usuarios":
                            ficha.setData(ctrl.get_usuario(id))
                        case "productos":
                            ficha.setData(ctrl.get_producto(id))
                        case "pqrss":
                            ficha.setData(ctrl.get_pqrs(id))
                        case "denuncias":
                            ficha.setData(ctrl.get_denuncia(id))
                    break
    
    def agregar_items(self, items):
        ids = self.obtener_ids()
        for it in items:
            if it.id in ids:
                continue
            ids.add(it.id)
            match self.tipo:
                case "usuarios":
                    ficha = UsuarioCard(it)
                case "productos":
                    ficha = ProductoCard(it)
                case "pqrss":
                    ficha = PqrsCard(it)
                case "denuncias":
                    ficha = DenunciaCard(it)
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
    
    def _click_event(self, item_id):
        self.card_clic.emit(item_id)
    
    def set_sombrear(self, item_id=0):
        for i in range(self.count()):
            item = self.item(i)
            widget = self.itemWidget(item)
            widget.setPulsado(widget.id == item_id)
