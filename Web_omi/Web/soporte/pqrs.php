<?php
require_once '../config.php';
if (!defined('USE_LARAVEL_API')) require_once __DIR__ . '/../config_api.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';
$mis_pqrs_list = []; // Lista normalizada (PHP o Laravel)

// Motivos de PQRS (deben coincidir con los IDs de la tabla motivos en DB). Iconos planos Remix Icon.
$motivos = [
    1 => ['nombre' => 'Pregunta', 'icon_class' => 'ri-question-line', 'descripcion' => 'Tengo una duda sobre el funcionamiento del sistema'],
    2 => ['nombre' => 'Queja', 'icon_class' => 'ri-error-warning-line', 'descripcion' => 'Quiero expresar mi inconformidad con algo'],
    3 => ['nombre' => 'Reclamo', 'icon_class' => 'ri-megaphone-line', 'descripcion' => 'Tengo un problema que necesita solución'],
    4 => ['nombre' => 'Sugerencia', 'icon_class' => 'ri-lightbulb-line', 'descripcion' => 'Tengo una idea para mejorar el sistema'],
    5 => ['nombre' => 'Agradecimiento', 'icon_class' => 'ri-heart-line', 'descripcion' => 'Quiero agradecer al equipo']
];

$useLaravel = defined('USE_LARAVEL_API') && USE_LARAVEL_API;
$mensaje_min = $useLaravel ? 20 : 20;
$mensaje_max = $useLaravel ? 512 : 600; // Laravel API limita a 512

// Procesar envío de PQRS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motivo_id = intval($_POST['motivo_id'] ?? 0);
    $mensaje = sanitize($_POST['mensaje'] ?? '');
    
    if ($motivo_id < 1 || $motivo_id > 5) {
        $error = 'Selecciona un tipo de solicitud válido';
    } elseif (strlen($mensaje) < $mensaje_min) {
        $error = 'El mensaje debe tener al menos ' . $mensaje_min . ' caracteres';
    } elseif (strlen($mensaje) > $mensaje_max) {
        $error = 'El mensaje no puede exceder ' . $mensaje_max . ' caracteres';
    } elseif ($useLaravel && isset($_SESSION['api_token'])) {
        // Enviar a API Laravel POST /api/pqrs
        $apiUrl = rtrim(LARAVEL_API_URL, '/') . '/pqrs';
        $payload = json_encode(['mensaje' => $mensaje, 'motivo_id' => $motivo_id]);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $_SESSION['api_token']
                ]),
                'content' => $payload
            ]
        ]);
        $resp = @file_get_contents($apiUrl, false, $ctx);
        $code = 0;
        if (isset($http_response_header[0]) && preg_match('/\d{3}/', $http_response_header[0], $m)) $code = (int)$m[0];
        $data = $resp ? json_decode($resp, true) : null;
        if ($code === 201 && $data && !empty($data['success'])) {
            $success = $data['message'] ?? '¡Tu solicitud ha sido enviada correctamente! Te responderemos pronto.';
        } else {
            $error = ($data['message'] ?? null) ?: ($data['errors']['mensaje'][0] ?? $data['errors']['motivo_id'][0] ?? 'Error al enviar la solicitud. Intenta de nuevo.');
        }
    } else {
        // PHP nativo: insertar en BD
        $conn = getDBConnection();
        $estado_id = 1;
        $stmt = $conn->prepare("INSERT INTO pqrs (usuario_id, mensaje, motivo_id, estado_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $user['id'], $mensaje, $motivo_id, $estado_id);
        if ($stmt->execute()) {
            $success = '¡Tu solicitud ha sido enviada correctamente! Te responderemos pronto.';
        } else {
            $error = 'Error al enviar la solicitud. Intenta de nuevo.';
        }
        $stmt->close();
        $conn->close();
    }
}

