<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();

$chat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($chat_id <= 0) {
    header('Location: mis_chats.php');
    exit;
}

$chat_raw = apiGetChat($chat_id);

// Si la API no devuelve comprador_id/vendedor_id, buscar en la lista de chats
if ($chat_raw && empty($chat_raw['comprador_id']) && empty($chat_raw['vendedor_id'])) {
    $chats_list = apiGetChats();
    foreach (is_array($chats_list) ? $chats_list : [] as $c) {
        $cid = (int)($c['id'] ?? $c['chat_id'] ?? 0);
        if ($cid === $chat_id) {
            $chat_raw['comprador_id'] = $c['comprador_id'] ?? 0;
            $chat_raw['vendedor_id']  = $c['vendedor_id']  ?? 0;
            $chat_raw['estado_id']    = $c['estado_id']    ?? 1;
            $chat_raw['fecha_venta']  = $c['fecha_venta']  ?? null;
            break;
        }
    }
}

if (!$chat_raw) {
    $chats_list = apiGetChats();
    foreach (is_array($chats_list) ? $chats_list : [] as $c) {
        $cid = (int)($c['id'] ?? $c['chat_id'] ?? 0);
        if ($cid === $chat_id) {
            $chat_raw = $c;
            break;
        }
    }
}

$cid_from_raw = (int)($chat_raw['id'] ?? $chat_raw['chat_id'] ?? 0);
if (!$chat_raw || $cid_from_raw === 0) {
    header('Location: mis_chats.php');
    exit;
}

$p = $chat_raw['producto'] ?? $chat_raw['product'] ?? [];
if (!is_array($p)) $p = [];

$comp = $chat_raw['comprador'] ?? $chat_raw['buyer'] ?? [];
if (!is_array($comp)) $comp = [];

$vend = $p['vendedor'] ?? $chat_raw['vendedor'] ?? $chat_raw['seller'] ?? [];
if (!is_array($vend)) $vend = [];

$chat = [
    'id'                   => (int)($chat_raw['id'] ?? $chat_raw['chat_id'] ?? 0),
    'comprador_id'         => (int)($chat_raw['comprador_id'] ?? $comp['id'] ?? 0),
    'vendedor_id'          => (int)($chat_raw['vendedor_id'] ?? $vend['id'] ?? $p['vendedor']['id'] ?? 0),
    'comprador_nombre'     => $comp['nickname'] ?? $comp['name'] ?? $comp['nombre'] ?? '',
    'comprador_avatar'     => $comp['imagen'] ?? $comp['avatar'] ?? $comp['image'] ?? '',
    'vendedor_nombre'      => $vend['nickname'] ?? $vend['name'] ?? $vend['nombre'] ?? '',
    'vendedor_avatar'      => $vend['imagen'] ?? $vend['avatar'] ?? $vend['image'] ?? '',
    'producto_id'          => (int)($chat_raw['producto_id'] ?? $p['id'] ?? 0),
    'producto_nombre'      => $p['nombre'] ?? $p['name'] ?? $p['title'] ?? $chat_raw['producto_nombre'] ?? '',
    'producto_precio'      => (float)($p['precio'] ?? $p['price'] ?? $chat_raw['producto_precio'] ?? 0),
    'producto_disponibles' => (int)($p['disponibles'] ?? $p['stock'] ?? 1),
    'producto_imagen'      => $p['imagen'] ?? $p['image'] ?? (isset($p['imagenes'][0]) ? ($p['imagenes'][0]['imagen'] ?? $p['imagenes'][0]['url'] ?? '') : ''),
    'estado_id'            => (int)($chat_raw['estado_id'] ?? 1),
    'fecha_venta'          => $chat_raw['fecha_venta'] ?? null,
];

// Si faltan datos del otro usuario, sacarlos del campo 'usuario' que sí viene en la API
if (empty($chat['comprador_nombre']) && empty($chat['vendedor_nombre'])) {
    $otroUsuario = $chat_raw['usuario'] ?? [];
    if (!empty($otroUsuario)) {
        // Determinar si el usuario actual es comprador o vendedor
        // Si comprador_id == user id → el otro es vendedor
        if ($chat['comprador_id'] === (int)$user['id']) {
            $chat['vendedor_nombre'] = $otroUsuario['nickname'] ?? '';
            $chat['vendedor_avatar'] = $otroUsuario['imagen'] ?? '';
        } else {
            $chat['comprador_nombre'] = $otroUsuario['nickname'] ?? '';
            $chat['comprador_avatar'] = $otroUsuario['imagen'] ?? '';
        }
    }
}

