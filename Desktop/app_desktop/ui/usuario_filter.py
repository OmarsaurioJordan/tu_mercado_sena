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

class UsuarioFilter(QWidget):
    clicAplicar = Signal(dict)

    def __init__(self):
        super().__init__()

        ctrlData = QApplication.instance().property("controls").get_data()

        layVertical = QVBoxLayout()
        layVertical.setSpacing(10)
        self.txtNickname = TxtEdit("Nickname", "nickname")
        layVertical.addWidget(self.txtNickname)
        self.txtEmail = TxtEdit("Email", "email")
        layVertical.addWidget(self.txtEmail)
        self.selRol = Selector(
            [["Todos", 0]] + ctrlData.get_roles_basicos(),
            "rol...", "Rol", 1
        )
        self.selRol.set_index_from_data(1)
        layVertical.addWidget(self.selRol)
        self.selEstado = Selector(
            [["Todos", 0]] + ctrlData.get_estados_basicos() + [["Act-Inv", 100], ["Bloq-Denun", 101]],
            "estado...", "Estado", 1
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
        print("UsuarioFilter: emitir_aplicar")
        filtros = self.obtener_filtros()
        self.clicAplicar.emit(filtros)

    def obtener_filtros(self):
        filtros = {
            "nickname": self.txtNickname.get_value(),
            "email": self.txtEmail.get_value(),
            "rol_id": self.selRol.get_data(),
            "estado_id": self.selEstado.get_data(),
            "dias_activo": self.dias_actividad.get_value(),
            "con_link": self.chkLink.get_bool(),
            "con_descripcion": self.chkTexto.get_bool(),
            "con_productos": self.chkProductos.get_bool(),
            "registro_desde": self.date_registro_min.get_value_utc(),
            "registro_hasta": self.date_registro_max.get_value_utc()
        }
        return filtros
