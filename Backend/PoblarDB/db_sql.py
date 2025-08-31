import mysql.connector
from mysql.connector import IntegrityError

class Conector:
    conexion = None

    @classmethod
    def get_conexion(cls):
        if cls.conexion is None or not cls.conexion.is_connected():
            cls.conexion = mysql.connector.connect(
                host="localhost",
                user="root",
                password="",
                database="tu_mercado_sena"
            )
        return cls.conexion
    
    @classmethod
    def run_sql(cls, sql="", valores=None, is_select=False):
        cls.get_conexion()
        cursor = cls.conexion.cursor()
        if is_select:
            cursor.execute(sql, valores or ())
            res = cursor.fetchall()
            cursor.close()
            return res
        try:
            cursor.execute(sql, valores or ())
            cls.conexion.commit()
            id = cursor.lastrowid
        except IntegrityError as e:
            id = -1
        cursor.close()
        return id

    @classmethod
    def close_conexion(cls):
        cls.get_conexion().close()
