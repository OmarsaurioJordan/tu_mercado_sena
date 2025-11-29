class Session:
    _instance = None

    def __new__(cls):
        if cls._instance is None:
            cls._instance = super(Session, cls).__new__(cls)
            cls._instance._initialized = False
        return cls._instance

    def __init__(self):
        if self._initialized:
            return
        self._initialized = True
        # agregar los atributos globales
        self.admin_token = ""
        self.admin_correo = ""

    def set_login(self, token="", correo=""):
        self.admin_token = token
        self.admin_correo = correo

    def get_login(self):
        return {
            "token": self.admin_token,
            "correo": self.admin_correo
        }
