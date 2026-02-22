from PySide6.QtWidgets import (
    QFrame, QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication, QSizePolicy
)
from PySide6.QtCore import Qt, Signal

class DenunciaCard(QFrame):
    card_clic = Signal(int)

    def __init__(self, denuncia):
        super().__init__()
        self.id = denuncia.id

        ctrlDenuncia = QApplication.instance().property("controls").get_denuncias()
        ctrlDenuncia.denuncia_signal.hubo_cambio.connect(self.actualizar)

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setSizePolicy(QSizePolicy.Policy.Expanding,
            QSizePolicy.Policy.Fixed)

        self.dias = QLabel("")
        self.dias.setStyleSheet("color: #777777;")
        self.dias.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.denunciante = QLabel("")
        self.denunciante.setWordWrap(True)
        font = self.denunciante.font()
        font.setBold(True)
        self.denunciante.setFont(font)
        self.denunciante.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.motivo = QLabel("")
        self.motivo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layParteA = QVBoxLayout()
        layParteA.addWidget(self.denunciante)
        layParteA.addWidget(self.motivo)

        self.denunciado = QLabel("")
        self.denunciado.setWordWrap(True)
        self.denunciado.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.tipo = QLabel("")
        self.tipo.setStyleSheet("color: #777777;")
        self.tipo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layParteB = QVBoxLayout()
        layParteB.addWidget(self.denunciado)
        layParteB.addWidget(self.tipo)

        layHorizontal = QHBoxLayout()
        layHorizontal.addWidget(self.dias)
        layHorizontal.addLayout(layParteA)
        layHorizontal.addLayout(layParteB)
        self.setLayout(layHorizontal)
        self.setData(denuncia)

    def setData(self, denuncia):
        if denuncia is None:
            return
        self.dias.setText(str(denuncia.dias) + " días")
        self.denunciante.setText(denuncia.denunciante_name)
        self.denunciado.setText(denuncia.usuario_name)
        data = QApplication.instance().property("controls").get_data()
        motivo = data.get_row("motivos", denuncia.motivo_id)
        mot_txt = motivo["nombre"].capitalize() if motivo is not None else "???"
        self.motivo.setText(mot_txt)
        self.tipo.setText(denuncia.tipo)
        self.estado_color = {
            1: "#e6e5e5", # activo
            11: "#999898", # resuelto
        }.get(denuncia.estado_id, "#f88eef") # error
        self.setPulsado()

    def actualizar(self, id=0):
        if id == self.id:
            ctrlDenuncia = QApplication.instance().property("controls").get_denuncias()
            self.setData(ctrlDenuncia.get_denuncia(id))

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
