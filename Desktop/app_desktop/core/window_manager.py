from PySide6.QtWidgets import (
    QMainWindow, QStackedWidget, QApplication
)
from windows.login_window import LoginWindow
from windows.menu_window import MenuWindow
from windows.tools_window import ToolsWindow
from windows.config_window import ConfigWindow
from windows.lock_window import LockWindow
from windows.sessions_window import SessionsWindow
from windows.stats_window import StatsWindow
from core.app_config import DEBUG_NO_LOGIN
from core.session import Session

class WindowManager(QMainWindow):

    def __init__(self):
        super().__init__()
        self.setWindowTitle("TuMercadoSena-Desktop")
        self.resize(800, 600)

        self.stack = QStackedWidget()
        self.setCentralWidget(self.stack)
        self.views = {}
        if DEBUG_NO_LOGIN:
            self.set_tools()
        else:
            self.set_login()
        self.show()

    def set_login(self):
        ses = Session()
        ses.set_login()
        login = LoginWindow()
        self.views = {"login": login}
        self.limpiar_stack()
        self.stack.addWidget(login)
        self.change_tool("login")

    def set_tools(self, token="", email="", id=0):
        ses = Session()
        ses.set_login(token, email, id)
        self.views = {
            "menu": MenuWindow(),
            "tools": ToolsWindow(),
            "config": ConfigWindow(),
            "lock": LockWindow(),
            "sessions": SessionsWindow(),
            "stats": StatsWindow()
        }
        self.limpiar_stack()
        for v in self.views.values():
            self.stack.addWidget(v)
        self.change_tool("menu")

    def change_tool(self, tool_name):
        self.stack.setCurrentWidget(self.views[tool_name])

    def limpiar_stack(self):
        QApplication.instance().property("controls").limpiar()
        while self.stack.count() > 0:
            widget = self.stack.widget(0)
            self.stack.removeWidget(widget)
            widget.deleteLater()
