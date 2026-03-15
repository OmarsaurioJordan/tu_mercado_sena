from PySide6.QtCore import QTimer, QObject, QEvent, Signal
from PySide6.QtWidgets import QApplication

class Watchdog(QObject):
    shot = Signal()

    def __init__(self):
        super().__init__()

        self.is_activo = False
        self.timeout_ms = 60000

        self.timer = QTimer()
        self.timer.setSingleShot(True)
        self.timer.timeout.connect(self.disparo)
        
        QApplication.instance().installEventFilter(self)

    def eventFilter(self, obj, event):
        if event.type() in (
            QEvent.MouseMove,
            QEvent.MouseButtonPress,
            QEvent.MouseButtonRelease,
            QEvent.KeyPress,
            QEvent.KeyRelease,
            QEvent.Wheel
        ):
            self.reiniciar()
        return False
    
    def setSegundos(self, seg=60):
        self.timeout_ms = seg * 1000
    
    def desactivar(self):
        self.is_activo = False
        self.timer.stop()
    
    def activar(self):
        self.is_activo = True
        self.timer.start(self.timeout_ms)

    def reiniciar(self):
        if self.is_activo:
            self.timer.start(self.timeout_ms)

    def disparo(self):
        self.is_activo = False
        if self.timeout_ms != 0:
            self.shot.emit()
