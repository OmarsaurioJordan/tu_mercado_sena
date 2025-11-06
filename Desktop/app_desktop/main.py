import sys
from PySide6.QtWidgets import QApplication
from windows.tools_window import ToolsWindow

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = ToolsWindow()
    window.show()
    sys.exit(app.exec())
