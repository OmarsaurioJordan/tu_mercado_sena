<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "api_tms";
$debug = true; // habilita o inhabilita validacion con token

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error conexión DB"]));
}
$conn->set_charset("utf8");

function validation() {
    global $debug, $conn;
    if (!$debug) {
        
        if (!isset($_GET["admin_email"]) || !isset($_GET["admin_token"])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan credenciales"]);
            exit;
        }
        $admin_email = $_GET["admin_email"];
        $admin_token = $_GET['admin_token'];

        $sql = "UPDATE tokens_de_sesion SET ultimo_uso = NOW()
            WHERE jti = ? AND dispositivo = 'desktop' AND cuenta_id =
            (SELECT id FROM cuentas WHERE email = ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $admin_token, $admin_email);
        $stmt->execute();

        if ($stmt->errno) {
            http_response_code(500);
            echo json_encode(["error" => "Error ejecución DB"]);
            exit;
        }
        if ($stmt->affected_rows == 0) {
            http_response_code(404);
            echo json_encode(["error" => "Credenciales inválidas"]);
            exit;
        }
    }
}

function auditar($suceso_id, $descripcion) {
    global $debug, $conn;
    if (!$debug) {

        if (!isset($_GET["admin_email"])) {
            return;
        }
        $admin_email = $_GET["admin_email"];

        $sql = "INSERT INTO auditorias (administrador_id, suceso_id, descripcion, fecha_registro)
            SELECT usuarios.id, ?, ?, NOW()
            FROM usuarios
            JOIN cuentas ON cuentas.id = usuarios.cuenta_id
            WHERE cuentas.email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $suceso_id, $descripcion, $admin_email);
        $stmt->execute();
    }
}

function valida_edit_admin() {
    // llamado siempre despues de validation() y de obtencion de id
    global $debug, $conn;
    if (!$debug) {

        $admin_email = $_GET["admin_email"];
        $id = $_GET["id"];

        $sql = "SELECT 1 FROM usuarios u LEFT JOIN cuentas c
            ON c.id = u.cuenta_id WHERE c.email = ? AND u.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $admin_email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            http_response_code(404);
            echo json_encode(["error" => "Credenciales inválidas"]);
            exit;
        }
    }
}
?>
