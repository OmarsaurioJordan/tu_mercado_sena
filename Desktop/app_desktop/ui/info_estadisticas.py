import random
from datetime import datetime, timedelta
from PySide6.QtWidgets import QWidget, QVBoxLayout, QHBoxLayout, QLabel, QApplication
from PySide6.QtCharts import QChart, QChartView, QBarSeries, QBarSet, QBarCategoryAxis, QValueAxis
from PySide6.QtCore import Qt
from services.alerta import Alerta
from components.scroll import Scroll
from components.selector import Selector
from components.boton import Boton

class InfoEstadisticas(QWidget):

    def __init__(self):
        super().__init__()
        self.max_value = 100
        self.num_datos = 60

        self.textoInfo = QLabel()
        self.textoInfo.setAlignment(
            Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignTop
        )
        info = Scroll(self.textoInfo)
        info.setFixedWidth(260)

        self.barset = QBarSet("Transacciónes")
        self.series = QBarSeries()
        self.series.append(self.barset)
        self.chart = QChart()
        self.chart.addSeries(self.series)
        self.chart.setTitle("")
        self.chart.legend().setVisible(False)
        self.axisY = QValueAxis()
        self.chart.addAxis(self.axisY, Qt.AlignLeft)
        self.series.attachAxis(self.axisY)
        self.axisX = QBarCategoryAxis()
        self.chart.addAxis(self.axisX, Qt.AlignBottom)
        self.series.attachAxis(self.axisX)
        self.chart_view = QChartView(self.chart)

        self.selInfo = Selector(
            [["Transacciónes", 0], ["Usuarios", 1], ["Productos", 2], ["Denuncias", 3], ["Chats", 4]],
            "estadística...", "Estadística", 0
        )
        self.selInfo.onCambio.connect(self.graficar)

        self.btnCSV = Boton("CSV")
        self.btnCSV.clicked.connect(lambda: Alerta("Información", "Esta funcionalidad aún no está disponible", 2))

        herramientas = QHBoxLayout()
        herramientas.addWidget(self.selInfo)
        herramientas.addStretch()
        herramientas.addWidget(self.btnCSV)

        graficacion = QVBoxLayout()
        graficacion.addLayout(herramientas)
        graficacion.addWidget(self.chart_view)

        layFondo = QHBoxLayout()
        layFondo.addWidget(info)
        layFondo.addLayout(graficacion)
        layFondo.setContentsMargins(20, 20, 20, 20)
        self.setLayout(layFondo)
        self.graficar()

    def graficar(self):
        titulo = self.selInfo.get_text()
        self.chart.setTitle(titulo + " por Semana")
        self.barset.remove(0, self.barset.count())
        self.axisX.clear()
        fechas, valores = self.datos_random(self.num_datos, self.max_value)
        for v in valores:
            self.barset.append(v)
        self.axisX.append(fechas)
        self.axisY.setRange(0, self.max_value)
        self.chart_view.update()

    def datos_random(self, max_semanas, max_value):
        fechas = []
        valores = []
        hoy = datetime.now()
        for i in range(max_semanas):
            semana = hoy - timedelta(weeks=(max_semanas-1)-i)
            fechas.append(semana.strftime("%W"))
            valores.append(random.randint(0, max_value))
        return fechas, valores

    def actualizar(self):
        manager = QApplication.instance().property("controls")
        ctrlData = manager.get_data()
        info = ctrlData.api_informacion()
        if info:
            texto = f"""
                ESTADÍSTICAS:

                📊 CHATS
                Activos: {info['cht_activos']}
                Eliminados: {info['cht_eliminados']}
                Vendidos: {info['cht_vendidos']}
                Devueltos: {info['cht_devueltos']}
                Censurados: {info['cht_censurados']}

                👤 USUARIOS
                Activos: {info['usr_activos']}
                Invisibles: {info['usr_invisibles']}
                Eliminados: {info['usr_eliminados']}
                Bloq/Denu: {info['usr_bloqdenuns']}

                📦 PRODUCTOS
                Activos: {info['prd_activos']}
                Invisibles: {info['prd_invisibles']}
                Eliminados: {info['prd_eliminados']}
                Bloq/Denu: {info['prd_bloqdenuns']}
                """
            self.textoInfo.setText(texto)
