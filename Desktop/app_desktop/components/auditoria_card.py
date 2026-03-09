from PySide6.QtWidgets import (
    QFrame, QVBoxLayout, QHBoxLayout, QLabel, QApplication, QSizePolicy
)
from PySide6.QtCore import Qt, Signal

class AuditoriaCard(QFrame):
    card_clic = Signal(int) # id auditoria

    def __init__(self, auditoria, parent=None):
        super().__init__(parent)
        self.id = auditoria.id
        self.administrador_id = auditoria.administrador_id
        self.miItem = None

        ctrlAuditoria = QApplication.instance().property("controls").get_auditorias()
        ctrlAuditoria.auditoria_signal.hubo_cambio.connect(self.actualizar)

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setSizePolicy(QSizePolicy.Policy.Expanding,
            QSizePolicy.Policy.Fixed)
        self.estado_color = "#e6e5e5"

        self.cuando = QLabel("", self)
        self.cuando.setStyleSheet("color: #777777;")
        self.cuando.setSizePolicy(QSizePolicy.Policy.Minimum, QSizePolicy.Policy.Fixed)
        self.cuando.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.nickname = QLabel("", self)
        self.nickname.setWordWrap(True)
        font = self.nickname.font()
        font.setBold(True)
        self.nickname.setFont(font)
        self.nickname.setAlignment(
            Qt.AlignmentFlag.AlignRight | Qt.AlignmentFlag.AlignTop
        )

        self.email = QLabel("", self)
        self.email.setStyleSheet("color: #777777;")
        self.email.setSizePolicy(QSizePolicy.Policy.Minimum, QSizePolicy.Policy.Fixed)
        self.email.setAlignment(
            Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignTop
        )

        self.mensaje = QLabel("", self)
        self.mensaje.setWordWrap(True)
        self.mensaje.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layTitulo = QHBoxLayout()
        layTitulo.addWidget(self.nickname)
        layTitulo.addSpacing(10)
        layTitulo.addWidget(self.email)

        layBody = QVBoxLayout()
        layBody.addLayout(layTitulo)
        layBody.addWidget(self.mensaje)

        layHorizontal = QHBoxLayout()
        layHorizontal.addWidget(self.cuando)
        layHorizontal.addLayout(layBody)
        layHorizontal.setStretch(0, 0)
        layHorizontal.setStretch(1, 1)
        self.setLayout(layHorizontal)
        self.setData(auditoria)

    def setData(self, auditoria):
        if auditoria is None:
            return
        print(f"AuditoriaCard {auditoria.id}: setData")
        self.administrador_id = auditoria.administrador_id
        self.cuando.setText(auditoria.fecha_registro.replace(" ", "\n") + " - " + str(auditoria.dias) + " días")
        self.nickname.setText(auditoria.nickname)
        self.email.setText(auditoria.email + (" (Admin)" if auditoria.rol_id != 1 else ""))
        ctrlData = QApplication.instance().property("controls").get_data()
        suceso = ctrlData.get_row("sucesos", auditoria.suceso_id)
        if suceso:
            suceso = suceso["nombre"].capitalize()
        else:
            suceso = "???"
        self.mensaje.setText(suceso + ": " + auditoria.descripcion)
        self.setPulsado()

    def actualizar(self, id=0):
        if id != 0 and id == self.id:
            print(f"AuditoriaCard {id}: actualizar")
            ctrlAuditoria = QApplication.instance().property("controls").get_auditorias()
            self.setData(ctrlAuditoria.get_auditoria(id))

    def setPulsado(self, is_pulsado=False):
        print(f"AuditoriaCard {self.id}: setPulsado")
        if is_pulsado:
            self.setStyleSheet(f"""
                AuditoriaCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                AuditoriaCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        print(f"AuditoriaCard {self.id}: mousePressEvent")
        self.card_clic.emit(self.administrador_id)
        super().mousePressEvent(event)
