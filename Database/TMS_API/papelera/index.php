<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

validation();

$cond = "";
$vars = [];

$usuario_id = isset($_GET["usuario_id"]) ? $_GET["usuario_id"] : "0";
if ($usuario_id != "0") {
    $cond .= " AND usuario_id = ?";
    $vars[] = $usuario_id;
}

$con_imagen = isset($_GET["con_imagen"]) ? $_GET["con_imagen"] : "0";
if ($con_imagen == "1") {
    $cond .= " AND (imagen NOT NULL AND imagen != '')";
}
else if ($con_imagen == "2") {
    $cond .= " AND (imagen IS NULL OR imagen = '')";
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

$id = isset($_GET["id"]) ? $_GET["id"] : "0";
if ($id != "0") {
    # no lleva concatenacion porque id sobreescribe a las otras condiciones
    $cond = " AND id = ?";
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
    $curs = "(fecha_registro = ? AND id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT id, usuario_id, mensaje, imagen, fecha_registro
    FROM papelera
    WHERE (fecha_registro < ? OR $curs) $cond 
    ORDER BY fecha_registro DESC, id DESC $lim";

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
    echo json_encode(["error" => "papalera no encontrada"]);
    exit;
}

echo json_encode($pqrss);
?>
