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

// Verificar si ya existe una compra confirmada en este chat
$compra_confirmada = false;
$stmt_check = $conn->prepare("SELECT COUNT(*) as tiene_confirmacion FROM mensajes WHERE chat_id = ? AND mensaje LIKE '%✅ COMPRA CONFIRMADA%'");
$stmt_check->bind_param("i", $chat_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();
$compra_confirmada = ($row_check['tiene_confirmacion'] > 0);
$stmt_check->close();

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
$stmt_check = $conn->prepare("SELECT COUNT(*) as tiene_confirmacion FROM mensajes WHERE chat_id = ? AND mensaje LIKE '%✅ COMPRA CONFIRMADA%'");
$stmt_check->bind_param("i", $chat_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();
$compra_confirmada = ($row_check['tiene_confirmacion'] > 0);
$stmt_check->close();

// Verificar si el chat está bloqueado (devuelto)
$chat_bloqueado = ($chat['estado_id'] == 8); // estado_id = 8 es "devuelto"

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
                                <!-- Botón Confirmar Compra (oculto si ya hay compra confirmada) -->
                                <button type="button" 
                                        id="btnConfirmarCompra"
                                        onclick="mostrarFormularioConfirmacion()" 
                                        class="btn-confirmar-compra"
                                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 0.6rem 1rem; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; align-items: center; gap: 0.5rem; display: <?php echo $compra_confirmada ? 'none' : 'flex'; ?>;">
                                    <i class="ri-check-double-line"></i>
                                    <span>Confirmar Compra</span>
                                </button>
                                
                                <!-- Botón Devolver (solo visible si hay compra confirmada) -->
                                <button type="button" 
                                        id="btnDevolver"
                                        onclick="mostrarFormularioDevolucion()" 
                                        class="btn-devolucion-header"
                                        style="background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); color: white; padding: 0.6rem 1rem; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; align-items: center; gap: 0.5rem; display: <?php echo $compra_confirmada ? 'flex' : 'none'; ?>;">
                                    <i class="ri-arrow-go-back-line"></i>
                                    <span>Devolver</span>
                                </button>
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
                            <textarea name="mensaje" id="messageInput" placeholder="Escribe un mensaje..." required rows="2"></textarea>
                            <button type="submit" class="btn-primary">Enviar</button>
                        </form>
                    </div>
                <?php endif; ?>
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
                    
                    // Verificar rol
                    <?php if ($es_vendedor): ?>
                    console.log('✅ Eres VENDEDOR - Deberías ver botones en solicitudes de devolución del comprador');
                    <?php else: ?>
                    console.log('✅ Eres COMPRADOR - Deberías ver botones en solicitudes de confirmación del vendedor');
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
        // Mostrar los datos reales del producto
        const precioFormateado = window.productoPrecioFormateado || 'N/A';
        const disponibles = window.productoDisponibles || 0;
        
        const detalles = prompt(
            'Ingresa los detalles de la venta:\n\n' +
            'Precio del producto: ' + precioFormateado + '\n' +
            'Disponibles: ' + disponibles
        );
        
        if (!detalles || detalles.trim() === '') return;
        
        if (confirm('¿Enviar solicitud de confirmación?')) {
            solicitarConfirmacion(detalles.trim());
        }
    }
    
    function solicitarConfirmacion(detalles) {
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('detalles', detalles);
        
        // Deshabilitar botón para evitar doble envío
        const btn = event.target;
        if (btn) btn.disabled = true;
        
        fetch('../api/solicitar_confirmacion.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // NO recargar, dejar que el polling cargue el mensaje
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
                if (btn) btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al enviar la solicitud');
            if (btn) btn.disabled = false;
        });
    }
    
    function responderConfirmacion(accion, mensajeId) {
        // Verificar si ya se procesó esta confirmación
        const key = `confirmacion_${mensajeId}`;
        if (window.confirmacionesChat[key]) {
            console.log('Confirmación ya procesada:', mensajeId);
            return;
        }
        
        const msg = accion === 'confirmar' ? '¿Confirmar esta compra?' : '¿Rechazar esta compra?';
        if (!confirm(msg)) return;
        
        // Marcar como procesada
        window.confirmacionesChat[key] = true;
        
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('accion', accion);
        formData.append('mensaje_id', mensajeId);
        
        fetch('../api/responder_confirmacion.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Ocultar los botones de este mensaje específico
                const buttonsDiv = document.getElementById(`buttons-${mensajeId}`);
                if (buttonsDiv) {
                    buttonsDiv.style.display = 'none';
                }
                
                // Si se confirmó la compra, ocultar botón Confirmar y mostrar botón Devolver
                if (accion === 'confirmar') {
                    const btnConfirmarCompra = document.getElementById('btnConfirmarCompra');
                    const btnDevolver = document.getElementById('btnDevolver');
                    
                    if (btnConfirmarCompra) {
                        btnConfirmarCompra.style.display = 'none';
                    }
                    if (btnDevolver) {
                        btnDevolver.style.display = 'flex';
                    }
                }
            } else {
                alert('Error: ' + data.message);
                // Permitir reintentar si hubo error
                delete window.confirmacionesChat[key];
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al procesar la respuesta');
            // Permitir reintentar si hubo error
            delete window.confirmacionesChat[key];
        });
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
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('motivo', motivo);
        
        fetch('../api/solicitar_devolucion.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error al enviar la solicitud');
        });
    }
    
    function responderDevolucion(accion, mensajeId) {
        // Verificar si ya se procesó esta devolución
        const key = `devolucion_${mensajeId}`;
        if (window.confirmacionesChat[key]) {
            console.log('Devolución ya procesada:', mensajeId);
            return;
        }
        
        const msg = accion === 'aceptar' ? '¿Aceptar la devolución?' : '¿Rechazar la devolución?';
        if (!confirm(msg)) return;
        
        // Marcar como procesada
        window.confirmacionesChat[key] = true;
        
        const formData = new FormData();
        formData.append('chat_id', window.chatId);
        formData.append('accion', accion);
        formData.append('mensaje_id', mensajeId);
        
        fetch('../api/responder_devolucion.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Ocultar los botones de este mensaje específico
                const buttonsDiv = document.getElementById(`buttons-${mensajeId}`);
                if (buttonsDiv) {
                    buttonsDiv.style.display = 'none';
                }
            } else {
                alert('Error: ' + data.message);
                // Permitir reintentar si hubo error
                delete window.confirmacionesChat[key];
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al procesar la respuesta');
            // Permitir reintentar si hubo error
            delete window.confirmacionesChat[key];
        });
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
        
        fetch('../api/denunciar_usuario.php', {
            method: 'POST',
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




