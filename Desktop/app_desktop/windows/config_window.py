from PySide6.QtWidgets import (
    QMainWindow, QWidget
)
from ui.header_layout import HeaderLayout

class ConfigWindow(QMainWindow):

    def __init__(self):
        super().__init__()
        self.setWindowTitle("TuMercadoSena-Desktop")
        self.resize(800, 600)

        header = HeaderLayout()
        central = QWidget()
        central.setLayout(header)
        self.setCentralWidget(central)
