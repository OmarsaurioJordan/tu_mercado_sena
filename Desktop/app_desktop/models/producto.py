from PySide6.QtCore import QThreadPool, Signal, QObject
from PySide6.QtGui import QPixmap
from services.load_image import ImageWorker
from core.app_config import IMAGE_PROD_LINK

class ProductoSignal(QObject):
    ok_image = Signal(int)

class Producto:
    
    def __init__(self, id, nombre, subcategoria_id, integridad_id, vendedor_id, descripcion, estado_id, precio, disponibles, fecha_registro, fecha_actualiza, vendedor_nickname, categoria_id, imagenes):
        self.id = id
        self.nombre = nombre
        self.subcategoria_id = subcategoria_id
        self.categoria_id = categoria_id
        self.integridad_id = integridad_id
        self.vendedor_id = vendedor_id
        self.vendedor_nickname = vendedor_nickname
        self.estado_id = estado_id
        self.descripcion = descripcion
        self.precio = precio
        self.disponibles = disponibles
        self.fecha_registro = fecha_registro
        self.fecha_actualiza = fecha_actualiza
        # carga de imagenes
        self.imagenes = imagenes
        self.img_signal = ProductoSignal()
        self.img_pix = []
        self.is_img_load = []
        self.workers = []

    @staticmethod
    def from_json(data):
        pro = Producto(
            id = int(data.get('id')),
            nombre = data.get('nombre'),
            subcategoria_id = int(data.get('subcategoria_id')),
            categoria_id = int(data.get('categoria_id')),
            integridad_id = int(data.get('integridad_id')),
            vendedor_id = int(data.get('vendedor_id')),
            vendedor_nickname = data.get('vendedor_nickname'),
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
            pro.is_img_load.append(False)
        return pro

    def load_images(self, only_first=False):
        for i in range(len(self.imagenes)):
            if not self.is_img_load[i]:
                if only_first:
                    if self.imagenes[i]:
                        self.load_image(i)
                        break
                else:
                    self.load_image(i)
            elif only_first:
                break

    def load_image(self, img_ind=0):
        if not self.imagenes[img_ind]:
            self.workers.append(ImageWorker(""))
            QThreadPool.globalInstance().start(self.workers[-1])
            return
        url = IMAGE_PROD_LINK + self.imagenes[img_ind]
        self.workers.append(ImageWorker(url))
        self.workers[-1].signals.finished.connect(
            lambda img, i = img_ind: self.set_image(img, i))
        QThreadPool.globalInstance().start(self.workers[-1])

    def set_image(self, image, img_ind=0):
        if not image.isNull():
            pix = QPixmap.fromImage(image)
        else:
            pix = QPixmap()
        self.img_pix[img_ind] = pix
        self.is_img_load[img_ind] = True
        self.img_signal.ok_image.emit(self.id)

    def get_portada(self):
        for i in range(len(self.imagenes)):
            if self.is_img_load[i]:
                return self.img_pix[i]
        return QPixmap("assets/sprites/img_null.png")
    
    def get_no_portada(self):
        imgs = []
        busca_portada = True
        for i in range(len(self.imagenes)):
            if busca_portada:
                if self.is_img_load[i]:
                    busca_portada = False
                    continue
            imgs.append(self.img_pix[i])
        return imgs
