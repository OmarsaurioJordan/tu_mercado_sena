<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

function debugLog($label, $data) {
    echo "<div style='background:#111;color:#0f0;padding:10px;margin:10px;font-family:monospace;font-size:12px'>";
    echo "<strong>" . htmlspecialchars((string)$label) . "</strong><br>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo "</div>";
}

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$user = $user ?? [];

if (!empty($_GET['debug_api'])) {
    debugLog("USE_LARAVEL_API", defined('USE_LARAVEL_API') ? USE_LARAVEL_API : 'NO DEFINIDA');
    debugLog("API_BASE_URL", defined('API_BASE_URL') ? API_BASE_URL : 'NO DEFINIDA');
    debugLog("Token", getToken());
    debugLog("Usuario sesión", $user);
}

if (isset($user['estado_id']) && (int)$user['estado_id'] !== 1) {
    header("Location: bloqueado.php");
    exit;
}

$error = '';
$success = '';
$subcategorias = [];
$integridad_list = [];
$debug_post = null;

$formData = [
    'nombre' => '',
    'descripcion' => '',
    'precio' => '',
    'disponibles' => '1',
    'subcategoria_id' => '',
    'integridad_id' => '1',
];


/* =============================
   Cargar catálogos desde API
============================= */
if (defined('USE_LARAVEL_API') && USE_LARAVEL_API) {
    if (!empty($_GET['debug_api'])) {
        $rawCategorias = apiRequest('/categorias', 'GET', [], getToken());
        debugLog("RAW API /categorias", $rawCategorias);

        $rawIntegridades = apiRequest('/integridades', 'GET');
        debugLog("RAW API /integridades", $rawIntegridades);
    }

    $categoriasResponse = apiGetCategorias();

    if (!empty($_GET['debug_api'])) {
        debugLog("Categorias recibidas RAW", $categoriasResponse);
    }

    if (is_array($categoriasResponse)) {
        if (isset($categoriasResponse['data']) && is_array($categoriasResponse['data'])) {
            $categorias = $categoriasResponse['data'];

            if (isset($categorias['items']) && is_array($categorias['items'])) {
                $categorias = $categorias['items'];
            }
        } else {
            $categorias = $categoriasResponse;
        }
    } else {
        $categorias = [];
    }

    if (!empty($_GET['debug_api'])) {
        debugLog("Categorias normalizadas", $categorias);
    }

    $subcategorias = [];

    foreach ($categorias as $cat) {
        $subs = [];

        if (isset($cat['subcategorias']) && is_array($cat['subcategorias'])) {
            $subs = $cat['subcategorias'];
        } elseif (isset($cat['subcategoria']) && is_array($cat['subcategoria'])) {
            $subs = $cat['subcategoria'];
        } elseif (isset($cat['children']) && is_array($cat['children'])) {
            $subs = $cat['children'];
        }

        foreach ($subs as $sub) {
            $subcategorias[] = [
                'id' => $sub['id'] ?? '',
                'nombre' => $sub['nombre'] ?? ($sub['descripcion'] ?? 'Sin nombre'),
                'categoria_nombre' => $cat['nombre'] ?? ($cat['descripcion'] ?? 'Sin categoría'),
            ];
        }
    }

    if (!empty($_GET['debug_api'])) {
        debugLog("Subcategorias procesadas", $subcategorias);
    }

    $integridad_list = apiGetIntegridad();

    if (!empty($_GET['debug_api'])) {
        debugLog("Integridades recibidas", $integridad_list);
    }

    if (!is_array($integridad_list)) {
        $integridad_list = [];
    }
} else {
    $error = 'La publicación web ahora requiere USE_LARAVEL_API habilitado.';
}

