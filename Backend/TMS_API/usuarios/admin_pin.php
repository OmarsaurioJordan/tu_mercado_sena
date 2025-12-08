<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

if (!isset($_GET["correo"]) || !isset($_GET["pin"])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan credenciales"]);
    exit;
}
$correo = $_GET["correo"];
$pin = $_GET["pin"];

$sql = "SELECT 1 FROM correos WHERE correo = ? AND pin=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $correo, $pin);
$stmt->execute();
$result = $stmt->get_result();
$info = $result->fetch_assoc();

if ($info) {
    echo json_encode(["Ok" => "1"]);
    exit;
}
echo json_encode(["Ok" => "0"]);
?>
