import sys
from PySide6.QtWidgets import QApplication
from windows.tools_window import ToolsWindow
from PySide6.QtGui import QFont

if __name__ == "__main__":
    app = QApplication(sys.argv)
    app.setFont(QFont("Times New Roman", 12))
    window = ToolsWindow()
    window.show()
    sys.exit(app.exec())
