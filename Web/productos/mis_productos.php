<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$user = $user ?? [];

if (isset($user['estado_id']) && (int)$user['estado_id'] !== 1) {
    header("Location: bloqueado.php");
    exit;
}

$error = '';
$success = '';
$productos_list = [];

function apiBaseHost() {
    if (!defined('API_BASE_URL')) {
        return '';
    }
    return preg_replace('#/api/?$#', '', rtrim(API_BASE_URL, '/'));
}

function normalizarImagenProducto($producto) {
    $defaultImage = getAbsoluteBaseUrl() . 'assets/images/default-product.jpg';
    $apiHost = apiBaseHost();

    if (empty($producto['fotos']) || !is_array($producto['fotos'])) {
        return $defaultImage;
    }

    $foto = $producto['fotos'][0] ?? null;
    if (!$foto || !is_array($foto)) {
        return $defaultImage;
    }

    $url = $foto['url'] ?? $foto['imagen_url'] ?? $foto['path'] ?? $foto['imagen'] ?? null;
    if (!$url || !is_string($url)) {
        return $defaultImage;
    }

    $url = trim($url);
    if ($url === '') {
        return $defaultImage;
    }

    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }

    if (strpos($url, '/storage/') === 0 || strpos($url, '/uploads/') === 0) {
        return $apiHost . $url;
    }

    if (strpos($url, 'storage/') === 0 || strpos($url, 'uploads/') === 0) {
        return $apiHost . '/' . ltrim($url, '/');
    }

    return $apiHost . '/storage/' . ltrim($url, '/');
}

function obtenerCategoriaNombre($producto) {
    return $producto['subcategoria']['categoria']['nombre'] ?? '';
}

function obtenerSubcategoriaNombre($producto) {
    return $producto['subcategoria']['nombre'] ?? '';
}

function obtenerEstadoNombre($producto) {
    return $producto['estado']['nombre'] ?? '';
}

function obtenerEstadoId($producto) {
    return (int)($producto['estado_id'] ?? $producto['estado']['id'] ?? 0);
}

function obtenerPrecioProducto($producto) {
    return number_format((float)($producto['precio'] ?? 0), 0, ',', '.');
}

function obtenerDisponibles($producto) {
    return (int)($producto['disponibles'] ?? 0);
}

/* =========================
   Acciones por API
   1 = activo
   2 = invisible
   3 = eliminado
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $productoId = (int)($_POST['producto_id'] ?? 0);

    if ($productoId <= 0) {
        header('Location: mis_productos.php?msg_error=producto_invalido');
        exit;
    }

    if ($accion === 'eliminar') {
        $resp = apiEliminarProducto($productoId);

        if (!empty($resp['success'])) {
            header('Location: mis_productos.php?msg=eliminado');
            exit;
        }

        header('Location: mis_productos.php?msg_error=eliminar');
        exit;
    }

    if ($accion === 'ocultar') {
        $resp = apiCambiarEstadoProducto($productoId, 2);

        if (!empty($resp['success'])) {
            header('Location: mis_productos.php?msg=ocultado');
            exit;
        }

        header('Location: mis_productos.php?msg_error=ocultar');
        exit;
    }

    if ($accion === 'activar') {
        $resp = apiCambiarEstadoProducto($productoId, 1);

        if (!empty($resp['success'])) {
            header('Location: mis_productos.php?msg=activado');
            exit;
        }

        header('Location: mis_productos.php?msg_error=activar');
        exit;
    }
}

if (!empty($_GET['msg'])) {
    if ($_GET['msg'] === 'eliminado') {
        $success = 'Producto eliminado correctamente.';
    } elseif ($_GET['msg'] === 'ocultado') {
        $success = 'Producto ocultado correctamente.';
    } elseif ($_GET['msg'] === 'activado') {
        $success = 'Producto activado correctamente.';
    }
}

if (!empty($_GET['msg_error'])) {
    if ($_GET['msg_error'] === 'producto_invalido') {
        $error = 'Producto inválido.';
    } elseif ($_GET['msg_error'] === 'eliminar') {
        $error = 'No se pudo eliminar el producto.';
    } elseif ($_GET['msg_error'] === 'ocultar') {
        $error = 'No se pudo ocultar el producto.';
    } elseif ($_GET['msg_error'] === 'activar') {
        $error = 'No se pudo activar el producto.';
    }
}

/* =========================
   Cargar productos solo por API
========================= */
$productos_list = apiGetMisProductos();

if (!is_array($productos_list)) {
    $productos_list = [];
}

/* Mostrar activos e invisibles. Ocultar solo eliminados */
$productos_list = array_values(array_filter($productos_list, function ($producto) {
    return obtenerEstadoId($producto) !== 3;
}));

