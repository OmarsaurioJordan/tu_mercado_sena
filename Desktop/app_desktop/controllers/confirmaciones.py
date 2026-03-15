from PySide6.QtWidgets import (QApplication, QMessageBox)

def confirma_ejecucion(tipo="", id=0, value=0):
    print(f"confirma_ejecucion: confirma_ejecucion-{tipo}")
    manager = QApplication.instance().property("controls")
    match tipo:
        case "usuario_estado":
            ctrlUsuario = manager.get_usuarios()
            return ctrlUsuario.set_estado(id, value)
        case "usuario_rol":
            ctrlUsuario = manager.get_usuarios()
            return ctrlUsuario.set_rol(id, value)
        case "producto_estado":
            ctrlProducto = manager.get_productos()
            return ctrlProducto.set_estado(id, value)
        case "producto_integridad":
            ctrlProducto = manager.get_productos()
            return ctrlProducto.set_integridad(id, value)
        case "producto_categoria":
            ctrlProducto = manager.get_productos()
            return ctrlProducto.set_categoria(id, value)
        case "producto_subcategoria":
            ctrlProducto = manager.get_productos()
            return ctrlProducto.set_subcategoria(id, value)
        case "pqrs_estado":
            manager = QApplication.instance().property("manager")
            resp = QMessageBox.question(manager, "Confirmación", "¿Desea dar por cerrada la PQRS sin enviar respuesta al usuario? Se recomienda escribir y enviar algun mensaje")
            if resp == QMessageBox.Yes:
                ctrlPqrs = manager.get_pqrss()
                return ctrlPqrs.set_estado(id, value)
        case "pqrs_motivo":
            ctrlPqrs = manager.get_pqrss()
            return ctrlPqrs.set_motivo(id, value)
        case "denuncia_estado":
            ctrlDenuncia = manager.get_denuncias()
            return ctrlDenuncia.set_estado(id, value)
        case "denuncia_motivo":
            ctrlDenuncia = manager.get_denuncias()
            return ctrlDenuncia.set_motivo(id, value)
        case "chat_estado":
            ctrlChat = manager.get_chats()
            return ctrlChat.set_estado(id, value)
    return False

def confirma_pregunta(tipo=""):
    match tipo:
        case "usuario_estado":
            return "¿Desea cambiar el estado del usuario por "
        case "usuario_rol":
            return "¿Desea cambiar el rol del usuario por "
        case "producto_estado":
            return "¿Desea cambiar el estado del producto por "
        case "producto_integridad":
            return "¿Desea cambiar la integridad del producto por "
        case "producto_categoria":
            return "¿Desea cambiar la categoría del producto por "
        case "producto_subcategoria":
            return "¿Desea cambiar la subcategoría del producto por "
        case "pqrs_estado":
            return "¿Desea cambiar el estado del PQRS por "
        case "pqrs_motivo":
            return "¿Desea cambiar el motivo del PQRS por "
        case "denuncia_estado":
            return "¿Desea cambiar el estado de la denuncia por "
        case "denuncia_motivo":
            return "¿Desea cambiar el motivo de la denuncia por "
        case "chat_estado":
            return "¿Desea cambiar el estado del chat por "
    return ""
