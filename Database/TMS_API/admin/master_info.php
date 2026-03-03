<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

$sql = "SELECT u.descripcion AS descripcion, u.link AS link
    FROM usuarios u
    LEFT JOIN roles r ON r.id = u.rol_id
    WHERE r.nombre = 'master' LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$info = $result->fetch_assoc();

if (!$info) {
    http_response_code(404);
    echo json_encode(["error" => "Sin administrador master"]);
    exit;
}

echo json_encode($info);
?>
