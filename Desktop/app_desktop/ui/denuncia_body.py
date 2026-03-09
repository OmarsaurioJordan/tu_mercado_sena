from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QLineEdit, QGroupBox, QTextEdit, QApplication
)
from PySide6.QtCore import Qt, Signal
from components.selector import Selector
from components.usuario_card import UsuarioCard
from components.producto_card import ProductoCard
from components.boton import Boton

class DenunciaBody(QWidget):
    cambioData = Signal(int) # id denuncia
    card_usuario_clic = Signal(int) # id usuario
    card_producto_clic = Signal(int) # id producto
    card_chat_clic = Signal(int) # id chat

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

        self.nickname = QLabel("", self)
        self.nickname.setWordWrap(True)
        font = self.nickname.font()
        font.setBold(True)
        font.setPointSize(20)
        self.nickname.setFont(font)
        self.nickname.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.email = QLabel("", self)
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

        self.botonChat = Boton("Chat Denunciado")
        self.botonChat.clicked.connect(self.goToChat)
        botonLayout = QHBoxLayout()
        botonLayout.addStretch()
        botonLayout.addWidget(self.botonChat)
        botonLayout.addStretch()

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
        layVertical.addSpacing(5)
        layVertical.addLayout(botonLayout)
        layVertical.addSpacing(20)
        layVertical.addWidget(self.registro)
        layVertical.addStretch()
        self.setLayout(layVertical)
        self.resetData()
    
    def labelFechas(self, texto=""):
        label = QLabel(texto, self)
        label.setStyleSheet("color: #777777;")
        label.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        return label
    
    def limpiarFichas(self):
        print(f"DenunciaBody {self.id}: limpiarFichas")
        for pfi in [self.portaFicha, self.portaFichas]:
            while pfi.count():
                item = pfi.takeAt(0)
                widget = item.widget()
                if widget is not None:
                    widget.deleteLater()
        self.update()

    def resetData(self):
        print(f"DenunciaBody {self.id}: resetData")
        self.id = 0
        self.denunciante_id = 0
        self.producto_id = 0
        self.usuario_id = 0
        self.chat_id = 0
        self.email.setText("*** email ***")
        self.nickname.setText("*** ??? ***")
        self.registro.setText("Registro")
        self.sel_motivo.set_index(0)
        self.sel_estado.set_index(0)
        self.sel_motivo.set_ente_id(0)
        self.sel_estado.set_ente_id(0)
        self.limpiarFichas()
        self.botonChat.setEnabled(False)
        self.cambioData.emit(0)

    def setData(self, denuncia):
        if denuncia is None:
            self.resetData()
            return
        print(f"DenunciaBody {denuncia.id}: setData")
        self.id = denuncia.id
        self.denunciante_id = denuncia.denunciante_id
        self.usuario_id = denuncia.usuario_id
        self.producto_id = denuncia.producto_id
        self.chat_id = denuncia.chat_id
        self.cambioData.emit(denuncia.id)
        self.email.setText(denuncia.email)
        self.nickname.setText(denuncia.denunciante_name)
        self.registro.setText("Registro\n" + denuncia.fecha_registro.replace(" ", "\n") + "\n" + str(denuncia.dias) + " días")
        self.sel_motivo.set_index_from_data(denuncia.motivo_id)
        self.sel_estado.set_index_from_data(denuncia.estado_id)
        self.sel_motivo.set_ente_id(denuncia.id)
        self.sel_estado.set_ente_id(denuncia.id)
        self.sel_estado.set_disabled(denuncia.estado_id != 1)
        self.limpiarFichas()
        self.newFicha(denuncia.denunciante_id, True, self.portaFicha)
        self.newFicha(denuncia.usuario_id, True, self.portaFichas)
        self.newFicha(denuncia.producto_id, False, self.portaFichas)
        self.botonChat.setEnabled(denuncia.chat_id != 0)

    def newFicha(self, id=0, is_usuario=True, layer_padre=None):
        if id != 0:
            print(f"DenunciaBody {id}: newFicha")
            if is_usuario:
                ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
                usr = ctrlUsuario.get_usuario(id)
                ficha = UsuarioCard(usr, parent=self)
                ficha.card_clic.connect(self._click_usuario_event)
            else:
                ctrlProducto = QApplication.instance().property("controls").get_productos()
                prod = ctrlProducto.get_producto(id)
                ficha = ProductoCard(prod, parent=self)
                ficha.card_clic.connect(self._click_producto_event)
            contenedor = QWidget()
            lay = QHBoxLayout(contenedor)
            lay.addStretch()
            lay.addWidget(ficha)
            lay.addStretch()
            layer_padre.addWidget(contenedor)
    
    def _click_usuario_event(self, user_id):
        print(f"DenunciaBody {self.id}: _click_usuario_event")
        self.card_usuario_clic.emit(user_id)
    
    def _click_producto_event(self, prod_id):
        print(f"DenunciaBody {self.id}: _click_producto_event")
        self.card_producto_clic.emit(prod_id)
    
    def goToChat(self):
        print(f"DenunciaBody {self.id}: goToChat")
        self.card_chat_clic.emit(self.chat_id)

    def actualizar(self, id=0):
        if self.id == 0 or id != self.id:
            return
        print(f"DenunciaBody {id}: actualizar")
        ctrlDenuncia = QApplication.instance().property("controls").get_denuncias()
        self.setData(ctrlDenuncia.get_denuncia(id))

    def set_is_seleccionado(self, usuario_id=0):
        if usuario_id != 0:
            print(f"DenunciaBody {self.id} - {usuario_id}: set_is_seleccionado")
            if usuario_id != self.denunciante_id and usuario_id != self.usuario_id:
                self.resetData()
    
    def set_is_producto(self, producto_id=0):
        if producto_id != 0:
            print(f"DenunciaBody {self.id} - {producto_id}: set_is_producto")
            if producto_id != self.producto_id:
                self.resetData()
