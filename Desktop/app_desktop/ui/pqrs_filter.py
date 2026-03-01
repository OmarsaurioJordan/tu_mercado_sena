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

class PqrsFilter(QWidget):
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
        self.selMotivo = Selector(
            [["Todos", 0]] + ctrlData.get_motivos("pqrs"),
            "motivo...", "Motivo", 0
        )
        layVertical.addWidget(self.selMotivo)
        self.selEstado = Selector(
            [["Todos", 0]] + ctrlData.get_estados_resueltos(),
            "estado...", "Estado", 1
        )
        layVertical.addWidget(self.selEstado)
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
        print("PqrsFilter: emitir_aplicar")
        filtros = self.obtener_filtros()
        self.clicAplicar.emit(filtros)

    def obtener_filtros(self):
        filtros = {
            "nickname": self.txtNickname.get_value(),
            "email": self.txtEmail.get_value(),
            "motivo_id": self.selMotivo.get_data(),
            "estado_id": self.selEstado.get_data(),
            "registro_desde": self.date_registro_min.get_value_utc(),
            "registro_hasta": self.date_registro_max.get_value_utc()
        }
        return filtros
