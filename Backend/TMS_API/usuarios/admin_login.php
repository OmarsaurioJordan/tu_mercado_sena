<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

if (!isset($_GET["email"]) || !isset($_GET["password"])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan credenciales"]);
    exit;
}
$email = $_GET["email"];
$password = $_GET["password"];

$sql = "SELECT u.id AS id, u.cuenta_id AS cuenta_id, r.nombre AS rol, c.password AS pass
    FROM usuarios u
    LEFT JOIN cuentas c ON c.id = u.cuenta_id
    LEFT JOIN roles r ON r.id = u.rol_id
    WHERE c.email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
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
if ($stmt->errno === 0) {

    $token = bin2hex(random_bytes(32));
    $cuenta_id = $info['cuenta_id'];

    $sql = "SELECT id FROM tokens_de_sesion
        WHERE cuenta_id = ? AND dispositivo = 'desktop'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cuenta_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $sql = "UPDATE tokens_de_sesion SET jti = ?, ultimo_uso = NOW()
            WHERE cuenta_id = ? AND dispositivo = 'desktop'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $token, $cuenta_id);
    }
    else {
        $sql = "INSERT INTO tokens_de_sesion (cuenta_id, dispositivo, jti, ultimo_uso)
            VALUES (?, 'desktop', ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $cuenta_id, $token);
    }
    if ($stmt->execute()) {
        echo json_encode(["token" => $token, "id" => $admin_id]);
        exit;
    }
}

http_response_code(500);
echo json_encode(["error" => "Error ejecución DB"]);
?>
