from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QGroupBox, QTextEdit, QApplication
)
from PySide6.QtCore import Qt, Signal
from components.selector import Selector
from components.usuario_card import UsuarioCard
from components.producto_card import ProductoCard

class DenunciaBody(QWidget):
    cambioData = Signal(int)
    card_usuario_clic = Signal(int)
    card_producto_clic = Signal(int)

    def __init__(self):
        super().__init__()
        self.id = 0
        self.denunciante_id = 0
        self.producto_id = 0
        self.usuario_id = 0
        self.chat_id = 0

        ctrlDenuncia = QApplication.instance().property("controls").get_denuncias()
        ctrlDenuncia.denuncia_signal.hubo_cambio.connect(self.actualizar)

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
        self.sel_motivo = Selector(ctrlData.get_motivos("denuncia"),
            "motivo...", "Motivo", 0, "denuncia_motivo")
        self.sel_motivo.set_disabled(True)
        laySelectores.addWidget(self.sel_motivo)
        laySelectores.addSpacing(10)
        self.sel_estado = Selector(ctrlData.get_estados_resueltos(),
            "estado...", "Estado", 0, "denuncia_estado")
        laySelectores.addWidget(self.sel_estado)

        self.portaFichas = QVBoxLayout()

        self.registro = self.labelFechas("Registro")

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
        layVertical.addLayout(self.portaFichas)
        layVertical.addSpacing(20)
        layVertical.addWidget(self.registro)
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
    
    def limpiarFichas(self):
        for pfi in [self.portaFicha, self.portaFichas]:
            while pfi.count():
                item = pfi.takeAt(0)
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
        self.registro.setText("Registro")
        self.sel_motivo.set_index(0)
        self.sel_estado.set_index(0)
        self.sel_motivo.set_ente_id(0)
        self.sel_estado.set_ente_id(0)
        self.limpiarFichas()
        self.cambioData.emit(0)

    def setData(self, denuncia):
        if denuncia is None:
            self.resetData()
            return
        self.id = denuncia.id
        self.email.setText(denuncia.email)
        self.nickname.setText(denuncia.denunciante_name)
        self.registro.setText("Registro\n" + denuncia.fecha_registro.replace(" ", "\n") + "\n" + str(denuncia.dias) + " días")
        self.sel_motivo.set_index_from_data(denuncia.motivo_id)
        self.sel_estado.set_index_from_data(denuncia.estado_id)
        self.sel_motivo.set_ente_id(denuncia.id)
        self.sel_estado.set_ente_id(denuncia.id)
        self.limpiarFichas()
        self.newFicha(denuncia.denunciante_id, True, self.portaFicha)
        self.newFicha(denuncia.usuario_id, True, self.portaFichas)
        self.newFicha(denuncia.producto_id, False, self.portaFichas)
        # Tarea falta chat
        self.cambioData.emit(denuncia.id)

    def newFicha(self, id=0, is_usuario=True, layer_padre=None):
        if id != 0:
            if is_usuario:
                ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
                usr = ctrlUsuario.get_usuario(id)
                ficha = UsuarioCard(usr)
                ficha.card_clic.connect(self._click_usuario_event)
            else:
                ctrlProducto = QApplication.instance().property("controls").get_productos()
                prod = ctrlProducto.get_producto(id)
                ficha = ProductoCard(prod)
                ficha.card_clic.connect(self._click_producto_event)
            layer_padre.addWidget(ficha)
    
    def _click_usuario_event(self, user_id):
        self.card_usuario_clic.emit(user_id)
    
    def _click_producto_event(self, prod_id):
        self.card_producto_clic.emit(prod_id)

    def actualizar(self, id=0):
        if id == self.id:
            ctrlDenuncia = QApplication.instance().property("controls").get_denuncias()
            self.setData(ctrlDenuncia.get_denuncia(id))

    def set_is_seleccionado(self, usuario_id=0):
        if usuario_id != 0:
            if usuario_id != self.denunciante_id and usuario_id != self.usuario_id:
                self.resetData()
    
    def set_is_producto(self, producto_id=0):
        if producto_id != 0:
            if producto_id != self.producto_id:
                self.resetData()
