<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();

/* Toggle favorito vía API */
if (isset($_GET['vendedor_id'])) {
    $vendedor_id = (int)$_GET['vendedor_id'];
    $ids = apiGetFavoritos();
    $quitar = in_array($vendedor_id, $ids, true);
    apiToggleFavorito($vendedor_id, !$quitar);
    header('Location: ' . getBasePath() . 'perfil/favoritos.php');
    exit;
}

$vendedores_favoritos = apiGetFavoritosVendedores();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>Mis Vendedores Favoritos</h1>
            </div>
            
           <div class="products-grid">
<?php if (!empty($vendedores_favoritos)): ?>
    <?php foreach ($vendedores_favoritos as $v): ?>
        
        <div class="product-card seller-card">

            <!-- Avatar del vendedor -->
            <img src="<?php echo getAvatarUrl($v['imagen'] ?? ''); ?>"
                alt="Avatar de <?php echo htmlspecialchars($v['nickname'] ?? ''); ?>"
                class="product-image">

            <div class="product-info">
                <h3 class="product-name">
                    <?php echo htmlspecialchars($v['nickname'] ?? ''); ?>
                </h3>

                <p class="product-category">
                    <?php echo nl2br(htmlspecialchars($v['descripcion'] ?? '')); ?>
                </p>

                <?php if (!empty($v['link'])): ?>
                <p>
                    <a href="<?php echo htmlspecialchars($v['link']); ?>" target="_blank">
                        🔗 Enlace del vendedor
                    </a>
                </p>
                <?php endif; ?>
            </div>

            <div class="product-actions">
                <a href="<?= getBasePath() ?>perfil/perfil_publico.php?id=<?= (int)$v['id'] ?>" class="btn-primary">
                    Ver Perfil
                </a>

                <a href="<?= getBasePath() ?>perfil/favoritos.php?vendedor_id=<?= (int)$v['id'] ?>"
                   class="btn-small"
                   onclick="return confirm('¿Quieres quitar a este vendedor de tus favoritos?');">
                    Quitar de Favoritos
                </a>
            </div>

        </div>

    <?php endforeach; ?>

<?php else: ?>
    <div class="no-products">
        <p>No has agregado vendedores a tus favoritos todavía.</p>
        <a href="<?= getBaseUrl() ?>index.php" class="btn-primary">Explorar</a>
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
    <script src="<?= getBaseUrl() ?>script.js"></script>
</body>
</html>
