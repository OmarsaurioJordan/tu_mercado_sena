<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';
date_default_timezone_set('America/Bogota');

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$producto_id = isset($_GET['producto_id']) ? (int)$_GET['producto_id'] : 0;

if ($producto_id <= 0) {
    header('Location: ../index.php');
    exit;
}

$producto = apiGetProducto($producto_id);
if (!$producto || empty($producto['id'])) {
    header('Location: ../index.php');
    exit;
}

$vendedor_id = $producto['vendedor_id'] ?? $producto['usuario_id'] ?? $producto['vendedor']['id'] ?? 0;
if ($user['id'] == $vendedor_id) {
    header("Location: ../productos/producto.php?id=" . $producto_id);
    exit;
}

// Crear chat desde PHP (evita CORS y fallos de JS); si la API devuelve chat_id redirigimos al chat
$crear = apiCrearChatPorProducto($producto_id);
if ($crear['success'] && $crear['chat_id'] > 0) {
    header('Location: chat.php?id=' . $crear['chat_id']);
    exit;
}

$base = getBaseUrl();
$api_token = getToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciando chat...</title>
    <link rel="stylesheet" href="<?= $base ?>styles.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;flex-direction:column;gap:1rem;">
    <p id="status">Iniciando conversación...</p>
    <script>
        window.USE_LARAVEL_API = true;
        window.LARAVEL_API_URL = <?= json_encode(defined('LARAVEL_API_URL') ? rtrim(LARAVEL_API_URL, '/') . '/' : '') ?>;
        window.API_TOKEN_FROM_SERVER = <?= json_encode($api_token ?: '') ?>;
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script>
        (function() {
            var productoId = <?= (int)$producto_id ?>;
            var token = window.API_TOKEN_FROM_SERVER || localStorage.getItem('api_token');
            if (!token) {
                window.location.href = '<?= $base ?>auth/login.php?redirect=' + encodeURIComponent('<?= $base ?>chat/contactar.php?producto_id=' + productoId);
                return;
            }
            if (token && typeof localStorage !== 'undefined') localStorage.setItem('api_token', token);
            var baseUrl = (window.API_CONFIG && window.API_CONFIG.LARAVEL_URL) ? window.API_CONFIG.LARAVEL_URL : window.LARAVEL_API_URL || '';
            var url = baseUrl + 'productos/' + productoId + '/chats';
            fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, status: r.status, data: data }; }); })
            .then(function(result) {
                var data = result.data;
                var chatId = (data.data && data.data.id) ? data.data.id : (data.chat && data.chat.id) ? data.chat.id : (data.id != null) ? data.id : null;
                if ((result.ok || result.status === 201) && chatId) {
                    window.location.href = '<?= $base ?>chat/chat.php?id=' + chatId;
                } else {
                    var msg = (data && data.message) || (data && data.errors && Object.values(data.errors).flat().join('. ')) || 'No se pudo iniciar el chat.';
                    document.getElementById('status').innerHTML = msg + ' <a href="<?= $base ?>productos/producto.php?id=<?= $producto_id ?>">Volver al producto</a>';
                }
            })
            .catch(function(err) {
                document.getElementById('status').innerHTML = 'Error de conexión. <a href="<?= $base ?>productos/producto.php?id=<?= $producto_id ?>">Volver al producto</a>';
            });
        })();
    </script>
</body>
</html>
