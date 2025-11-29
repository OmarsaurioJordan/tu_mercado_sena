<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

if (!isset($_GET["id"])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta id"]);
    exit;
}
$id = $_GET["id"];

$sql = "SELECT u.id AS id, c.correo AS correo, u.rol_id AS rol_id, u.nombre AS nombre, u.avatar AS avatar, u.descripcion AS descripcion, u.link AS link, u.estado_id AS estado_id, u.fecha_registro AS fecha_registro, u.fecha_actualiza AS fecha_actualiza, u.fecha_reciente AS fecha_reciente
FROM usuarios u LEFT JOIN correos c ON u.correo_id = c.id
WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    http_response_code(404);
    echo json_encode(["error" => "Usuario no encontrado"]);
    exit;
}

echo json_encode($usuario);
?>
