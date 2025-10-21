from PySide6.QtWidgets import QMainWindow, QPushButton, QTableWidget, QTableWidgetItem
from core.services import obtener_productos

class MainWindow(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Cliente API DB")
        self.setGeometry(200, 200, 800, 600)

        self.table = QTableWidget(self)
        self.setCentralWidget(self.table)

        btn = QPushButton("Cargar Datos", self)
        btn.clicked.connect(self.load_data)
        self.addToolBar("Acciones").addWidget(btn)

    def load_data(self):
        productos = obtener_productos()
        self.table.setRowCount(len(productos))
        self.table.setColumnCount(2)
        self.table.setHorizontalHeaderLabels(["ID", "Nombre"])

        for i, prod in enumerate(productos):
            self.table.setItem(i, 0, QTableWidgetItem(str(prod["id"])))
            self.table.setItem(i, 1, QTableWidgetItem(prod["nombre"]))
