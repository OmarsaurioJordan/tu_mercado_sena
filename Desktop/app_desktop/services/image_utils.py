from PySide6.QtGui import QPixmap, QPainter, QPainterPath
from PySide6.QtCore import Qt

def circular_pixmap(pixmap: QPixmap, size: int) -> QPixmap:
    img = pixmap.scaled(size, size, Qt.KeepAspectRatioByExpanding, Qt.SmoothTransformation)

    circular = QPixmap(size, size)
    circular.fill(Qt.transparent)

    painter = QPainter(circular)
    painter.setRenderHint(QPainter.Antialiasing, True)

    path = QPainterPath()
    path.addEllipse(0, 0, size, size)
    painter.setClipPath(path)

    painter.drawPixmap(0, 0, img)
    painter.end()
    return circular
