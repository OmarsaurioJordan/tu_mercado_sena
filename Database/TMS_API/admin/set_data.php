<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

validation();

if (!isset($_GET["id"]) || !isset($_GET["old_password"])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan credenciales"]);
    exit;
}
$id = $_GET["id"];
$old_password = $_GET["old_password"];

valida_password($id, $old_password);

$cond = "";
$vars = [];

$nickname = isset($_GET["nickname"]) ? $_GET["nickname"] : "";
if ($nickname != "") {
    $cond .= ", u.nickname = ?";
    $vars[] = $nickname;
}

$descripcion = isset($_GET["descripcion"]) ? $_GET["descripcion"] : "";
if ($descripcion != "") {
    $cond .= ", u.descripcion = ?";
    $vars[] = $descripcion;
}

$link = isset($_GET["link"]) ? $_GET["link"] : "";
if ($link != "") {
    $cond .= ", u.link = ?";
    $vars[] = $link;
}

$pin = isset($_GET["pin"]) ? $_GET["pin"] : "";
if ($pin != "") {
    $cond .= ", c.pin = ?";
    $vars[] = $pin;
}

$password = isset($_GET["password"]) ? $_GET["password"] : "";
if ($password != "") {
    $cond .= ", c.password = ?";
    $vars[] = encriptar($password);
}

if (empty($vars)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos"]);
    exit;
}

$cond = substr($cond, 2);
$vars[] = $id;

$sql = "UPDATE usuarios AS u
    INNER JOIN cuentas AS c ON u.cuenta_id = c.id
    SET $cond
    WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$types = str_repeat("s", count($vars));
$stmt->bind_param($types, ...$vars);
$stmt->execute();
if ($stmt->errno === 0) {

    if ($pin != "" || $password != "") {
        auditar(6, "cambio password o pin $id");
    }

    echo json_encode(["Ok" => "1"]);
    exit;
}

echo json_encode(["Ok" => "0"]);
?>
