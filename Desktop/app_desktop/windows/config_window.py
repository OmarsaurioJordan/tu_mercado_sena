from PySide6.QtWidgets import QWidget
from ui.header_layout import HeaderLayout
from ui.tools_config import ToolsConfig

class ConfigWindow(QWidget):

    def __init__(self):
        super().__init__()

        self.config = ToolsConfig()
        header = HeaderLayout(self.config)
        self.setLayout(header)
    
    def setAdministrador(self):
        self.config.setAdministrador()
