from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel
)

class HeaderLayout(QVBoxLayout):

    def __init__(self, widget=None):
        super().__init__()

        self.addWidget(QLabel("Tu Mercado Sena"))
        
        if widget == None:
            self.addWidget(QLabel())
        else:
            self.addWidget(widget)
