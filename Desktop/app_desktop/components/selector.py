from PySide6.QtWidgets import (
    QWidget, QComboBox, QLabel, QVBoxLayout, QStyledItemDelegate, QMessageBox
)
from PySide6.QtCore import Qt, Signal
from controllers.confirmaciones import confirma_ejecucion, confirma_pregunta

class CenterDelegate(QStyledItemDelegate):
    def paint(self, painter, option, index):
        option.displayAlignment = Qt.AlignCenter
        super().paint(painter, option, index)

class Selector(QWidget):
    onCambio = Signal()

    # items es array [] con parejas [string, data]
    def __init__(self, items=[], placeholder="", titulo="", selected=0, conf_tipo=""):
        super().__init__()

        self.valor_anterior = 0
        self.combo = QComboBox()
        self.combo.setPlaceholderText(placeholder)
        self.set_items(items, selected)
        
        self.conf_tipo = conf_tipo
        self.ente_id = 0

        self.combo.currentIndexChanged.connect(self.validar_cambio)

        self.combo.setEditable(True)
        line_edit = self.combo.lineEdit()
        line_edit.setAlignment(Qt.AlignCenter)
        line_edit.setReadOnly(True)
        self.combo.setItemDelegate(CenterDelegate())
        self.combo.setStyleSheet("""
            Selector {
                border: 1px solid #cccccc;
                border-radius: 5px;
                padding: 5px;
                qproperty-alignment: 'AlignCenter';
            }
            Selector::drop-down {
                subcontrol-origin: padding;
                subcontrol-position: top right;
                width: 15px;
                border-left-width: 1px;
                border-left-color: darkgray;
                border-left-style: solid;
                border-top-right-radius: 3px;
                border-bottom-right-radius: 3px;
            }
        """)

        layVertical = QVBoxLayout()
        if titulo != "":
            lblTitulo = QLabel(titulo)
            lblTitulo.setAlignment(
                Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignBottom
            )
            layVertical.addWidget(lblTitulo)
        layVertical.addWidget(self.combo)
        self.setLayout(layVertical)
    
    def set_items(self, items=[], index=0):
        # items es array [] con parejas [string, data]
        self.combo.blockSignals(True)
        self.combo.clear()
        for it in items:
            self.combo.addItem(it[0], it[1])
        self.combo.blockSignals(False)
        self.set_index(index)

    def set_disabled(self, is_disabled=False):
        self.combo.setDisabled(is_disabled)

    def get_index(self):
        return self.combo.currentIndex()
    
    def get_data(self):
        return self.combo.currentData()
    
    def get_text(self):
        return self.combo.currentText()
    
    def set_index(self, index):
        self.combo.blockSignals(True)
        self.combo.setCurrentIndex(index)
        self.combo.blockSignals(False)
        self.valor_anterior = index
    
    def set_index_from_data(self, data):
        index = self.combo.findData(data)
        if index != -1:
            self.set_index(index)
        else:
            self.set_index(0)

    def set_ente_id(self, id):
        self.ente_id = id

    def validar_cambio(self, new_index):
        print("Selector: validar_cambio-init")
        self.onCambio.emit()
        
        if new_index == self.valor_anterior or self.conf_tipo == "":
            return

        resp = QMessageBox.question(self, "Confirmación",
            confirma_pregunta(self.conf_tipo) + self.combo.itemText(new_index) + "?")
        if resp == QMessageBox.Yes:
            print("Selector: validar_cambio-ejecucion")
            if confirma_ejecucion(self.conf_tipo, self.ente_id, self.combo.currentData()):
                print("Selector: validar_cambio-ok")
                self.valor_anterior = new_index
                return
        
        print("Selector: validar_cambio-back")
        self.set_index(self.valor_anterior)
