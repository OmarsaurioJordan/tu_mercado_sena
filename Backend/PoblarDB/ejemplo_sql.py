import mysql.connector

# conectar a la base de datos
conexion = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="tms_api"
)

# hacer la consulta
cursor = conexion.cursor()
cursor.execute("SELECT * FROM categorias")

# recorrer los resultados
for fila in cursor.fetchall():
    print(fila[1])

# finalizar las consultas
cursor.close()
conexion.close()
