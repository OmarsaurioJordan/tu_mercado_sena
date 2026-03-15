from PySide6.QtWidgets import (
    QFrame, QVBoxLayout, QHBoxLayout, QLabel, QApplication, QSizePolicy
)
from PySide6.QtCore import Qt, Signal
from PySide6.QtGui import QPixmap

class PapeleraCard(QFrame):
    card_clic = Signal(int) # id papelera

    def __init__(self, papelera, parent=None):
        super().__init__(parent)
        self.id = papelera.id
        self.miItem = None
        self.con_imagen = papelera.mensaje == "" and papelera.imagen != ""

        ctrlPapelera = QApplication.instance().property("controls").get_papelera()
        ctrlPapelera.papelera_signal.hubo_cambio.connect(self.actualizar)

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setSizePolicy(QSizePolicy.Policy.Expanding,
            QSizePolicy.Policy.Fixed)
        
        if self.con_imagen:
            self.datos = QLabel(self)
            self.datos.setScaledContents(True)
            self.datos.setFixedSize(120, 120)
            self.datos.setPixmap(QPixmap("assets/sprites/img_null.png"))
        else:
            self.datos = QLabel("", self)
            self.datos.setWordWrap(True)
            self.datos.setTextInteractionFlags(Qt.NoTextInteraction)
        self.datos.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.fecha_registro = QLabel("", self)
        self.fecha_registro.setStyleSheet("color: #777777;")
        self.fecha_registro.setSizePolicy(QSizePolicy.Policy.Minimum, QSizePolicy.Policy.Fixed)
        self.fecha_registro.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layParte = QVBoxLayout()
        layHor = QHBoxLayout()
        layHor.addStretch()
        layHor.addWidget(self.datos)
        layHor.addStretch()
        layParte.addLayout(layHor)
        layParte.addWidget(self.fecha_registro)

        self.setLayout(layParte)
        self.setData(papelera)

    def setData(self, papelera):
        if papelera is None:
            return
        print(f"PapeleraCard {papelera.id}: setData")
        self.fecha_registro.setText(papelera.fecha_registro)
        if self.con_imagen:
            self.datos.setPixmap(papelera.img_pix)
        else:
            self.datos.setText(papelera.mensaje)
        self.estado_color = "#e6e5e5"
        self.setPulsado()

    def actualizar(self, id=0):
        if id != 0 and id == self.id:
            print(f"PapeleraCard {id}: actualizar")
            ctrlPapelera = QApplication.instance().property("controls").get_papelera()
            self.setData(ctrlPapelera.get_papelera(id))

    def setPulsado(self, is_pulsado=False):
        print(f"PapeleraCard {self.id}: setPulsado")
        if is_pulsado:
            self.setStyleSheet(f"""
                PapeleraCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                PapeleraCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        print(f"PapeleraCard {self.id}: mousePressEvent")
        self.card_clic.emit(self.id)
        super().mousePressEvent(event)
