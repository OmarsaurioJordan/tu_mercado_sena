from PySide6.QtWidgets import QMessageBox

# flag_tipo: 0:Ok, 1:alert, 2:info, 3:error, 4:pregunta

class Alerta(QMessageBox):
    def __init__(self, titulo="", texto="", flag_tipo=0):
        super().__init__()
        match flag_tipo:
            case 0: # Ok
                self.setIcon(QMessageBox.Information)
            case 1: # alert
                self.setIcon(QMessageBox.Warning)
            case 2: # info
                self.setIcon(QMessageBox.Information)
            case 3: # error
                self.setIcon(QMessageBox.Critical)
            case 4: # pregunta
                self.setIcon(QMessageBox.Question)
        self.setWindowTitle(titulo)
        self.setText(texto)
        self.exec()
