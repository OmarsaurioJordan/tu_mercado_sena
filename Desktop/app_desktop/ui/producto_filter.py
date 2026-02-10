from PySide6.QtWidgets import (
    QWidget, QVBoxLayout
)
from PySide6.QtCore import QDate, Signal
from components.txt_edit import TxtEdit
from components.selector import Selector
from components.checkbox import Checkbox
from components.spinbox import SpinBox
from components.date_edit import DateEdit
from components.boton import Boton
from core.app_config import PRECIO_MAX

class Usuariofilter(QWidget):
    clicAplicar = Signal(dict)

    def __init__(self):
        super().__init__()

        layVertical = QVBoxLayout()
        layVertical.setSpacing(10)
        self.txtNombre = TxtEdit("Nombre", "nombre")
        layVertical.addWidget(self.txtNombre)


        self.selIntegridad = Selector(
            ["Todas", "Nuevo", "Usado", "Reparado", "Reciclable"],
            "integ...", "Integridad", 0
        )
        layVertical.addWidget(self.selIntegridad)
        self.selEstado = Selector(
            ["Todos", "Activo", "Invisible", "Eliminado", "Bloqueado", "Denunciado", "Act-Inv", "Bloq-Denun"],
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
            "categoria_id": self.selCategoria.get_index(),
            "subcategoria_id": self.selSubcategoria.get_index(),
            "integridad_id": self.selIntegridad.get_index(),
            "estado_id": self.selEstado.get_index(),
            "precio_min": self.precio_min.get_value(),
            "precio_max": self.precio_max.get_value(),
            "con_descripcion": self.chkTexto.get_bool(),
            "registro_desde": self.date_registro_min.get_value_utc(),
            "registro_hasta": self.date_registro_max.get_value_utc()
        }
        return filtros
