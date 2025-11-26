from PySide6.QtWidgets import (
    QMainWindow, QWidget
)
from ui.header_layout import HeaderLayout
from ui.tools_widget import ToolsWidget

class ToolsWindow(QMainWindow):

    def __init__(self):
        super().__init__()
        self.setWindowTitle("TuMercadoSena-Desktop")
        self.resize(800, 600)

        header = HeaderLayout(ToolsWidget())
        central = QWidget()
        central.setLayout(header)
        self.setCentralWidget(central)
