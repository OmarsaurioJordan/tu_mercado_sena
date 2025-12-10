from PySide6.QtWidgets import QApplication

def confirma_ejecucion(tipo="", id=0, value=0):
    manager = QApplication.instance().property("controls")
    match tipo:
        case "usuario_rol":
            ctrlUsuario = manager.get_usuarios()
            return ctrlUsuario.set_rol(id, 3 if value == 0 else 2)
        case "usuario_estado":
            ctrlUsuario = manager.get_usuarios()
            return ctrlUsuario.set_estado(id, value + 1)
    return False

def confirma_pregunta(tipo=""):
    match tipo:
        case "usuario_rol":
            return "¿Desea cambiar el rol del usuario por "
        case "usuario_estado":
            return "¿Desea cambiar el estado del usuario por "
    return ""
