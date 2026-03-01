from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QGroupBox, QTextEdit, QApplication
)
from PySide6.QtCore import Qt, Signal
from PySide6.QtGui import QPixmap
from components.selector import Selector

class ProductoBody(QWidget):
    cambioData = Signal(int)

    def __init__(self):
        super().__init__()
        self.current_product = None
        self.id = 0

        ctrlProducto = QApplication.instance().property("controls").get_productos()
        ctrlProducto.producto_signal.hubo_cambio.connect(self.actualizar)

        ctrlData = QApplication.instance().property("controls").get_data()

        self.imagen = self.setImagen()
        self.imagenes = QVBoxLayout()

        self.nombre = QLabel("", self)
        self.nombre.setWordWrap(True)
        font = self.nombre.font()
        font.setBold(True)
        font.setPointSize(20)
        self.nombre.setFont(font)
        self.nombre.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        laySelectoresA = QHBoxLayout()
        self.sel_categoria = Selector(
            [["", 0]], "categoría...", "Categoría", 0, "producto_categoria")
        self.sel_categoria.set_disabled(True)
        laySelectoresA.addWidget(self.sel_categoria)
        laySelectoresA.addSpacing(10)
        self.sel_subcategoria = Selector(
            [["", 0]], "subcategoría...", "Subcategoría", 0, "producto_subcategoria")
        self.sel_subcategoria.set_disabled(True)
        laySelectoresA.addWidget(self.sel_subcategoria)

        laySelectoresB = QHBoxLayout()
        self.sel_integridad = Selector(ctrlData.get_to_selector("integridad"),
            "integridad...", "Integridad", 0, "producto_integridad")
        self.sel_integridad.set_disabled(True)
        laySelectoresB.addWidget(self.sel_integridad)
        laySelectoresB.addSpacing(10)
        self.sel_estado = Selector(ctrlData.get_estados_basicos(),
            "estado...", "Estado", 0, "producto_estado")
        laySelectoresB.addWidget(self.sel_estado)

        self.descripcion = QLabel("", self)
        self.descripcion.setWordWrap(True)
        self.descripcion.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        layCantidades = QHBoxLayout()
        self.precio = QLabel("Precio", self)
        self.precio.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        layCantidades.addWidget(self.precio)
        layCantidades.addSpacing(10)
        self.disponibles = QLabel("Disponibles", self)
        self.disponibles.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        layCantidades.addWidget(self.disponibles)

        layFechas = QHBoxLayout()
        self.registro = self.labelFechas("Registro")
        layFechas.addWidget(self.registro)
        layFechas.addSpacing(10)
        self.edicion = self.labelFechas("Edición")
        layFechas.addWidget(self.edicion)

        layVertical = QVBoxLayout()
        layVertical.addSpacing(10)
        layVertical.addWidget(self.imagen, alignment=Qt.AlignCenter)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.nombre)
        layVertical.addSpacing(10)
        layVertical.addLayout(laySelectoresA)
        layVertical.addSpacing(10)
        layVertical.addLayout(laySelectoresB)
        layVertical.addSpacing(20)
        layVertical.addWidget(self.descripcion)
        layVertical.addSpacing(20)
        layVertical.addLayout(layCantidades)
        layVertical.addSpacing(20)
        layVertical.addLayout(layFechas)
        layVertical.addSpacing(20)
        layVertical.addLayout(self.imagenes)
        layVertical.addStretch()
        self.setLayout(layVertical)
        self.resetData()
    
    def setImagen(self, pixmap=None):
        imagen = QLabel(self)
        if pixmap:
            imagen.setPixmap(pixmap)
        else:
            imagen.setPixmap(QPixmap("assets/sprites/img_null.png"))
        imagen.setScaledContents(True)
        imagen.setFixedSize(256, 256)
        imagen.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        return imagen

    def labelFechas(self, texto=""):
        label = QLabel(texto, self)
        label.setStyleSheet("color: #777777;")
        label.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        return label

    def changeProducto(self, producto=None):
        if self.current_product is not None:
            try:
                self.current_product.img_signal.ok_image.disconnect(self.on_image_loaded)
            except Exception:
                pass
        self.current_product = producto

    def resetData(self):
        print(f"UsuarioBody {self.id}: resetData")
        self.changeProducto(None)
        self.id = 0
        self.nombre.setText("*** ??? ***")
        self.descripcion.setText("*** descripción vacía ***")
        self.precio.setText("Precio\n$ 0")
        self.disponibles.setText("Disponibles\n0")
        self.registro.setText("Registro")
        self.edicion.setText("Edición")
        self.sel_categoria.set_index(0)
        self.sel_subcategoria.set_index(0)
        self.sel_integridad.set_index(0)
        self.sel_estado.set_index(0)
        self.sel_categoria.set_ente_id(0)
        self.sel_subcategoria.set_ente_id(0)
        self.sel_integridad.set_ente_id(0)
        self.sel_estado.set_ente_id(0)
        self.imagen.setPixmap(QPixmap("assets/sprites/img_null.png"))
        self.limpiarImagenes()
        self.cambioData.emit(0)

    def limpiarImagenes(self):
        print(f"UsuarioBody {self.id}: limpiarImagenes")
        while self.imagenes.count():
            item = self.imagenes.takeAt(0)
            widget = item.widget()
            if widget is not None:
                widget.deleteLater()
        self.imagenes.update()
        self.update()

    def setData(self, producto):
        if producto is None:
            self.resetData()
            return
        print(f"UsuarioBody {producto.id}: setData")
        self.changeProducto(producto)
        producto.img_signal.ok_image.connect(self.on_image_loaded)
        producto.load_images()

        self.id = producto.id
        self.nombre.setText(producto.nombre)
        if producto.descripcion == "":
            self.descripcion.setText("*** descripción vacía ***")
        else:
            self.descripcion.setText(producto.descripcion)
        self.precio.setText("Precio\n$ " + str(int(producto.precio)))
        self.disponibles.setText("Disponibles\n" + str(producto.disponibles))
        self.registro.setText("Registro\n" + producto.fecha_registro.replace(" ", "\n"))
        fecha_actualiza = producto.fecha_actualiza or ""
        self.edicion.setText("Edición\n" + fecha_actualiza.replace(" ", "\n"))
        ctrlData = QApplication.instance().property("controls").get_data()
        self.sel_categoria.set_items(ctrlData.get_to_selector("categorias"))
        self.sel_subcategoria.set_items(ctrlData.get_subcategorias_to_selector(producto.categoria_id))
        self.sel_categoria.set_index_from_data(producto.categoria_id)
        self.sel_subcategoria.set_index_from_data(producto.subcategoria_id)
        self.sel_integridad.set_index_from_data(producto.integridad_id)
        self.sel_estado.set_index_from_data(producto.estado_id)
        self.sel_categoria.set_ente_id(producto.id)
        self.sel_subcategoria.set_ente_id(producto.id)
        self.sel_integridad.set_ente_id(producto.id)
        self.sel_estado.set_ente_id(producto.id)
        self.on_image_loaded(producto.id)
        self.cambioData.emit(producto.id)

    def actualizar(self, id=0):
        if self.id == 0 or id != self.id:
            return
        print(f"UsuarioBody {id}: actualizar")
        ctrlProducto = QApplication.instance().property("controls").get_productos()
        self.setData(ctrlProducto.get_producto(id))

    def on_image_loaded(self, prod_id):
        if self.current_product and self.current_product.id == prod_id:
            print(f"UsuarioBody {prod_id}: on_image_loaded")
            self.imagen.setPixmap(self.current_product.get_portada().copy())
            self.imagen.repaint()
            self.limpiarImagenes()
            for img in self.current_product.get_no_portada():
                self.imagenes.addWidget(self.setImagen(img.copy()), alignment=Qt.AlignCenter)
                self.imagenes.addSpacing(4)
            self.update()

    def set_is_seleccionado(self, vendedor_id=0):
        if self.current_product is not None:
            print(f"UsuarioBody {self.id} - {vendedor_id}: set_is_seleccionado")
            if vendedor_id != self.current_product.vendedor_id:
                self.resetData()
