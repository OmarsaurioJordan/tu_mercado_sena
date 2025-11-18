from PySide6.QtWidgets import (
    QWidget, QScrollArea, QHBoxLayout, QLabel
)
from PySide6.QtCore import Qt

class Scroll(QWidget):

    def __init__(self, widget=None):
        super().__init__()
        self.scroll = QScrollArea()
        self.scroll.setWidgetResizable(True)
        self.scroll.setVerticalScrollBarPolicy(Qt.ScrollBarAlwaysOff)
        self.scroll.setHorizontalScrollBarPolicy(Qt.ScrollBarAlwaysOff)
        if widget == None:
            self.scroll.setWidget(QLabel())
        else:
            self.scroll.setWidget(widget)
        layMargen = QHBoxLayout()
        layMargen.setContentsMargins(10, 10, 10, 10)
        layMargen.addWidget(self.scroll)
        self.setLayout(layMargen)
