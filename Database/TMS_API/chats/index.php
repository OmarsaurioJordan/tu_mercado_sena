<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

$cond = "";
$vars = [];

$estado_id = isset($_GET["estado_id"]) ? $_GET["estado_id"] : "0";
if ($estado_id != "0") {
    if ($estado_id == "100") {
        $cond .= " AND ch.estado_id IN ('12', '13', '14')";
    }
    else {
        $cond .= " AND ch.estado_id = ?";
        $vars[] = $estado_id;
    }
}

$comprador_id = isset($_GET["comprador_id"]) ? $_GET["comprador_id"] : "0";
$vendedor_id = isset($_GET["vendedor_id"]) ? $_GET["vendedor_id"] : "0";
if ($comprador_id != "0" && $vendedor_id != "0") {
    $cond .= " AND (ch.comprador_id = ? OR uv.id = ?)";
    $vars[] = $comprador_id;
    $vars[] = $vendedor_id;
}
else if ($vendedor_id != "0") {
    $cond .= " AND uv.id = ?";
    $vars[] = $vendedor_id;
}
else if ($comprador_id != "0") {
    $cond .= " AND ch.comprador_id = ?";
    $vars[] = $comprador_id;
}

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND ch.fecha_venta >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND ch.fecha_venta <= ?";
    $vars[] = $registro_hasta;
}

$id = isset($_GET["id"]) ? $_GET["id"] : "0";
if ($id != "0") {
    # no lleva concatenacion porque id sobreescribe a las otras condiciones
    $cond = " AND ch.id = ?";
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
    $curs = "(ch.fecha_venta = ? AND ch.id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT ch.id AS id, ch.comprador_id AS comprador_id, ch.producto_id AS producto_id, ch.estado_id AS estado_id, ch.precio AS precio, ch.fecha_venta AS fecha_venta, ch.comentario AS comentario, ch.calificacion AS calificacion, ch.cantidad AS cantidad, uv.nickname AS vendedor_name, uc.nickname AS comprador_name, p.nombre AS producto_name, uv.id AS vendedor_id, DATEDIFF(NOW(), ch.fecha_venta) AS dias
    FROM chats ch
    LEFT JOIN productos p ON ch.producto_id = p.id
    LEFT JOIN usuarios uv ON p.vendedor_id = uv.id
    LEFt JOIN usuarios uc ON ch.comprador_id = uc.id
    WHERE (ch.fecha_venta < ? OR $curs) $cond 
    ORDER BY ch.fecha_venta DESC, ch.id DESC $lim";

$stmt = $conn->prepare($sql);
if (count($vars) > 0) {
    $types = str_repeat("s", count($vars));
    $stmt->bind_param($types, ...$vars);
}
$stmt->execute();
$result = $stmt->get_result();
$chats = $result->fetch_all(MYSQLI_ASSOC);

if (!$chats) {
    http_response_code(404);
    echo json_encode(["error" => "chats no encontrados"]);
    exit;
}

echo json_encode($chats);
?>
