from PySide6.QtWidgets import (
    QFrame, QVBoxLayout, QHBoxLayout, QLabel, QApplication, QSizePolicy
)
from PySide6.QtCore import Qt, Signal

class DenunciaCard(QFrame):
    card_clic = Signal(int)

    def __init__(self, denuncia, parent=None):
        super().__init__(parent)
        self.id = denuncia.id
        self.miItem = None

        ctrlDenuncia = QApplication.instance().property("controls").get_denuncias()
        ctrlDenuncia.denuncia_signal.hubo_cambio.connect(self.actualizar)

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setSizePolicy(QSizePolicy.Policy.Expanding,
            QSizePolicy.Policy.Fixed)

        self.dias = QLabel("", self)
        self.dias.setStyleSheet("color: #777777;")
        self.dias.setSizePolicy(QSizePolicy.Policy.Minimum, QSizePolicy.Policy.Fixed)
        self.dias.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.denunciante = QLabel("", self)
        self.denunciante.setWordWrap(True)
        font = self.denunciante.font()
        font.setBold(True)
        self.denunciante.setFont(font)
        self.denunciante.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.motivo = QLabel("", self)
        self.motivo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layParteA = QVBoxLayout()
        layParteA.addWidget(self.denunciante)
        layParteA.addWidget(self.motivo)

        self.denunciado = QLabel("", self)
        self.denunciado.setWordWrap(True)
        font = self.denunciado.font()
        font.setBold(True)
        self.denunciado.setFont(font)
        self.denunciado.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.tipo = QLabel("", self)
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
        layHorizontal.setStretch(0, 0)
        layHorizontal.setStretch(1, 1)
        layHorizontal.setStretch(2, 1)
        self.setLayout(layHorizontal)
        self.setData(denuncia)

    def setData(self, denuncia):
        if denuncia is None:
            return
        print(f"DenunciaCard {denuncia.id}: setData")
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
            11: "#B9B9B9", # resuelto
        }.get(denuncia.estado_id, "#f88eef") # error
        self.setPulsado()

    def actualizar(self, id=0):
        if id != 0 and id == self.id:
            print(f"DenunciaCard {id}: actualizar")
            ctrlDenuncia = QApplication.instance().property("controls").get_denuncias()
            self.setData(ctrlDenuncia.get_denuncia(id))

    def setPulsado(self, is_pulsado=False):
        print(f"DenunciaCard {self.id}: setPulsado")
        if is_pulsado:
            self.setStyleSheet(f"""
                DenunciaCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                DenunciaCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        print(f"DenunciaCard {self.id}: mousePressEvent")
        self.card_clic.emit(self.id)
        super().mousePressEvent(event)
