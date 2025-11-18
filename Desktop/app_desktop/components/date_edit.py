from PySide6.QtWidgets import (
    QWidget, QDateEdit, QLabel, QHBoxLayout
)
from PySide6.QtCore import Qt, QDate

class DateEdit(QWidget):
    def __init__(self, texto="", fecha_por_defecto=None):
        super().__init__()
        label = QLabel(texto)
        label.setAlignment(Qt.AlignVCenter | Qt.AlignRight)
        self.dateedit = QDateEdit()
        self.dateedit.setCalendarPopup(True)
        if fecha_por_defecto:
            self.dateedit.setDate(fecha_por_defecto)
        else:
            self.dateedit.setDate(QDate.currentDate())
        layout = QHBoxLayout()
        layout.addWidget(label)
        layout.addWidget(self.dateedit)
        self.setLayout(layout)
    
    def get_value(self):
        return self.dateedit.date().toString("yyyy-MM-dd")
