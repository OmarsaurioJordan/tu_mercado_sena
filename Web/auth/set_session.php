<?php
/**
 * Establece la sesión PHP con los datos del usuario tras login/registro vía API Laravel.
 * Se llama desde el front (fetch POST) después de recibir user + token de Laravel.
 * Así el resto del sitio sigue usando isLoggedIn() y $_SESSION.
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data || empty($data['usuario_id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Datos de usuario requeridos']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['usuario_id']      = (int)($data['usuario_id']);
$_SESSION['usuario_nombre']  = $data['nickname']          ?? '';
$_SESSION['usuario_rol']     = (int)($data['rol_id']      ?? 1);
$_SESSION['usuario_imagen']  = $data['imagen']            ?? '';
$_SESSION['cuenta_id']       = (int)($data['cuenta_id']   ?? 0);
$_SESSION['nickname']        = $data['nickname']          ?? '';
$_SESSION['imagen']          = $data['imagen']            ?? '';
$_SESSION['estado_id']       = (int)($data['estado_id']   ?? 1);
$_SESSION['notifica_push']   = (int)($data['notifica_push']   ?? 1);
$_SESSION['notifica_correo'] = (int)($data['notifica_correo'] ?? 0);
$_SESSION['uso_datos']       = (int)($data['uso_datos']       ?? 0);
$_SESSION['descripcion']     = $data['descripcion']       ?? '';
$_SESSION['link']            = $data['link']              ?? '';
$_SESSION['email']           = $data['email']             ?? '';

if (!empty($data['api_token'])) {
    $_SESSION['api_token'] = $data['api_token'];
}

session_write_close();

echo json_encode(['ok' => true]);