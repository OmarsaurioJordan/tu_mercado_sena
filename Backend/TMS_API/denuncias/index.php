<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

$cond = "";
$vars = [];

$nickname = isset($_GET["nickname"]) ? $_GET["nickname"] : "";
if ($nickname != "") {
    $cond .= " AND u.nickname LIKE ?";
    $vars[] = "%$nickname%";
}

$motivo_id = isset($_GET["motivo_id"]) ? $_GET["motivo_id"] : "0";
if ($motivo_id != "0") {
    $cond .= " AND d.motivo_id == ?";
    $vars[] = $motivo_id;
}

$estado_id = isset($_GET["estado_id"]) ? $_GET["estado_id"] : "0";
if ($estado_id != "0") {
    switch ($estado_id) {
        case "1":
            $cond .= " AND d.estado_id = 1"; // activo
            break;
        case "2":
            $cond .= " AND d.estado_id = 11"; // resuelto
            break;
    }
}

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND d.fecha_registro >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND d.fecha_registro <= ?";
    $vars[] = $registro_hasta;
}

$email = isset($_GET["email"]) ? $_GET["email"] : "";
if ($email != "") {
    # no lleva concatenacion porque email sobreescribe a las otras condiciones
    $cond = " AND c.email = ?";
    $vars = [$email];
}

$id = isset($_GET["id"]) ? $_GET["id"] : 0;
if ($id != 0) {
    # no lleva concatenacion porque id sobreescribe a las otras condiciones
    $cond = " AND d.id = ?";
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
    $curs = "(d.fecha_registro = ? AND d.id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT d.id AS id, d.denunciante_id AS denunciante_id, d.producto_id AS producto_id, d.usuario_id AS usuario_id, d.chat_id AS chat_id, d.motivo_id AS motivo_id, d.estado_id AS estado_id, d.fecha_registro AS fecha_registro, u.nickname AS denunciante, dp.nombre AS producto, du.nickname AS usuario, c.email AS email, DATEDIFF(NOW(), d.fecha_registro) AS dias
    FROM denuncias d
    LEFT JOIN usuarios u ON d.denunciante_id = u.id
    LEFT JOIN cuentas c ON u.cuenta_id = c.id
    LEFT JOIN productos dp ON d.producto_id = dp.id
    LEFt JOIN usuarios du ON d.usuario_id = du.id
    WHERE (d.fecha_registro < ? OR $curs) $cond 
    ORDER BY d.fecha_registro DESC, d.id DESC $lim";

$stmt = $conn->prepare($sql);
if (count($vars) > 0) {
    $types = str_repeat("s", count($vars));
    $stmt->bind_param($types, ...$vars);
}
$stmt->execute();
$result = $stmt->get_result();
$pqrs = $result->fetch_all(MYSQLI_ASSOC);

if (!$pqrs) {
    http_response_code(404);
    echo json_encode(["error" => "denuncias no encontradas"]);
    exit;
}

echo json_encode($pqrs);
?>
