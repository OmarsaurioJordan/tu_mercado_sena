from PySide6.QtWidgets import (
    QFrame, QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt, Signal
from PySide6.QtGui import QPixmap

class ProductoCard(QFrame):
    card_clic = Signal(int)

    def __init__(self, producto):
        super().__init__()
        self.id = producto.id

        ctrlProducto = QApplication.instance().property("controls").get_productos()
        ctrlProducto.producto_signal.hubo_cambio.connect(self.actualizar)

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setMaximumWidth(500)

        self.imagen = QLabel()
        self.imagen.setScaledContents(True)
        self.imagen.setFixedSize(48, 48)
        self.imagen.setPixmap(QPixmap("assets/sprites/img_null.png"))

        self.nombre = QLabel("")
        self.nombre.setWordWrap(True)
        font = self.nombre.font()
        font.setBold(True)
        self.nombre.setFont(font)
        self.nombre.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.vendedor = QLabel("")
        self.vendedor.setWordWrap(True)
        self.vendedor.setStyleSheet("color: #777777;")
        self.vendedor.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layNombreVendedor = QVBoxLayout()
        layNombreVendedor.addWidget(self.nombre)
        layNombreVendedor.addWidget(self.vendedor)

        layHorizontal = QHBoxLayout()
        layHorizontal.addWidget(self.imagen)
        layHorizontal.addLayout(layNombreVendedor)
        self.setLayout(layHorizontal)
        self.setData(producto)

    def setData(self, producto):
        if producto is None:
            return
        self.imagen.setPixmap(producto.get_portada().copy())
        self.imagen.repaint()
        self.nombre.setText(producto.nombre)
        self.vendedor.setText(producto.vendedor_nickname)
        self.estado_color = {
            1: "#e6e5e5", # activo
            2: "#d2edf8", # invisible
            3: "#999898", # eliminado
            4: "#f7d9ac", # bloqueado
            10: "#f4f7ac" # denunciado
        }.get(producto.estado_id, "#f88eef") # error
        self.setPulsado()

    def actualizar(self, id=0):
        if id == self.id:
            ctrlProducto = QApplication.instance().property("controls").get_productos()
            self.setData(ctrlProducto.get_producto(id))

    def setPulsado(self, is_pulsado=False):
        if is_pulsado:
            self.setStyleSheet(f"""
                ProductoCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                ProductoCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        self.card_clic.emit(self.id)
        super().mousePressEvent(event)
