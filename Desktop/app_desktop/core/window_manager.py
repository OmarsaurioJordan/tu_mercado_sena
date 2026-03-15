from PySide6.QtWidgets import QMainWindow, QStackedWidget, QApplication
from PySide6.QtGui import QShortcut, QKeySequence
from windows.login_window import LoginWindow
from windows.menu_window import MenuWindow
from windows.tools_window import ToolsWindow
from windows.config_window import ConfigWindow
from windows.lock_window import LockWindow
from windows.auditorias_window import AuditoriasWindow
from windows.stats_window import StatsWindow
from services.watchdog import Watchdog
from core.app_config import DEBUG_NO_LOGIN
from core.session import Session

class WindowManager(QMainWindow):

    def __init__(self):
        super().__init__()
        self.setWindowTitle("TMS-Administración")
        self.resize(800, 600)

        self.watchdog = Watchdog()
        self.watchdog.shot.connect(self.bloqueo_time)

        shortcut = QShortcut(QKeySequence("F2"), self)
        shortcut.activated.connect(lambda: print("..."))

        self.stack = QStackedWidget()
        self.setCentralWidget(self.stack)
        self.views = {}
        if DEBUG_NO_LOGIN:
            self.set_tools()
        else:
            self.set_login()
        self.show()
        self.showMaximized()

    def setTiempoBloqueo(self, segundos):
        self.watchdog.setSegundos(segundos)
        self.watchdog.reiniciar()

    def is_login(self):
        return self.stack.count() > 1

    def set_login(self):
        print("WindowManager: set_login")
        ses = Session()
        ses.set_login()
        login = LoginWindow()
        self.views = {"login": login}
        self.limpiar_stack()
        self.stack.addWidget(login)
        self.change_tool("login")

    def set_tools(self, token="", email="", id=0):
        print("WindowManager: set_tools")
        ses = Session()
        ses.set_login(token, email, id)
        self.views = {
            "menu": MenuWindow(),
            "tools": ToolsWindow(),
            "config": ConfigWindow(),
            "lock": LockWindow(),
            "auditorias": AuditoriasWindow(),
            "stats": StatsWindow()
        }
        self.limpiar_stack()
        for v in self.views.values():
            self.stack.addWidget(v)
        self.change_tool("menu")

    def change_tool(self, tool_name):
        print(f"WindowManager: change_tool-{tool_name}")
        self.stack.setCurrentWidget(self.views[tool_name])
        # si es el current stats, actualizar datos
        if tool_name == "stats":
            self.views["stats"].actualizar()
        # si es una ventana no bloqueante, activar watchdog
        if not tool_name in ["login", "lock"]:
            self.watchdog.activar()
        else:
            self.watchdog.desactivar()
        # colocar datos de edicion de admin
        if tool_name == "config":
            self.views["config"].setAdministrador()

    def limpiar_stack(self):
        print("WindowManager: limpiar_stack")
        QApplication.instance().property("controls").limpiar()
        while self.stack.count() > 0:
            widget = self.stack.widget(0)
            self.stack.removeWidget(widget)
            widget.deleteLater()

    def bloqueo_time(self):
        if self.is_login():
            if self.stack.currentWidget() != self.views["lock"]:
                self.change_tool("lock")
