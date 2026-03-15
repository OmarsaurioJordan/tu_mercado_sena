import sys
from PySide6.QtWidgets import QApplication
from core.window_manager import WindowManager
from core.controllers_manager import ControllersManager
from PySide6.QtGui import QFont
from PySide6.QtMultimedia import QSoundEffect
from PySide6.QtCore import QUrl

if __name__ == "__main__":
    print("*** Tu Mercado Sena ***")
    # inicializar configuracion de la App
    app = QApplication(sys.argv)
    app.setFont(QFont("Times New Roman", 12))
    # agregar controladores de API
    ctrls = ControllersManager()
    app.setProperty("controls", ctrls)
    # agregar ventanas de GUI
    manager = WindowManager()
    app.setProperty("manager", manager)
    # agregar sonido de notificacion
    sound = QSoundEffect()
    sound.setSource(QUrl.fromLocalFile("assets/sounds/notificacion.wav"))
    sound.setVolume(1.0)
    app.setProperty("sound_notifi", sound)
    # lanzar la App
    sys.exit(app.exec())
