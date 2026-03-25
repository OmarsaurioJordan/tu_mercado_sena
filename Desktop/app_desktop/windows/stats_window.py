from PySide6.QtWidgets import QWidget
from ui.header_layout import HeaderLayout
from ui.info_estadisticas import InfoEstadisticas

class StatsWindow(QWidget):

    def __init__(self):
        super().__init__()

        self.infotool = InfoEstadisticas()
        header = HeaderLayout(self.infotool)
        self.setLayout(header)
    
    def actualizar(self):
        self.infotool.actualizar()
