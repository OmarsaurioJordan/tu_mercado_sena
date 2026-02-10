from PySide6.QtCore import QThreadPool, Signal, QObject
from PySide6.QtGui import QPixmap
from services.load_image import ImageWorker
from core.app_config import IMAGE_PROD_LINK

class ProductoSignal(QObject):
    ok_image = Signal(int)

class Producto:
    
    def __init__(self, id, nombre, subcategoria_id, integridad_id, vendedor_id, descripcion, estado_id, precio, disponibles, fecha_registro, fecha_actualiza, imagenes):
        self.id = id
        self.nombre = nombre
        self.subcategoria_id = subcategoria_id
        self.integridad_id = integridad_id
        self.vendedor_id = vendedor_id
        self.estado_id = estado_id
        self.descripcion = descripcion
        self.precio = precio
        self.disponibles = disponibles
        self.fecha_registro = fecha_registro
        self.fecha_actualiza = fecha_actualiza
        self.imagenes = imagenes
        self.img_signal = ProductoSignal()
        self.img_pix = []
        self.img_ok = []

    @staticmethod
    def from_json(data):
        pro = Producto(
            id = int(data.get('id')),
            nombre = data.get('nombre'),
            subcategoria_id = int(data.get('subcategoria_id')),
            integridad_id = int(data.get('integridad_id')),
            vendedor_id = int(data.get('vendedor_id')),
            descripcion = data.get('descripcion'),
            estado_id = int(data.get('estado_id')),
            disponibles = int(data.get('disponibles')),
            precio = float(data.get('precio')),
            fecha_registro = data.get('fecha_registro'),
            fecha_actualiza = data.get('fecha_actualiza'),
            imagenes = data.get("imagenes", [])
        )
        for i in range(len(pro.imagenes)):
            pro.img_pix.append(QPixmap("assets/sprites/img_null.png"))
            pro.img_ok.append(False)
        for i in range(len(pro.imagenes)):
            if pro.imagenes[i] != "":
                pro.load_image(i)
                break
        return pro

    def load_images(self):
        for i in range(len(self.imagenes)):
            self.load_image(i)

    def load_image(self, img_ind=0):
        if self.imagenes[img_ind] == "":
            return
        url = IMAGE_PROD_LINK + self.imagenes[img_ind]
        worker = ImageWorker(url, False)
        worker.signals.finished.connect(
            lambda img, i = img_ind: self.set_image(img, i))
        QThreadPool.globalInstance().start(worker)

    def set_image(self, image, img_ind=0):
        self.img_pix[img_ind] = image
        self.img_ok[img_ind] = True
        self.img_signal.ok_image.emit(self.id)

    def get_portada(self):
        for i in range(len(self.imagenes)):
            if self.img_ok[i]:
                return self.img_pix[i]
        return QPixmap("assets/sprites/img_null.png")

    def vendedorNickname(self):
        return "" # Tarea dar el nombre del vendedor
