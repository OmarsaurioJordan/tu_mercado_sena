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

$rol_id = isset($_GET["rol_id"]) ? $_GET["rol_id"] : "0";
if ($rol_id != "0") {
    $cond .= " AND u.rol_id = $rol_id";
}

$estado_id = isset($_GET["estado_id"]) ? $_GET["estado_id"] : "0";
if ($estado_id != "0") {
    switch ($estado_id) {
        case "1":
        case "2":
        case "3":
        case "4":
            $cond .= " AND u.estado_id = " . $estado_id; // act, inv, elim, bloq
            break;
        case "5":
            $cond .= " AND u.estado_id = 10"; // denunciado
            break;
        case "6":
            $cond .= " AND u.estado_id IN (1, 2)"; // act-inv
            break;
        case "6":
            $cond .= " AND u.estado_id IN (4, 10)"; // bloq-denun
            break;
    }
}

$con_link = isset($_GET["con_link"]) ? $_GET["con_link"] : "0";
if ($con_link != "0") {
    $cond .= " AND u.link != ''";
}

$con_descripcion = isset($_GET["con_descripcion"]) ? $_GET["con_descripcion"] : "0";
if ($con_descripcion != "0") {
    $cond .= " AND u.descripcion != ''";
}

$con_productos = isset($_GET["con_productos"]) ? $_GET["con_productos"] : "0";
if ($con_productos != "0") {
    $cond .= " AND EXISTS (SELECT 1 FROM productos p WHERE p.vendedor_id = u.id LIMIT 1)";
}

$dias_activo = isset($_GET["dias_activo"]) ? $_GET["dias_activo"] : "0";
if ($dias_activo != "0") {
    $cond .= " AND u.fecha_reciente >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $vars[] = $dias_activo;
}

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND u.fecha_registro >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND u.fecha_registro <= ?";
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
    $curs = "(u.fecha_registro = ? AND u.id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT u.id AS id, c.email AS email, u.rol_id AS rol_id, u.nickname AS nickname, u.imagen AS imagen, u.descripcion AS descripcion, u.link AS link, u.estado_id AS estado_id, u.fecha_registro AS fecha_registro, u.fecha_actualiza AS fecha_actualiza, u.fecha_reciente AS fecha_reciente
    FROM usuarios u
    LEFT JOIN cuentas c ON u.cuenta_id = c.id
    WHERE (u.fecha_registro < ? OR $curs) AND u.rol_id != 3 $cond 
    ORDER BY u.fecha_registro DESC, u.id DESC $lim";

$stmt = $conn->prepare($sql);
if (count($vars) > 0) {
    $types = str_repeat("s", count($vars));
    $stmt->bind_param($types, ...$vars);
}
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

if (!$usuarios) {
    http_response_code(404);
    echo json_encode(["error" => "Usuarios no encontrados"]);
    exit;
}

echo json_encode($usuarios);
?>
