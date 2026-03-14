<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// Usuario autenticado

$user = getCurrentUser();
$chat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($chat_id <= 0) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Obtener información del chat
$stmt = $conn->prepare("SELECT c.*, 
       p.nombre AS producto_nombre, 
       p.precio AS producto_precio, 
       p.disponibles AS producto_disponibles,
       p.id AS producto_id,
       u_comprador.nickname AS comprador_nombre, 
       u_comprador.id AS comprador_id,
       u_comprador.imagen AS comprador_avatar,
       u_vendedor.nickname AS vendedor_nombre, 
       u_vendedor.id AS vendedor_id,
       u_vendedor.imagen AS vendedor_avatar
FROM chats c
INNER JOIN productos p ON c.producto_id = p.id
INNER JOIN usuarios u_comprador ON c.comprador_id = u_comprador.id
INNER JOIN usuarios u_vendedor ON p.vendedor_id = u_vendedor.id
WHERE c.id = ?");

if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}
    

$stmt->bind_param("i", $chat_id);
$stmt->execute();
$result = $stmt->get_result();
$chat = $result->fetch_assoc();
$stmt->close();

if (!$chat) {
    header('Location: index.php');
    exit;
}

// Verificar que el usuario es parte del chat
$es_comprador = $user['id'] == $chat['comprador_id'];
$es_vendedor = $user['id'] == $chat['vendedor_id'];

if (!$es_comprador && !$es_vendedor) {
    header('Location: index.php');
    exit;
}
if ($es_comprador) {
    $stmt = $conn->prepare("UPDATE chats SET visto_comprador = 1 WHERE id = ?");
} else {
    $stmt = $conn->prepare("UPDATE chats SET visto_vendedor = 1 WHERE id = ?");
}
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$stmt->close();

// Obtener mensajes (los nuevos se cargan vía AJAX)
$stmt = $conn->prepare("SELECT * FROM mensajes WHERE chat_id = ? ORDER BY fecha_registro ASC");
$stmt->bind_param("i", $chat_id);
$stmt->execute();
$mensajes_result = $stmt->get_result();
$stmt->close();

// Verificar si hay compra confirmada: estado_id 5 = vendido (API no crea mensajes en chat)
$compra_confirmada = (($chat['estado_id'] ?? 0) == 5);

// Verificar si el chat está bloqueado (devuelto)
$chat_bloqueado = ($chat['estado_id'] == 8); // estado_id = 8 es "devuelto"

// Calcular días restantes para cierre automático
require_once '../api/config_cierre_automatico.php';
$dias_espera = defined('DIAS_ESPERA_CIERRE') ? DIAS_ESPERA_CIERRE : 7;
$dias_restantes = null;
$fecha_cierre = null;

if ($chat['fecha_venta'] && !$chat_bloqueado) {
    $fecha_venta_obj = new DateTime($chat['fecha_venta']);
    $fecha_actual = new DateTime();
    $dias_transcurridos = $fecha_actual->diff($fecha_venta_obj)->days;
    $dias_restantes = $dias_espera - $dias_transcurridos;
    
    // Calcular fecha de cierre
    $fecha_cierre_obj = clone $fecha_venta_obj;
    $fecha_cierre_obj->modify("+{$dias_espera} days");
    $fecha_cierre = $fecha_cierre_obj->format('d/m/Y');
}