// Obtener PQRS anteriores del usuario
if ($useLaravel && isset($_SESSION['api_token'])) {
    $apiUrl = rtrim(LARAVEL_API_URL, '/') . '/pqrs';
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\nAuthorization: Bearer " . $_SESSION['api_token']
        ]
    ]);
    $resp = @file_get_contents($apiUrl, false, $ctx);
    $data = $resp ? json_decode($resp, true) : null;
    if ($data && !empty($data['data']) && is_array($data['data'])) {
        $raw = array_slice($data['data'], 0, 10);
        foreach ($raw as $row) {
            $mis_pqrs_list[] = [
                'id' => $row['id'],
                'usuario_id' => $row['usuario_id'],
                'mensaje' => $row['mensaje'],
                'motivo_id' => $row['motivo_id'],
                'estado_id' => $row['estado_id'] ?? null,
                'fecha_registro' => $row['fecha_registro'] ?? '',
                'estado_nombre' => isset($row['estado']['nombre']) ? $row['estado']['nombre'] : 'pendiente'
            ];
        }
    }
} else {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT p.id, p.usuario_id, p.mensaje, p.motivo_id, p.estado_id, p.fecha_registro, e.nombre as estado_nombre
        FROM pqrs p
        INNER JOIN estados e ON p.estado_id = e.id
        WHERE p.usuario_id = ?
        ORDER BY p.fecha_registro DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $mis_pqrs_list[] = $row;
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PQRS - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
    <style>
        .pqrs-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .pqrs-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .pqrs-type {
            padding: 1.2rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--color-bg);
        }
        
        .pqrs-type:hover {
            border-color: var(--color-primary);
            transform: translateY(-2px);
        }
        
        .pqrs-type.selected {
            border-color: var(--color-primary);
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: white;
        }
        .pqrs-type.selected .icon { color: inherit; }
        
        .pqrs-type .icon {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            display: block;
            color: var(--color-primary);
        }
        
        .pqrs-type .name {
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: var(--color-text-light);
            margin-top: 0.5rem;
        }
        
        .char-counter.warning {
            color: #e74c3c;
        }
        
        .pqrs-history {
            margin-top: 3rem;
        }
        
        .pqrs-item {
            background: var(--color-bg);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
        }
        
        .pqrs-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .pqrs-item-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--color-primary);
        }
        
        .pqrs-item-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-activo {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-resuelto {
            background: #d4edda;
            color: #155724;
        }
        
        .pqrs-item-date {
            font-size: 0.85rem;
            color: var(--color-text-light);
            margin-top: 0.5rem;
        }
        
        .pqrs-success-card {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 1px solid rgba(45, 199, 92, 0.3);
            border-radius: 16px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(45, 199, 92, 0.12);
        }
        .pqrs-success-card .pqrs-success-icon {
            width: 56px;
            height: 56px;
            min-width: 56px;
            border-radius: 50%;
            background: var(--color-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }
        .pqrs-success-card .pqrs-success-text h3 {
            margin: 0 0 0.35rem 0;
            font-size: 1.2rem;
            color: #1b5e20;
            font-weight: 700;
        }
        .pqrs-success-card .pqrs-success-text p {
            margin: 0;
            font-size: 0.95rem;
            color: #2e7d32;
            line-height: 1.45;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container pqrs-container">
            <div class="page-header">
                <h1><i class="ri-file-text-line" aria-hidden="true" style="vertical-align: middle; margin-right: 0.25rem;"></i> PQRS</h1>
                <p>Preguntas, Quejas, Reclamos y Sugerencias</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="pqrs-success-card" role="alert">
                    <div class="pqrs-success-icon" aria-hidden="true">
                        <i class="ri-checkbox-circle-fill"></i>
                    </div>
                    <div class="pqrs-success-text">
                        <h3>¡Solicitud enviada!</h3>
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="pqrs.php" class="form-container">
                <h2>Enviar Nueva Solicitud</h2>
                
                <div class="form-group">
                    <label>Tipo de Solicitud *</label>
                    <input type="hidden" name="motivo_id" id="motivo_id" required>
                    <div class="pqrs-types">
                        <?php foreach ($motivos as $id => $motivo): ?>
                            <div class="pqrs-type" data-id="<?= $id ?>" onclick="selectType(this, <?= $id ?>)">
                                <span class="icon"><i class="<?= htmlspecialchars($motivo['icon_class']) ?>" aria-hidden="true"></i></span>
                                <span class="name"><?= htmlspecialchars($motivo['nombre']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="mensaje">Mensaje *</label>
                    <textarea id="mensaje" name="mensaje" rows="6" 
                              minlength="20" maxlength="600" required
                              placeholder="Describe tu solicitud con detalle..."
                              oninput="updateCharCounter(this)"></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span>/600 caracteres
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Enviar Solicitud</button>
            </form>
            
            <?php if (count($mis_pqrs_list) > 0): ?>
                <div class="pqrs-history">
                    <h2>Mis Solicitudes Anteriores</h2>
                    
                    <?php foreach ($mis_pqrs_list as $pqrs): ?>
                        <div class="pqrs-item">
                            <div class="pqrs-item-header">
                                <span class="pqrs-item-type">
                                    <?php $m = $motivos[$pqrs['motivo_id']] ?? null; ?>
                                    <?php if ($m): ?><i class="<?= htmlspecialchars($m['icon_class']) ?>" aria-hidden="true" style="margin-right: 0.35rem; vertical-align: middle;"></i><?php endif; ?>
                                    <?= htmlspecialchars($motivos[$pqrs['motivo_id']]['nombre'] ?? 'Solicitud') ?>
                                </span>
                                <span class="pqrs-item-status status-<?= strtolower($pqrs['estado_nombre']) ?>">
                                    <?= htmlspecialchars($pqrs['estado_nombre']) ?>
                                </span>
                            </div>
                            <p><?= nl2br(htmlspecialchars($pqrs['mensaje'])) ?></p>
                            <div class="pqrs-item-date">
                                Enviado: <?= date('d/m/Y H:i', strtotime($pqrs['fecha_registro'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
        function selectType(element, id) {
            document.querySelectorAll('.pqrs-type').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('motivo_id').value = id;
        }
        
        function updateCharCounter(textarea) {
            const counter = document.getElementById('charCount');
            const length = textarea.value.length;
            counter.textContent = length;
            
            if (length > 550) {
                counter.parentElement.classList.add('warning');
            } else {
                counter.parentElement.classList.remove('warning');
            }
        }
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js"></script>
</body>
</html>
