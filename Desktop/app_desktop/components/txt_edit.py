from PySide6.QtWidgets import (
    QWidget, QLineEdit, QLabel, QVBoxLayout
)
from PySide6.QtCore import Qt

class TxtEdit(QWidget):
    def __init__(self, titulo="", placeholder=""):
        super().__init__()
        self.lineEdit = QLineEdit()
        self.lineEdit.setAlignment(Qt.AlignCenter)
        self.lineEdit.setPlaceholderText(placeholder)
        layVertical = QVBoxLayout()
        if titulo != "":
            lblTitulo = QLabel(titulo)
            lblTitulo.setAlignment(
                Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignBottom
            )
            layVertical.addWidget(lblTitulo)
        layVertical.addWidget(self.lineEdit)
        self.setLayout(layVertical)
    
    def get_value(self):
        return self.lineEdit.text().strip()
