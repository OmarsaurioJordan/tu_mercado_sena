<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

if (!isset($_GET["email"]) || !isset($_GET["rol"])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta email o rol"]);
    exit;
}
$email = $_GET["email"];
$rol = $_GET["rol"];

$sql = "UPDATE usuarios AS u
    INNER JOIN cuentas AS c
    ON u.cuenta_id = c.id
    SET u.rol_id = ?
    WHERE c.email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $rol, $email);
$stmt->execute();
if ($stmt->errno === 0) {
    echo json_encode(["Ok" => "1"]);
    exit;
}
echo json_encode(["Ok" => "0"]);
?>
