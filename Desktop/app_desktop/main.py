import sys
from PySide6.QtWidgets import QApplication
from windows.window_manager import WindowManager
from PySide6.QtGui import QFont

if __name__ == "__main__":
    app = QApplication(sys.argv)
    app.setFont(QFont("Times New Roman", 12))
    manager = WindowManager()
    app.setProperty("manager", manager)
    sys.exit(app.exec())
