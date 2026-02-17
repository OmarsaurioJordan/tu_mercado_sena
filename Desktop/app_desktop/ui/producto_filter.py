from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QApplication
)
from PySide6.QtCore import QDate, Signal
from components.txt_edit import TxtEdit
from components.selector import Selector
from components.checkbox import Checkbox
from components.spinbox import SpinBox
from components.date_edit import DateEdit
from components.boton import Boton
from core.app_config import PRECIO_MAX

class ProductoFilter(QWidget):
    clicAplicar = Signal(dict)

    def __init__(self):
        super().__init__()

        layVertical = QVBoxLayout()
        layVertical.setSpacing(10)
        self.txtNombre = TxtEdit("Producto", "nombre")
        layVertical.addWidget(self.txtNombre)

        ctrlData = QApplication.instance().property("controls").get_data()

        self.selCategoria = Selector(
            [["Todas", 0]] + ctrlData.get_to_selector("categorias"),
            "categ...", "Categoria", 0
        )
        self.selCategoria.onCambio.connect(self.cambioCategoria)
        layVertical.addWidget(self.selCategoria)
        textos = self.limitar_textos(ctrlData.get_to_selector("subcategorias"))
        self.selSubcategoria = Selector(
            [["Todas", 0]] + textos,
            "subcat...", "Subcategoria", 0
        )
        layVertical.addWidget(self.selSubcategoria)

        self.selIntegridad = Selector(
            [["Todas", 0]] + ctrlData.get_to_selector("integridad"),
            "integ...", "Integridad", 0
        )
        layVertical.addWidget(self.selIntegridad)
        self.selEstado = Selector(
            [["Todos", 0]] + ctrlData.get_estados_basicos() + [["Act-Inv", 100], ["Bloq-Denun", 101]],
            "estado...", "Estado", 1
        )
        layVertical.addWidget(self.selEstado)

        self.chkTexto = Checkbox("Con Texto")
        layVertical.addWidget(self.chkTexto)
        self.precio_min = SpinBox("Precio min", 0, PRECIO_MAX, 0)
        layVertical.addWidget(self.precio_min)
        self.precio_max = SpinBox("Precio max", 0, PRECIO_MAX, PRECIO_MAX)
        layVertical.addWidget(self.precio_max)
        self.date_registro_min = DateEdit("Reg. desde", QDate(2010, 1, 1))
        layVertical.addWidget(self.date_registro_min)
        self.date_registro_max = DateEdit("Reg. hasta")
        layVertical.addWidget(self.date_registro_max)

        self.btnAplicar = Boton("Aplicar")
        self.btnAplicar.clicked.connect(self.emitir_aplicar)
        layVertical.addWidget(self.btnAplicar)
        layVertical.addStretch()
        self.setLayout(layVertical)

    def emitir_aplicar(self):
        filtros = self.obtener_filtros()
        self.clicAplicar.emit(filtros)

    def obtener_filtros(self):
        filtros = {
            "nombre": self.txtNombre.get_value(),
            "categoria_id": self.selCategoria.get_data(),
            "subcategoria_id": self.selSubcategoria.get_data(),
            "integridad_id": self.selIntegridad.get_data(),
            "estado_id": self.selEstado.get_data(),
            "precio_min": self.precio_min.get_value(),
            "precio_max": self.precio_max.get_value(),
            "con_descripcion": self.chkTexto.get_bool(),
            "registro_desde": self.date_registro_min.get_value_utc(),
            "registro_hasta": self.date_registro_max.get_value_utc()
        }
        return filtros
    
    def cambioCategoria(self):
        ctrlData = QApplication.instance().property("controls").get_data()
        ind = self.selCategoria.get_data()
        if ind == 0:
            textos = self.limitar_textos(ctrlData.get_to_selector("subcategorias"))
            self.selSubcategoria.set_items([["Todas", 0]] + textos)
        else:
            textos = self.limitar_textos(ctrlData.get_subcategorias_to_selector(ind))
            self.selSubcategoria.set_items([["Todas", 0]] + textos)

    def limitar_textos(self, textos, max_len=23):
        for txt in textos:
            txt[0] = self.limitar_texto(txt[0], max_len)
        return textos

    def limitar_texto(self, texto, max_len=23):
        if len(texto) <= max_len:
            return texto
        return texto[:max_len-3] + "..."
