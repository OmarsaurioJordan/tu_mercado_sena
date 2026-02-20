from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QGroupBox, QTextEdit, QApplication
)
from PySide6.QtCore import Qt, Signal
from components.selector import Selector
from components.usuario_card import UsuarioCard

class PqrsBody(QWidget):
    cambioData = Signal(int)
    card_clic = Signal(int)

    def __init__(self):
        super().__init__()
        self.id = 0

        ctrlPqrs = QApplication.instance().property("controls").get_pqrs()
        ctrlPqrs.pqrs_signal.hubo_cambio.connect(self.actualizar)

        ctrlData = QApplication.instance().property("controls").get_data()

        self.portaFicha = QVBoxLayout()

        self.nickname = QLabel("")
        self.nickname.setWordWrap(True)
        font = self.nickname.font()
        font.setBold(True)
        font.setPointSize(20)
        self.nickname.setFont(font)
        self.nickname.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.email = QLabel("")
        self.email.setWordWrap(True)
        self.email.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        laySelectores = QHBoxLayout()
        self.sel_motivo = Selector(ctrlData.get_motivos("pqrs"),
            "motivo...", "Motivo", 0, "pqrs_motivo")
        self.sel_motivo.set_disabled(True)
        laySelectores.addWidget(self.sel_motivo)
        laySelectores.addSpacing(10)
        self.sel_estado = Selector(ctrlData.get_estados_resueltos(),
            "estado...", "Estado", 0, "pqrs_estado")
        laySelectores.addWidget(self.sel_estado)

        self.descripcion = QLabel("")
        self.descripcion.setWordWrap(True)
        self.descripcion.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.registro = self.labelFechas("Registro")

        groupMensaje = QGroupBox("Mensaje")
        layMsj = QVBoxLayout()
        self.mensaje = QTextEdit()
        self.mensaje.setAlignment(Qt.AlignJustify)
        self.mensaje.setPlaceholderText("escribe un texto que será enviado al email del usuario, se agregarán automáticamente cabecera y pie de página con saludo e información del administrador remitente")
        layMsj.addWidget(self.mensaje)
        layMsj.addSpacing(10)
        self.btnMensaje = Boton("Enviar Mensaje")
        layMsj.addWidget(self.btnMensaje)
        groupMensaje.setLayout(layMsj)

        layVertical = QVBoxLayout()
        layVertical.addSpacing(10)
        layVertical.addLayout(self.portaFicha)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.nickname)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.email)
        layVertical.addSpacing(10)
        layVertical.addLayout(laySelectores)
        layVertical.addSpacing(20)
        layVertical.addWidget(self.descripcion)
        layVertical.addSpacing(20)
        layVertical.addWidget(self.registro)
        layVertical.addSpacing(20)
        layVertical.addWidget(groupMensaje)
        layVertical.addStretch()
        self.setLayout(layVertical)
        self.resetData()
    
    def labelFechas(self, texto=""):
        label = QLabel(texto)
        label.setStyleSheet("color: #777777;")
        label.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        return label
    
    def limpiarFicha(self):
        while self.portaFicha.count():
            item = self.portaFicha.takeAt(0)
            widget = item.widget()
            if widget is not None:
                widget.setParent(None)
                widget.deleteLater()
            del item
        self.update()

    def resetData(self):
        self.id = 0
        self.email.setText("*** email ***")
        self.nickname.setText("*** ??? ***")
        self.descripcion.setText("*** mensaje vacío ***")
        self.registro.setText("Registro")
        self.mensaje.setText("")
        self.sel_motivo.set_index(0)
        self.sel_estado.set_index(0)
        self.sel_motivo.set_ente_id(0)
        self.sel_estado.set_ente_id(0)
        self.limpiarFicha()
        self.cambioData.emit(0)

    def setData(self, pqrs):
        if pqrs is None:
            self.resetData()
            return
        self.id = pqrs.id
        self.email.setText(pqrs.email)
        self.nickname.setText(pqrs.usuario_name)
        if pqrs.mensaje == "":
            self.descripcion.setText("*** mensaje vacío ***")
        else:
            self.descripcion.setText(pqrs.mensaje)
        self.registro.setText("Registro\n" + pqrs.fecha_registro.replace(" ", "\n") + "\n" + str(pqrs.dias) + " días")
        self.sel_motivo.set_index_from_data(pqrs.motivo_id)
        self.sel_estado.set_index_from_data(pqrs.estado_id)
        self.sel_motivo.set_ente_id(pqrs.id)
        self.sel_estado.set_ente_id(pqrs.id)
        self.limpiarFicha()
        self.newFicha(pqrs.usuario_id)
        self.cambioData.emit(pqrs.id)

    def newFicha(self, id=0):
        if id != 0:
            ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
            usr = ctrlUsuario.get_usuario(id)
            ficha = UsuarioCard(usr)
            ficha.card_clic.connect(self._click_event)
            self.portaFicha.addWidget(ficha)
    
    def _click_event(self, user_id):
        self.card_clic.emit(user_id)

    def actualizar(self, id=0):
        if id == self.id:
            ctrlPqrs = QApplication.instance().property("controls").get_pqrs()
            self.setData(ctrlPqrs.get_pqrs(id))

    def set_is_seleccionado(self, seleccionado_id=0):
        if seleccionado_id != self.id:
            self.resetData()
