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

$sql = "SELECT p.id AS id, p.usuario_id AS usuario_id, p.mensaje AS mensaje, p.motivo_id AS motivo_id, p.estado_id AS estado_id, p.fecha_registro AS fecha_registro, u.nickname AS nickname, c.email AS email, u.imagen AS imagen, DATEDIFF(NOW(), p.fecha_registro) AS dias
    FROM pqrs p
    LEFT JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN cuentas c ON u.cuenta_id = c.id
    WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    http_response_code(404);
    echo json_encode(["error" => "PQRS no encontrada"]);
    exit;
}

echo json_encode($data);
?>
