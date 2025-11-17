import requests
from core.app_config import API_BASE_URL
from models.usuario import Usuario

class CtrlUsuario:

    def obtener_usuario(self, id):
        params = {"id": id}
        response = requests.get(API_BASE_URL + "usuarios/get.php", params=params)
        if response.status_code == 200:
            return Usuario.from_json(response.json())
        return None

    def obtener_filtro(self, nombre, correo, rol_id, estado_id, con_link, con_descripcion, con_productos, dias_activo, registro_desde, registro_hasta):
        params = {
            "nombre": nombre,
            "correo": correo,
            "rol_id": rol_id,
            "estado_id": estado_id,
            "con_link": con_link,
            "con_descripcion": con_descripcion,
            "con_productos": con_productos,
            "dias_activo": dias_activo,
            "registro_desde": registro_desde,
            "registro_hasta": registro_hasta
        }
        response = requests.get(API_BASE_URL + "usuarios", params=params)
        usuarios = []
        if response.status_code == 200:
            for item in response.json():
                usuarios.append(Usuario.from_json(item))
        return usuarios
