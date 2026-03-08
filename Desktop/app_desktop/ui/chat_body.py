from PySide6.QtWidgets import (
    QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
)
from PySide6.QtCore import Qt, Signal
from components.selector import Selector
from components.usuario_card import UsuarioCard
from components.producto_card import ProductoCard
from core.app_config import CALIFICACION_MAX

class ChatBody(QWidget):
    cambioData = Signal(int) # id chat
    card_usuario_clic = Signal(int) # id usuario
    card_producto_clic = Signal(int) # id producto

    def __init__(self):
        super().__init__()
        self.id = 0
        self.comprador_id = 0
        self.producto_id = 0
        self.vendedor_id = 0

        ctrlChat = QApplication.instance().property("controls").get_chats()
        ctrlChat.chat_signal.hubo_cambio.connect(self.actualizar)

        ctrlData = QApplication.instance().property("controls").get_data()

        self.portaFicha = QVBoxLayout()
        self.calificacion = QLabel("", self)
        self.calificacion.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        compradorLayout = QHBoxLayout()
        compradorLayout.addLayout(self.portaFicha)
        compradorLayout.addStretch()
        compradorLayout.addWidget(self.calificacion)

        self.sel_estado = Selector(ctrlData.get_estados_chats(),
            "estado...", "Estado", 0, "chat_estado")
        self.precio = QLabel("", self)
        self.precio.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        self.cantidad = QLabel("", self)
        self.cantidad.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        datosLayout = QVBoxLayout()
        datosLayout.addSpacing(10)
        datosLayout.addWidget(self.sel_estado)
        datosLayout.addSpacing(10)
        datosLayout.addWidget(self.precio)
        datosLayout.addSpacing(10)
        datosLayout.addWidget(self.cantidad)

        self.portaFichas = QVBoxLayout()
        vendedorLayout = QHBoxLayout()
        vendedorLayout.addLayout(datosLayout)
        vendedorLayout.addStretch()
        vendedorLayout.addWidget(self.portaFichas)
        
        self.portaChat = QVBoxLayout()

        layVertical = QVBoxLayout()
        layVertical.addSpacing(10)
        layVertical.addLayout(compradorLayout)
        layVertical.addSpacing(10)
        layVertical.addWidget(vendedorLayout)
        layVertical.addSpacing(10)
        layVertical.addLayout(self.portaChat)
        layVertical.addStretch()
        self.setLayout(layVertical)
        self.resetData()
    
    def limpiarFichas(self):
        print(f"ChatBody {self.id}: limpiarFichas")
        for pfi in [self.portaFicha, self.portaFichas]:
            while pfi.count():
                item = pfi.takeAt(0)
                widget = item.widget()
                if widget is not None:
                    widget.deleteLater()
        self.update()

    def limpiarMensajes(self):
        pass

    def setCalificacion(self, numero=0):
        # coloca 🌑🌕 hasta alcanzar limite CALIFICACION_MAX
        numero = max(0, min(numero, CALIFICACION_MAX))
        return "🌕" * numero + "🌑" * (CALIFICACION_MAX - numero)

    def resetData(self):
        print(f"ChatBody {self.id}: resetData")
        self.id = 0
        self.comprador_id = 0
        self.producto_id = 0
        self.vendedor_id = 0
        self.calificacion.setText(self.setCalificacion(0) + "\n\n*** reseña del comprador ***")
        self.precio.setText("$ ???")
        self.cantidad.setText("???")
        self.sel_estado.set_index(0)
        self.sel_estado.set_ente_id(0)
        self.limpiarFichas()
        self.limpiarMensajes()
        self.cambioData.emit(0)

    def setData(self, chat):
        if chat is None:
            self.resetData()
            return
        print(f"ChatBody {chat.id}: setData")
        self.id = chat.id
        self.comprador_id = chat.comprador_id
        self.producto_id = chat.producto_id
        self.vendedor_id = chat.vendedor_id
        self.cambioData.emit(chat.id)
        self.calificacion.setText(self.setCalificacion(chat.calificacion) + "\n\n" + chat.comentario)
        self.precio.setText("$ " + str(chat.precio))
        self.cantidad.setText(str(chat.cantidad))
        self.sel_estado.set_index_from_data(chat.estado_id)
        self.sel_estado.set_ente_id(chat.id)
        self.limpiarFichas()
        self.newFicha(chat.comprador_id, True, self.portaFicha)
        self.newFicha(chat.vendedor_id, True, self.portaFichas)
        self.newFicha(chat.producto_id, False, self.portaFichas)

    def newFicha(self, id=0, is_usuario=True, layer_padre=None):
        if id != 0:
            print(f"ChatBody {id}: newFicha")
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
        print(f"ChatBody {self.id}: _click_usuario_event")
        self.card_usuario_clic.emit(user_id)
    
    def _click_producto_event(self, prod_id):
        print(f"ChatBody {self.id}: _click_producto_event")
        self.card_producto_clic.emit(prod_id)

    def actualizar(self, id=0):
        if self.id == 0 or id != self.id:
            return
        print(f"ChatBody {id}: actualizar")
        ctrlChat = QApplication.instance().property("controls").getchats()
        self.setData(ctrlChat.get_chat(id))

    def set_is_seleccionado(self, usuario_id=0):
        if usuario_id != 0:
            print(f"ChatBody {self.id} - {usuario_id}: set_is_seleccionado")
            if usuario_id != self.comprador_id and usuario_id != self.vendedor_id:
                self.resetData()
    
    def set_is_producto(self, producto_id=0):
        if producto_id != 0:
            print(f"ChatBody {self.id} - {producto_id}: set_is_producto")
            if producto_id != self.producto_id:
                self.resetData()
