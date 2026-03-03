<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

$sql = "SELECT 
    (SELECT COUNT(*) FROM denuncias WHERE estado_id = 1) AS denuncias,
    (SELECT COUNT(*) FROM pqrs WHERE estado_id = 1) AS pqrss;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$res = $result->fetch_all(MYSQLI_ASSOC);

if (!$res) {
    http_response_code(404);
    echo json_encode(["error" => "Datos no encontrados"]);
    return;
}

echo json_encode($res);
?>