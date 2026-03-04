<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

validation();

if (!isset($_GET["id"]) || !isset($_GET["mensaje"]) || !isset($_GET["motivo_id"])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta id o mensaje o motivo"]);
    exit;
}
$id = $_GET["id"];
$mensaje = $_GET["mensaje"];
$motivo_id = $_GET["motivo_id"];

$sql = "INSERT INTO notificaciones (usuario_id, motivo_id, mensaje, visto, fecha_registro) VALUES (?, ?, ?, 0, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $id, $motivo_id, $mensaje);
$stmt->execute();
if ($stmt->errno === 0) {

    auditar(4, "enviar mail $id -> $estado");

    echo json_encode(["Ok" => "1"]);
    exit;
}

echo json_encode(["Ok" => "0"]);
?>