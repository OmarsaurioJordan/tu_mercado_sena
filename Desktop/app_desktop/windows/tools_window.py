from PySide6.QtWidgets import (
    QWidget
)
from ui.header_layout import HeaderLayout
from ui.tools_widget import ToolsWidget

class ToolsWindow(QWidget):

    def __init__(self):
        super().__init__()

        header = HeaderLayout(ToolsWidget())
        self.setLayout(header)
