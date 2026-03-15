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
    
    def get_value(self):
        return self.checkbox.isChecked()
    
    def get_bool(self):
        return 1 if self.checkbox.isChecked() else 0
