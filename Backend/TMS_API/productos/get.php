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

$sql = "SELECT p.id AS id, p.nombre AS nombre, p.subcategoria_id AS subcategoria_id, p.integridad_id AS integridad_id, p.vendedor_id AS vendedor_id, p.estado_id AS estado_id, p.descripcion AS descripcion, p.precio AS precio, p.disponibles AS disponibles, p.fecha_registro AS fecha_registro, p.fecha_actualiza AS fecha_actualiza, u.nickname AS vendedor_nickname, s.categoria_id AS categoria_id
    FROM productos p
    LEFT JOIN usuarios u ON p.vendedor_id = u.id
    LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
    WHERE p.id = ?";

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
