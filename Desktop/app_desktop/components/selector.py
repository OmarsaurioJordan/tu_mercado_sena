from PySide6.QtWidgets import (
    QWidget, QComboBox, QLabel, QVBoxLayout, QStyledItemDelegate
)
from PySide6.QtCore import Qt

class CenterDelegate(QStyledItemDelegate):
    def paint(self, painter, option, index):
        option.displayAlignment = Qt.AlignCenter
        super().paint(painter, option, index)

class Selector(QWidget):
    def __init__(self, items=[], placeholder="", titulo="", selected=0):
        super().__init__()

        self.combo = QComboBox()
        self.combo.addItems(items)
        self.combo.setPlaceholderText(placeholder)
        self.combo.setCurrentIndex(selected)
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
    
    def get_index(self):
        return self.combo.currentIndex()
    
    def get_text(self):
        return self.combo.currentText()
    
    def set_index(self, index):
        self.combo.setCurrentIndex(index)
