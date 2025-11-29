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

class Usuariofilter(QWidget):
    clicAplicar = Signal(dict)

    def __init__(self):
        super().__init__()

        layVertical = QVBoxLayout()
        layVertical.setSpacing(10)
        self.txtNombre = TxtEdit("Nombre", "nombre")
        layVertical.addWidget(self.txtNombre)
        self.txtCorreo = TxtEdit("Correo", "correo")
        layVertical.addWidget(self.txtCorreo)
        self.selRol = Selector(
            ["Todos", "Prosumer", "Admin"],
            "rol...", "Rol", 1
        )
        layVertical.addWidget(self.selRol)
        self.selEstado = Selector(
            ["Todos", "Activo", "Invisible", "Bloqueado", "Eliminado", "Act-Inv", "Act-Inv-Bloq"],
            "estado...", "Estado", 6
        )
        layVertical.addWidget(self.selEstado)
        self.chkLink = Checkbox("Con Link")
        layVertical.addWidget(self.chkLink)
        self.chkTexto = Checkbox("Con Texto")
        layVertical.addWidget(self.chkTexto)
        self.chkProductos = Checkbox("Con Productos")
        layVertical.addWidget(self.chkProductos)
        self.dias_actividad = SpinBox("Días activo", 0, 365, 0)
        layVertical.addWidget(self.dias_actividad)
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
            "correo": self.txtCorreo.get_value(),
            "rol_id": self.selRol.get_index(),
            "estado_id": self.selEstado.get_index(),
            "dias_activo": self.dias_actividad.get_value(),
            "con_link": self.chkLink.get_bool(),
            "con_descripcion": self.chkTexto.get_bool(),
            "con_productos": self.chkProductos.get_bool(),
            "registro_desde": self.date_registro_min.get_value(),
            "registro_hasta": self.date_registro_max.get_value()
        }
        return filtros
