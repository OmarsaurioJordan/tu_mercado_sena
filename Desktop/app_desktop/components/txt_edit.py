from PySide6.QtWidgets import QWidget, QLineEdit, QLabel, QVBoxLayout
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

    def set_limit(self, limite):
        self.lineEdit.setMaxLength(limite)
    
    def get_value(self):
        return self.lineEdit.text().strip()
    
    def set_value(self, texto=""):
        self.lineEdit.setText(texto)
    
    def setPlaceholder(self, placeholder="", value_too=False, limpiar=False):
        self.lineEdit.setPlaceholderText(placeholder)
        if value_too:
            self.lineEdit.setText(placeholder)
        elif limpiar:
            self.lineEdit.setText("")
    
    def passwordMode(self, is_pass_mode=True):
        if is_pass_mode:
            self.lineEdit.setEchoMode(QLineEdit.Password)
        else:
            self.lineEdit.setEchoMode(QLineEdit.Normal)