/* =============================
   Procesar POST y publicar vía API
============================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['nombre'] = trim($_POST['nombre'] ?? '');
    $formData['descripcion'] = trim($_POST['descripcion'] ?? '');
    $formData['precio'] = trim($_POST['precio'] ?? '');
    $formData['disponibles'] = trim($_POST['disponibles'] ?? '1');
    $formData['subcategoria_id'] = trim($_POST['subcategoria_id'] ?? '');
    $formData['integridad_id'] = trim($_POST['integridad_id'] ?? '1');

    $nombre = sanitize($formData['nombre']);
    $descripcion = sanitize($formData['descripcion']);
    $precio = (float)$formData['precio'];
    $disponibles = (int)$formData['disponibles'];
    $subcategoria_id = (int)$formData['subcategoria_id'];
    $integridad_id = (int)$formData['integridad_id'];

    if (!empty($_GET['debug_api'])) {
        debugLog("POST _POST", $_POST);
        debugLog("POST _FILES", $_FILES);
        debugLog("Payload parseado", [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'disponibles' => $disponibles,
            'subcategoria_id' => $subcategoria_id,
            'integridad_id' => $integridad_id,
        ]);
    }

    if (
        $nombre === '' ||
        $descripcion === '' ||
        $precio <= 0 ||
        $disponibles <= 0 ||
        $subcategoria_id <= 0 ||
        $integridad_id <= 0
    ) {
        $error = 'Por favor completa todos los campos correctamente.';
    } elseif (empty($_FILES['imagenes']['name'][0])) {
        $error = 'Debes subir al menos una imagen del producto.';
    } else {
        $totalImagenes = count($_FILES['imagenes']['name']);

        if ($totalImagenes > 5) {
            $error = 'Máximo 5 imágenes.';
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/avif', 'image/webp'];

            for ($i = 0; $i < $totalImagenes; $i++) {
                $err = $_FILES['imagenes']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                $type = $_FILES['imagenes']['type'][$i] ?? '';

                if ($err !== UPLOAD_ERR_OK) {
                    $error = 'Una de las imágenes no se pudo cargar correctamente.';
                    break;
                }

                if (!in_array($type, $allowedTypes, true)) {
                    $error = 'Solo se permiten imágenes JPG, PNG, GIF, AVIF o WEBP.';
                    break;
                }
            }
        }
    }

    if ($error === '') {
        $payload = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'disponibles' => $disponibles,
            'subcategoria_id' => $subcategoria_id,
            'integridad_id' => $integridad_id,
        ];

        if (!empty($_GET['debug_api'])) {
            debugLog("Payload enviado a apiCrearProducto", $payload);
        }

        $response = apiCrearProducto($payload, $_FILES['imagenes'] ?? null);

        if (!empty($_GET['debug_api'])) {
            debugLog("Respuesta apiCrearProducto", $response);
        }

        if (!empty($_GET['debug_post'])) {
            $debug_post = $response;
        }

        $ok = isset($response['success']) && $response['success'] === true;
        $body = $response['data'] ?? [];
        $productoId = $body['data']['id'] ?? $body['id'] ?? null;

        if (!empty($_GET['debug_api'])) {
            debugLog("Producto ID detectado", $productoId);
        }

        if ($ok && $productoId) {
            header('Location: producto.php?id=' . urlencode((string)$productoId));
            exit;
        }

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
                : 'No se pudo publicar el producto.';
        } else {
            $error = $apiMessage !== ''
                ? htmlspecialchars($apiMessage)
                : 'No se pudo publicar el producto usando la API.';
        }

        if (!empty($_GET['debug_api'])) {
            debugLog("Error final mostrado", $error);
        }
    } else {
        if (!empty($_GET['debug_api'])) {
            debugLog("Error de validación antes de API", $error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Producto - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getAbsoluteBaseUrl() ?>styles.css?v=<?= time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="form-container">
                <h1>Publicar Nuevo Producto</h1>

                <?php if ($error): ?>
                    <div class="error-message"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="publicar.php<?= !empty($_GET['debug_api']) ? '?debug_api=1' : '' ?>" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            required
                            maxlength="64"
                            value="<?= htmlspecialchars($formData['nombre']) ?>"
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
                        ><?= htmlspecialchars($formData['descripcion']) ?></textarea>
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
                                required
                                value="<?= htmlspecialchars($formData['precio']) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="disponibles">Cantidad Disponible *</label>
                            <input
                                type="number"
                                id="disponibles"
                                name="disponibles"
                                min="1"
                                required
                                value="<?= htmlspecialchars($formData['disponibles']) ?>"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subcategoria_id">Categoría *</label>
                        <select id="subcategoria_id" name="subcategoria_id" required>
                            <option value="">Selecciona una Categoría</option>
                            <?php
                            $current_categoria = '';
                            foreach ($subcategorias as $subcat):
                                if ($current_categoria !== ($subcat['categoria_nombre'] ?? '')):
                                    if ($current_categoria !== '') echo '</optgroup>';
                                    $current_categoria = $subcat['categoria_nombre'] ?? '';
                                    echo '<optgroup label="' . htmlspecialchars($current_categoria) . '">';
                                endif;
                            ?>
                                <option
                                    value="<?= htmlspecialchars((string)$subcat['id']) ?>"
                                    <?= ((string)$formData['subcategoria_id'] === (string)$subcat['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($subcat['nombre'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_categoria !== '') echo '</optgroup>'; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="integridad_id">Condición *</label>
                        <select id="integridad_id" name="integridad_id" required>
                            <option value="">Selecciona una Condición</option>
                            <?php foreach ($integridad_list as $int): ?>
                                <option
                                    value="<?= htmlspecialchars((string)$int['id']) ?>"
                                    <?= ((string)$formData['integridad_id'] === (string)$int['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($int['nombre'] ?? '') ?> -
                                    <?= htmlspecialchars($int['descripcion'] ?? '') ?>
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
                                <input
                                    type="file"
                                    id="imagenes"
                                    name="imagenes[]"
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/avif,image/webp"
                                    multiple
                                >
                            </label>
                            <div id="previsualizaciones" class="previsualizaciones-grid"></div>
                        </div>
                        <small>Formatos aceptados: JPG, PNG, GIF, AVIF, WEBP. Máximo 5 imágenes.</small>
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

    <?php if ($debug_post !== null): ?>
    <div style="margin:20px;padding:15px;background:#eef6ff;border:1px solid #99c2ff;font-family:monospace;font-size:12px;">
        <strong>Debug POST /productos</strong><br>
        <pre><?= htmlspecialchars(json_encode($debug_post, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    </div>
    <?php endif; ?>

    <script>
        window.BASE_URL = '<?= getAbsoluteBaseUrl() ?>';

        document.querySelector('.product-form').addEventListener('submit', function(e) {
            var input = document.getElementById('imagenes');

            if (!input || !input.files || input.files.length === 0) {
                e.preventDefault();
                alert('Debes subir al menos una imagen del producto.');
                var area = document.getElementById('dropArea');
                if (area) area.style.outline = '2px solid #c00';
                return false;
            }

            if (input.files.length > 5) {
                e.preventDefault();
                alert('Máximo 5 imágenes.');
                return false;
            }
        });
    </script>

    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getAbsoluteBaseUrl() ?>script.js"></script>
</body>
</html>