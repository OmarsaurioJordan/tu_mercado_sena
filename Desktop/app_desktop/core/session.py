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
        self.admin_email = ""
        self.admin_id = 0

    def set_login(self, token="", email="", id=0):
        self.admin_token = token
        self.admin_email = email
        self.admin_id = id

    def get_login(self):
        return {
            "token": self.admin_token,
            "email": self.admin_email,
            "id": self.admin_id
        }
