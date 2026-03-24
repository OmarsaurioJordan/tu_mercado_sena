<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();

if (isset($_GET['desbloquear'])) {
    $bloqueado_id = (int)$_GET['desbloquear'];
    apiDesbloquearUsuario($bloqueado_id);
    header("Location: bloqueados.php?msg=desbloqueado");
    exit;
}

$r = apiGetBloqueados();
$bloqueados_raw = [];
if ($r['success'] && isset($r['data'])) {
    $d = $r['data'];
    $list = $d['data'] ?? $d['bloqueados'] ?? (isset($d[0]) ? $d : []);
    foreach (is_array($list) ? $list : [] as $b) {
    // ✅ ahora busca usuario_bloqueado también
    $u = $b['usuario_bloqueado'] ?? $b['usuario'] ?? $b['bloqueado'] ?? $b;
    $bloqueados_raw[] = [
        'id'          => $b['id'] ?? 0,
        'bloqueado_id'=> (int)($u['id'] ?? $b['bloqueado_id'] ?? 0),
        'nickname'    => $u['nickname'] ?? '',
        'imagen'      => $u['imagen']   ?? '',
        'descripcion' => $u['descripcion'] ?? '',
    ];
}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Bloqueados - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getAbsoluteBaseUrl() ?>styles.css?v=<?= time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>🚫 Usuarios Bloqueados</h1>
            </div>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'desbloqueado'): ?>
                <div class="success-message">Usuario desbloqueado correctamente</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'bloqueado'): ?>
                <div class="success-message">Usuario bloqueado correctamente</div>
            <?php endif; ?>
            
            <div class="products-grid">
                <?php if (!empty($bloqueados_raw)): ?>
                    <?php foreach ($bloqueados_raw as $b): ?>
                        <div class="product-card seller-card">
                            <img src="<?= getAvatarUrl($b['imagen'] ?? ''); ?>" 
                                 alt="Avatar de <?= htmlspecialchars($b['nickname']); ?>"
                                 class="product-image">
                            <div class="product-info">
                                <h3 class="product-name"><?= htmlspecialchars($b['nickname']); ?></h3>
                                <?php if (!empty($b['descripcion'])): ?>
                                    <p class="product-category"><?= htmlspecialchars(mb_substr($b['descripcion'], 0, 50)); ?>...</p>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <a href="bloqueados.php?desbloquear=<?= (int)$b['bloqueado_id']; ?>" 
                                   class="btn-primary"
                                   onclick="return confirm('¿Desbloquear a este usuario?');">
                                   Desbloquear
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No has bloqueado a ningún usuario.</p>
                        <a href="../index.php" class="btn-primary">Explorar productos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getAbsoluteBaseUrl() ?>script.js"></script>
</body>
</html>
