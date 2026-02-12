<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

validation();

if (!isset($_GET["email"]) || !isset($_GET["pin"])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan credenciales"]);
    exit;
}
$email = $_GET["email"];
$pin = $_GET["pin"];

if ($pin == "") {
    $sql = "SELECT 1 FROM cuentas WHERE email = ? AND pin IN (?, NULL)";
}
else {
    $sql = "SELECT 1 FROM cuentas WHERE email = ? AND pin = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $pin);
$stmt->execute();
$result = $stmt->get_result();
$info = $result->fetch_assoc();

if ($info) {
    echo json_encode(["Ok" => "1"]);
    exit;
}
echo json_encode(["Ok" => "0"]);
?>
