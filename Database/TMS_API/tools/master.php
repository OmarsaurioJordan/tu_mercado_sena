<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("../config.php");

$nickname = "Administrador Master";
$mail = "admin@soy.sena.edu.co";
$password = "123456789";
$descripcion = "Información de contacto:\n***$nickname***\ntel: 333666\nmail: $mail\ndir: cra 1 # 12-69\nlugar: edif 328B";
$link = "https://omwekiatl.xyz/";

$sql = "SELECT 1 FROM usuarios WHERE rol_id = '3' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$row = $stmt->fetch();
if ($row) {
    exit;
}

$pass = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO cuentas (email, password, clave, notifica_correo, notifica_push, uso_datos, pin, fecha_clave) VALUES ('$mail', '$pass', NULL, '1', '1', '1', '1234', NOW())";
$stmt = $conn->prepare($sql);
$stmt->execute();
$id = $conn->lastInsertId();

$sql = "INSERT INTO usuarios (cuenta_id, nickname, imagen, descripcion, link, rol_id, estado_id, fecha_registro, fecha_actualiza, fecha_reciente) VALUES ($id, $nickname, '', $descripcion, $link, '3', '1', NOW(), NOW(), NOW())";
$stmt = $conn->prepare($sql);
$stmt->execute();
?>