// Si la API no devolvió datos del producto, obtenerlos por producto_id
if ($chat['producto_id'] > 0 && (empty($chat['producto_nombre']) || $chat['producto_precio'] <= 0)) {
    $producto = apiGetProducto($chat['producto_id']);
    if ($producto && is_array($producto)) {
        $chat['producto_nombre']      = $producto['nombre'] ?? $producto['name'] ?? $chat['producto_nombre'];
        $chat['producto_precio']      = (float)($producto['precio'] ?? $producto['price'] ?? $chat['producto_precio']);
        $chat['producto_disponibles'] = (int)($producto['disponibles'] ?? $producto['stock'] ?? $chat['producto_disponibles']);
        if (empty($chat['producto_imagen']) && !empty($producto['imagenes'][0])) {
            $first = $producto['imagenes'][0];
            $chat['producto_imagen'] = is_array($first) ? ($first['imagen'] ?? $first['url'] ?? '') : (string)$first;
        } elseif (empty($chat['producto_imagen'])) {
            $chat['producto_imagen'] = $producto['imagen'] ?? $producto['image'] ?? '';
        }
        if (empty($chat['vendedor_nombre']) && !empty($producto['vendedor'])) {
            $v = $producto['vendedor'];
            $chat['vendedor_nombre'] = $v['nickname'] ?? $v['name'] ?? $v['nombre'] ?? $chat['vendedor_nombre'];
            $chat['vendedor_avatar'] = $v['imagen'] ?? $v['avatar'] ?? $chat['vendedor_avatar'];
        }
    }
}

$es_comprador = (int)$user['id'] === $chat['comprador_id'];
$es_vendedor  = (int)$user['id'] === $chat['vendedor_id'];
// Si aún no se puede determinar el rol, asumir por el campo usuario de la API
// (usuario = el OTRO, así que si viene → el current user es el opuesto)
if (!$es_comprador && !$es_vendedor && !empty($chat_raw['usuario'])) {
    $otroId = (int)($chat_raw['usuario']['id'] ?? 0);
    if ($otroId > 0 && $otroId !== (int)$user['id']) {
        // El current user es comprador si el otro es vendedor o viceversa
        // Usamos comprador_id si existe, si no asumimos que el current user es comprador
        if ($chat['comprador_id'] > 0) {
            $es_comprador = ($chat['comprador_id'] === (int)$user['id']);
            $es_vendedor  = !$es_comprador;
        } else {
            $es_comprador = true; // fallback
        }
    }
}

if (!$es_comprador && !$es_vendedor) {
    header('Location: mis_chats.php');
    exit;
}

apiMarcarVistoChat($chat_id);

$mensajes_raw    = apiGetMensajes($chat_id);
$mensajes_result = is_array($mensajes_raw) ? $mensajes_raw : [];

$compra_confirmada = (($chat['estado_id'] ?? 0) == 5);
$chat_bloqueado    = ($chat['estado_id'] == 8);

require_once __DIR__ . '/../api/config_cierre_automatico.php';
$dias_espera    = defined('DIAS_ESPERA_CIERRE') ? DIAS_ESPERA_CIERRE : 7;
$dias_restantes = null;
$fecha_cierre   = null;

