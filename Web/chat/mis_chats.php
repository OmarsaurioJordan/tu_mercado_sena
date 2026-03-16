<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$chats_raw = apiGetChats();

function _normChat($c, $userId) {
    $p = $c['producto'] ?? $c['product'] ?? [];
    if (!is_array($p)) $p = [];
    $comp = $c['comprador'] ?? $c['buyer'] ?? [];
    $vend = $c['vendedor'] ?? $c['seller'] ?? $p['vendedor'] ?? [];
    if (!is_array($comp)) $comp = [];
    if (!is_array($vend)) $vend = [];
    $id = (int)($c['id'] ?? $c['chat_id'] ?? 0);
    // Imagen del producto: puede venir en product.imagen, imagenes[0], product.photo, o a nivel chat
    $img = $p['imagen'] ?? $p['image'] ?? $p['foto'] ?? $p['photo'] ?? null;
    if (empty($img) && !empty($p['imagenes'][0])) {
        $first = $p['imagenes'][0];
        $img = is_array($first) ? ($first['imagen'] ?? $first['url'] ?? $first['path'] ?? '') : (string)$first;
    }
    if (empty($img)) $img = $c['producto_imagen'] ?? $c['product_image'] ?? '';
    return [
        'chat_id' => $id,
        'comprador_id' => (int)($c['comprador_id'] ?? $comp['id'] ?? 0),
        'vendedor_id' => (int)($c['vendedor_id'] ?? $vend['id'] ?? 0),
        'comprador_nombre' => $comp['nickname'] ?? $comp['name'] ?? $comp['nombre'] ?? '',
        'comprador_avatar' => $comp['imagen'] ?? $comp['avatar'] ?? $comp['image'] ?? '',
        'vendedor_nombre' => $vend['nickname'] ?? $vend['name'] ?? $vend['nombre'] ?? '',
        'vendedor_avatar' => $vend['imagen'] ?? $vend['avatar'] ?? $vend['image'] ?? '',
        'visto_comprador' => (int)($c['visto_comprador'] ?? 0),
        'visto_vendedor' => (int)($c['visto_vendedor'] ?? 0),
        'fecha_venta' => $c['fecha_venta'] ?? null,
        'estado_id' => (int)($c['estado_id'] ?? 1),
        'producto_id' => (int)($c['producto_id'] ?? $p['id'] ?? 0),
        'producto_nombre' => $c['producto_nombre'] ?? $p['nombre'] ?? $p['name'] ?? $p['title'] ?? '',
        'producto_precio' => (float)($c['producto_precio'] ?? $p['precio'] ?? $p['price'] ?? 0),
        'producto_imagen' => $img,
        'ultimo_mensaje' => $c['ultimo_mensaje'] ?? $c['last_message'] ?? $c['last_message_text'] ?? $c['mensaje'] ?? '',
        'ultima_fecha' => $c['ultima_fecha'] ?? $c['last_message_at'] ?? $c['updated_at'] ?? '',
        'primera_fecha' => $c['primera_fecha'] ?? $c['created_at'] ?? '',
    ];
}

$chats_result = array_map(function ($c) use ($user) {
    return _normChat($c, $user['id']);
}, is_array($chats_raw) ? $chats_raw : []);

// Si la API no incluyó producto en el chat, rellenar con apiGetProducto (máx. 10 para no saturar)
$filled = 0;
foreach ($chats_result as &$row) {
    if ($filled >= 10) break;
    if ($row['producto_id'] > 0 && (empty($row['producto_nombre']) || $row['producto_precio'] <= 0)) {
        $prod = apiGetProducto($row['producto_id']);
        if ($prod && is_array($prod)) {
            $row['producto_nombre'] = $prod['nombre'] ?? $prod['name'] ?? $row['producto_nombre'];
            $row['producto_precio'] = (float)($prod['precio'] ?? $prod['price'] ?? 0);
            if (empty($row['producto_imagen'])) {
                $first = $prod['imagenes'][0] ?? null;
                $row['producto_imagen'] = $first && is_array($first) ? ($first['imagen'] ?? $first['url'] ?? '') : ($prod['imagen'] ?? $prod['image'] ?? '');
            }
            if (empty($row['vendedor_nombre']) && !empty($prod['vendedor'])) {
                $v = $prod['vendedor'];
                $row['vendedor_nombre'] = $v['nickname'] ?? $v['name'] ?? $v['nombre'] ?? '';
                $row['vendedor_avatar'] = $v['imagen'] ?? $v['avatar'] ?? $row['vendedor_avatar'];
            }
            $filled++;
        }
    }
}
unset($row);

