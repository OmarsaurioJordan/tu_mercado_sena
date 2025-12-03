import sys
from PySide6.QtWidgets import QApplication
from core.window_manager import WindowManager
from core.controllers_manager import ControllersManager
from PySide6.QtGui import QFont

if __name__ == "__main__":
    app = QApplication(sys.argv)
    app.setFont(QFont("Times New Roman", 12))
    ctrls = ControllersManager()
    app.setProperty("controls", ctrls)
    manager = WindowManager()
    app.setProperty("manager", manager)
    sys.exit(app.exec())
