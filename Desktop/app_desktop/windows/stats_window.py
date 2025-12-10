from PySide6.QtWidgets import (
    QWidget
)
from ui.header_layout import HeaderLayout

class StatsWindow(QWidget):

    def __init__(self):
        super().__init__()

        header = HeaderLayout()
        self.setLayout(header)
