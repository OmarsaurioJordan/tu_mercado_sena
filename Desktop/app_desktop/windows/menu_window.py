from PySide6.QtWidgets import QMainWindow, QPushButton, QTableWidget

class MenuWindow(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("TuMercadoSena-Desktop")
        self.setGeometry(200, 200, 800, 600)

        self.table = QTableWidget(self)
        self.setCentralWidget(self.table)

        btn = QPushButton("Cargar Datos", self)
        self.addToolBar("Acciones").addWidget(btn)
