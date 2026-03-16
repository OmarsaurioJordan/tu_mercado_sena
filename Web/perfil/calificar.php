<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;

if ($chat_id <= 0) {
    header('Location: historial.php');
    exit;
}

// Cargar compra desde API: chat o desde historial
$compra = apiGetChat($chat_id);
if (!$compra) {
    $compras = apiGetHistorialCompras();
    foreach (is_array($compras) ? $compras : [] as $c) {
        if ((int)($c['chat_id'] ?? $c['id'] ?? 0) === $chat_id) {
            $compra = $c;
            break;
        }
    }
}

if (!$compra) {
    header('Location: historial.php');
    exit;
}

$producto_id = $compra['producto_id'] ?? $compra['producto']['id'] ?? 0;
$producto_nombre = $compra['producto_nombre'] ?? $compra['producto']['nombre'] ?? 'Producto';
$vendedor_nombre = $compra['vendedor_nombre'] ?? $compra['vendedor']['nickname'] ?? $compra['vendedor']['nombre'] ?? '';
$fecha_venta = $compra['fecha_venta'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calificacion = isset($_POST['calificacion']) ? (int)$_POST['calificacion'] : 0;
    $comentario = isset($_POST['comentario']) ? trim(sanitize($_POST['comentario'] ?? '')) : '';
    if ($calificacion >= 1 && $calificacion <= 5) {
        $comentario = mb_substr($comentario, 0, 512);
        $res = apiCalificarChat($chat_id, $calificacion, $comentario);
    }
    header('Location: historial.php?mensaje=calificado');
    exit;
}

$imgUrl = getProductMainImage($producto_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar compra - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .calificar-card { max-width: 500px; margin: 2rem auto; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 2rem; }
        .calificar-card h1 { margin-bottom: 1.5rem; color: var(--color-primary); font-size: 1.5rem; }
        .calificar-producto { display: flex; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        .calificar-producto img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .stars-input { display: flex; gap: 0.5rem; margin: 1rem 0; font-size: 2rem; }
        .stars-input label { cursor: pointer; color: #ddd; }
        .stars-input input { display: none; }
        .stars-input input:checked ~ label, .stars-input label:hover, .stars-input label:hover ~ label { color: #ffc107; }
        .stars-input label:hover { transform: scale(1.1); }
        .btn-block { width: 100%; margin-top: 1rem; padding: 0.75rem; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="calificar-card">
                <h1><i class="ri-star-smile-line"></i> Calificar tu compra</h1>

                <div class="calificar-producto">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($producto_nombre) ?>">
                    <div>
                        <h3 style="margin: 0 0 0.25rem 0;"><?= htmlspecialchars($producto_nombre) ?></h3>
                        <p style="margin: 0; color: #666;">Vendedor: <?= htmlspecialchars($vendedor_nombre) ?></p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem;"><?= $fecha_venta ? date('d/m/Y', strtotime($fecha_venta)) : '' ?></p>
                    </div>
                </div>

                <form method="post" action="">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.25rem;">Calificación (estrellas)</label>
                    <div class="stars-input">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="calificacion" value="<?= $i ?>" id="star<?= $i ?>" required>
                            <label for="star<?= $i ?>">★</label>
                        <?php endfor; ?>
                    </div>

                    <button type="submit" class="btn-primary btn-block"><i class="ri-send-plane-line"></i> Enviar calificación</button>
                    <a href="historial.php" class="btn-small" style="display: block; text-align: center; margin-top: 0.75rem;">Volver al historial</a>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container"><p>&copy; 2025 Tu Mercado SENA.</p></div>
    </footer>
</body>
</html>
