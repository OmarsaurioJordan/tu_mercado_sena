<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

validation();

$cond = "";
$vars = [];

$palabras = isset($_GET["palabras"]) ? $_GET["palabras"] : "";
if ($palabras != "") {
    $cond .= " AND m.mensaje LIKE ?";
    $vars[] = "%$palabras%";
}

$activos = isset($_GET["activos"]) ? $_GET["activos"] : "0";
if ($activos != "0") {
    $cond .= " AND ch.estado_id IN ('1', '6', '7')";
}

$registro_desde = isset($_GET["registro_desde"]) ? $_GET["registro_desde"] : "";
if ($registro_desde != "") {
    $cond .= " AND m.fecha_registro >= ?";
    $vars[] = $registro_desde;
}

$registro_hasta = isset($_GET["registro_hasta"]) ? $_GET["registro_hasta"] : "";
if ($registro_hasta != "") {
    $cond .= " AND m.fecha_registro <= ?";
    $vars[] = $registro_hasta;
}

$chat_id = isset($_GET["chat_id"]) ? $_GET["chat_id"] : "0";
if ($chat_id != "0") {
    $cond .= " AND m.chat_id = ?";
    $vars[] = $chat_id;
}

$con_imagen = isset($_GET["con_imagen"]) ? $_GET["con_imagen"] : "0";
if ($con_imagen != "0" && $palabras == "") {
    $cond .= " AND (m.imagen NOT NULL AND m.imagen != '')";
}

$id = isset($_GET["id"]) ? $_GET["id"] : "0";
if ($id != "0") {
    # no lleva concatenacion porque id sobreescribe a las otras condiciones
    $cond = " AND m.id = ?";
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
    $curs = "(m.fecha_registro = ? AND m.id < ?)";
    array_unshift($vars, $cursor_id);
    array_unshift($vars, $cursor_fecha);
}
array_unshift($vars, $cursor_fecha);

$sql = "SELECT m.id AS id, m.es_comprador AS es_comprador, m.chat_id AS chat_id, m.mensaje AS mensaje, m.imagen AS imagen, m.fecha_registro AS fecha_registro
    FROM mensajes m
    LEFT JOIN chats ch ON ch.id = m.chat_id
    WHERE (m.fecha_registro < ? OR $curs) $cond 
    ORDER BY m.fecha_registro DESC, m.id DESC $lim";

$stmt = $conn->prepare($sql);
if (count($vars) > 0) {
    $types = str_repeat("s", count($vars));
    $stmt->bind_param($types, ...$vars);
}
$stmt->execute();
$result = $stmt->get_result();
$mensajes = $result->fetch_all(MYSQLI_ASSOC);

if (!$mensajes) {
    http_response_code(404);
    echo json_encode(["error" => "mensajes no encontrados"]);
    exit;
}

echo json_encode($mensajes);
?>
