<?php
/**
 * Restablecer contraseña. El flujo actual usa solo la API (forgot_password → código → nueva contraseña).
 * Este enlace con token ya no usa SQL; si el usuario llega aquí, se le indica que use "Recuperar contraseña".
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_api.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    header('Location: forgot_password.php');
    exit;
}

// No se usa SQL. El restablecimiento se hace desde forgot_password.php con la API (validar correo → clave → reestablecer).
$msg = '';
$showForm = false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Crear nueva contraseña</title>
    <link rel="stylesheet" href="<?= getAbsoluteBaseUrl() ?>styles.css?v=<?= time(); ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">Crear nueva contraseña</h1>

            <div class="auth-link" style="margin-bottom: 1rem;">
                Para restablecer tu contraseña de forma segura, usa la opción <strong>¿Olvidaste tu contraseña?</strong> en la pantalla de inicio de sesión. Te enviaremos un código a tu correo y podrás crear una nueva contraseña.
            </div>

            <p class="auth-link"><a href="login.php">Ir al inicio de sesión</a></p>
            <p class="auth-link"><a href="forgot_password.php">Recuperar contraseña</a></p>
        </div>
    </div>
</body>
</html>
