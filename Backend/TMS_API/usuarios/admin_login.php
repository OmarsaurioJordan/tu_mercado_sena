<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

if (!isset($_GET["correo"]) || !isset($_GET["password"])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan credenciales"]);
    exit;
}
$correo = $_GET["correo"];
$password = $_GET["password"];

$sql = "SELECT u.id AS id, r.nombre AS rol, u.password AS pass FROM usuarios u LEFT JOIN correos c ON c.id = u.correo_id LEFT JOIN roles r ON r.id = u.rol_id WHERE c.correo = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();
$info = $result->fetch_assoc();

if (!$info) {
    http_response_code(404);
    echo json_encode(["error" => "Credenciales inválidas"]);
    exit;
}

if (!password_verify($password, $info["pass"])) {
    http_response_code(404);
    echo json_encode(["error" => "Credenciales inválidas"]);
    exit;
}

if ($info['rol'] != "administrador" && $info['rol'] != "master") {
    http_response_code(404);
    echo json_encode(["error" => "No tiene permisos"]);
    exit;
}

$admin_id = $info['id'];
$admin_ip = $_SERVER['REMOTE_ADDR'];

$sql = "INSERT INTO login_ip (usuario_id, ip_direccion) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $admin_id, $admin_ip);
$stmt->execute();
if ($stmt->affected_rows > 0) {

    $token = bin2hex(random_bytes(32));

    $sql = "UPDATE usuarios SET token_admin = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $token, $admin_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(["token" => $token]);
        exit;
    }
}

http_response_code(500);
die(json_encode(["error" => "Error ejecución DB"]));
?>
