from PySide6.QtWidgets import QWidget, QVBoxLayout, QHBoxLayout, QGroupBox, QApplication, QMessageBox
from ui.usuario_edit import UsuarioEdit
from components.txt_edit import TxtEdit
from components.spinbox import SpinBox
from components.selector import Selector
from components.boton import Boton
from components.scroll import Scroll
from services.alerta import Alerta

class ToolsConfig(QWidget):

    def __init__(self):
        super().__init__()

        panelPin = QGroupBox("Editar PIN")
        panelPin.setObjectName("panel")
        layPanel1 = QVBoxLayout()
        self.pinNew = TxtEdit("Nuevo PIN", "1234...")
        self.pinNew.set_limit(4)
        self.pinNew.passwordMode(True)
        self.passOld1 = TxtEdit("Contraseña Actual", "ingrese su contraseña...")
        self.passOld1.passwordMode(True)
        btnPin = Boton("Cambiar PIN")
        btnPin.clicked.connect(self.cambioPin)
        layPanel1.addWidget(self.pinNew)
        layPanel1.addWidget(self.passOld1)
        layPanel1.addWidget(btnPin)
        panelPin.setLayout(layPanel1)
        
        panelPass = QGroupBox("Editar Contraseña")
        panelPass.setObjectName("panel")
        layPanel2 = QVBoxLayout()
        self.passNew1 = TxtEdit("Nueva Contraseña", "digite una contraseña...")
        self.passNew1.passwordMode(True)
        self.passNew2 = TxtEdit("Repita la Contraseña", "reescriba la contraseña...")
        self.passNew2.passwordMode(True)
        self.passOld2 = TxtEdit("Contraseña Actual", "ingrese su contraseña...")
        self.passOld2.passwordMode(True)
        btnPass = Boton("Cambiar Contraseña")
        btnPass.clicked.connect(self.cambioPass)
        layPanel2.addWidget(self.passNew1)
        layPanel2.addWidget(self.passNew2)
        layPanel2.addWidget(self.passOld2)
        layPanel2.addWidget(btnPass)
        panelPass.setLayout(layPanel2)

        layCredenciales = QVBoxLayout()
        layCredenciales.addWidget(panelPin)
        layCredenciales.addSpacing(20)
        layCredenciales.addWidget(panelPass)
        layCredenciales.addStretch()

        panelNotifi = QGroupBox("Editar Notificaciónes")
        panelNotifi.setObjectName("panel")
        layPanel3 = QVBoxLayout()
        self.spinVolumen = SpinBox("Volúmen", 0, 100, 100)
        self.spinVolumen.spin.valueChanged.connect(self.setVolumen)
        layPanel3.addWidget(self.spinVolumen)
        panelNotifi.setLayout(layPanel3)
        
        panelWatchdog = QGroupBox("Editar Bloqueo PIN")
        panelWatchdog.setObjectName("panel")
        layPanel4 = QVBoxLayout()
        self.selBloqueo = Selector(
            [["desactivado", 0], ["1 min", 60], ["3 min", 180], ["10 min", 600], ["30 min", 1800], ["1 hora", 3600], ["3 horas", 10800], ["6 horas", 21600]],
            "tiempo...", "Tiempo de Bloqueo", 2, "tiempo_bloqueo"
        )
        layPanel4.addWidget(self.selBloqueo)
        panelWatchdog.setLayout(layPanel4)
        
        layFuncionamiento = QVBoxLayout()
        layFuncionamiento.addWidget(panelNotifi)
        layFuncionamiento.addSpacing(20)
        layFuncionamiento.addWidget(panelWatchdog)
        layFuncionamiento.addStretch()

        self.userBody = UsuarioEdit()
        layEditor = QVBoxLayout()
        layEditor.addWidget(Scroll(self.userBody))

        layFondo = QHBoxLayout()
        layFondo.addLayout(layCredenciales)
        layFondo.addStretch()
        layFondo.addLayout(layFuncionamiento)
        layFondo.addStretch()
        layFondo.addLayout(layEditor)
        layFondo.setContentsMargins(20, 20, 20, 20)
        self.setLayout(layFondo)
        self.estiloPanel()

    def estiloPanel(self):
        self.setStyleSheet("""
            QGroupBox#panel {
                border: 1px solid;
                margin-top: 10px;
                font-weight: bold;
            }
            QGroupBox::title#panel {
                subcontrol-origin: margin;
                subcontrol-position: top left;
                padding: 3px 6px;
            }
            """)

    def setAdministrador(self):
        self.userBody.setAdministrador()
    
    def setVolumen(self):
        sound = QApplication.instance().property("sound_notifi")
        sound.setVolume(self.spinVolumen.get_value() / 100.0)

    def cambioPin(self):
        if self.userBody.id == 0:
            return
        if self.passOld1.get_value() == "":
            Alerta("Advertencia", "requiere su contraseña...", 1)
            return
        resp = QMessageBox.question(self, "Confirmación", "¿Desea cambiar el PIN?")
        if resp == QMessageBox.Yes:
            print("ToolsConfig: cambioPin")
            ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
            data = {
                "pin": self.pinNew.get_value()
            }
            if ctrlUsuario.set_data(self.userBody.id, self.passOld1.get_value(), data):
                Alerta("Éxito", "el PIN se ha actualizado!!!", 0)
            else:
                Alerta("Fallo", "no se pudo actualizar...", 3)
            self.pinNew.set_value("")
            self.passOld1.set_value("")

    def cambioPass(self):
        if self.userBody.id == 0:
            return
        if self.passOld2.get_value() == "":
            Alerta("Advertencia", "requiere su contraseña...", 1)
            return
        if self.passNew1.get_value() == "" or self.passNew1.get_value() != self.passNew2.get_value():
            Alerta("Advertencia", "digite contraseñas que coincidan", 1)
            return
        resp = QMessageBox.question(self, "Confirmación", "¿Desea cambiar la contraseña?")
        if resp == QMessageBox.Yes:
            print("ToolsConfig: cambioPass")
            ctrlUsuario = QApplication.instance().property("controls").get_usuarios()
            data = {
                "password": self.passNew1.get_value()
            }
            if ctrlUsuario.set_data(self.userBody.id, self.passOld2.get_value(), data):
                Alerta("Éxito", "la contraseña se ha actualizado!!!", 0)
            else:
                Alerta("Fallo", "no se pudo actualizar...", 3)
            self.pinNew.set_value("")
            self.passOld1.set_value("")
