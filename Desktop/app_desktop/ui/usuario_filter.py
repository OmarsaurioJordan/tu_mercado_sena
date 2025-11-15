from PySide6.QtWidgets import (
    QWidget, QVBoxLayout
)
from PySide6.QtCore import QDate
from components.txt_edit import TxtEdit
from components.selector import Selector
from components.checkbox import Checkbox
from components.spinbox import SpinBox
from components.date_edit import DateEdit

class Usuariofilter(QWidget):
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
        self.chkConectados = Checkbox("Con Link")
        layVertical.addWidget(self.chkConectados)
        self.chkConectados = Checkbox("Con Texto")
        layVertical.addWidget(self.chkConectados)
        self.chkConectados = Checkbox("Con Productos")
        layVertical.addWidget(self.chkConectados)
        self.dias_actividad = SpinBox("Días activo", 0, 365, 8)
        layVertical.addWidget(self.dias_actividad)
        self.date_registro_min = DateEdit("Registros desde", QDate(2010, 1, 1))
        layVertical.addWidget(self.date_registro_min)
        self.date_registro_max = DateEdit("Registros hasta")
        layVertical.addWidget(self.date_registro_max)
        layVertical.addStretch()
        self.setLayout(layVertical)
