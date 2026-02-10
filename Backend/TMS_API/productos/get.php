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

$sql = "SELECT id, nombre, subcategoria_id, integridad_id, vendedor_id, estado_id, descripcion, precio, disponibles, fecha_registro, fecha_actualiza
    FROM productos
    WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();

if (!$producto) {
    http_response_code(404);
    echo json_encode(["error" => "Producto no encontrado"]);
    exit;
}

$sql = "SELECT imagen FROM fotos WHERE producto_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $producto["id"]);
$stmt->execute();
$result = $stmt->get_result();

$imagenes = [];
while ($row = $result->fetch_assoc()) {
    $imagenes[] = $row["imagen"];
}
$producto["imagenes"] = $imagenes;

echo json_encode($producto);
?>
