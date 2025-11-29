from windows.login_window import LoginWindow
from windows.menu_window import MenuWindow
from windows.tools_window import ToolsWindow
from windows.config_window import ConfigWindow
from windows.lock_window import LockWindow
from windows.sessions_window import SessionsWindow
from windows.stats_window import StatsWindow
from core.app_config import DEBUG_NO_LOGIN
from core.session import Session

class WindowManager:

    def __init__(self):
        if DEBUG_NO_LOGIN:
            self.set_tools()
        else:
            self.set_login()

    def set_login(self):
        ses = Session()
        ses.set_login()
        self.views = {
            "login": LoginWindow()
        }
        self.change_tool("login")

    def set_tools(self, token="", correo=""):
        ses = Session()
        ses.set_login(token, correo)
        self.views = {
            "menu": MenuWindow(),
            "tools": ToolsWindow(),
            "config": ConfigWindow(),
            "lock": LockWindow(),
            "sessions": SessionsWindow(),
            "stats": StatsWindow()
        }
        self.change_tool("menu")

    def change_tool(self, tool_name):
        for tool in self.views.values():
            tool.hide()
        self.views[tool_name].show()
