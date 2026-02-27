import os
import shutil
import random
from PIL import Image, ImageDraw

class MakeImg:
    folders = ["img_productos", "img_mensajes", "img_backup", "img_test", "img_usuarios"]
    width = 200 # talla de las imagenes en px
    height = 200
    figuras = 20 # cuantas figuras maximo habra, fig/2 a fig
    init = False

    @classmethod
    def init_folders(cls):
        if not cls.init:
            for fol in cls.folders:
                if os.path.exists(fol):
                    shutil.rmtree(fol)
                os.makedirs(fol)
            cls.init = True

    @classmethod
    def run_img(cls, ind_folder=3, filename="", tipo_img=""):
        cls.init_folders()
        # tipo_img: bad, joda, etc
        img = Image.new("RGB", (cls.width, cls.height), color=cls.color_azar())
        draw = ImageDraw.Draw(img)
        for _ in range(random.randint(round(cls.figuras / 2), cls.figuras)):
            shape = random.choice(["lin", "cir", "rec"])
            x1r, y1r = cls.punto_xy_azar()
            x2, y2 = cls.punto_xy_azar()
            x1 = min(x1r, x2)
            x2 = max(x1r, x2)
            y1 = min(y1r, y2)
            y2 = max(y1r, y2)
            color = cls.color_azar()
            r = random.random() < 0.333
            if tipo_img == "bad" and r:
                color = (255, 0, 0)
            elif tipo_img == "joda" and r:
                color = (0, 0, 255)
            match shape:
                case "lin":
                    wdt = random.randint(1, 8)
                    draw.line((x1, y1, x2, y2), fill=color, width=wdt)
                case "cir":
                    draw.ellipse((x1, y1, x2, y2), outline=color, fill=None)
                case "rec":
                    draw.rectangle((x1, y1, x2, y2), outline=color, fill=None)
        fpath = os.path.join(cls.folders[ind_folder], filename)
        img.save(fpath)

    @classmethod
    def color_azar(cls):
        return (
            random.randint(50,200),
            random.randint(50,200),
            random.randint(50,200)
        )
    
    @classmethod
    def punto_xy_azar(cls):
        return random.randint(0, cls.width), random.randint(0, cls.height)

    @classmethod
    def clear_all(cls):
        for fol in cls.folders:
            if os.path.exists(fol):
                for filename in os.listdir(fol):
                    if filename == ".gitkeep":
                        continue
                    file_path = os.path.join(fol, filename)
                    try:
                        if os.path.isfile(file_path) or os.path.islink(file_path):
                            os.unlink(file_path)
                    except Exception as e:
                        print("error: " + file_path)
