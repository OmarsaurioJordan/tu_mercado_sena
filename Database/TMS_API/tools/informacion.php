<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

$sql = "SELECT 
    (SELECT COUNT(*) FROM chats WHERE estado_id IN (1, 6, 7)) AS cht_activos,
    (SELECT COUNT(*) FROM chats WHERE estado_id = 2) AS cht_eliminados,
    (SELECT COUNT(*) FROM chats WHERE estado_id = 5) AS cht_vendidos,
    (SELECT COUNT(*) FROM chats WHERE estado_id = 8) AS cht_devueltos,
    (SELECT COUNT(*) FROM chats WHERE estado_id = 9) AS cht_censurados,
    (SELECT COUNT(*) FROM usuarios WHERE estado_id = 1) AS usr_activos,
    (SELECT COUNT(*) FROM usuarios WHERE estado_id = 2) AS usr_invisibles,
    (SELECT COUNT(*) FROM usuarios WHERE estado_id = 3) AS usr_eliminados,
    (SELECT COUNT(*) FROM usuarios WHERE estado_id IN (4, 10)) AS usr_bloqdenuns,
    (SELECT COUNT(*) FROM productos WHERE estado_id = 1) AS prd_activos,
    (SELECT COUNT(*) FROM productos WHERE estado_id = 2) AS prd_invisibles,
    (SELECT COUNT(*) FROM productos WHERE estado_id = 3) AS prd_eliminados,
    (SELECT COUNT(*) FROM productos WHERE estado_id IN (4, 10)) AS prd_bloqdenuns;";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$res = $result->fetch_all(MYSQLI_ASSOC);

if (!$res) {
    http_response_code(404);
    echo json_encode(["error" => "Información no encontrada"]);
    return;
}

echo json_encode($res);
?>