from PySide6.QtWidgets import (
    QFrame, QVBoxLayout, QHBoxLayout, QLabel, QApplication, QSizePolicy
)
from PySide6.QtCore import Qt, Signal

class PqrsCard(QFrame):
    card_clic = Signal(int)

    def __init__(self, pqrs, parent=None):
        super().__init__(parent)
        self.id = pqrs.id
        self.miItem = None

        ctrlPqrs = QApplication.instance().property("controls").get_pqrss()
        ctrlPqrs.pqrs_signal.hubo_cambio.connect(self.actualizar)

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

        self.nickname = QLabel("", self)
        self.nickname.setWordWrap(True)
        font = self.nickname.font()
        font.setBold(True)
        self.nickname.setFont(font)
        self.nickname.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.motivo = QLabel("", self)
        self.motivo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layHeader = QVBoxLayout()
        layHeader.addWidget(self.nickname)
        layHeader.addWidget(self.motivo)

        self.mensaje = QLabel("", self)
        self.mensaje.setWordWrap(True)
        self.mensaje.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layHorizontal = QHBoxLayout()
        layHorizontal.addWidget(self.dias)
        layHorizontal.addLayout(layHeader)
        layHorizontal.addWidget(self.mensaje)
        layHorizontal.setStretch(0, 0)
        layHorizontal.setStretch(1, 1)
        layHorizontal.setStretch(2, 1)
        self.setLayout(layHorizontal)
        self.setData(pqrs)

    def setData(self, pqrs):
        if pqrs is None:
            return
        print(f"PqrsCard {pqrs.id}: setData")
        self.dias.setText(str(pqrs.dias) + " días")
        self.nickname.setText(pqrs.usuario_name)
        data = QApplication.instance().property("controls").get_data()
        motivo = data.get_row("motivos", pqrs.motivo_id)
        mot_txt = motivo["nombre"].capitalize() if motivo is not None else "???"
        self.motivo.setText(mot_txt)
        self.mensaje.setText(self.elide_text(pqrs.mensaje, 120))
        self.estado_color = {
            1: "#e6e5e5", # activo
            11: "#B9B9B9", # resuelto
        }.get(pqrs.estado_id, "#f88eef") # error
        self.setPulsado()

    def actualizar(self, id=0):
        if id != 0 and id == self.id:
            print(f"PqrsCard {id}: actualizar")
            ctrlPqrs = QApplication.instance().property("controls").get_pqrss()
            self.setData(ctrlPqrs.get_pqrs(id))

    def setPulsado(self, is_pulsado=False):
        print(f"PqrsCard {self.id}: setPulsado")
        if is_pulsado:
            self.setStyleSheet(f"""
                PqrsCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                PqrsCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        print(f"PqrsCard {self.id}: mousePressEvent")
        self.card_clic.emit(self.id)
        super().mousePressEvent(event)

    def elide_text(self, text, max_chars=120):
        if len(text) > max_chars:
            return text[:max_chars].rstrip() + "..."
        return text
