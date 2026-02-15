<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

$cond = "";
$vars = [];

$nombre = isset($_GET["nombre"]) ? $_GET["nombre"] : "";
if ($nombre != "") {
    $cond .= " AND p.nombre LIKE ?";
    $vars[] = "%$nombre%";
}

$subcategoria_id = isset($_GET["subcategoria_id"]) ? $_GET["subcategoria_id"] : "0";
if ($subcategoria_id != "0") {
    $cond .= " AND p.subcategoria_id == ?";
    $vars[] = $subcategoria_id;
}

$categoria_id = isset($_GET["categoria_id"]) ? $_GET["categoria_id"] : "0";
if ($categoria_id != "0") {
    $cond .= " AND s.categoria_id == ?";
    $vars[] = $categoria_id;
}

$integridad_id = isset($_GET["integridad_id"]) ? $_GET["integridad_id"] : "0";
if ($integridad_id != "0") {
    $cond .= " AND p.integridad_id == ?";
    $vars[] = $integridad_id;
}

$estado_id = isset($_GET["estado_id"]) ? $_GET["estado_id"] : "0";
if ($estado_id != "0") {
    switch ($estado_id) {
        case "100":
            $cond .= " AND p.estado_id IN (1, 2)"; // act-inv
            break;
        case "101":
            $cond .= " AND p.estado_id IN (4, 10)"; // bloq-denun
            break;
        default:
            $cond .= " AND p.estado_id = " . $estado_id; // act, inv, elim, bloq
            break;
    }
}

$precio_min = isset($_GET["precio_min"]) ? $_GET["precio_min"] : 0;
if ($precio_min != 0) {
    $cond .= " AND p.precio >= ?";
    $vars[] = $precio_min;
}

$precio_max = isset($_GET["precio_max"]) ? $_GET["precio_max"] : 0;
if ($precio_max != 0) {
    $cond .= " AND p.precio <= ?";
    $vars[] = $precio_max;
}

$con_descripcion = isset($_GET["con_descripcion"]) ? $_GET["con_descripcion"] : "0";
if ($con_descripcion != "0") {
    $cond .= " AND p.descripcion != ''";
}

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND p.fecha_registro >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND p.fecha_registro <= ?";
    $vars[] = $registro_hasta;
}

$id = isset($_GET["id"]) ? $_GET["id"] : 0;
if ($id != 0) {
    # no lleva concatenacion porque id sobreescribe a las otras condiciones
    $cond = " AND p.id = ?";
    $vars = [$id];
}

$limite = isset($_GET["limite"]) ? $_GET["limite"] : "";
$lim = "";
if ($limite != "") {
    $lim = " LIMIT ?";
    $vars[] = $limite;
}

$cursor_fecha = isset($_GET["cursor_fecha"]) ? $_GET["cursor_fecha"] : date("Y-m-d H:i:s");
$cursor_id = isset($_GET["cursor_id"]) ? $_GET["cursor_id"] : "";
$curs = "1";
if ($cursor_id != "") {
    $curs = "(p.fecha_registro = ? AND p.id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT p.id AS id, p.nombre AS nombre, p.subcategoria_id AS subcategoria_id, p.integridad_id AS integridad_id, p.vendedor_id AS vendedor_id, p.estado_id AS estado_id, p.descripcion AS descripcion, p.precio AS precio, p.disponibles AS disponibles, p.fecha_registro AS fecha_registro, p.fecha_actualiza AS fecha_actualiza, u.nickname AS vendedor_nickname, s.categoria_id AS categoria_id
    FROM productos p
    LEFT JOIN usuarios u ON p.vendedor_id = u.id
    LEFT JOIN subcategorias s ON p.subcategoria_id = s.id
    WHERE (p.fecha_registro < ? OR $curs) $cond 
    ORDER BY p.fecha_registro DESC, p.id DESC $lim";

$stmt = $conn->prepare($sql);
if (count($vars) > 0) {
    $types = str_repeat("s", count($vars));
    $stmt->bind_param($types, ...$vars);
}
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($prod = $result->fetch_assoc()) {

    $sql = "SELECT imagen FROM fotos WHERE producto_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prod["id"]);
    $stmt->execute();
    $resImg = $stmt->get_result();

    $imagenes = [];
    while ($img = $resImg->fetch_assoc()) {
        $imagenes[] = $img["imagen"];
    }

    $prod["imagenes"] = $imagenes;
    $productos[] = $prod;
}

if (count($productos) === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Productos no encontrados"]);
    exit;
}

echo json_encode($productos);
?>
