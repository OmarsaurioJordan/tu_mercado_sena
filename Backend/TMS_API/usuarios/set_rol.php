<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

if (!isset($_GET["id"]) || !isset($_GET["rol"])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta id o rol"]);
    exit;
}
$id = $_GET["id"];
$rol = $_GET["rol"];

$sql = "UPDATE usuarios SET rol_id=? WHERE id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $rol, $id);
$stmt->execute();
if ($stmt->errno === 0) {
    echo json_encode(["Ok" => "1"]);
    exit;
}

echo json_encode(["Ok" => "0"]);
?>
