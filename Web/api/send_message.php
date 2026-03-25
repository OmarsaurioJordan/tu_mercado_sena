
<?php
require_once '../config.php';
header('Content-Type: application/json');

// Establecer zona horaria colombiana (NECESARIO para la función formato_tiempo_relativo)
date_default_timezone_set('America/Bogota');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$chat_id = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

if ($chat_id <= 0 || empty($mensaje)) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

// Verificar que el usuario pertenece al chat
$stmt = $conn->prepare("
    SELECT c.comprador_id, p.vendedor_id, c.producto_id 
    FROM chats c
    INNER JOIN productos p ON c.producto_id = p.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $chat_id);
$stmt->execute();

$result = $stmt->get_result();
$chat = $result->fetch_assoc();
$stmt->close();

if (!$chat) {
    echo json_encode(['success' => false, 'error' => 'Chat no encontrado']);
    $conn->close();
    exit;
}

// Determinar rol
$es_comprador = ($user['id'] == $chat['comprador_id']);
$es_vendedor  = ($user['id'] == $chat['vendedor_id']);

if (!$es_comprador && !$es_vendedor) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    $conn->close();
    exit;
}

// Sanitizar mensaje
$mensaje = sanitize($mensaje);

// Determinar rol del mensaje
$es_comprador_msg = $es_comprador ? 1 : 0;
$imagen = ' ';

// ✅ INSERCIÓN CORREGIDA: Dejamos que el campo fecha_registro en MySQL use CURRENT_TIMESTAMP().
$stmt = $conn->prepare("
    INSERT INTO mensajes (es_comprador, chat_id, mensaje, imagen)
    VALUES (?, ?, ?, ?)
");
// Quitamos 's' del bind_param y quitamos el parámetro de fecha
$stmt->bind_param("iiss", $es_comprador_msg, $chat_id, $mensaje, $imagen); 

if ($stmt->execute()) {
    $message_id = $conn->insert_id;

    // Actualizar estado de "visto"
    if ($es_comprador) {
        $stmt2 = $conn->prepare("UPDATE chats SET visto_vendedor = 0, visto_comprador = 1 WHERE id = ?");
    } else {
        $stmt2 = $conn->prepare("UPDATE chats SET visto_comprador = 0, visto_vendedor = 1 WHERE id = ?");
    }
    $stmt2->bind_param("i", $chat_id);
    $stmt2->execute();
    $stmt2->close();

    // Notificación emergente para el destinatario (registro en BD)
    $destinatario_id = $es_comprador ? (int)$chat['vendedor_id'] : (int)$chat['comprador_id'];
    $stmt_prod = $conn->prepare("SELECT nombre FROM productos WHERE id = ?");
    $stmt_prod->bind_param("i", $chat['producto_id']);
    $stmt_prod->execute();
    $prod = $stmt_prod->get_result()->fetch_assoc();
    $stmt_prod->close();
    $nombre_producto = $prod ? $prod['nombre'] : 'Chat';
    $mensaje_notif = 'Nuevo mensaje en chat: ' . $nombre_producto;
    $motivo_id = 8; // comprador/contacto
    $stmt_ins = $conn->prepare("INSERT INTO notificaciones (usuario_id, motivo_id, mensaje, visto) VALUES (?, ?, ?, 0)");
    $stmt_ins->bind_param("iis", $destinatario_id, $motivo_id, $mensaje_notif);
    $stmt_ins->execute();
    $stmt_ins->close();

    // Notificación por correo si el destinatario tiene notifica_correo activo
    $stmt_cuenta = $conn->prepare("
        SELECT c.email, c.notifica_correo
        FROM usuarios u
        INNER JOIN cuentas c ON u.cuenta_id = c.id
        WHERE u.id = ?
    ");
    $stmt_cuenta->bind_param("i", $destinatario_id);
    $stmt_cuenta->execute();
    $cuenta_dest = $stmt_cuenta->get_result()->fetch_assoc();
    $stmt_cuenta->close();
    if ($cuenta_dest && !empty($cuenta_dest['notifica_correo']) && !empty($cuenta_dest['email'])) {
        $asunto = 'Nuevo mensaje en Tu Mercado SENA';
        $cuerpo = "Tienes un nuevo mensaje en el chat del producto: " . $nombre_producto . ".\n\n";
        $cuerpo .= "Entra a la aplicación para verlo.\n\n— Tu Mercado SENA";
        enviar_correo_notificacion($cuenta_dest['email'], $asunto, $cuerpo);
    }

    // RECUPERAR EL MENSAJE Y LA FECHA REAL GUARDADA
    $stmt3 = $conn->prepare("SELECT id, es_comprador, mensaje, fecha_registro FROM mensajes WHERE id = ?");
    $stmt3->bind_param("i", $message_id);
    $stmt3->execute();
    $result = $stmt3->get_result();
    $message = $result->fetch_assoc();
    $stmt3->close();

    // ✅ Formatear el mensaje para que JS lo muestre con saltos de línea y sanitizado
    $message['mensaje'] = nl2br(htmlspecialchars($message['mensaje']));
    
    // ✅ Formatear el tiempo usando la función relativa (será "Ahora")
    $message['tiempo_relativo'] = formato_tiempo_relativo($message['fecha_registro']);
    $message['es_mio'] = 1;

    
    // ✅ CLAVE: Devolver el objeto $message completo bajo la clave 'message'
    echo json_encode([
        'success' => true,
        'message' => $message 
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar mensaje']);
}

$stmt->close();
$conn->close();
?>