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

$producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($producto_id <= 0) {
    header('Location: mis_productos.php');
    exit;
}

function apiBaseHost() {
    if (!defined('API_BASE_URL')) {
        return '';
    }
    return preg_replace('#/api/?$#', '', rtrim(API_BASE_URL, '/'));
}

function normalizarImagenProducto($producto) {
    $apiHost = apiBaseHost();

    if (empty($producto['fotos']) || !is_array($producto['fotos'])) {
        return '';
    }

    $foto = $producto['fotos'][0] ?? null;
    if (!$foto || !is_array($foto)) {
        return '';
    }

    $url = $foto['url'] ?? $foto['imagen_url'] ?? $foto['path'] ?? $foto['imagen'] ?? null;
    if (!$url || !is_string($url)) {
        return '';
    }

    $url = trim($url);
    if ($url === '') {
        return '';
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

function apiGetEstadosEditarProducto() {
    return [
        ['id' => 1, 'nombre' => 'activo'],
        ['id' => 2, 'nombre' => 'invisible'],
        ['id' => 3, 'nombre' => 'agotado'],
    ];
}

$error = '';
$success = '';

/* ===============================
   1. Obtener producto vía API
================================= */
$producto = apiGetProducto($producto_id);

if (!$producto) {
    header('Location: mis_productos.php');
    exit;
}

/* ===============================
   2. Verificar que el producto es del usuario
================================= */
$vendedorId = (int)($producto['vendedor_id'] ?? $producto['vendedor']['id'] ?? 0);
$userId = (int)($user['id'] ?? 0);

if ($vendedorId !== $userId) {
    header('Location: mis_productos.php');
    exit;
}

/* ===============================
   3. Obtener imagen actual del producto
================================= */
$foto_actual = normalizarImagenProducto($producto);

/* ===============================
   4. Obtener categorías, subcategorías, integridad
================================= */
$categorias = apiGetCategorias();
$integridad_list = apiGetIntegridad();
$estados_list = apiGetEstadosEditarProducto();

$subcategorias = [];
foreach (is_array($categorias) ? $categorias : [] as $cat) {
    $subs = isset($cat['subcategorias']) && is_array($cat['subcategorias'])
        ? $cat['subcategorias']
        : [];

    foreach ($subs as $sub) {
        $sub['categoria_nombre'] = $cat['nombre'] ?? '';
        $subcategorias[] = $sub;
    }
}

/* ===============================
   5. Procesar el formulario POST
================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre'] ?? '');
    $descripcion = sanitize($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $disponibles = intval($_POST['disponibles'] ?? 1);
    $subcategoria_id = intval($_POST['subcategoria_id'] ?? 0);
    $integridad_id = intval($_POST['integridad_id'] ?? 1);
    $estado_id = intval($_POST['estado_id'] ?? 1);

    if (empty($nombre) || empty($descripcion) || $precio <= 0 || $subcategoria_id <= 0) {
        $error = 'Por favor completa todos los campos correctamente';
    } else {
        $payload = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'disponibles' => $disponibles,
            'subcategoria_id' => $subcategoria_id,
            'integridad_id' => $integridad_id,
            'estado_id' => $estado_id,
        ];

        $imagenesParaEnviar = null;

        if (isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $mime = mime_content_type($_FILES['imagen']['tmp_name']);
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];

            if (!in_array($mime, $allowed, true)) {
                $error = 'Formato de imagen no permitido';
            } else {
                $imagenesParaEnviar = [
                    'name' => [$_FILES['imagen']['name']],
                    'type' => [$_FILES['imagen']['type']],
                    'tmp_name' => [$_FILES['imagen']['tmp_name']],
                    'error' => [$_FILES['imagen']['error']],
                    'size' => [$_FILES['imagen']['size']],
                ];
            }
        }

        if ($error === '') {
            $response = apiActualizarProducto($producto_id, $payload, $imagenesParaEnviar);

            if (!empty($response['success'])) {
                $success = 'Producto actualizado exitosamente';

                $producto = apiGetProducto($producto_id);
                if ($producto) {
                    $foto_actual = normalizarImagenProducto($producto);
                }
            } else {
                $apiMessage = $response['message'] ?? '';
                $apiErrors = $response['errors'] ?? null;

                if (is_array($apiErrors) && !empty($apiErrors)) {
                    $mensajes = [];
                    foreach ($apiErrors as $campo => $erroresCampo) {
                        if (is_array($erroresCampo)) {
                            foreach ($erroresCampo as $msg) {
                                $mensajes[] = $msg;
                            }
                        } elseif (is_string($erroresCampo)) {
                            $mensajes[] = $erroresCampo;
                        }
                    }

                    $error = !empty($mensajes)
                        ? implode('<br>', array_map('htmlspecialchars', $mensajes))
                        : 'No se pudo actualizar el producto.';
                } else {
                    $error = $apiMessage !== ''
                        ? htmlspecialchars($apiMessage)
                        : 'No se pudo actualizar el producto.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="form-container">
                <h1>Editar Producto</h1>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="editar_producto.php?id=<?php echo $producto_id; ?>" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="<?php echo htmlspecialchars($producto['nombre'] ?? ''); ?>"
                            required
                            maxlength="64"
                        >
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción *</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="5"
                            required
                            maxlength="512"
                        ><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio">Precio (COP) *</label>
                            <input
                                type="number"
                                id="precio"
                                name="precio"
                                step="0.01"
                                min="0"
                                value="<?php echo htmlspecialchars((string)($producto['precio'] ?? '0')); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="disponibles">Cantidad Disponible *</label>
                            <input
                                type="number"
                                id="disponibles"
                                name="disponibles"
                                min="1"
                                value="<?php echo htmlspecialchars((string)($producto['disponibles'] ?? '1')); ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subcategoria_id">Categoría *</label>
                        <select id="subcategoria_id" name="subcategoria_id" required>
                            <option value="">Selecciona una categoría</option>
                            <?php
                            $current_categoria = '';
                            foreach ($subcategorias as $subcat):
                                if ($current_categoria != ($subcat['categoria_nombre'] ?? '')):
                                    if ($current_categoria != '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($subcat['categoria_nombre'] ?? '') . '">';
                                    $current_categoria = $subcat['categoria_nombre'] ?? '';
                                endif;
                            ?>
                                <option
                                    value="<?php echo $subcat['id']; ?>"
                                    <?php echo ((int)($producto['subcategoria_id'] ?? $producto['subcategoria']['id'] ?? 0) === (int)$subcat['id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($subcat['nombre'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_categoria != '') echo '</optgroup>'; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="integridad_id">Condición *</label>
                        <select id="integridad_id" name="integridad_id" required>
                            <?php foreach (is_array($integridad_list) ? $integridad_list : [] as $int): ?>
                                <option
                                    value="<?php echo $int['id']; ?>"
                                    <?php echo ((int)($producto['integridad_id'] ?? $producto['integridad']['id'] ?? 0) === (int)$int['id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($int['nombre'] ?? ''); ?> -
                                    <?php echo htmlspecialchars($int['descripcion'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estado_id">Estado *</label>
                        <select id="estado_id" name="estado_id" required>
                            <?php foreach ($estados_list as $estado): ?>
                                <option
                                    value="<?php echo $estado['id']; ?>"
                                    <?php echo ((int)($producto['estado_id'] ?? $producto['estado']['id'] ?? 0) === (int)$estado['id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($estado['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="imagen">Nueva Imagen del Producto (opcional)</label>
                        <?php if (!empty($foto_actual)): ?>
                            <p>Imagen actual:</p>
                            <img
                                src="<?= htmlspecialchars($foto_actual) ?>"
                                style="max-width:200px;"
                                alt="Imagen actual del producto"
                            >
                        <?php endif; ?>
                        <input
                            type="file"
                            id="imagen"
                            name="imagen"
                            accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif"
                        >
                        <small>Formatos aceptados: JPG, PNG, GIF, WEBP, AVIF. Deja vacío para mantener la imagen actual.</small>
                    </div>

                    <button type="submit" class="btn-primary">Guardar Cambios</button>
                    <a href="mis_productos.php" class="btn-secondary">Cancelar</a>
                </form>
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