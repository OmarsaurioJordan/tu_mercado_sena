from PySide6.QtWidgets import (
    QWidget
)
from ui.header_layout import HeaderLayout
from ui.auditorias_widget import AuditoriasWidget

class AuditoriasWindow(QWidget):

    def __init__(self):
        super().__init__()

        header = HeaderLayout(AuditoriasWidget())
        self.setLayout(header)
