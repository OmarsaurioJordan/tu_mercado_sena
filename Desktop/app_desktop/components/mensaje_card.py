from PySide6.QtWidgets import QFrame, QVBoxLayout, QHBoxLayout, QLabel, QApplication, QSizePolicy
from PySide6.QtCore import Qt, Signal
from PySide6.QtGui import QPixmap
from services.recursos import newSprit

class MensajeCard(QFrame):
    card_clic = Signal(int) # id mensaje

    def __init__(self, mensaje, parent=None, is_dialogo=False):
        super().__init__(parent)
        self.id = mensaje.id
        self.chat_id = mensaje.chat_id
        self.miItem = None
        self.is_dialogo = is_dialogo
        self.es_comprador = mensaje.es_comprador != 0
        self.con_imagen = mensaje.mensaje == "" and mensaje.imagen != ""

        if self.is_dialogo:
            ctrlMensaje = QApplication.instance().property("controls").get_dialogo()
        else:
            ctrlMensaje = QApplication.instance().property("controls").get_mensajes()
        ctrlMensaje.mensaje_signal.hubo_cambio.connect(self.actualizar)

        self.setFrameShape(QFrame.Shape.StyledPanel)
        self.setFrameShadow(QFrame.Shadow.Raised)
        self.setSizePolicy(QSizePolicy.Policy.Expanding,
            QSizePolicy.Policy.Fixed)
        
        if self.con_imagen:
            self.datos = QLabel(self)
            self.datos.setScaledContents(True)
            self.datos.setFixedSize(120, 120)
            self.datos.setPixmap(QPixmap(newSprit("img_null.png")))
        else:
            self.datos = QLabel("", self)
            self.datos.setWordWrap(True)
            self.datos.setTextInteractionFlags(Qt.NoTextInteraction)
        if self.es_comprador:
            self.datos.setAlignment(
                Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignTop)
        else:
            self.datos.setAlignment(
                Qt.AlignmentFlag.AlignRight | Qt.AlignmentFlag.AlignTop)

        self.fecha_registro = QLabel("", self)
        self.fecha_registro.setStyleSheet("color: #777777;")
        self.fecha_registro.setSizePolicy(QSizePolicy.Policy.Minimum, QSizePolicy.Policy.Fixed)
        if self.es_comprador:
            self.fecha_registro.setAlignment(
                Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignTop)
        else:
            self.fecha_registro.setAlignment(
                Qt.AlignmentFlag.AlignRight | Qt.AlignmentFlag.AlignTop)

        layParte = QVBoxLayout()
        if self.con_imagen:
            correImagen = QHBoxLayout()
            if self.es_comprador:
                correImagen.addWidget(self.datos)
                correImagen.addStretch()
            else:
                correImagen.addStretch()
                correImagen.addWidget(self.datos)
            layParte.addLayout(correImagen)
        else:
            layParte.addWidget(self.datos)
        layParte.addWidget(self.fecha_registro)

        layHorizontal = QHBoxLayout()
        if self.es_comprador:
            layHorizontal.addLayout(layParte)
            layHorizontal.addSpacing(50)
        else:
            layHorizontal.addSpacing(50)
            layHorizontal.addLayout(layParte)
        self.setLayout(layHorizontal)
        self.setData(mensaje)

    def setData(self, mensaje):
        if mensaje is None:
            return
        print(f"MensajeCard {mensaje.id}: setData")
        self.chat_id = mensaje.chat_id
        self.fecha_registro.setText(mensaje.fecha_registro)
        if self.con_imagen:
            self.datos.setPixmap(mensaje.img_pix)
        else:
            self.datos.setText(mensaje.mensaje)
        self.estado_color = {
            1: "#e6e5e5", # activo
            6: "#e6e5e5", # esperando
            5: "#d2edf8", # vendido
            7: "#d2edf8", # devolviendo
            3: "#B9B9B9", # eliminado
            8: "#f7d9ac", # devuelto
            9: "#f4f7ac" # censurado
        }.get(mensaje.estado_id, "#f88eef") # error
        self.setPulsado()

    def actualizar(self, id=0):
        if id != 0 and id == self.id:
            print(f"MensajeCard {id}: actualizar")
            if self.is_dialogo:
                ctrlMensaje = QApplication.instance().property("controls").get_dialogo()
            else:
                ctrlMensaje = QApplication.instance().property("controls").get_mensajes()
            self.setData(ctrlMensaje.get_mensaje(id))

    def setPulsado(self, is_pulsado=False):
        print(f"MensajeCard {self.id}: setPulsado")
        if is_pulsado:
            self.setStyleSheet(f"""
                MensajeCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                MensajeCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        print(f"MensajeCard {self.id}: mousePressEvent")
        self.card_clic.emit(self.chat_id)
        super().mousePressEvent(event)