// NO cerrar la conexión aquí, la necesitamos para verificar respuestas
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
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
        [data-theme="dark"] .chat-input-bar:focus-within { border-color: var(--color-primary); }
        .chat-input-row { position: relative; flex: 1; min-width: 0; }
        .chat-input-wrap { flex: 1; display: flex; align-items: center; gap: 0.5rem; position: relative; min-height: 44px; min-width: 0; }
        .chat-input-wrap textarea { flex: 1; min-height: 40px; max-height: 120px; resize: none; padding: 0.6rem 0.75rem; border: none; background: transparent; font-size: 0.95rem; line-height: 1.4; }
        .chat-input-wrap textarea::placeholder { color: var(--color-text-light, #888); }
        .chat-input-wrap textarea:focus { outline: none; }
        /* Botón emoji: bien visible (carita clara + barra verde) */
        .btn-emoji { width: 44px; height: 44px; min-width: 44px; border: 2px solid var(--color-primary); border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #fff; color: var(--color-primary); transition: background 0.2s, color 0.2s, transform 0.2s; position: relative; }
        .btn-emoji i { font-size: 1.6rem; }
        .btn-emoji::after { content: ''; position: absolute; bottom: 5px; left: 50%; transform: translateX(-50%); width: 18px; height: 3px; background: var(--color-primary); border-radius: 2px; }
        .btn-emoji:hover { background: var(--color-primary); color: #fff; }
        .btn-emoji:hover::after { background: #fff; }
        .btn-emoji:active { transform: scale(0.95); }
        [data-theme="dark"] .btn-emoji { background: #2a2f32; border-color: #00a884; color: #00a884; }
        [data-theme="dark"] .btn-emoji::after { background: #00a884; }
        [data-theme="dark"] .btn-emoji:hover { background: #00a884; color: #fff; }
        [data-theme="dark"] .btn-emoji:hover::after { background: #2a2f32; }
        /* Botón enviar fijo a la derecha */
        .chat-send-btn { width: 44px; height: 44px; min-width: 44px; margin-left: auto; border: none; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: transform 0.2s, box-shadow 0.2s; }
        .chat-send-btn:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .chat-send-btn:active { transform: scale(0.98); }
        .chat-send-btn i { font-size: 1.2rem; }
        /* Panel emojis tipo WhatsApp */
        .emoji-picker-whatsapp { display: none; position: absolute; bottom: 100%; left: 0; right: 0; margin-bottom: 0.5rem; background: var(--color-bg, #fff); border: 1px solid var(--border-color, #e0e0e0); border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); z-index: 50; overflow: hidden; max-height: 320px; flex-direction: column; }
        .emoji-picker-whatsapp.show { display: flex; }
        [data-theme="dark"] .emoji-picker-whatsapp { background: #2a2f32; border-color: #3b4043; }
        .emoji-picker-search { padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--border-color); }
        .emoji-picker-search input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; background: var(--color-bg-secondary); color: var(--color-text); }
        .emoji-picker-tabs { display: flex; gap: 0.25rem; padding: 0.4rem 0.5rem; border-bottom: 1px solid var(--border-color); background: var(--color-bg-secondary, #f5f5f5); }
        [data-theme="dark"] .emoji-picker-tabs { background: #1f2326; }
        .emoji-tab { width: 36px; height: 36px; border: none; background: none; border-radius: 8px; cursor: pointer; font-size: 1.2rem; padding: 0; display: flex; align-items: center; justify-content: center; }
        .emoji-tab:hover { background: rgba(0,0,0,0.06); }
        .emoji-tab.active { background: var(--color-primary); color: #fff; }
        .emoji-picker-grid-wrap { flex: 1; overflow-y: auto; padding: 0.5rem; max-height: 220px; }
        .emoji-picker-category { display: none; }
        .emoji-picker-category.active { display: block; }
        .emoji-picker-category h4 { font-size: 0.75rem; color: var(--color-text-light); margin: 0.5rem 0 0.35rem; padding: 0 0.25rem; }
        .emoji-picker-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.2rem; }
        .emoji-btn-wa { width: 36px; height: 36px; border: none; background: none; border-radius: 8px; cursor: pointer; font-size: 1.5rem; padding: 0; display: flex; align-items: center; justify-content: center; }
        .emoji-btn-wa:hover { background: var(--color-bg-secondary, #e8e8e8); transform: scale(1.15); }
        [data-theme="dark"] .emoji-btn-wa:hover { background: #3b4043; }
        @media (max-width: 768px) {
            .chat-input-bar { padding: 0.35rem 0.5rem 0.35rem 0.6rem; border-radius: 22px; gap: 0.35rem; max-width: 100%; }
            .chat-input-wrap textarea { min-height: 44px; padding-left: 0; font-size: 16px; }
            .btn-emoji i { font-size: 1.5rem; }
            .emoji-picker-whatsapp { width: 100%; left: 0; right: 0; max-height: min(280px, 50vh); }
        }
        @media (max-width: 380px) {
            .chat-input-bar { padding: 0.3rem 0.4rem 0.3rem 0.5rem; gap: 0.25rem; }
            .btn-emoji, .chat-send-btn { width: 40px; height: 40px; min-width: 40px; }
            .chat-input-wrap textarea { font-size: 15px; }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="chat-container">
                <div class="chat-header" style="position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="<?= getAvatarUrl($es_comprador ? $chat['vendedor_avatar'] : $chat['comprador_avatar']) ?>" 
                                 alt="<?= htmlspecialchars($es_comprador ? $chat['vendedor_nombre'] : $chat['comprador_nombre']) ?>"
                                 style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-primary);">
                            <div>
                                <h2>
                                <?php echo htmlspecialchars($chat['producto_nombre']); ?> — 
                                 <?php echo htmlspecialchars($es_comprador ? $chat['vendedor_nombre'] : $chat['comprador_nombre']); ?>
                                </h2>
                                <p>Precio: <?php echo formatPrice($chat['producto_precio']); ?></p>
                                <p>
                                    <?php if ($es_comprador): ?>
                                        Vendedor: <?php echo htmlspecialchars($chat['vendedor_nombre']); ?>
                                    <?php else: ?>
                                        Comprador: <?php echo htmlspecialchars($chat['comprador_nombre']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <?php if ($dias_restantes !== null && $dias_restantes >= 0 && !$chat_bloqueado): ?>
                                <!-- Contador de días restantes -->
                                <div style="background: <?= $dias_restantes <= 2 ? 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)' : ($dias_restantes <= 5 ? 'linear-gradient(135deg, #FFC107 0%, #FFB300 100%)' : 'linear-gradient(135deg, #28a745 0%, #20c997 100%)') ?>; color: white; padding: 0.6rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 0.25rem; min-width: 120px;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="ri-time-line"></i>
                                        <span style="font-size: 1.2rem; font-weight: 700;"><?= $dias_restantes ?></span>
                                    </div>
                                    <span style="font-size: 0.75rem; opacity: 0.9;">
                                        <?= $dias_restantes == 1 ? 'día restante' : 'días restantes' ?>
                                    </span>
                                    <span style="font-size: 0.7rem; opacity: 0.8;">
                                        Cierre: <?= $fecha_cierre ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!$chat_bloqueado): ?>
                                <!-- Botón Iniciar venta: solo VENDEDOR (API: vendedor inicia, comprador responde) -->
                                <?php if ($es_vendedor && ($chat['estado_id'] ?? 0) == 1 && !$compra_confirmada): ?>
                                <button type="button" 
                                        id="btnConfirmarCompra"
                                        onclick="mostrarFormularioConfirmacion()" 
                                        class="btn-confirmar-compra"
                                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 0.6rem 1rem; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; align-items: center; gap: 0.5rem; display: flex;">
                                    <i class="ri-check-double-line"></i>
                                    <span>Iniciar venta</span>
                                </button>
                                <?php endif; ?>
                                
                                <!-- Botones Aceptar/Rechazar: solo COMPRADOR cuando vendedor inició (estado 6 = esperando) -->
                                <?php if ($es_comprador && ($chat['estado_id'] ?? 0) == 6 && !$compra_confirmada): ?>
                                <button type="button" onclick="responderConfirmacion('confirmar', 0)" class="btn-small" style="background: #28a745; color: white; padding: 0.6rem 1rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                    <i class="ri-check-line"></i> Aceptar compra
                                </button>
                                <button type="button" onclick="responderConfirmacion('rechazar', 0)" class="btn-small" style="background: #dc3545; color: white; padding: 0.6rem 1rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                    <i class="ri-close-line"></i> Rechazar compra
                                </button>
                                <?php endif; ?>
                                
                                <!-- Botón Devolver: solo COMPRADOR cuando hay compra confirmada (estado 5 = vendido) -->
                                <?php if ($es_comprador && $compra_confirmada): ?>
                                <button type="button" 
                                        id="btnDevolver"
                                        onclick="mostrarFormularioDevolucion()" 
                                        class="btn-devolucion-header"
                                        style="background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: white; padding: 0.6rem 1rem; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; align-items: center; gap: 0.5rem; display: flex;">
                                    <i class="ri-arrow-go-back-line"></i>
                                    <span>Devolver</span>
                                </button>
                                <?php endif; ?>
                                
                                <!-- Botón Aceptar devolución: solo VENDEDOR cuando comprador inició devolución (estado 7 = devolviendo) -->
                                <?php if ($es_vendedor && ($chat['estado_id'] ?? 0) == 7): ?>
                                <button type="button" onclick="responderDevolucionHeader('aceptar')" class="btn-small" style="background: #28a745; color: white; padding: 0.6rem 1rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                    <i class="ri-check-line"></i> Aceptar devolución
                                </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Indicador de chat cerrado -->
                                <div style="background: #e74c3c; color: white; padding: 0.6rem 1rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="ri-lock-line"></i>
                                    <span>Chat Cerrado</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php $otro_usuario_id = $es_comprador ? $chat['vendedor_id'] : $chat['comprador_id']; ?>
                            <div class="chat-header-menu-wrap" style="position: relative;">
                                <button type="button" 
                                        onclick="document.getElementById('chatMenuDropdown').classList.toggle('show')" 
                                        class="btn-small"
                                        style="background: var(--color-background); border: 1px solid var(--color-border); color: var(--color-text); padding: 0.5rem 0.75rem;"
                                        title="Más opciones">
                                    <i class="ri-more-2-fill" style="font-size: 1.2rem;"></i>
                                </button>
                                <div id="chatMenuDropdown" class="chat-menu-dropdown">
                                    <button type="button" class="chat-menu-item chat-menu-item-danger" onclick="document.getElementById('chatMenuDropdown').classList.remove('show'); mostrarFormularioDenuncia();">
                                        <i class="ri-flag-line"></i> Reportar vendedor
                                    </button>
                                    <button type="button" class="chat-menu-item chat-menu-item-danger" onclick="document.getElementById('chatMenuDropdown').classList.remove('show'); if(confirm('¿Eliminar esta conversación?')) eliminarChat(<?= $chat_id ?>);">
                                        <i class="ri-delete-bin-line"></i> Eliminar chat
                                    </button>
                                    <a href="../perfil/perfil_publico.php?id=<?= $otro_usuario_id ?>" class="chat-menu-item" style="color: var(--color-primary);">
                                        <i class="ri-user-line"></i> Ver perfil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($dias_restantes !== null && $dias_restantes <= 3 && $dias_restantes >= 0 && !$chat_bloqueado): ?>
                    <!-- Mensaje de advertencia cuando quedan pocos días -->
                    <div style="background: <?= $dias_restantes <= 1 ? 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)' : 'linear-gradient(135deg, #FFC107 0%, #FFB300 100%)' ?>; color: white; padding: 1rem; border-radius: 8px; margin: 1rem 0; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="ri-alarm-warning-line" style="font-size: 2rem;"></i>
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 0.25rem 0; font-size: 1rem;">
                                <?= $dias_restantes == 0 ? '⚠️ ¡Último día!' : ($dias_restantes == 1 ? '⚠️ ¡Queda 1 día!' : "⚠️ Quedan {$dias_restantes} días") ?>
                            </h4>
                            <p style="margin: 0; font-size: 0.9rem; opacity: 0.95;">
                                Este chat se cerrará automáticamente el <strong><?= $fecha_cierre ?></strong>. 
                                <?= $dias_restantes <= 1 ? 'Si necesitas más tiempo, contacta al ' . ($es_comprador ? 'vendedor' : 'comprador') . ' por otro medio.' : 'Asegúrate de completar tu transacción antes de esa fecha.' ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="chat-messages" id="chatMessages">
                    <?php 
                    $last_message_id = 0;
                    while ($mensaje = $mensajes_result->fetch_assoc()): 
                        $last_message_id = max($last_message_id, $mensaje['id']);
                        $message_class = ($mensaje['es_comprador'] == 1 && $es_comprador) || 
                                         ($mensaje['es_comprador'] == 0 && $es_vendedor) ? 'message-sent' : 'message-received';
                        // Verificar si es solicitud de devolución o confirmación
                        $es_solicitud_devolucion = strpos($mensaje['mensaje'], 'SOLICITUD DE DEVOLUCIÓN') !== false;
                        $es_solicitud_confirmacion = strpos($mensaje['mensaje'], 'SOLICITUD DE CONFIRMACIÓN') !== false;
                        
                        // Verificar si ya tiene respuesta
                        $tiene_respuesta = false;
                        if ($es_solicitud_devolucion || $es_solicitud_confirmacion) {
                            $patron_respuesta = $es_solicitud_devolucion ? 
                                '%✅ DEVOLUCIÓN ACEPTADA%' : '%✅ COMPRA CONFIRMADA%';
                            $patron_rechazo = $es_solicitud_devolucion ? 
                                '%❌ DEVOLUCIÓN RECHAZADA%' : '%❌ COMPRA RECHAZADA%';
                            
                            $stmt_check = $conn->prepare("
                                SELECT COUNT(*) as tiene_respuesta
                                FROM mensajes 
                                WHERE chat_id = ? 
                                AND id > ?
                                AND (mensaje LIKE ? OR mensaje LIKE ?)
                            ");
                            $stmt_check->bind_param("iiss", $chat_id, $mensaje['id'], $patron_respuesta, $patron_rechazo);
                            $stmt_check->execute();
                            $result_check = $stmt_check->get_result();
                            $row_check = $result_check->fetch_assoc();
                            $tiene_respuesta = ($row_check['tiene_respuesta'] > 0);
                            $stmt_check->close();
                        }
                        
                        // Mostrar botones según quién recibe el mensaje
                        // CONFIRMACIÓN: Ambos pueden responder
                        // DEVOLUCIÓN: Ambos pueden responder
                        
                        // Mostrar botones de confirmación si es solicitud de confirmación y no tiene respuesta
                        $mostrar_botones_confirmacion = $es_solicitud_confirmacion && !$tiene_respuesta;
                        
                        // Mostrar botones de devolución si es solicitud de devolución y no tiene respuesta
                        $mostrar_botones_devolucion = $es_solicitud_devolucion && !$tiene_respuesta;
                    ?>
                        <div id="message-<?php echo $mensaje['id']; ?>" class="message <?php echo $message_class; ?>">
                            <p><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></p>
                            <?php

?>
<span class="message-time"><?php echo formato_tiempo_relativo($mensaje['fecha_registro']); ?></span>
                            
                            <?php if ($mostrar_botones_devolucion): ?>
                                <div id="buttons-<?php echo $mensaje['id']; ?>" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                    <button onclick="responderDevolucion('aceptar', <?php echo $mensaje['id']; ?>)" 
                                            style="flex: 1; padding: 0.75rem; background: #28a745; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                        <i class="ri-check-line"></i> Aceptar
                                    </button>
                                    <button onclick="responderDevolucion('rechazar', <?php echo $mensaje['id']; ?>)" 
                                            style="flex: 1; padding: 0.75rem; background: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                        <i class="ri-close-line"></i> Rechazar
                                    </button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($mostrar_botones_confirmacion): ?>
                                <div id="buttons-<?php echo $mensaje['id']; ?>" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                    <button onclick="responderConfirmacion('confirmar', <?php echo $mensaje['id']; ?>)" 
                                            style="flex: 1; padding: 0.75rem; background: #28a745; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                        <i class="ri-check-line"></i> Confirmar
                                    </button>
                                    <button onclick="responderConfirmacion('rechazar', <?php echo $mensaje['id']; ?>)" 
                                            style="flex: 1; padding: 0.75rem; background: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                        <i class="ri-close-line"></i> Rechazar
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <?php $conn->close(); // Cerrar conexión después de procesar mensajes ?>
                
                <?php if ($chat_bloqueado): ?>
                    <!-- Chat cerrado: no se muestra caja de mensaje ni panel de aviso -->
                <?php else: ?>
                    <div class="chat-input">
                        <form class="message-form" id="messageForm">
                            <div class="chat-input-bar">
                                <div class="chat-input-row">
                                    <div class="chat-input-wrap">
                                        <button type="button" class="btn-emoji" id="btnEmoji" title="Emojis" aria-label="Emojis">
                                            <i class="ri-emotion-smile-line"></i>
                                        </button>
                                        <textarea name="mensaje" id="messageInput" placeholder="Escribe un mensaje..." required rows="1"></textarea>
                                    </div>
                                    <div class="emoji-picker-whatsapp" id="emojiPicker" role="listbox" aria-label="Selector de emojis">
                                    <div class="emoji-picker-search">
                                        <input type="text" id="emojiSearch" placeholder="Buscar emoji" autocomplete="off">
                                    </div>
                                    <div class="emoji-picker-tabs">
                                        <button type="button" class="emoji-tab active" data-tab="caras" title="Emoticonos y personas">😀</button>
                                        <button type="button" class="emoji-tab" data-tab="gestos" title="Gestos">👋</button>
                                        <button type="button" class="emoji-tab" data-tab="objetos" title="Objetos">💬</button>
                                        <button type="button" class="emoji-tab" data-tab="simbolos" title="Símbolos">❤️</button>
                                    </div>
                                    <div class="emoji-picker-grid-wrap">
                                        <div class="emoji-picker-category active" id="cat-caras">
                                            <h4>Emoticonos y personas</h4>
                                            <div class="emoji-picker-grid" data-category="caras"><?php
                                            $caras = ['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','😐','😑','😶','😏','😒','🙄','😬','😮','😯','😲','😳','🥺','😢','😭','😤','😡','🤬','😈','💀','👻','🤡'];
                                            foreach ($caras as $e) { echo '<button type="button" class="emoji-btn-wa" data-emoji="'.htmlspecialchars($e).'">'.$e.'</button>'; }
                                            ?></div>
                                        </div>
                                        <div class="emoji-picker-category" id="cat-gestos">
                                            <h4>Gestos y manos</h4>
                                            <div class="emoji-picker-grid" data-category="gestos"><?php
                                            $gestos = ['👋','🤚','🖐️','✋','🖖','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💪','🦾','🦿'];
                                            foreach ($gestos as $e) { echo '<button type="button" class="emoji-btn-wa" data-emoji="'.htmlspecialchars($e).'">'.$e.'</button>'; }
                                            ?></div>
                                        </div>
                                        <div class="emoji-picker-category" id="cat-objetos">
                                            <h4>Objetos y actividades</h4>
                                            <div class="emoji-picker-grid" data-category="objetos"><?php
                                            $objetos = ['💬','💭','📦','📨','📩','📧','✉️','📮','📪','📫','🎉','🎊','🎁','🏆','⭐','🌟','✨','🔥','💯','✅','❌','❓','❗','💡','📌','🔔','🔒','🔓','❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝'];
                                            foreach ($objetos as $e) { echo '<button type="button" class="emoji-btn-wa" data-emoji="'.htmlspecialchars($e).'">'.$e.'</button>'; }
                                            ?></div>
                                        </div>
                                        <div class="emoji-picker-category" id="cat-simbolos">
                                            <h4>Símbolos</h4>
                                            <div class="emoji-picker-grid" data-category="simbolos"><?php
                                            $simbolos = ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','👍','👎','👌','✌️','🤞','🤟','✅','❌','❓','❗','‼️','⁉️','💯','🔅','🔆','📛','🔰','♻️','✳️','❇️','🔱','📵','🔞'];
                                            foreach ($simbolos as $e) { echo '<button type="button" class="emoji-btn-wa" data-emoji="'.htmlspecialchars($e).'">'.$e.'</button>'; }
                                            ?></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="chat-send-btn" title="Enviar" aria-label="Enviar">
                                    <i class="ri-send-plane-fill"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <script>
                    // Emoji picker tipo WhatsApp: pestañas, búsqueda e insertar
                    (function() {
                        var btn = document.getElementById('btnEmoji');
                        var picker = document.getElementById('emojiPicker');
                        var input = document.getElementById('messageInput');
                        var searchInput = document.getElementById('emojiSearch');
                        if (!btn || !picker || !input) return;

                        function insertEmoji(emoji) {
                            var start = input.selectionStart, end = input.selectionEnd;
                            var text = input.value;
                            input.value = text.slice(0, start) + emoji + text.slice(end);
                            input.selectionStart = input.selectionEnd = start + emoji.length;
                            input.focus();
                        }

                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            picker.classList.toggle('show');
                            if (picker.classList.contains('show')) searchInput.value = '';
                        });

                        picker.querySelectorAll('.emoji-tab').forEach(function(tab) {
                            tab.addEventListener('click', function() {
                                picker.querySelectorAll('.emoji-tab').forEach(function(t) { t.classList.remove('active'); });
                                picker.querySelectorAll('.emoji-picker-category').forEach(function(c) { c.classList.remove('active'); });
                                this.classList.add('active');
                                var id = 'cat-' + this.getAttribute('data-tab');
                                var cat = document.getElementById(id);
                                if (cat) cat.classList.add('active');
                            });
                        });

                        picker.querySelectorAll('.emoji-btn-wa').forEach(function(btnEl) {
                            btnEl.addEventListener('click', function() {
                                insertEmoji(this.getAttribute('data-emoji') || '');
                            });
                        });

                        if (searchInput) {
                            searchInput.addEventListener('input', function() {
                                var q = (this.value || '').trim().toLowerCase();
                                var activeTab = picker.querySelector('.emoji-tab.active');
                                var activeTabId = activeTab ? activeTab.getAttribute('data-tab') : 'caras';
                                picker.querySelectorAll('.emoji-picker-category').forEach(function(cat) {
                                    var grid = cat.querySelector('.emoji-picker-grid');
                                    if (!grid) return;
                                    var categoryId = grid.getAttribute('data-category');
                                    if (!q) {
                                        grid.querySelectorAll('.emoji-btn-wa').forEach(function(b) { b.style.display = ''; });
                                        cat.classList.toggle('active', categoryId === activeTabId);
                                        cat.style.display = categoryId === activeTabId ? 'block' : 'none';
                                        return;
                                    }
                                    var show = false;
                                    grid.querySelectorAll('.emoji-btn-wa').forEach(function(b) {
                                        var em = (b.getAttribute('data-emoji') || '');
                                        var visible = em === q || (q.length >= 1 && em.indexOf(q) !== -1);
                                        b.style.display = visible ? '' : 'none';
                                        if (visible) show = true;
                                    });
                                    cat.classList.toggle('active', show);
                                    cat.style.display = show ? 'block' : 'none';
                                });
                            });
                        }

                        document.addEventListener('click', function(e) {
                            if (picker.classList.contains('show') && !picker.contains(e.target) && !btn.contains(e.target)) {
                                picker.classList.remove('show');
                            }
                        });
                    })();
                </script>
                <script>
                    // Guardar último ID de mensaje para AJAX
                    window.lastMessageId = <?php echo $last_message_id; ?>;
                    window.chatId = <?php echo $chat_id; ?>;
                    window.chatBloqueado = <?php echo $chat_bloqueado ? 'true' : 'false'; ?>;
                    // Datos del producto para confirmación
                    window.productoPrecio = <?php echo $chat['producto_precio']; ?>;
                    window.productoDisponibles = <?php echo $chat['producto_disponibles']; ?>;
                    window.productoPrecioFormateado = '<?php echo formatPrice($chat['producto_precio']); ?>';
                    // Variable global para rastrear confirmaciones procesadas
                    window.confirmacionesChat = window.confirmacionesChat || {};
                    
                    // Verificar rol (API: vendedor inicia venta, comprador acepta/rechaza)
                    <?php if ($es_vendedor): ?>
                    console.log('✅ Eres VENDEDOR - Iniciar venta (estado 1) o Aceptar devolución (estado 7)');
                    <?php else: ?>
                    console.log('✅ Eres COMPRADOR - Aceptar/Rechazar compra (estado 6) o Devolver (estado 5)');
                    <?php endif; ?>
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
        // Variable global para rutas de API
        window.BASE_URL = '<?= getBaseUrl() ?>';
    </script>
    <script>
    document.addEventListener('click', function(e) {
        var dd = document.getElementById('chatMenuDropdown');
        var btn = document.querySelector('.chat-header-menu-wrap button');
        if (dd && dd.classList.contains('show') && btn && !dd.contains(e.target) && !btn.contains(e.target)) {
            dd.classList.remove('show');
        }
    });
    
    // SISTEMA DE CONFIRMACIÓN
    function mostrarFormularioConfirmacion() {
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelIniciarCompraventaUrl === 'function';
        if (useLaravel) {
            if (confirm('¿Iniciar proceso de compraventa? Precio: ' + (window.productoPrecioFormateado || 'N/A') + ', Cantidad: ' + (window.productoDisponibles || 1))) {
                solicitarConfirmacion(null);
            }
            return;
        }
        const precioFormateado = window.productoPrecioFormateado || 'N/A';
        const disponibles = window.productoDisponibles || 0;
        const detalles = prompt('Ingresa los detalles de la venta:\n\nPrecio del producto: ' + precioFormateado + '\nDisponibles: ' + disponibles);
        if (!detalles || detalles.trim() === '') return;
        if (confirm('¿Enviar solicitud de confirmación?')) {
            solicitarConfirmacion(detalles.trim());
        }
    }
    
    function solicitarConfirmacion(detalles) {
        const btn = event && event.target ? event.target : null;
        if (btn) btn.disabled = true;
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelIniciarCompraventaUrl === 'function';
        if (useLaravel) {
            const cantidad = window.productoDisponibles || 1;
            const precio = window.productoPrecio || 0;
            fetch(window.getLaravelIniciarCompraventaUrl(window.chatId), {
                method: 'PATCH',
                headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' },
                body: JSON.stringify({ cantidad: cantidad, precio: precio })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    // Añadir mensaje en el chat para que el comprador vea contexto (la API no lo crea)
                    const msgTexto = '💰 He iniciado el proceso de venta. Por favor acepta o rechaza la compra.';
                    fetch(window.getLaravelSendMessageUrl(window.chatId), {
                        method: 'POST',
                        headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mensaje: msgTexto })
                    })
                    .then(() => { alert(data.message || 'Proceso iniciado'); if (typeof window.location !== 'undefined') window.location.reload(); })
                    .catch(() => { alert(data.message || 'Proceso iniciado'); if (typeof window.location !== 'undefined') window.location.reload(); });
                } else {
                    alert('Error: ' + (data.message || 'No se pudo iniciar'));
                    if (btn) btn.disabled = false;
                }
            })
            .catch(err => { console.error(err); alert('Error al enviar'); if (btn) btn.disabled = false; });
            return;
        }
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('detalles', detalles);
        fetch((typeof getApiUrl === 'function' ? getApiUrl('api/solicitar_confirmacion.php') : '../api/solicitar_confirmacion.php'), {
            method: 'POST',
            headers: (window.getApiHeaders && window.getApiHeaders()) || {},
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) { alert(data.message); } else { alert('Error: ' + data.message); if (btn) btn.disabled = false; }
        })
        .catch(err => { console.error(err); alert('Error al enviar'); if (btn) btn.disabled = false; });
    }
    
    function responderConfirmacion(accion, mensajeId) {
        const key = `confirmacion_${mensajeId}`;
        if (window.confirmacionesChat[key]) return;
        const msg = accion === 'confirmar' ? '¿Aceptar esta compra?' : '¿Rechazar esta solicitud de venta?';
        if (!confirm(msg)) return;
        window.confirmacionesChat[key] = true;
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelTerminarCompraventaUrl === 'function';
        if (useLaravel) {
            const body = { 
                confirmacion: accion === 'confirmar',
                calificacion: null,
                comentario: null
            };
            fetch(window.getLaravelTerminarCompraventaUrl(window.chatId), {
                method: 'PATCH',
                headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            })
            .then(res => res.json().then(data => ({ ok: res.ok, status: res.status, data })))
            .then(({ ok, status, data }) => {
                if (ok && (data.success || data.status === 'success')) {
                    const msgTexto = accion === 'confirmar' ? '✅ He aceptado la compra.' : '❌ He rechazado la solicitud de venta.';
                    fetch(window.getLaravelSendMessageUrl(window.chatId), {
                        method: 'POST',
                        headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mensaje: msgTexto })
                    })
                    .then(() => {
                        alert(data.message || (accion === 'confirmar' ? 'Venta concretada' : 'Proceso cancelado'));
                        if (typeof window.location !== 'undefined') window.location.reload();
                    })
                    .catch(() => {
                        alert(data.message || (accion === 'confirmar' ? 'Venta concretada' : 'Proceso cancelado'));
                        if (typeof window.location !== 'undefined') window.location.reload();
                    });
                } else {
                    const msg = data.error || data.message || (status >= 500 ? 'Error del servidor. Revisa los logs de Laravel.' : 'No se pudo procesar');
                    alert('Error: ' + msg);
                    delete window.confirmacionesChat[key];
                }
            })
            .catch(err => { console.error(err); alert('Error al procesar'); delete window.confirmacionesChat[key]; });
            return;
        }
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('accion', accion);
        formData.append('mensaje_id', mensajeId);
        fetch((typeof getApiUrl === 'function' ? getApiUrl('api/responder_confirmacion.php') : '../api/responder_confirmacion.php'), {
            method: 'POST',
            headers: (window.getApiHeaders && window.getApiHeaders()) || {},
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                const buttonsDiv = document.getElementById('buttons-' + mensajeId);
                if (buttonsDiv) buttonsDiv.style.display = 'none';
                if (accion === 'confirmar') {
                    const btnConfirmar = document.getElementById('btnConfirmarCompra');
                    const btnDevolver = document.getElementById('btnDevolver');
                    if (btnConfirmar) btnConfirmar.style.display = 'none';
                    if (btnDevolver) btnDevolver.style.display = 'flex';
                }
            } else {
                alert('Error: ' + data.message);
                delete window.confirmacionesChat[key];
            }
        })
        .catch(err => { console.error(err); alert('Error al procesar'); delete window.confirmacionesChat[key]; });
    }
    
    // SISTEMA DE DEVOLUCIÓN
    function mostrarFormularioDevolucion() {
        const motivo = prompt('¿Por qué deseas devolver este producto?\n\nEscribe el motivo:');
        
        if (!motivo || motivo.trim() === '') {
            return;
        }
        
        if (confirm('¿Solicitar la devolución?')) {
            solicitarDevolucion(motivo.trim());
        }
    }
    
    function solicitarDevolucion(motivo) {
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelIniciarDevolucionUrl === 'function';
        if (useLaravel) {
            fetch(window.getLaravelIniciarDevolucionUrl(window.chatId), {
                method: 'PATCH',
                headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    const msgTexto = '⚠️ He solicitado la devolución de este producto.\n\nMotivo: ' + motivo;
                    fetch(window.getLaravelSendMessageUrl(window.chatId), {
                        method: 'POST',
                        headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mensaje: msgTexto })
                    })
                    .then(() => {
                        alert(data.message || 'Proceso de devolución iniciado');
                        if (typeof window.location !== 'undefined') window.location.reload();
                    })
                    .catch(() => {
                        alert(data.message || 'Proceso de devolución iniciado');
                        if (typeof window.location !== 'undefined') window.location.reload();
                    });
                } else {
                    alert('Error: ' + (data.message || 'No se pudo iniciar'));
                }
            })
            .catch(err => { console.error(err); alert('Error al enviar'); });
            return;
        }
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('motivo', motivo);
        fetch((typeof getApiUrl === 'function' ? getApiUrl('api/solicitar_devolucion.php') : '../api/solicitar_devolucion.php'), {
            method: 'POST',
            headers: (window.getApiHeaders && window.getApiHeaders()) || {},
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) alert(data.message);
            else alert('Error: ' + data.message);
        })
        .catch(err => { console.error(err); alert('Error al enviar'); });
    }
    
    function responderDevolucionHeader(accion) {
        if (accion !== 'aceptar') return;
        if (!confirm('¿Aceptar la devolución?')) return;
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelTerminarDevolucionUrl === 'function';
        if (!useLaravel) return;
        fetch(window.getLaravelTerminarDevolucionUrl(window.chatId), {
            method: 'PATCH',
            headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success || data.status === 'success') {
                const msgTexto = '✅ He aceptado la devolución.';
                fetch(window.getLaravelSendMessageUrl(window.chatId), {
                    method: 'POST',
                    headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mensaje: msgTexto })
                })
                .then(() => {
                    alert(data.message || 'Devolución registrada');
                    if (typeof window.location !== 'undefined') window.location.reload();
                })
                .catch(() => {
                    alert(data.message || 'Devolución registrada');
                    if (typeof window.location !== 'undefined') window.location.reload();
                });
            } else {
                alert('Error: ' + (data.message || data.error || 'No se pudo procesar'));
            }
        })
        .catch(err => { console.error(err); alert('Error al procesar'); });
    }
    
    function responderDevolucion(accion, mensajeId) {
        const key = `devolucion_${mensajeId}`;
        if (window.confirmacionesChat[key]) return;
        const msg = accion === 'aceptar' ? '¿Aceptar la devolución?' : '¿Rechazar la devolución?';
        if (!confirm(msg)) return;
        window.confirmacionesChat[key] = true;
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelTerminarDevolucionUrl === 'function';
        if (useLaravel) {
            if (accion === 'rechazar') {
                alert('La API no soporta rechazar devoluciones. Solo puedes aceptar.');
                delete window.confirmacionesChat[key];
                return;
            }
            fetch(window.getLaravelTerminarDevolucionUrl(window.chatId), {
                method: 'PATCH',
                headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    const msgTexto = '✅ He aceptado la devolución.';
                    fetch(window.getLaravelSendMessageUrl(window.chatId), {
                        method: 'POST',
                        headers: { ...(window.getApiHeaders && window.getApiHeaders() || {}), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mensaje: msgTexto })
                    })
                    .then(() => {
                        alert(data.message || 'Devolución registrada');
                        if (typeof window.location !== 'undefined') window.location.reload();
                    })
                    .catch(() => {
                        alert(data.message || 'Devolución registrada');
                        if (typeof window.location !== 'undefined') window.location.reload();
                    });
                } else {
                    alert('Error: ' + (data.message || 'No se pudo procesar'));
                    delete window.confirmacionesChat[key];
                }
            })
            .catch(err => { console.error(err); alert('Error al procesar'); delete window.confirmacionesChat[key]; });
            return;
        }
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('accion', accion);
        formData.append('mensaje_id', mensajeId);
        fetch((typeof getApiUrl === 'function' ? getApiUrl('api/responder_devolucion.php') : '../api/responder_devolucion.php'), {
            method: 'POST',
            headers: (window.getApiHeaders && window.getApiHeaders()) || {},
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                const buttonsDiv = document.getElementById('buttons-' + mensajeId);
                if (buttonsDiv) buttonsDiv.style.display = 'none';
            } else {
                alert('Error: ' + data.message);
                delete window.confirmacionesChat[key];
            }
        })
        .catch(err => { console.error(err); alert('Error al procesar'); delete window.confirmacionesChat[key]; });
    }
    
    // SISTEMA DE DENUNCIA
    function mostrarFormularioDenuncia() {
        const motivo = prompt(
            '¿Por qué deseas denunciar a este usuario?\n\n' +
            'Escribe el motivo de la denuncia:\n' +
            '(Ejemplo: Producto defectuoso, estafa, comportamiento inapropiado, etc.)'
        );
        
        if (!motivo || motivo.trim() === '') {
            return;
        }
        
        if (confirm('¿Confirmas que deseas enviar esta denuncia?\n\nMotivo: ' + motivo)) {
            enviarDenuncia(motivo.trim());
        }
    }
    
    function enviarDenuncia(motivo) {
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('motivo', motivo);
        
        fetch((typeof getApiUrl === 'function' ? getApiUrl('api/denunciar_usuario.php') : '../api/denunciar_usuario.php'), {
            method: 'POST',
            headers: (window.getApiHeaders && window.getApiHeaders()) || {},
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ Denuncia enviada correctamente.\n\nGracias por tu reporte. Nuestro equipo revisará el caso.');
            } else {
                alert('❌ Error: ' + (data.message || 'No se pudo enviar la denuncia'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('❌ Error al enviar la denuncia. Por favor, intenta de nuevo.');
        });
    }
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js"></script>
</body>
</html>




