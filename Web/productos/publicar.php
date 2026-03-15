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
if (isset($user['estado_id']) && $user['estado_id'] != 1) {
    header("Location: bloqueado.php");
    exit;
}

$error = '';
$success = '';
$subcategorias = [];
$integridad_list = [];

$debug_cat = null;
$debug_int = null;
if (defined('USE_LARAVEL_API') && USE_LARAVEL_API) {
    // Una sola llamada: GET /categorias (no se usa /subcategorias).
    // Consumo: recibes categorias → foreach categorias → dentro foreach categoria.subcategorias
    $categorias = apiGetCategorias();
    foreach (is_array($categorias) ? $categorias : [] as $cat) {
        $subs = isset($cat['subcategorias']) && is_array($cat['subcategorias']) ? $cat['subcategorias'] : [];
        foreach ($subs as $sub) {
            $sub['categoria_nombre'] = $cat['nombre'] ?? '';
            $subcategorias[] = $sub;
        }
    }
    $integridad_list = apiGetIntegridad();
    if (!is_array($integridad_list)) $integridad_list = [];
    if (!empty($_GET['debug_cat'])) {
        $debug_cat = apiRequest('/categorias', 'GET', [], getToken());
        $debug_int = apiRequest('/integridades', 'GET', [], getToken());
    }
} else {
    $conn = getDBConnection();
    $subcategorias_query = "SELECT sc.*, c.nombre as categoria_nombre FROM subcategorias sc 
                           INNER JOIN categorias c ON sc.categoria_id = c.id ORDER BY c.nombre, sc.nombre";
    $subcategorias_result = $conn->query($subcategorias_query);
    while ($row = $subcategorias_result->fetch_assoc()) $subcategorias[] = $row;
    $integridad_result = $conn->query("SELECT * FROM integridad ORDER BY id");
    while ($row = $integridad_result->fetch_assoc()) $integridad_list[] = $row;
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre'] ?? '');
    $descripcion = sanitize($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $disponibles = intval($_POST['disponibles'] ?? 1);
    $subcategoria_id = intval($_POST['subcategoria_id'] ?? 0);
    $integridad_id = intval($_POST['integridad_id'] ?? 1);
    
    if (empty($nombre) || empty($descripcion) || $precio <= 0 || $subcategoria_id <= 0) {
        $error = 'Por favor completa todos los campos correctamente';
    } else {
        // Crear producto vía PHP/MySQL (sin tocar API). vendedor_id desde sesión (usuario_id correcto).
        $conn = getDBConnection();
        $vendedor_id = $user['id'] ?? 0;
        $estado_id = 1;
        $stmt = $conn->prepare("INSERT INTO productos (nombre, subcategoria_id, integridad_id, vendedor_id, estado_id, descripcion, precio, disponibles) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiiisdi", $nombre, $subcategoria_id, $integridad_id, $vendedor_id, $estado_id, $descripcion, $precio, $disponibles);
        if ($stmt->execute()) {
            $producto_id = $conn->insert_id;
            $stmt->close();
            if (!empty($_FILES['imagenes']['name'][0])) {
                $total_imagenes = min(count($_FILES['imagenes']['name']), 5);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/avif', 'image/webp'];
                for ($i = 0; $i < $total_imagenes; $i++) {
                    if (($_FILES['imagenes']['error'][$i] ?? 0) === UPLOAD_ERR_OK && in_array($_FILES['imagenes']['type'][$i] ?? '', $allowedTypes)) {
                        $ext = pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION);
                        $nombreArchivo = uniqid("img_") . "_" . $i . "." . $ext;
                        if (move_uploaded_file($_FILES['imagenes']['tmp_name'][$i], __DIR__ . "/../uploads/productos/" . $nombreArchivo)) {
                            $stmtImg = $conn->prepare("INSERT INTO fotos (producto_id, imagen) VALUES (?, ?)");
                            $stmtImg->bind_param("is", $producto_id, $nombreArchivo);
                            $stmtImg->execute();
                            $stmtImg->close();
                        }
                    }
                }
            }
            $conn->close();
            header('Location: producto.php?id=' . $producto_id);
            exit;
        }
        $error = 'Error al publicar producto: ' . $conn->error;
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Producto - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="form-container">
                <h1>Publicar Nuevo Producto</h1>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="publicar.php" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" required maxlength="64">
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción *</label>
                        <textarea id="descripcion" name="descripcion" rows="5" required maxlength="512"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio">Precio (COP) *</label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="disponibles">Cantidad Disponible *</label>
                            <input type="number" id="disponibles" name="disponibles" min="1" value="1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subcategoria_id">Categoría *</label>
                        <select id="subcategoria_id" name="subcategoria_id" required>
                            <option value="">Selecciona una Categoría</option>
                            <?php
                            $current_categoria = '';
                            foreach ($subcategorias as $subcat):
                                if ($current_categoria != $subcat['categoria_nombre']):
                                    if ($current_categoria != '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($subcat['categoria_nombre']) . '">';
                                    $current_categoria = $subcat['categoria_nombre'];
                                endif;
                            ?>
                                <option value="<?php echo $subcat['id']; ?>">
                                    <?php echo htmlspecialchars($subcat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_categoria != '') echo '</optgroup>'; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="integridad_id">Condición *</label>
                        <select id="integridad_id" name="integridad_id" required>
                            <option value="">Selecciona una Condición</option>
                            <?php foreach ($integridad_list as $int): ?>
                                <option value="<?php echo $int['id']; ?>">
                                    <?php echo htmlspecialchars($int['nombre']); ?> - 
                                    <?php echo htmlspecialchars($int['descripcion'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagenes">Imágenes del Producto (mínimo 1, máximo 5) *</label>
                        <div class="multiple-images-upload">
                            <label for="imagenes" class="upload-area" id="dropArea">
                                <div class="upload-icon">📸</div>
                                <div class="upload-text">Haz clic o arrastra las imágenes aquí</div>
                                <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/avif,image/webp" multiple required>
                            </label>
                            <div id="previsualizaciones" class="previsualizaciones-grid"></div>
                        </div>
                        <small>Formatos aceptados: JPG, PNG, GIF, AVIF, WEBP. Se recomienda un tamaño cuadrado.</small>
                    </div>
                    
                    <button type="submit" class="btn-primary">Publicar Producto</button>
                    <a href="../index.php" class="btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>
    <?php if ($debug_cat !== null): ?>
    <div style="margin:20px;padding:15px;background:#f5f5f5;border:1px solid #ccc;font-family:monospace;font-size:12px;">
        <strong>Debug API (categorías / condición)</strong> — Añade <code>?debug_cat=1</code> a la URL para ver esto.<br>
        Token en sesión: <?= function_exists('getToken') && getToken() ? 'Sí' : 'No' ?>.<br>
        <strong>GET /categorias</strong>: success=<?= isset($debug_cat['success']) ? ($debug_cat['success'] ? 'true' : 'false') : '?' ?>, http_code=<?= $debug_cat['http_code'] ?? '?' ?><br>
        <pre><?= htmlspecialchars(json_encode($debug_cat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <strong>GET /integridad</strong>: success=<?= isset($debug_int['success']) ? ($debug_int['success'] ? 'true' : 'false') : '?' ?>, http_code=<?= $debug_int['http_code'] ?? '?' ?><br>
        <pre><?= htmlspecialchars(json_encode($debug_int, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    </div>
    <?php endif; ?>
    <script>
        window.BASE_URL = '<?= getBaseUrl() ?>';
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js"></script>
</body>
</html>



