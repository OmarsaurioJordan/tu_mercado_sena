<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

validation();

if (!isset($_GET["id"]) || !isset($_GET["estado"])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta id o estado"]);
    exit;
}
$id = $_GET["id"];
$estado = $_GET["estado"];

if ($estado > 4 || $estado < 1) {
    http_response_code(400);
    echo json_encode(["error" => "estado inválido"]);
    exit;
}

$sql = "UPDATE productos SET estado_id = ? WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $estado, $id);
$stmt->execute();
if ($stmt->errno === 0) {
    echo json_encode(["Ok" => "1"]);
    exit;
}

echo json_encode(["Ok" => "0"]);
?>
