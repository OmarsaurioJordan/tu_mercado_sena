<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

$cond = "";
$vars = [];

$nombre = isset($_GET["nombre"]) ? $_GET["nombre"] : "";
if ($nombre != "") {
    $cond .= " AND nombre LIKE ?";
    $vars[] = "%$nombre%";
}

$subcategoria_id = isset($_GET["subcategoria_id"]) ? $_GET["subcategoria_id"] : "0";
if ($subcategoria_id != "0") {
    $cond .= " AND subcategoria_id == ?";
    $vars[] = $subcategoria_id;
}

$categoria_id = isset($_GET["categoria_id"]) ? $_GET["categoria_id"] : "0";
if ($categoria_id != "0") {
    $ssql = "SELECT id FROM subcategorias WHERE categoria_id = ?";
    $sstmt = $conn->prepare($ssql);
    $sstmt->bind_param("i", $categoria_id);
    $sstmt->execute();
    $sresult = $sstmt->get_result();
    $cat = "";
    while ($row = $sresult->fetch_assoc()) {
        $cat .= $row["id"]. ",";
    }
    if ($cat != "") {
        $cat = substr($cat, 0, -1);
    }
    $cond .= " AND subcategoria_id IN (". $cat .")";
}

$integridad_id = isset($_GET["integridad_id"]) ? $_GET["integridad_id"] : "0";
if ($integridad_id != "0") {
    $cond .= " AND integridad_id == ?";
    $vars[] = $integridad_id;
}

$estado_id = isset($_GET["estado_id"]) ? $_GET["estado_id"] : "0";
if ($estado_id != "0") {
    switch ($estado_id) {
        case "1":
        case "2":
        case "3":
        case "4":
            $cond .= " AND estado_id = " . $estado_id; // act, inv, elim, bloq
            break;
        case "5":
            $cond .= " AND estado_id = 10"; // denunciado
            break;
        case "6":
            $cond .= " AND estado_id IN (1, 2)"; // act-inv
            break;
        case "6":
            $cond .= " AND estado_id IN (4, 10)"; // bloq-denun
            break;
    }
}

$precio_min = isset($_GET["precio_min"]) ? $_GET["precio_min"] : 0;
if ($precio_min != 0) {
    $cond .= " AND precio >= ?";
    $vars[] = $precio_min;
}

$precio_max = isset($_GET["precio_max"]) ? $_GET["precio_max"] : 0;
if ($precio_max != 0) {
    $cond .= " AND precio <= ?";
    $vars[] = $precio_max;
}

$con_descripcion = isset($_GET["con_descripcion"]) ? $_GET["con_descripcion"] : "0";
if ($con_descripcion != "0") {
    $cond .= " AND descripcion != ''";
}

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND fecha_registro >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND fecha_registro <= ?";
    $vars[] = $registro_hasta;
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
    $curs = "(fecha_registro = ? AND id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT id, nombre, subcategoria_id, integridad_id, vendedor_id, estado_id, descripcion, precio, disponibles, fecha_registro, fecha_actualiza
    FROM productos
    WHERE (fecha_registro < ? OR $curs) $cond 
    ORDER BY fecha_registro DESC, id DESC $lim";

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
