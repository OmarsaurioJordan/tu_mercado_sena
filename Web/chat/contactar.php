<?php
require_once '../config.php';
date_default_timezone_set('America/Bogota');

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$producto_id = isset($_GET['producto_id']) ? (int)$_GET['producto_id'] : 0;

if ($producto_id <= 0) {
    header('Location: ../productos/index.php');
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT p.*, u.id as vendedor_id 
    FROM productos p
    INNER JOIN usuarios u ON p.vendedor_id = u.id
    WHERE p.id = ? AND p.estado_id = 1
");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$producto) {
    $conn->close();
    header('Location: ../productos/index.php');
    exit;
}

if ($user['id'] == $producto['vendedor_id']) {
    $conn->close();
    header("Location: ../productos/producto.php?id=$producto_id");
    exit;
}

require_once __DIR__ . '/../config_api.php';

// Usar API Laravel para crear el chat (el front hace POST con el token)
if (defined('USE_LARAVEL_API') && USE_LARAVEL_API) {
    $conn->close();
    $base = getBaseUrl();
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
    <p>Iniciando conversación...</p>
    <script>
        window.USE_LARAVEL_API = true;
        window.LARAVEL_API_URL = <?= json_encode(defined('LARAVEL_API_URL') ? LARAVEL_API_URL : '') ?>;
    </script>
    <script src="<?= $base ?>js/api-config.js"></script>
    <script>
        (function() {
            var productoId = <?= (int)$producto_id ?>;
            var token = localStorage.getItem('api_token');
            if (!token) {
                window.location.href = '<?= $base ?>auth/login.php?redirect=' + encodeURIComponent(window.location.pathname + '?producto_id=' + productoId);
                return;
            }
            var url = (window.API_CONFIG && window.API_CONFIG.LARAVEL_URL) ? window.API_CONFIG.LARAVEL_URL + 'productos/' + productoId + '/chats' : '<?= (defined('LARAVEL_API_URL') ? LARAVEL_API_URL : '') ?>productos/' + productoId + '/chats';
            fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
            .then(function(result) {
                var data = result.data;
                var chatId = (data.data && data.data.id) ? data.data.id : (data.id || null);
                if (result.ok && chatId) {
                    window.location.href = '<?= $base ?>chat/chat.php?id=' + chatId;
                } else {
                    var msg = (data.message || (data.errors && Object.values(data.errors).flat().join('. ')) || 'No se pudo iniciar el chat.');
                    document.body.innerHTML = '<p>' + msg + ' <a href="<?= $base ?>productos/producto.php?id=<?= $producto_id ?>">Volver al producto</a></p>';
                }
            })
            .catch(function() {
                document.body.innerHTML = '<p>Error de conexión. <a href="<?= $base ?>productos/producto.php?id=<?= $producto_id ?>">Volver al producto</a></p>';
            });
        })();
    </script>
</body>
</html>
    <?php
    exit;
}

// Flujo PHP: crear chat en BD
$stmt = $conn->prepare("
    SELECT id FROM chats 
    WHERE comprador_id = ? AND producto_id = ? AND estado_id = 1
");
$stmt->bind_param("ii", $user['id'], $producto_id);
$stmt->execute();
$chat_existente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($chat_existente) {
    $conn->close();
    header("Location: chat.php?id=" . $chat_existente['id']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO chats (comprador_id, producto_id, estado_id, visto_comprador, visto_vendedor)
    VALUES (?, ?, 1, 0, 0)
");
$stmt->bind_param("ii", $user['id'], $producto_id);
$stmt->execute();
$chat_id = $conn->insert_id;
$stmt->close();

if (isset($_POST['mensaje_inicial']) && !empty(trim($_POST['mensaje_inicial']))) {
    $mensaje = sanitize($_POST['mensaje_inicial']);
    $es_comprador = 1;
    $stmt = $conn->prepare("
        INSERT INTO mensajes (es_comprador, chat_id, mensaje)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $es_comprador, $chat_id, $mensaje);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: chat.php?id=$chat_id");
exit;

