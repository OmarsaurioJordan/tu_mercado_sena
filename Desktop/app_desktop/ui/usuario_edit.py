from PySide6.QtWidgets import QWidget, QVBoxLayout, QHBoxLayout, QLabel, QTextEdit, QApplication, QMessageBox
from PySide6.QtCore import Qt
from PySide6.QtGui import QPixmap
from core.app_config import DESCRIPCION_MAX
from components.boton import Boton
from components.txt_edit import TxtEdit
from services.alerta import Alerta
from core.session import Session
from services.recursos import newSprit

class UsuarioEdit(QWidget):

    def __init__(self):
        super().__init__()
        self.id = 0

        ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
        ctrlUsuario.usuario_signal.hubo_cambio.connect(self.actualizar)

        self.imagen = QLabel(self)
        self.imagen.setPixmap(QPixmap(newSprit("avatar.png")))
        self.imagen.setScaledContents(True)
        self.imagen.setFixedSize(192, 192)
        self.imagen.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.nickname = TxtEdit("Nickname", "")
        #font = self.nickname.font()
        #font.setBold(True)
        #font.setPointSize(20)
        #self.nickname.setFont(font)
        #self.nickname.setAlignment(
        #    Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        #)

        self.email = QLabel("", self)
        self.email.setWordWrap(True)
        self.email.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )

        self.link = TxtEdit("Link", "")
        #self.link.setWordWrap(True)
        #self.link.setStyleSheet("color: #777777;")
        #self.link.setOpenExternalLinks(True)
        #self.link.setTextFormat(Qt.RichText)
        #self.link.setAlignment(
        #    Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        #)

        titulo = QLabel("Descripción")
        font = titulo.font()
        font.setBold(True)
        titulo.setFont(font)
        titulo.setAlignment(
            Qt.AlignmentFlag.AlignHCenter | Qt.AlignmentFlag.AlignVCenter
        )
        self.descripcion = QTextEdit()
        self.descripcion.setAlignment(Qt.AlignJustify)
        self.descripcion.setPlaceholderText("descripción, si es el admin Master, será la información de contacto visible para todos")
        self.descripcion.textChanged.connect(self.limitar_mensaje)

        self.passOld = TxtEdit("Contraseña Actual", "ingrese su contraseña...")
        self.passOld.passwordMode(True)
        
        self.btnEditar = Boton("Guardar Cambios")
        self.btnEditar.clicked.connect(self.setEdicion)

        layFechas = QHBoxLayout()
        self.registro = self.labelFechas("Registro")
        layFechas.addWidget(self.registro)
        layFechas.addSpacing(10)
        self.edicion = self.labelFechas("Edición")
        layFechas.addWidget(self.edicion)
        layFechas.addSpacing(10)
        self.actividad = self.labelFechas("Actividad")
        layFechas.addWidget(self.actividad)

        layVertical = QVBoxLayout()
        layVertical.addSpacing(10)
        layVertical.addWidget(self.imagen, alignment=Qt.AlignCenter)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.nickname)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.email)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.link)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.descripcion)
        layVertical.addSpacing(20)
        layVertical.addLayout(layFechas)
        layVertical.addSpacing(20)
        layVertical.addWidget(self.passOld)
        layVertical.addSpacing(10)
        layVertical.addWidget(self.btnEditar)
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
    
    def resetData(self):
        print(f"UsuarioEdit {self.id}: resetData")
        self.id = 0
        self.email.setText("*** email ***")
        self.nickname.setPlaceholder("nickname", False, True)
        self.descripcion.setText("")
        self.link.setPlaceholder("link", False, True)
        self.registro.setText("Registro")
        self.edicion.setText("Edición")
        self.actividad.setText("Actividad")
        self.passOld.set_value("")
        self.imagen.setPixmap(QPixmap(newSprit("avatar.png")))

    def setData(self, usuario):
        if usuario is None:
            self.resetData()
            return
        print(f"UsuarioEdit {usuario.id}: setData")
        self.id = usuario.id
        self.email.setText(usuario.email)
        self.nickname.setPlaceholder(usuario.nickname, True)
        self.descripcion.setText(usuario.descripcion)
        self.link.setPlaceholder(usuario.link, True)
        fecha = usuario.fecha_registro or ""
        self.registro.setText("Registro\n" + fecha.replace(" ", "\n"))
        fecha = usuario.fecha_actualiza or ""
        self.edicion.setText("Edición\n" + fecha.replace(" ", "\n"))
        fecha = usuario.fecha_reciente or ""
        self.actividad.setText("Actividad\n" + fecha.replace(" ", "\n"))
        self.passOld.set_value("")
        self.imagen.setPixmap(usuario.img_pix)

    def actualizar(self, id=0):
        if self.id == 0 or id != self.id:
            return
        print(f"UsuarioEdit {id}: actualizar")
        ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
        self.setData(ctrlUsuario.get_usuario(id))
    
    def setAdministrador(self):
        ses = Session()
        admindata = ses.get_login()
        if admindata["id"] > 0:
            ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
            usuario = ctrlUsuario.get_usuario(admindata["id"])
            self.setData(usuario)

    def setEdicion(self):
        if self.id == 0:
            return
        if self.passOld.get_value() == "":
            Alerta("Advertencia", "requiere su contraseña...", 1)
            return
        resp = QMessageBox.question(self, "Confirmación", "¿Desea actualizar sus datos de usuario?")
        if resp == QMessageBox.Yes:
            print("UsuarioEdit: setEdicion")
            ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
            data = {
                "nickname": self.nickname.get_value(),
                "descripcion": self.descripcion.toPlainText(),
                "link": self.link.get_value()
            }
            if ctrlUsuario.set_data(self.id, self.passOld.get_value(), data):
                Alerta("Éxito", "los datos han sido actualizados!!!", 0)
            else:
                Alerta("Fallo", "no se pudo actualizar...", 3)
            self.passOld.set_value("")

    def limitar_mensaje(self):
        text = self.descripcion.toPlainText()
        if len(text) > DESCRIPCION_MAX:
            self.descripcion.blockSignals(True)
            self.descripcion.setPlainText(text[:DESCRIPCION_MAX])
            self.descripcion.blockSignals(False)
            cursor = self.descripcion.textCursor()
            cursor.movePosition(cursor.End)
            self.descripcion.setTextCursor(cursor)
