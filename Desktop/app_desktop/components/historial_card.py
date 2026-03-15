from PySide6.QtWidgets import (
    QFrame, QVBoxLayout, QHBoxLayout, QLabel, QApplication, QSizePolicy
)
from PySide6.QtCore import Qt, Signal

class HistorialCard(QFrame):
    card_clic = Signal(int) # id chat

    def __init__(self, chat, parent=None):
        super().__init__(parent)
        self.id = chat.id
        self.miItem = None

        ctrlChat = QApplication.instance().property("controls").get_chats()
        ctrlChat.chat_signal.hubo_cambio.connect(self.actualizar)

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

        self.comprador = QLabel("", self)
        self.comprador.setWordWrap(True)
        font = self.comprador.font()
        font.setBold(True)
        self.comprador.setFont(font)
        self.comprador.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.informacion = QLabel("", self)
        self.informacion.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layParteA = QVBoxLayout()
        layParteA.addWidget(self.comprador)
        dupla = QHBoxLayout()
        dupla.addWidget(self.informacion)
        dupla.addWidget(self.dias)
        layParteA.addLayout(dupla)

        self.vendedor = QLabel("", self)
        self.vendedor.setWordWrap(True)
        font = self.vendedor.font()
        font.setBold(True)
        self.vendedor.setFont(font)
        self.vendedor.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        self.producto = QLabel("", self)
        self.producto.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignTop
        )

        layParteB = QVBoxLayout()
        layParteB.addWidget(self.vendedor)
        layParteB.addWidget(self.producto)

        line = QFrame()
        line.setFrameShape(QFrame.Shape.VLine)
        line.setFrameShadow(QFrame.Shadow.Sunken)

        layHorizontal = QHBoxLayout()
        layHorizontal.addLayout(layParteA)
        layHorizontal.addWidget(line)
        layHorizontal.addLayout(layParteB)
        self.setLayout(layHorizontal)
        self.setData(chat)

    def setData(self, chat):
        if chat is None:
            return
        print(f"ChatCard {chat.id}: setData")
        self.dias.setText(str(chat.dias) + " días")
        self.vendedor.setText(chat.vendedor_name)
        self.comprador.setText(chat.comprador_name)
        self.producto.setText(chat.producto_name)
        self.informacion.setText("$ " + str(int(chat.precio)))
        self.estado_color = {
            1: "#e6e5e5", # activo
            6: "#e6e5e5", # esperando
            5: "#d2edf8", # vendido
            7: "#d2edf8", # devolviendo
            3: "#B9B9B9", # eliminado
            8: "#f7d9ac", # devuelto
            9: "#f4f7ac" # censurado
        }.get(chat.estado_id, "#f88eef") # error
        self.setPulsado()

    def actualizar(self, id=0):
        if id != 0 and id == self.id:
            print(f"ChatCard {id}: actualizar")
            ctrlChat = QApplication.instance().property("controls").get_chats()
            self.setData(ctrlChat.get_chat(id))

    def setPulsado(self, is_pulsado=False):
        print(f"ChatCard {self.id}: setPulsado")
        if is_pulsado:
            self.setStyleSheet(f"""
                HistorialCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                HistorialCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        print(f"ChatCard {self.id}: mousePressEvent")
        self.card_clic.emit(self.id)
        super().mousePressEvent(event)
