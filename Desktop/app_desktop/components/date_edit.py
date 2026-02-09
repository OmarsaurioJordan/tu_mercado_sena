from datetime import datetime, time, timezone
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
    
    def get_value_utc(self):
        qdate = self.dateedit.date()
        now = datetime.now().time()
        local_dt = datetime(
            qdate.year(), qdate.month(), qdate.day(),
            now.hour, now.minute, now.second, now.microsecond
        ).astimezone()
        utc_dt = local_dt.astimezone(timezone.utc)
        return utc_dt.strftime('%Y-%m-%d')