// Ordenar por ultima_fecha descendente
usort($chats_result, function ($a, $b) {
    $ta = strtotime($a['ultima_fecha'] ?: $a['primera_fecha'] ?: '0');
    $tb = strtotime($b['ultima_fecha'] ?: $b['primera_fecha'] ?: '0');
    return $tb - $ta;
});

require_once __DIR__ . '/../api/config_cierre_automatico.php';
$dias_espera = defined('DIAS_ESPERA_CIERRE') ? DIAS_ESPERA_CIERRE : 7;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Chats - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
    <style>
        /* Estilos específicos para la lista de chats */
        .chats-page-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .chats-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chats-page-header h1 {
            color: var(--color-primary);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chat-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .chat-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background-color: var(--color-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .chat-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .chat-item.unread {
            border-left: 4px solid var(--color-primary);
            background-color: var(--color-bg-secondary);
        }

        .chat-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--color-accent);
            flex-shrink: 0;
        }

        .chat-content {
            flex: 1;
            min-width: 0;
        }

        .chat-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.25rem;
        }

        .chat-user-name {
            font-weight: 600;
            color: var(--color-primary);
            font-size: 1.1rem;
        }

        .chat-time {
            font-size: 0.8rem;
            color: var(--color-text-light);
            white-space: nowrap;
        }

        .chat-product-name {
            font-size: 0.9rem;
            color: var(--color-text-light);
            margin-bottom: 0.25rem;
        }

        .chat-last-message {
            font-size: 0.9rem;
            color: var(--color-text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-product-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .no-chats {
            text-align: center;
            padding: 3rem;
            background-color: var(--color-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .no-chats-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .no-chats h2 {
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }

        .no-chats p {
            color: var(--color-text-light);
            margin-bottom: 1.5rem;
        }

        .unread-badge {
            background-color: var(--color-primary);
            color: white;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            margin-left: 0.5rem;
        }

        .btn-eliminar-chat {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            margin-left: 0.5rem;
        }

        .btn-eliminar-chat:hover {
            background: #c0392b;
            transform: scale(1.1);
        }

        .btn-eliminar-chat i {
            font-size: 1.2rem;
        }

        @media (max-width: 600px) {
            .chat-item {
                padding: 0.75rem;
            }

            .chat-avatar {
                width: 50px;
                height: 50px;
            }

            .chat-product-img {
                width: 40px;
                height: 40px;
            }

            .chats-page-header h1 {
                font-size: 1.4rem;
            }
            
            .btn-eliminar-chat {
                width: 32px;
                height: 32px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container chats-page-container">
            <div class="chats-page-header">
                <h1><i class="ri-chat-3-line"></i> Mis Conversaciones</h1>
            </div>

            <?php if (!empty($chats_result)): ?>
                <div class="chat-list">
                    <?php foreach ($chats_result as $chat): 
                        // Determinar si el usuario actual es comprador o vendedor
                        $es_comprador = ($user['id'] == $chat['comprador_id']);
                        
                        // Determinar el otro usuario (con quien se chatea)
                        $otro_nombre = $es_comprador ? $chat['vendedor_nombre'] : $chat['comprador_nombre'];
                        $otro_avatar = $es_comprador ? $chat['vendedor_avatar'] : $chat['comprador_avatar'];
                        
                        // Verificar si hay mensajes sin leer
                        $sin_leer = false;
                        if ($es_comprador && !$chat['visto_comprador']) {
                            $sin_leer = true;
                        } elseif (!$es_comprador && !$chat['visto_vendedor']) {
                            $sin_leer = true;
                        }
                        
                        // Calcular días restantes si hay fecha_venta
                        $dias_restantes = null;
                        if ($chat['fecha_venta'] && $chat['estado_id'] != 8) {
                            $fecha_venta_obj = new DateTime($chat['fecha_venta']);
                            $fecha_actual = new DateTime();
                            $dias_transcurridos = $fecha_actual->diff($fecha_venta_obj)->days;
                            $dias_restantes = $dias_espera - $dias_transcurridos;
                        }
                        
                        // Formatear tiempo del último mensaje
                        $tiempo = $chat['ultima_fecha'] ? formato_tiempo_relativo($chat['ultima_fecha']) : ($chat['primera_fecha'] ? formato_tiempo_relativo($chat['primera_fecha']) : 'Reciente');
                    ?>
                        <div class="chat-item <?= $sin_leer ? 'unread' : '' ?>">
                            <a href="chat.php?id=<?= $chat['chat_id'] ?>" class="chat-item-link" style="display: contents;">
                                <img src="<?= getAvatarUrl($otro_avatar) ?>" alt="<?= htmlspecialchars($otro_nombre) ?>" class="chat-avatar">
                                <div class="chat-content">
                                    <div class="chat-top-row">
                                        <span class="chat-user-name">
                                            <?= htmlspecialchars($otro_nombre) ?>
                                            <?php if ($sin_leer): ?>
                                                <span class="unread-badge">Nuevo</span>
                                            <?php endif; ?>
                                            <?php if ($chat['estado_id'] == 8): ?>
                                                <span style="background: #e74c3c; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">Cerrado</span>
                                            <?php elseif ($dias_restantes !== null && $dias_restantes >= 0 && $dias_restantes <= 3): ?>
                                                <span style="background: <?= $dias_restantes <= 1 ? '#e74c3c' : '#FFC107' ?>; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">
                                                    <i class="ri-time-line"></i> <?= $dias_restantes ?> día<?= $dias_restantes != 1 ? 's' : '' ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="chat-time"><?= $tiempo ?></span>
                                    </div>
                                    <div class="chat-product-name"><i class="ri-box-3-line"></i> <?= htmlspecialchars($chat['producto_nombre'] ?: 'Producto') ?> — <?= formatPrice($chat['producto_precio']) ?></div>
                                    <div class="chat-last-message">
                                        <?php if ($chat['ultimo_mensaje']): ?>
                                            <?= htmlspecialchars(mb_substr($chat['ultimo_mensaje'], 0, 50)) ?><?= strlen($chat['ultimo_mensaje']) > 50 ? '...' : '' ?>
                                        <?php else: ?>
                                            <em>Sin mensajes aún</em>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php
                                $imgUrl = '';
                                if (!empty($chat['producto_imagen'])) {
                                    $imgUrl = (strpos($chat['producto_imagen'], 'http') === 0)
                                        ? (function_exists('getProductImageUrlPHP') ? getProductImageUrlPHP($chat['producto_imagen']) : $chat['producto_imagen'])
                                        : (defined('LARAVEL_STORAGE_URL') ? rtrim(LARAVEL_STORAGE_URL, '/') . '/' . ltrim(str_replace('uploads/productos/', 'productos/', $chat['producto_imagen']), '/') : getBaseUrl() . 'uploads/productos/' . $chat['producto_imagen']);
                                }
                                if ($imgUrl): ?>
                                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($chat['producto_nombre'] ?: 'Producto') ?>" class="chat-product-img" onerror="this.onerror=null; this.src='<?= htmlspecialchars(getBaseUrl()) ?>assets/images/default-product.jpg';">
                                <?php else: ?>
                                    <div class="chat-product-img" style="background: var(--color-primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                        <i class="ri-image-line" style="font-size: 1.5rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <?php if ($chat['estado_id'] == 8): ?>
                                <button type="button" 
                                        onclick="eliminarChat(<?= $chat['chat_id'] ?>);"
                                        class="btn-eliminar-chat"
                                        title="Eliminar chat">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-chats">
                    <div class="no-chats-icon"><i class="ri-chat-3-line"></i></div>
                    <h2>No tienes conversaciones aún</h2>
                    <p>Cuando contactes a un vendedor o alguien te escriba, tus chats aparecerán aquí.</p>
                    <a href="../index.php" class="btn-primary">Explorar productos</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>
    <script>
        window.BASE_URL = '<?= getBaseUrl() ?>';
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js?v=<?= time(); ?>"></script>
</body>
</html>
