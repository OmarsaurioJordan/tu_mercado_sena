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

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND li.fecha_registro >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND li.fecha_registro <= ?";
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
    $cond = " AND li.id = ?";
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
    $curs = "(li.fecha_registro = ? AND li.id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT li.id AS id, li.usuario_id AS usuario_id, li.ip_direccion AS ip_direccion, li.informacion AS informacion, li.fecha_registro AS fecha_registro, u.nickname AS nickname, c.email AS email, DATEDIFF(NOW(), li.fecha_registro) AS dias
    FROM login_ip li
    LEFT JOIN usuarios u ON li.usuario_id = u.id
    LEFT JOIN cuentas c ON u.cuenta_id = c.id
    WHERE (li.fecha_registro < ? OR $curs) $cond 
    ORDER BY li.fecha_registro DESC, li.id DESC $lim";

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
    echo json_encode(["error" => "logins no encontrados"]);
    exit;
}

echo json_encode($pqrss);
?>
