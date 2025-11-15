from PySide6.QtWidgets import (
    QWidget, QHBoxLayout, QCheckBox, QLabel
)

class Checkbox(QWidget):
    def __init__(self, texto=""):
        super().__init__()
        layout = QHBoxLayout()
        self.checkbox = QCheckBox()
        self.label = QLabel(texto)
        layout.addWidget(self.checkbox)
        layout.addWidget(self.label)
        layout.addStretch()
        self.setLayout(layout)
