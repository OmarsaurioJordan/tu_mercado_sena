from PySide6.QtWidgets import QFrame, QVBoxLayout, QHBoxLayout, QLabel, QApplication, QSizePolicy
from PySide6.QtCore import Qt, Signal

class LoginCard(QFrame):
    card_clic = Signal(int) # id login

    def __init__(self, login, parent=None):
        super().__init__(parent)
        self.id = login.id
        self.usuario_id = login.usuario_id
        self.miItem = None

        ctrlLogin = QApplication.instance().property("controls").get_logins()
        ctrlLogin.login_signal.hubo_cambio.connect(self.actualizar)

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
        self.setData(login)

    def setData(self, login):
        if login is None:
            return
        print(f"LoginCard {login.id}: setData")
        self.usuario_id = login.usuario_id
        fecha = login.fecha_registro or ""
        self.cuando.setText(fecha.replace(" ", "\n") + " - " + str(login.dias) + " días")
        self.nickname.setText(login.nickname)
        self.email.setText(login.email + (" (Admin)" if login.rol_id != 1 else ""))
        self.mensaje.setText(login.ip_direccion + " - " + login.informacion)
        self.setPulsado()

    def actualizar(self, id=0):
        if id != 0 and id == self.id:
            print(f"LoginCard {id}: actualizar")
            ctrlLogin = QApplication.instance().property("controls").get_logins()
            self.setData(ctrlLogin.get_login(id))

    def setPulsado(self, is_pulsado=False):
        print(f"LoginCard {self.id}: setPulsado")
        if is_pulsado:
            self.setStyleSheet(f"""
                LoginCard {{
                    background-color: {self.estado_color};
                    border: 2px solid #696969;
                    border-radius: 10px;
                }}
            """)
        else:
            self.setStyleSheet(f"""
                LoginCard {{
                    background-color: {self.estado_color};
                    border: 1px solid #cccccc;
                    border-radius: 10px;
                }}
            """)

    def mousePressEvent(self, event):
        print(f"LoginCard {self.id}: mousePressEvent")
        self.card_clic.emit(self.usuario_id)
        super().mousePressEvent(event)
