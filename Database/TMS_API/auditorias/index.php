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

$suceso_id = isset($_GET["suceso_id"]) ? $_GET["suceso_id"] : "0";
if ($suceso_id != "0") {
    $cond .= " AND a.suceso_id = ?";
    $vars[] = $suceso_id;
}

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND a.fecha_registro >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND a.fecha_registro <= ?";
    $vars[] = $registro_hasta;
}

$email = isset($_GET["email"]) ? $_GET["email"] : "";
if ($email != "") {
    # no lleva concatenacion porque email sobreescribe a las otras condiciones
    $cond = " AND c.email = ?";
    $vars = [$email];
}

$id = isset($_GET["id"]) ? $_GET["id"] : "0";
if ($id != "0") {
    # no lleva concatenacion porque id sobreescribe a las otras condiciones
    $cond = " AND a.id = ?";
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
    $curs = "(a.fecha_registro = ? AND a.id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT a.id AS id, a.administrador_id AS administrador_id, a.descripcion AS descripcion, a.suceso_id AS suceso_id, a.fecha_registro AS fecha_registro, u.nickname AS nickname, c.email AS email, DATEDIFF(NOW(), a.fecha_registro) AS dias
    FROM auditorias a
    LEFT JOIN usuarios u ON a.administrador_id = u.id
    LEFT JOIN cuentas c ON u.cuenta_id = c.id
    WHERE (a.fecha_registro < ? OR $curs) $cond 
    ORDER BY a.fecha_registro DESC, a.id DESC $lim";

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
    echo json_encode(["error" => "auditorias no encontradas"]);
    exit;
}

echo json_encode($pqrss);
?>
