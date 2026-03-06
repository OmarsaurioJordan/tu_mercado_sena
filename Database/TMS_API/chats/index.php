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

$palabras = isset($_GET["palabras"]) ? $_GET["palabras"] : "";
if ($palabras != "") {
    $cond .= " AND ch.nickname LIKE ?";
    $vars[] = "%$palabras%";
}

$estado_id = isset($_GET["estado_id"]) ? $_GET["estado_id"] : "0";
if ($estado_id != "0") {
    switch ($estado_id) {
        case "1":
            $cond .= " AND ch.estado_id = 1"; // activo
            break;
        case "2":
            $cond .= " AND ch.estado_id = 11"; // resuelto
            break;
    }
}

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND ch.fecha_registro >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND ch.fecha_registro <= ?";
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
    $curs = "(ch.fecha_registro = ? AND ch.id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT ch.id AS id, ch.usuario_id AS usuario_id, ch.mensaje AS mensaje, ch.motivo_id AS motivo_id, ch.estado_id AS estado_id, ch.fecha_registro AS fecha_registro, u.nickname AS nickname, c.email AS email, DATEDIFF(NOW(), ch.fecha_registro) AS dias
    FROM pqrs p
    LEFT JOIN usuarios u ON ch.usuario_id = u.id
    LEFT JOIN cuentas c ON u.cuenta_id = c.id
    WHERE (ch.fecha_registro < ? OR $curs) $cond 
    ORDER BY ch.fecha_registro DESC, ch.id DESC $lim";

$stmt = $conn->prepare($sql);
if (count($vars) > 0) {
    $types = str_repeat("s", count($vars));
    $stmt->bind_param($types, ...$vars);
}
$stmt->execute();
$result = $stmt->get_result();
$pqrss = $result->fetch_all(MYSQLI_ASSOC);

if (!$pqrss) {
    http_response_code(404);
    echo json_encode(["error" => "PQRSs no encontradas"]);
    exit;
}

echo json_encode($pqrss);
?>
