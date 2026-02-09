<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "api_tms";
$debug = false;

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
?>
