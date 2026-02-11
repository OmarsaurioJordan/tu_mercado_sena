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
    $cond .= " AND p.motivo_id == ?";
    $vars[] = $motivo_id;
}

$estado_id = isset($_GET["estado_id"]) ? $_GET["estado_id"] : "0";
if ($estado_id != "0") {
    switch ($estado_id) {
        case "1":
            $cond .= " AND p.estado_id = 1"; // activo
            break;
        case "2":
            $cond .= " AND p.estado_id = 11"; // resuelto
            break;
    }
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

$email = isset($_GET["email"]) ? $_GET["email"] : "";
if ($email != "") {
    # no lleva concatenacion porque email sobreescribe a las otras condiciones
    $cond = " AND c.email = ?";
    $vars = [$email];
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

$sql = "SELECT p.id AS id, p.usuario_id AS usuario_id, p.mensaje AS mensaje, p.motivo_id AS motivo_id, p.estado_id AS estado_id, p.fecha_registro AS fecha_registro, u.nickname AS nickname, c.email AS email, u.imagen AS imagen, DATEDIFF(NOW(), p.fecha_registro) AS dias
    FROM pqrs p
    LEFT JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN cuentas c ON u.cuenta_id = c.id
    WHERE (p.fecha_registro < ? OR $curs) $cond 
    ORDER BY p.fecha_registro DESC, p.id DESC $lim";

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
    echo json_encode(["error" => "PQRS no encontradas"]);
    exit;
}

echo json_encode($pqrs);
?>
