<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "tu_mercado_sena";
$debug = false;

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error conexión DB"]));
}
$conn->set_charset("utf8");

function validation() {
    global $debug;
    if (!$debug) {
        if (!isset($_GET["admin_correo"]) || !isset($_GET["admin_token"])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan credenciales"]);
            exit;
        }
        $admin_correo = $_GET["admin_correo"];
        $admin_token = $_GET['admin_token'];
        $admin_ip = $_SERVER['REMOTE_ADDR'];
        $sql = "SELECT 1 FROM ";
    }
}
?>
