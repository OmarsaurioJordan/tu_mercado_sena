from PySide6.QtWidgets import (
    QPushButton
)
from PySide6.QtCore import QSize, Qt
from PySide6.QtGui import QIcon

class Boton(QPushButton):
    def __init__(self, texto="", icono="", size=32):
        super().__init__(texto)
        if icono != "":
            self.setIcon(QIcon("assets/sprites/" + icono + ".png"))
            self.setIconSize(QSize(size, size))
        self.setFocusPolicy(Qt.NoFocus)
        self.setStyleSheet("""
            QPushButton {
                padding: 6px 12px;
                border: 1px solid #cccccc;
                border-radius: 10px;
                background-color: #f8f8f8;
            }
            QPushButton:hover {
                background-color: #e8e8e8;
                border: 1px solid #bbbbbb;
            }
            QPushButton:pressed {
                background-color: #dcdcdc;
                border: 1px solid #aaaaaa;
            }
        """)
