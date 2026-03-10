<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

validation();

if (!isset($_GET["id"])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta id"]);
    exit;
}
$id = $_GET["id"];

valida_edit_admin();

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
    $vars[] = password_hash($password, PASSWORD_BCRYPT);
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
    WHERE id = ?";

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