if ($chat['fecha_venta'] && !$chat_bloqueado) {
    $fecha_venta_obj    = new DateTime($chat['fecha_venta']);
    $fecha_actual       = new DateTime();
    $dias_transcurridos = $fecha_actual->diff($fecha_venta_obj)->days;
    $dias_restantes     = $dias_espera - $dias_transcurridos;
    $fecha_cierre_obj   = clone $fecha_venta_obj;
    $fecha_cierre_obj->modify("+{$dias_espera} days");
    $fecha_cierre = $fecha_cierre_obj->format('d/m/Y');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getAbsoluteBaseUrl() ?>styles.css?v=<?= time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .chat-menu-dropdown { display: none; position: absolute; top: 100%; right: 0; margin-top: 0.25rem; background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); min-width: 180px; z-index: 100; overflow: hidden; border: 1px solid var(--color-border, #eee); }
        .chat-menu-dropdown.show { display: block; }
        [data-theme="dark"] .chat-menu-dropdown { background: var(--color-bg-secondary); border-color: var(--color-border); }
        .chat-menu-item { display: flex; align-items: center; gap: 0.5rem; width: 100%; padding: 0.65rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.95rem; text-decoration: none; color: inherit; text-align: left; transition: background 0.15s; }
        .chat-menu-item:hover { background: var(--color-bg-secondary, #f5f5f5); }
        .chat-menu-item i { font-size: 1.1rem; }
        .chat-menu-item-danger { color: #e74c3c !important; }
        .chat-input-bar { display: flex; align-items: flex-end; gap: 0.5rem; background: var(--color-bg, #fff); border: 1px solid var(--border-color, #e0e0e0); border-radius: 24px; padding: 0.4rem 0.4rem 0.4rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: border-color 0.2s, box-shadow 0.2s; }
        .chat-input-bar:focus-within { border-color: var(--color-primary); box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        [data-theme="dark"] .chat-input-bar { background: #2a2f32; border-color: #3b4043; }
        .chat-input-row { position: relative; flex: 1; min-width: 0; }
        .chat-input-wrap { flex: 1; display: flex; align-items: center; gap: 0.5rem; position: relative; min-height: 44px; min-width: 0; }
        .chat-input-wrap textarea { flex: 1; min-height: 40px; max-height: 120px; resize: none; padding: 0.6rem 0.75rem; border: none; background: transparent; font-size: 0.95rem; line-height: 1.4; }
        .chat-input-wrap textarea:focus { outline: none; }
        .btn-emoji { width: 44px; height: 44px; min-width: 44px; border: 2px solid var(--color-primary); border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #fff; color: var(--color-primary); transition: background 0.2s, color 0.2s; position: relative; }
        .btn-emoji i { font-size: 1.6rem; }
        .btn-emoji::after { content: ''; position: absolute; bottom: 5px; left: 50%; transform: translateX(-50%); width: 18px; height: 3px; background: var(--color-primary); border-radius: 2px; }
        .btn-emoji:hover { background: var(--color-primary); color: #fff; }
        .btn-emoji:hover::after { background: #fff; }
        .chat-send-btn { width: 44px; height: 44px; min-width: 44px; margin-left: auto; border: none; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: transform 0.2s, box-shadow 0.2s; }
        .chat-send-btn:hover { transform: scale(1.05); }
        .emoji-picker-whatsapp { display: none; position: absolute; bottom: 100%; left: 0; right: 0; margin-bottom: 0.5rem; background: var(--color-bg, #fff); border: 1px solid var(--border-color, #e0e0e0); border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); z-index: 50; overflow: hidden; max-height: 320px; flex-direction: column; }
        .emoji-picker-whatsapp.show { display: flex; }
        .emoji-picker-search { padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--border-color); }
        .emoji-picker-search input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; background: var(--color-bg-secondary); color: var(--color-text); }
        .emoji-picker-tabs { display: flex; gap: 0.25rem; padding: 0.4rem 0.5rem; border-bottom: 1px solid var(--border-color); background: var(--color-bg-secondary, #f5f5f5); }
        .emoji-tab { width: 36px; height: 36px; border: none; background: none; border-radius: 8px; cursor: pointer; font-size: 1.2rem; padding: 0; display: flex; align-items: center; justify-content: center; }
        .emoji-tab:hover { background: rgba(0,0,0,0.06); }
        .emoji-tab.active { background: var(--color-primary); color: #fff; }
        .emoji-picker-grid-wrap { flex: 1; overflow-y: auto; padding: 0.5rem; max-height: 220px; }
        .emoji-picker-category { display: none; }
        .emoji-picker-category.active { display: block; }
        .emoji-picker-category h4 { font-size: 0.75rem; color: var(--color-text-light); margin: 0.5rem 0 0.35rem; }
        .emoji-picker-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.2rem; }
        .emoji-btn-wa { width: 36px; height: 36px; border: none; background: none; border-radius: 8px; cursor: pointer; font-size: 1.5rem; padding: 0; display: flex; align-items: center; justify-content: center; }
        .emoji-btn-wa:hover { background: var(--color-bg-secondary, #e8e8e8); transform: scale(1.15); }
        @media (max-width: 768px) {
            .chat-input-bar { padding: 0.35rem 0.5rem 0.35rem 0.6rem; border-radius: 22px; }
            .chat-input-wrap textarea { min-height: 44px; font-size: 16px; }
            .emoji-picker-whatsapp { width: 100%; left: 0; right: 0; max-height: min(280px, 50vh); }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="chat-container">
                <?php
                $chat_producto_img_url = '';
                if (!empty($chat['producto_imagen'])) {
                    $chat_producto_img_url = (strpos($chat['producto_imagen'], 'http') === 0)
                        ? $chat['producto_imagen']
                        : (defined('LARAVEL_STORAGE_URL')
                            ? rtrim(LARAVEL_STORAGE_URL, '/') . '/' . ltrim(str_replace('uploads/productos/', 'productos/', $chat['producto_imagen']), '/')
                            : getAbsoluteBaseUrl() . 'uploads/productos/' . $chat['producto_imagen']);
                } else {
                    $chat_producto_img_url = getAbsoluteBaseUrl() . 'assets/images/default-product.jpg';
                }
                ?>
                <div class="chat-header" style="position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="<?= getAvatarUrl($es_comprador ? $chat['vendedor_avatar'] : $chat['comprador_avatar']) ?>"
                                 alt="<?= htmlspecialchars($es_comprador ? $chat['vendedor_nombre'] : $chat['comprador_nombre']) ?>"
                                 style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-primary);">
                            <?php if ($chat_producto_img_url): ?>
                            <a href="<?= getAbsoluteBaseUrl() ?>productos/producto.php?id=<?= (int)$chat['producto_id'] ?>" style="flex-shrink: 0;" title="Ver producto">
                                <img src="<?= htmlspecialchars($chat_producto_img_url) ?>"
                                     alt="<?= htmlspecialchars($chat['producto_nombre'] ?: 'Producto') ?>"
                                     style="width: 56px; height: 56px; border-radius: 10px; object-fit: cover; border: 2px solid rgba(255,255,255,0.5);">
                            </a>
                            <?php endif; ?>
                            <div>
                                <h2><?= htmlspecialchars($chat['producto_nombre'] ?: 'Producto') ?> —
                                    <?= htmlspecialchars($es_comprador ? $chat['vendedor_nombre'] : $chat['comprador_nombre']) ?></h2>
                                <p>Precio: <?= formatPrice($chat['producto_precio']) ?></p>
                                <p>
                                    <?php if ($es_comprador): ?>
                                        Vendedor: <?= htmlspecialchars($chat['vendedor_nombre']) ?>
                                    <?php else: ?>
                                        Comprador: <?= htmlspecialchars($chat['comprador_nombre']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <?php if ($dias_restantes !== null && $dias_restantes >= 0 && !$chat_bloqueado): ?>
                                <div style="background: <?= $dias_restantes <= 2 ? 'linear-gradient(135deg,#e74c3c,#c0392b)' : ($dias_restantes <= 5 ? 'linear-gradient(135deg,#FFC107,#FFB300)' : 'linear-gradient(135deg,#28a745,#20c997)') ?>; color:white; padding:0.6rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:600; display:flex; flex-direction:column; align-items:center; gap:0.25rem; min-width:120px;">
                                    <div style="display:flex;align-items:center;gap:0.5rem;">
                                        <i class="ri-time-line"></i>
                                        <span style="font-size:1.2rem;font-weight:700;"><?= $dias_restantes ?></span>
                                    </div>
                                    <span style="font-size:0.75rem;opacity:0.9;"><?= $dias_restantes == 1 ? 'día restante' : 'días restantes' ?></span>
                                    <span style="font-size:0.7rem;opacity:0.8;">Cierre: <?= $fecha_cierre ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!$chat_bloqueado): ?>
                                <?php if ($es_vendedor && ($chat['estado_id'] ?? 0) == 1 && !$compra_confirmada): ?>
                                <button type="button" id="btnConfirmarCompra" onclick="mostrarFormularioConfirmacion()"
                                        style="background:linear-gradient(135deg,#28a745,#20c997);color:white;padding:0.6rem 1rem;border:none;border-radius:8px;font-size:0.9rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:0.5rem;">
                                    <i class="ri-check-double-line"></i> Iniciar venta
                                </button>
                                <?php endif; ?>

                                <?php if ($es_comprador && ($chat['estado_id'] ?? 0) == 6 && !$compra_confirmada): ?>
                                <button type="button" onclick="responderConfirmacion('confirmar', 0)"
                                        style="background:#28a745;color:white;padding:0.6rem 1rem;border:none;border-radius:8px;font-weight:600;cursor:pointer;">
                                    <i class="ri-check-line"></i> Aceptar compra
                                </button>
                                <button type="button" onclick="responderConfirmacion('rechazar', 0)"
                                        style="background:#dc3545;color:white;padding:0.6rem 1rem;border:none;border-radius:8px;font-weight:600;cursor:pointer;">
                                    <i class="ri-close-line"></i> Rechazar compra
                                </button>
                                <?php endif; ?>

                                <?php if ($es_comprador && $compra_confirmada): ?>
                                <button type="button" id="btnDevolver" onclick="mostrarFormularioDevolucion()"
                                        style="background:linear-gradient(135deg,#FFC107,#FFB300);color:white;padding:0.6rem 1rem;border:none;border-radius:8px;font-size:0.9rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:0.5rem;">
                                    <i class="ri-arrow-go-back-line"></i> Devolver
                                </button>
                                <?php endif; ?>

                                <?php if ($es_vendedor && ($chat['estado_id'] ?? 0) == 7): ?>
                                <button type="button" onclick="responderDevolucionHeader('aceptar')"
                                        style="background:#28a745;color:white;padding:0.6rem 1rem;border:none;border-radius:8px;font-weight:600;cursor:pointer;">
                                    <i class="ri-check-line"></i> Aceptar devolución
                                </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <div style="background:#e74c3c;color:white;padding:0.6rem 1rem;border-radius:8px;font-size:0.9rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;">
                                    <i class="ri-lock-line"></i> Chat Cerrado
                                </div>
                            <?php endif; ?>

                            <?php $otro_usuario_id = $es_comprador ? $chat['vendedor_id'] : $chat['comprador_id']; ?>
                            <div class="chat-header-menu-wrap" style="position: relative;">
                                <button type="button"
                                        onclick="document.getElementById('chatMenuDropdown').classList.toggle('show')"
                                        class="btn-small"
                                        style="background:var(--color-background);border:1px solid var(--color-border);color:var(--color-text);padding:0.5rem 0.75rem;"
                                        title="Más opciones">
                                    <i class="ri-more-2-fill" style="font-size:1.2rem;"></i>
                                </button>
                                <div id="chatMenuDropdown" class="chat-menu-dropdown">
                                    <button type="button" class="chat-menu-item chat-menu-item-danger"
                                            onclick="document.getElementById('chatMenuDropdown').classList.remove('show'); mostrarFormularioDenuncia();">
                                        <i class="ri-flag-line"></i> Reportar chat
                                    </button>
                                    <button type="button" class="chat-menu-item chat-menu-item-danger"
                                            onclick="document.getElementById('chatMenuDropdown').classList.remove('show'); if(confirm('¿Eliminar esta conversación?')) eliminarChat(<?= $chat_id ?>);">
                                        <i class="ri-delete-bin-line"></i> Eliminar chat
                                    </button>
                                    <a href="<?= getAbsoluteBaseUrl() ?>perfil/vendedor.php?id=<?= (int)$otro_usuario_id ?>"
                                       class="chat-menu-item" style="color:var(--color-primary);">
                                        <i class="ri-user-line"></i> Ver perfil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($dias_restantes !== null && $dias_restantes <= 3 && $dias_restantes >= 0 && !$chat_bloqueado): ?>
                <div style="background:<?= $dias_restantes <= 1 ? 'linear-gradient(135deg,#e74c3c,#c0392b)' : 'linear-gradient(135deg,#FFC107,#FFB300)' ?>;color:white;padding:1rem;border-radius:8px;margin:1rem 0;display:flex;align-items:center;gap:1rem;">
                    <i class="ri-alarm-warning-line" style="font-size:2rem;"></i>
                    <div>
                        <h4 style="margin:0 0 0.25rem 0;">
                            <?= $dias_restantes == 0 ? '⚠️ ¡Último día!' : ($dias_restantes == 1 ? '⚠️ ¡Queda 1 día!' : "⚠️ Quedan {$dias_restantes} días") ?>
                        </h4>
                        <p style="margin:0;font-size:0.9rem;opacity:0.95;">
                            Este chat se cerrará el <strong><?= $fecha_cierre ?></strong>.
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="chat-messages" id="chatMessages">
                    <?php
                    $last_message_id = 0;
                    foreach ($mensajes_result as $mensaje):
                        $mid          = (int)($mensaje['id'] ?? 0);
                        $msg_text     = $mensaje['mensaje'] ?? $mensaje['content'] ?? $mensaje['text'] ?? '';
                        $es_comp_msg  = (int)($mensaje['es_comprador'] ?? $mensaje['from_buyer'] ?? 0);
                        $last_message_id = max($last_message_id, $mid);
                        $message_class = ($es_comp_msg == 1 && $es_comprador) || ($es_comp_msg == 0 && $es_vendedor)
                            ? 'message-sent' : 'message-received';

                        $es_solicitud_devolucion    = strpos($msg_text, 'SOLICITUD DE DEVOLUCIÓN') !== false;
                        $es_solicitud_confirmacion  = strpos($msg_text, 'SOLICITUD DE CONFIRMACIÓN') !== false;

                        $tiene_respuesta = false;
                        if ($es_solicitud_devolucion || $es_solicitud_confirmacion) {
                            foreach ($mensajes_result as $m2) {
                                $id2 = (int)($m2['id'] ?? 0);
                                if ($id2 <= $mid) continue;
                                $t = $m2['mensaje'] ?? $m2['content'] ?? '';
                                if (strpos($t, '✅') !== false || strpos($t, '❌') !== false) {
                                    $tiene_respuesta = true;
                                    break;
                                }
                            }
                        }
                        $mostrar_botones_confirmacion = $es_solicitud_confirmacion && !$tiene_respuesta;
                        $mostrar_botones_devolucion   = $es_solicitud_devolucion   && !$tiene_respuesta;
                    ?>
                        <div id="message-<?= $mid ?>" class="message <?= $message_class ?>">
                            <p><?= nl2br(htmlspecialchars($msg_text)) ?></p>
                            <span class="message-time"><?= formato_tiempo_relativo($mensaje['fecha_registro'] ?? $mensaje['created_at'] ?? '') ?></span>

                            <?php if ($mostrar_botones_devolucion): ?>
                                <div id="buttons-<?= $mid ?>" style="display:flex;gap:0.5rem;margin-top:1rem;">
                                    <button onclick="responderDevolucion('aceptar', <?= $mid ?>)"
                                            style="flex:1;padding:0.75rem;background:#28a745;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                                        <i class="ri-check-line"></i> Aceptar
                                    </button>
                                    <button onclick="responderDevolucion('rechazar', <?= $mid ?>)"
                                            style="flex:1;padding:0.75rem;background:#dc3545;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                                        <i class="ri-close-line"></i> Rechazar
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if ($mostrar_botones_confirmacion): ?>
                                <div id="buttons-<?= $mid ?>" style="display:flex;gap:0.5rem;margin-top:1rem;">
                                    <button onclick="responderConfirmacion('confirmar', <?= $mid ?>)"
                                            style="flex:1;padding:0.75rem;background:#28a745;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                                        <i class="ri-check-line"></i> Confirmar
                                    </button>
                                    <button onclick="responderConfirmacion('rechazar', <?= $mid ?>)"
                                            style="flex:1;padding:0.75rem;background:#dc3545;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                                        <i class="ri-close-line"></i> Rechazar
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!$chat_bloqueado): ?>
                <div class="chat-input">
                    <form class="message-form" id="messageForm">
                        <div class="chat-input-bar">
                            <div class="chat-input-row">
                                <div class="chat-input-wrap">
                                    <button type="button" class="btn-emoji" id="btnEmoji" title="Emojis">
                                        <i class="ri-emotion-smile-line"></i>
                                    </button>
                                    <textarea name="mensaje" id="messageInput" placeholder="Escribe un mensaje..." required rows="1"></textarea>
                                </div>
                                <div class="emoji-picker-whatsapp" id="emojiPicker">
                                    <div class="emoji-picker-search">
                                        <input type="text" id="emojiSearch" placeholder="Buscar emoji" autocomplete="off">
                                    </div>
                                    <div class="emoji-picker-tabs">
                                        <button type="button" class="emoji-tab active" data-tab="caras">😀</button>
                                        <button type="button" class="emoji-tab" data-tab="gestos">👋</button>
                                        <button type="button" class="emoji-tab" data-tab="objetos">💬</button>
                                        <button type="button" class="emoji-tab" data-tab="simbolos">❤️</button>
                                    </div>
                                    <div class="emoji-picker-grid-wrap">
                                        <div class="emoji-picker-category active" id="cat-caras">
                                            <h4>Emoticonos y personas</h4>
                                            <div class="emoji-picker-grid" data-category="caras"><?php
                                            $caras = ['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','😐','😑','😶','😏','😒','🙄','😬','😮','😯','😲','😳','🥺','😢','😭','😤','😡','🤬','😈','💀','👻','🤡'];
                                            foreach ($caras as $e) echo '<button type="button" class="emoji-btn-wa" data-emoji="'.htmlspecialchars($e).'">'.$e.'</button>';
                                            ?></div>
                                        </div>
                                        <div class="emoji-picker-category" id="cat-gestos">
                                            <h4>Gestos y manos</h4>
                                            <div class="emoji-picker-grid" data-category="gestos"><?php
                                            $gestos = ['👋','🤚','🖐️','✋','🖖','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💪','🦾','🦿'];
                                            foreach ($gestos as $e) echo '<button type="button" class="emoji-btn-wa" data-emoji="'.htmlspecialchars($e).'">'.$e.'</button>';
                                            ?></div>
                                        </div>
                                        <div class="emoji-picker-category" id="cat-objetos">
                                            <h4>Objetos</h4>
                                            <div class="emoji-picker-grid" data-category="objetos"><?php
                                            $objetos = ['💬','💭','📦','📨','📩','📧','✉️','📮','🎉','🎊','🎁','🏆','⭐','🌟','✨','🔥','💯','✅','❌','❓','❗','💡','📌','🔔','🔒','🔓'];
                                            foreach ($objetos as $e) echo '<button type="button" class="emoji-btn-wa" data-emoji="'.htmlspecialchars($e).'">'.$e.'</button>';
                                            ?></div>
                                        </div>
                                        <div class="emoji-picker-category" id="cat-simbolos">
                                            <h4>Símbolos</h4>
                                            <div class="emoji-picker-grid" data-category="simbolos"><?php
                                            $simbolos = ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','👍','👎','✅','❌','❓','❗','💯'];
                                            foreach ($simbolos as $e) echo '<button type="button" class="emoji-btn-wa" data-emoji="'.htmlspecialchars($e).'">'.$e.'</button>';
                                            ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="chat-send-btn" title="Enviar">
                                <i class="ri-send-plane-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <script>
                (function() {
                    var btn = document.getElementById('btnEmoji');
                    var picker = document.getElementById('emojiPicker');
                    var input = document.getElementById('messageInput');
                    var searchInput = document.getElementById('emojiSearch');
                    if (!btn || !picker || !input) return;

                    function insertEmoji(emoji) {
                        var start = input.selectionStart, end = input.selectionEnd;
                        input.value = input.value.slice(0, start) + emoji + input.value.slice(end);
                        input.selectionStart = input.selectionEnd = start + emoji.length;
                        input.focus();
                    }

                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        picker.classList.toggle('show');
                    });

                    picker.querySelectorAll('.emoji-tab').forEach(function(tab) {
                        tab.addEventListener('click', function() {
                            picker.querySelectorAll('.emoji-tab').forEach(function(t) { t.classList.remove('active'); });
                            picker.querySelectorAll('.emoji-picker-category').forEach(function(c) { c.classList.remove('active'); });
                            this.classList.add('active');
                            var cat = document.getElementById('cat-' + this.getAttribute('data-tab'));
                            if (cat) cat.classList.add('active');
                        });
                    });

                    picker.querySelectorAll('.emoji-btn-wa').forEach(function(b) {
                        b.addEventListener('click', function() { insertEmoji(this.getAttribute('data-emoji') || ''); });
                    });

                    document.addEventListener('click', function(e) {
                        if (picker.classList.contains('show') && !picker.contains(e.target) && !btn.contains(e.target)) {
                            picker.classList.remove('show');
                        }
                    });
                })();
                </script>

                <script>
                    window.lastMessageId   = <?= $last_message_id ?>;
                    window.chatId          = <?= $chat_id ?>;
                    window.chatBloqueado   = <?= $chat_bloqueado ? 'true' : 'false' ?>;
                    window.productoPrecio  = <?= $chat['producto_precio'] ?>;
                    window.productoDisponibles = <?= $chat['producto_disponibles'] ?>;
                    window.productoPrecioFormateado = '<?= formatPrice($chat['producto_precio']) ?>';
                    window.confirmacionesChat = window.confirmacionesChat || {};
                </script>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        window.BASE_URL = '<?= getAbsoluteBaseUrl() ?>';
    </script>
    <script>
    document.addEventListener('click', function(e) {
        var dd = document.getElementById('chatMenuDropdown');
        var btn = document.querySelector('.chat-header-menu-wrap button');
        if (dd && dd.classList.contains('show') && btn && !dd.contains(e.target) && !btn.contains(e.target)) {
            dd.classList.remove('show');
        }
    });

    function mostrarFormularioConfirmacion() {
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelIniciarCompraventaUrl === 'function';
        if (useLaravel) {
            if (confirm('¿Iniciar proceso de compraventa? Precio: ' + (window.productoPrecioFormateado || 'N/A'))) {
                solicitarConfirmacion(null);
            }
            return;
        }
        const detalles = prompt('Ingresa los detalles de la venta:');
        if (!detalles || detalles.trim() === '') return;
        if (confirm('¿Enviar solicitud?')) solicitarConfirmacion(detalles.trim());
    }

    function solicitarConfirmacion(detalles) {
        const btn = event && event.target ? event.target : null;
        if (btn) btn.disabled = true;
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelIniciarCompraventaUrl === 'function';
        if (useLaravel) {
            fetch(window.getLaravelIniciarCompraventaUrl(window.chatId), {
                method: 'PATCH',
                headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' },
                body: JSON.stringify({ cantidad: window.productoDisponibles || 1, precio: window.productoPrecio || 0 })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    fetch(window.getLaravelSendMessageUrl(window.chatId), {
                        method: 'POST',
                        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mensaje: '💰 He iniciado el proceso de venta. Por favor acepta o rechaza la compra.' })
                    }).then(() => { alert(data.message || 'Proceso iniciado'); window.location.reload(); })
                      .catch(() => { alert(data.message || 'Proceso iniciado'); window.location.reload(); });
                } else {
                    alert('Error: ' + (data.message || 'No se pudo iniciar'));
                    if (btn) btn.disabled = false;
                }
            })
            .catch(err => { console.error(err); alert('Error al enviar'); if (btn) btn.disabled = false; });
            return;
        }
    }

    function responderConfirmacion(accion, mensajeId) {
        const key = `confirmacion_${mensajeId}`;
        if (window.confirmacionesChat[key]) return;
        if (!confirm(accion === 'confirmar' ? '¿Aceptar esta compra?' : '¿Rechazar esta solicitud?')) return;
        window.confirmacionesChat[key] = true;
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelTerminarCompraventaUrl === 'function';
        if (useLaravel) {
            fetch(window.getLaravelTerminarCompraventaUrl(window.chatId), {
                method: 'PATCH',
                headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' },
                body: JSON.stringify({ confirmacion: accion === 'confirmar', calificacion: null, comentario: null })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (ok && (data.success || data.status === 'success')) {
                    const msgTexto = accion === 'confirmar' ? '✅ He aceptado la compra.' : '❌ He rechazado la solicitud.';
                    fetch(window.getLaravelSendMessageUrl(window.chatId), {
                        method: 'POST',
                        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mensaje: msgTexto })
                    }).then(() => { alert(data.message || (accion === 'confirmar' ? 'Venta concretada' : 'Proceso cancelado')); window.location.reload(); })
                      .catch(() => { window.location.reload(); });
                } else {
                    alert('Error: ' + (data.message || 'No se pudo procesar'));
                    delete window.confirmacionesChat[key];
                }
            })
            .catch(err => { console.error(err); alert('Error'); delete window.confirmacionesChat[key]; });
        }
    }

    // function mostrarFormularioDevolucion() {
    //     const motivo = prompt('¿Por qué deseas devolver este producto?');
    //     if (!motivo || motivo.trim() === '') return;
    //     if (confirm('¿Solicitar la devolución?')) solicitarDevolucion(motivo.trim());
    // }

    function solicitarDevolucion(motivo) {
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelIniciarDevolucionUrl === 'function';
        if (useLaravel) {
            fetch(window.getLaravelIniciarDevolucionUrl(window.chatId), {
                method: 'PATCH',
                headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    fetch(window.getLaravelSendMessageUrl(window.chatId), {
                        method: 'POST',
                        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mensaje: '⚠️ He solicitado la devolución.\n\nMotivo: ' + motivo })
                    }).then(() => { alert(data.message || 'Devolución iniciada'); window.location.reload(); })
                      .catch(() => window.location.reload());
                } else {
                    alert('Error: ' + (data.message || 'No se pudo iniciar'));
                }
            })
            .catch(err => { console.error(err); alert('Error al enviar'); });
        }
    }

    function responderDevolucionHeader(accion) {
        if (accion !== 'aceptar') return;
        if (!confirm('¿Aceptar la devolución?')) return;
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelTerminarDevolucionUrl === 'function';
        if (!useLaravel) return;
        fetch(window.getLaravelTerminarDevolucionUrl(window.chatId), {
            method: 'PATCH',
            headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success || data.status === 'success') {
                fetch(window.getLaravelSendMessageUrl(window.chatId), {
                    method: 'POST',
                    headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mensaje: '✅ He aceptado la devolución.' })
                }).then(() => { alert(data.message || 'Devolución registrada'); window.location.reload(); })
                  .catch(() => window.location.reload());
            } else {
                alert('Error: ' + (data.message || 'No se pudo procesar'));
            }
        })
        .catch(err => { console.error(err); alert('Error'); });
    }

    function responderDevolucion(accion, mensajeId) {
        const key = `devolucion_${mensajeId}`;
        if (window.confirmacionesChat[key]) return;
        if (!confirm(accion === 'aceptar' ? '¿Aceptar la devolución?' : '¿Rechazar la devolución?')) return;
        window.confirmacionesChat[key] = true;
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelTerminarDevolucionUrl === 'function';
        if (useLaravel) {
            if (accion === 'rechazar') {
                alert('La API no soporta rechazar devoluciones.');
                delete window.confirmacionesChat[key];
                return;
            }
            fetch(window.getLaravelTerminarDevolucionUrl(window.chatId), {
                method: 'PATCH',
                headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    fetch(window.getLaravelSendMessageUrl(window.chatId), {
                        method: 'POST',
                        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mensaje: '✅ He aceptado la devolución.' })
                    }).then(() => { alert(data.message || 'Devolución registrada'); window.location.reload(); })
                      .catch(() => window.location.reload());
                } else {
                    alert('Error: ' + (data.message || 'No se pudo procesar'));
                    delete window.confirmacionesChat[key];
                }
            })
            .catch(err => { console.error(err); alert('Error'); delete window.confirmacionesChat[key]; });
        }
    }

    // function mostrarFormularioDenuncia() {
    //     const motivo = prompt('¿Por qué deseas denunciar a este usuario?\n\nEscribe el motivo:');
    //     if (!motivo || motivo.trim() === '') return;
    //     if (confirm('¿Confirmas enviar esta denuncia?\n\nMotivo: ' + motivo)) {
    //         enviarDenuncia(motivo.trim());
    //     }
    // }

    function enviarDenuncia(motivo) {
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('motivo', motivo);
        fetch(getApiUrl('api/denunciar_usuario.php'), {
            method: 'POST',
            headers: (window.getApiHeaders ? window.getApiHeaders() : {}),
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) alert('✅ Denuncia enviada correctamente.');
            else alert('❌ Error: ' + (data.message || 'No se pudo enviar'));
        })
        .catch(err => { console.error(err); alert('❌ Error al enviar.'); });
    }
    </script>

    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getAbsoluteBaseUrl() ?>script.js"></script>
</body>
</html>