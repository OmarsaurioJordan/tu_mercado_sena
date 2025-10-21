from core.api_client import get, post

def obtener_productos():
    return get("productos")

def descargar_imagen(producto_id):
    data = get(f"productos/{producto_id}/imagen")
    return data["imagen_base64"]
