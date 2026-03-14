<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$productos_list = [];

// Usar PHP/MySQL para listar (alineado con publicar.php que crea vía PHP; la API usa cuenta_id).
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT p.*, sc.nombre AS subcategoria_nombre, c.nombre AS categoria_nombre, e.nombre AS estado_nombre, f.imagen AS producto_imagen FROM productos p INNER JOIN subcategorias sc ON p.subcategoria_id = sc.id INNER JOIN categorias c ON sc.categoria_id = c.id INNER JOIN estados e ON p.estado_id = e.id LEFT JOIN (SELECT producto_id, MIN(id) AS min_id FROM fotos GROUP BY producto_id) fmin ON fmin.producto_id = p.id LEFT JOIN fotos f ON f.id = fmin.min_id WHERE p.vendedor_id = ? ORDER BY p.fecha_registro DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $productos_list[] = $r;
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Productos - Tu Mercado SENA</title>
    <script>
        (function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();
    </script>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">

    <style>
        .mis-productos-card .product-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            background-color: var(--color-bg-secondary);
        }
        .mis-productos-card .product-actions .btn-small,
        .mis-productos-card .product-actions .btn-delete {
            flex: 1 1 0;
            min-width: 70px;
            text-align: center;
        }
        .mis-productos-card .product-actions .btn-ocultar {
            background: var(--color-secondary);
            color: white;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-delete {
            background: var(--color-danger);
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: .2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
        }
        .btn-delete:hover {
            filter: brightness(0.9);
            transform: translateY(-1px);
        }
    </style>

</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">

            <!-- MENSAJE CUANDO SE ELIMINA UN PRODUCTO -->
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'producto_eliminado'): ?>
                <div class="alert-success">
                    ✅ Producto eliminado correctamente.
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1>Mis Productos</h1>
                <a href="publicar.php" class="btn-primary">Publicar Nuevo Producto</a>
            </div>
            
            <div class="products-grid">
                <?php if (count($productos_list) > 0): ?>
                    <?php foreach ($productos_list as $producto): ?>
                        <div class="product-card mis-productos-card">
                            <a href="producto.php?id=<?php echo $producto['id']; ?>" class="product-card-link">

<?php
$imgUrl = !empty($producto['producto_imagen_url']) ? $producto['producto_imagen_url'] : (!empty($producto['producto_imagen']) ? (function_exists('getProductImageUrlPHP') ? getProductImageUrlPHP($producto['producto_imagen']) : (strpos($producto['producto_imagen'], 'http') === 0 ? $producto['producto_imagen'] : getBaseUrl() . 'uploads/productos/' . $producto['producto_imagen'])) : getBaseUrl() . 'assets/images/default-product.jpg');
?>
    <img src="<?= htmlspecialchars($imgUrl) ?>"
         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
         class="product-image"
         onerror="this.src='<?= getBaseUrl() ?>assets/images/default-product.jpg'">



                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                    <p class="product-price"><?php echo formatPrice($producto['precio']); ?></p>
                                    <p class="product-category"><?php echo htmlspecialchars($producto['categoria_nombre']); ?> - 
                                       <?php echo htmlspecialchars($producto['subcategoria_nombre']); ?></p>
                                    <span class="product-status status-<?php echo $producto['estado_id']; ?>">
                                        <?php echo htmlspecialchars($producto['estado_nombre']); ?>
                                    </span>
                                    <span class="product-stock">Disponibles: <?php echo $producto['disponibles']; ?></span>
                                </div>
                            </a>

                            <div class="product-actions">
                                <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-small">Editar</a>
                                <button type="button" 
                                        onclick="toggleVisibilidad(<?php echo $producto['id']; ?>, this)" 
                                        class="btn-small btn-ocultar">
                                    <?php echo $producto['estado_id'] == 1 ? 'Ocultar' : 'Mostrar'; ?>
                                </button>
                                <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>"
                                   class="btn-delete"
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.');">
                                    Eliminar
                                </a>
                            </div>


                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No has publicado ningún producto todavía.</p>

                        <a href="publicar.php" class="btn-primary">Publicar tu primer producto</a>
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
    <script>
    window.BASE_URL = '<?= getBaseUrl() ?>';
    function toggleVisibilidad(id, btn) {
        var base = window.BASE_URL || '<?= getBaseUrl() ?>';
        var apiUrl = base + 'api/toggle_visibilidad.php?id=' + id;
        fetch(apiUrl)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const card = btn.closest('.product-card');
                    const statusSpan = card.querySelector('.product-status');
                    
                    if (data.nuevo_estado === 2) {
                        btn.textContent = 'Mostrar';
                        statusSpan.textContent = 'invisible';
                        statusSpan.className = 'product-status status-2';
                    } else {
                        btn.textContent = 'Ocultar';
                        statusSpan.textContent = 'activo';
                        statusSpan.className = 'product-status status-1';
                    }
                    alert('Visibilidad actualizada');
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => alert('Error de conexion'));
    }
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js?v=<?= time(); ?>"></script>
</body>
</html>