if (!empty($_GET['debug_api'])) {
    echo "<div style='background:#111;color:#0f0;padding:10px;margin:10px;font-family:monospace;font-size:12px'>";
    echo "<strong>DEBUG apiGetMisProductos()</strong><pre>";
    print_r($productos_list);
    echo "</pre></div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Productos - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getAbsoluteBaseUrl() ?>styles.css?v=<?= time(); ?>">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .page-title {
            margin: 0;
        }

        .top-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            border: 1px solid #e9e9e9;
        }

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            background: #f5f5f5;
        }

        .product-body {
            padding: 16px;
        }

        .product-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .product-price {
            font-size: 22px;
            font-weight: 800;
            color: #1d4ed8;
            margin: 0 0 10px;
        }

        .product-meta {
            font-size: 14px;
            color: #444;
            margin-bottom: 6px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .btn-action,
        .btn-link {
            border: none;
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-danger {
            background: #dc2626;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e9e9e9;
            margin-top: 20px;
        }

        .message-success,
        .message-error {
            padding: 12px 14px;
            border-radius: 10px;
            margin: 16px 0;
        }

        .message-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .message-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Mis Productos</h1>

                <div class="top-actions">
                    <a href="publicar.php" class="btn-link btn-primary">Publicar producto</a>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="message-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (empty($productos_list)): ?>
                <div class="empty-state">
                    <h2>No tienes productos publicados</h2>
                    <p>Aún no has creado productos en Tu Mercado SENA.</p>
                    <!-- <a href="publicar.php" class="btn-link btn-primary">Publicar producto</a> -->
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($productos_list as $producto): ?>
                        <?php
                        $productoId = (int)($producto['id'] ?? 0);
                        $estadoId = obtenerEstadoId($producto);
                        $imgUrl = normalizarImagenProducto($producto);
                        $categoriaNombre = obtenerCategoriaNombre($producto);
                        $subcategoriaNombre = obtenerSubcategoriaNombre($producto);
                        $estadoNombre = obtenerEstadoNombre($producto);
                        $precioFormateado = obtenerPrecioProducto($producto);
                        $disponibles = obtenerDisponibles($producto);
                        ?>
                        <div class="product-card">
                            <img
                                src="<?= htmlspecialchars($imgUrl) ?>"
                                alt="<?= htmlspecialchars($producto['nombre'] ?? 'Producto') ?>"
                                class="product-image"
                                onerror="this.onerror=null;this.src='<?= htmlspecialchars(getAbsoluteBaseUrl() . 'assets/images/default-product.jpg') ?>';"
                            >

                            <div class="product-body">
                                <h2 class="product-title">
                                    <?= htmlspecialchars($producto['nombre'] ?? 'Sin nombre') ?>
                                </h2>

                                <div class="product-price">$ <?= $precioFormateado ?></div>

                                <?php if ($categoriaNombre !== ''): ?>
                                    <div class="product-meta">
                                        <strong>Categoría:</strong>
                                        <?= htmlspecialchars($categoriaNombre) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($subcategoriaNombre !== ''): ?>
                                    <div class="product-meta">
                                        <strong>Subcategoría:</strong>
                                        <?= htmlspecialchars($subcategoriaNombre) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="product-meta">
                                    <strong>Disponibles:</strong>
                                    <?= $disponibles ?>
                                </div>

                                <?php if ($estadoNombre !== ''): ?>
                                    <div class="product-meta">
                                        <strong>Estado:</strong>
                                        <?= htmlspecialchars($estadoNombre) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="product-actions">
                                    <a href="producto.php?id=<?= $productoId ?>" class="btn-link btn-primary">
                                        Ver
                                    </a>

                                    <a href="editar_producto.php?id=<?= $productoId ?>" class="btn-link btn-secondary">
                                        Editar
                                    </a>

                                    <?php if ($estadoId === 1): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="producto_id" value="<?= $productoId ?>">
                                            <input type="hidden" name="accion" value="ocultar">
                                            <button type="submit" class="btn-action btn-secondary">
                                                Ocultar
                                            </button>
                                        </form>
                                    <?php elseif ($estadoId === 2): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="producto_id" value="<?= $productoId ?>">
                                            <input type="hidden" name="accion" value="activar">
                                            <button type="submit" class="btn-action btn-secondary">
                                                Activar
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                        <input type="hidden" name="producto_id" value="<?= $productoId ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <button type="submit" class="btn-action btn-danger">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
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

    <script src="<?= getAbsoluteBaseUrl() ?>script.js"></script>
</body>
</html>