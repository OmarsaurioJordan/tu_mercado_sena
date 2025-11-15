from PySide6.QtWidgets import (
    QWidget, QHBoxLayout, QLabel, QSpinBox
)
from PySide6.QtCore import Qt

class SpinBox(QWidget):
    def __init__(self, texto="", minimo=0, maximo=100, valor=0):
        super().__init__()
        label = QLabel(texto)
        label.setAlignment(Qt.AlignVCenter | Qt.AlignRight)
        self.spin = QSpinBox()
        self.spin.setRange(minimo, maximo)
        self.spin.setValue(valor)
        layout = QHBoxLayout()
        layout.setSpacing(10)
        layout.addWidget(label)
        layout.addWidget(self.spin)
        self.setLayout(layout)
