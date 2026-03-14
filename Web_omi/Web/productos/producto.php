<?php

require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$producto_id = (int)$_GET['id'];
$user = getCurrentUser();
$producto = null;
$chat_existente = null;
$fotos = [];
$isFavorite = false;

if (defined('USE_LARAVEL_API') && USE_LARAVEL_API) {
    $raw = apiGetProducto($producto_id);
    if (!$raw) {
        header('Location: ../index.php');
        exit;
    }
    // Solo redirigir si no es activo Y el usuario no es el dueño
    $esDueño = $user && (($raw['vendedor_id'] ?? 0) == ($user['id'] ?? 0));
    if (($raw['estado_id'] ?? 1) != 1 && !$esDueño) {
        header('Location: ../index.php');
        exit;
    }
    $v = $raw['vendedor'] ?? [];
    $sc = $raw['subcategoria'] ?? [];
    $cat = $sc['categoria'] ?? [];
    $int = $raw['integridad'] ?? [];
    $producto = [
        'id' => $raw['id'],
        'nombre' => $raw['nombre'],
        'descripcion' => $raw['descripcion'],
        'precio' => $raw['precio'],
        'disponibles' => $raw['disponibles'],
        'fecha_registro' => $raw['fecha_registro'] ?? '',
        'vendedor_id' => $raw['vendedor_id'],
        'vendedor_nombre' => $v['nickname'] ?? '',
        'vendedor_desc' => $v['descripcion'] ?? '',
        'vendedor_imagen' => $v['imagen'] ?? '',
        'subcategoria_nombre' => $sc['nombre'] ?? '',
        'categoria_nombre' => $cat['nombre'] ?? '',
        'integridad_nombre' => $int['nombre'] ?? '',
        'integridad_desc' => $int['descripcion'] ?? ''
    ];
    $fotos = [];
    foreach ($raw['fotos'] ?? [] as $f) {
        $fotos[] = $f['url'] ?? $f['imagen'] ?? '';
    }
    $chats = apiGetChats();
    foreach (is_array($chats) ? $chats : [] as $c) {
        $pid = $c['producto_id'] ?? $c['producto']['id'] ?? 0;
        if ($pid == $producto_id) {
            $chat_existente = ['id' => $c['id'] ?? $c['chat_id'] ?? 0];
            break;
        }
    }
    $isFavorite = $user ? isSellerFavorite($user['id'], $producto['vendedor_id']) : false;
} else {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, u.nickname as vendedor_nombre, u.id as vendedor_id, u.descripcion as vendedor_desc, u.imagen as vendedor_imagen, sc.nombre as subcategoria_nombre, c.nombre as categoria_nombre, i.nombre as integridad_nombre, i.descripcion as integridad_desc FROM productos p INNER JOIN usuarios u ON p.vendedor_id = u.id INNER JOIN subcategorias sc ON p.subcategoria_id = sc.id INNER JOIN categorias c ON sc.categoria_id = c.id INNER JOIN integridad i ON p.integridad_id = i.id WHERE p.id = ? AND p.estado_id = 1");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$producto) {
        $conn->close();
        header('Location: ../index.php');
        exit;
    }
    if (isset($_POST['agregar_favorito'])) {
        $stmt = $conn->prepare("INSERT INTO favoritos (votante_id, votado_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $_SESSION['usuario_id'], $producto['vendedor_id']);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: ../perfil/favoritos.php");
            exit;
        }
        $stmt->close();
    }
    if ($user && $user['id'] != $producto['vendedor_id']) {
        $stmt = $conn->prepare("SELECT id FROM chats WHERE comprador_id = ? AND producto_id = ? AND estado_id = 1");
        $stmt->bind_param("ii", $user['id'], $producto_id);
        $stmt->execute();
        $chat_existente = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    $stmt = $conn->prepare("SELECT imagen FROM fotos WHERE producto_id = ? ORDER BY id ASC");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $fotos[] = $r['imagen'];
    $stmt->close();
    $conn->close();
    $isFavorite = $user ? isSellerFavorite($user['id'], $producto['vendedor_id']) : false;
}

$imagen_url = !empty($fotos) ? (strpos($fotos[0], 'http') === 0 ? $fotos[0] : '../uploads/productos/' . $fotos[0]) : "https://picsum.photos/seed/{$producto_id}/600/450";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
    </head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="product-detail">
                <div class="product-image-section">
                    <?php
                    $principal = $imagen_url;
                    $fotosForThumbs = $fotos;
                    ?>
                    <div class="product-gallery">
                        <div class="main-image-container">
                            <img src="<?= htmlspecialchars($principal) ?>" 
                                 alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                                 id="mainProductImage"
                                 class="product-detail-image"
                                 onerror="this.onerror=null; this.src='https://picsum.photos/seed/error/600/450?blur=5'">

                        </div>
                        
                        <?php if (count($fotosForThumbs) > 1): ?>
                            <div class="thumbnails-grid">
                                <?php foreach ($fotosForThumbs as $index => $fotoItem): 
                                    $thumbUrl = (strpos($fotoItem, 'http') === 0) ? $fotoItem : '../uploads/productos/' . $fotoItem;
                                ?>
                                    <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                         onclick="changeMainImage('<?= htmlspecialchars($thumbUrl) ?>', this)">
                                        <img src="<?= htmlspecialchars($thumbUrl) ?>" 
                                             alt="Miniatura <?= $index + 1 ?>"
                                             onerror="this.onerror=null; this.src='https://picsum.photos/seed/error/100/100?blur=5'">

                                    </div>
                                <?php endforeach; ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="product-detail-info">
                    <h1 class="product-detail-title"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                    <p class="product-detail-price"><?php echo formatPrice($producto['precio']); ?></p>
                    
                    <div class="product-meta">
                        <p><strong>Categoría:</strong> <?php echo htmlspecialchars($producto['categoria_nombre']); ?> - 
                            <?php echo htmlspecialchars($producto['subcategoria_nombre']); ?></p>
                        <p><strong>condición:</strong> <?php echo htmlspecialchars($producto['integridad_nombre']); ?></p>
                        <p><strong>Disponibles:</strong> <?php echo $producto['disponibles']; ?></p>
                        <p><strong>Publicado:</strong> <?php echo date('d/m/Y', strtotime($producto['fecha_registro'])); ?></p>
                    </div>
                    
                    <div class="product-description">
                        <h3>Descripción</h3>
                        <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                    </div>
                    
                    <div class="seller-info">
                        <h3>Vendedor</h3>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <img src="<?= getAvatarUrl($producto['vendedor_imagen']) ?>" 
                                 alt="<?= htmlspecialchars($producto['vendedor_nombre']) ?>"
                                 style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-primary);">
                            <div>
                                <p style="margin: 0;"><strong><a href="../perfil/vendedor.php?id=<?php echo $producto['vendedor_id']; ?>"><?php echo htmlspecialchars($producto['vendedor_nombre']); ?></a></strong></p>
                                <?php if ($producto['vendedor_desc']): ?>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: var(--color-text-light);"><?php echo htmlspecialchars($producto['vendedor_desc']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="../perfil/vendedor.php?id=<?php echo $producto['vendedor_id']; ?>" class="btn-small">Ver perfil del vendedor</a>
                    </div>

                    
                    <div class="product-actions">
                        
                        <?php if ($user['id'] == $producto['vendedor_id']): ?>
                            <a href="../productos/editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn-secondary">Editar Producto</a>
                            
                            <a href="../productos/eliminar_producto.php?id=<?php echo $producto['id']; ?>" 
                               class="btn-secondary"
                               onclick="return confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.');">
                               Eliminar Producto
                            </a>
                            
                        <?php else: ?>
                        <?php if ($user['id'] != $producto['vendedor_id']): ?>
                            <button type="button" 
                                id="btnFavorito"
                                data-vendedor-id="<?php echo $producto['vendedor_id']; ?>"
                                class="btn-favorite <?php echo $isFavorite ? 'active' : ''; ?>"
                                title="<?php echo $isFavorite ? 'Quitar de Favoritos' : 'Añadir a Favoritos'; ?>"
                                onclick="toggleFavorito(this)">
                                <i class="fav-icon <?php echo $isFavorite ? 'ri-heart-3-fill' : 'ri-heart-3-line'; ?>"></i>
                                <span class="fav-text"><?php echo $isFavorite ? 'En Favoritos' : 'Añadir a Favoritos'; ?></span>
                            </button>
                            
                            <!-- Botón Bloquear Usuario (RF09-001) -->
                            <button type="button" 
                                id="btnBloquear"
                                data-usuario-id="<?php echo $producto['vendedor_id']; ?>"
                                class="btn-small btn-danger"
                                title="Bloquear a este usuario"
                                onclick="toggleBloqueo(<?php echo $producto['vendedor_id']; ?>)">
                                <i class="ri-forbid-line"></i> Bloquear
                            </button>
                            
                            <!-- Botón Reportar Producto -->
                            <button type="button" 
                                id="btnReportar"
                                class="btn-small btn-warning"
                                title="Reportar este producto"
                                onclick="abrirModalReporte(<?php echo $producto['id']; ?>, <?php echo (int)($producto['vendedor_id'] ?? 0); ?>)">
                                <i class="ri-flag-line"></i> Reportar
                            </button>
                        <?php endif; ?>
                            <?php if ($chat_existente): ?>
                                <a href="../chat/chat.php?id=<?php echo $chat_existente['id']; ?>" class="btn-primary">Ver Conversación</a>
                            <?php else: ?>
                                <a href="../chat/contactar.php?producto_id=<?php echo $producto['id']; ?>" class="btn-primary">Contactar Vendedor</a>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <!-- Modal Reportar Producto -->
    <div id="modalReporte" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-reporte">
            <div class="modal-header">
                <h3>🚩 Reportar Producto</h3>
                <button type="button" class="modal-close" onclick="cerrarModalReporte()">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Por qué quieres reportar este producto?</p>
                <input type="hidden" id="reporteProductoId" value="">
                <input type="hidden" id="reporteUsuarioId" value="">
                
                <div class="reporte-opciones">
                    <label class="reporte-opcion">
                        <input type="radio" name="motivo_reporte" value="1">
                        <span class="opcion-content">
                            <i class="ri-spam-line"></i>
                            <strong>Producto prohibido</strong>
                            <small>Armas, drogas, artículos ilegales</small>
                        </span>
                    </label>
                    
                    <label class="reporte-opcion">
                        <input type="radio" name="motivo_reporte" value="2">
                        <span class="opcion-content">
                            <i class="ri-money-dollar-circle-line"></i>
                            <strong>Precio falso o engañoso</strong>
                            <small>El precio no corresponde a la realidad</small>
                        </span>
                    </label>
                    
                    <label class="reporte-opcion">
                        <input type="radio" name="motivo_reporte" value="3">
                        <span class="opcion-content">
                            <i class="ri-file-warning-line"></i>
                            <strong>Descripción engañosa</strong>
                            <small>Información falsa sobre el producto</small>
                        </span>
                    </label>
                    
                    <label class="reporte-opcion">
                        <input type="radio" name="motivo_reporte" value="4">
                        <span class="opcion-content">
                            <i class="ri-image-line"></i>
                            <strong>Imágenes inapropiadas</strong>

                            <small>Contenido ofensivo o engañoso</small>

                        </span>
                    </label>
                    
                    <label class="reporte-opcion">
                        <input type="radio" name="motivo_reporte" value="5">
                        <span class="opcion-content">
                            <i class="ri-error-warning-line"></i>
                            <strong>Posible estafa</strong>
                            <small>Sospecho que es fraudulento</small>
                        </span>
                    </label>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="comentarioReporte">Comentario adicional (opcional)</label>
                    <textarea id="comentarioReporte" rows="3" maxlength="300" 
                              placeholder="Describe el problema con Más detalle..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModalReporte()">Cancelar</button>
                <button type="button" class="btn-danger" onclick="enviarReporte()">
                    <i class="ri-send-plane-line"></i> Enviar Reporte
                </button>
            </div>
        </div>
    </div>
    
    <style>
        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border: none;
        }
        .btn-warning:hover {
            background: linear-gradient(135deg, #e67e22, #d35400);
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-content {
            background: var(--color-bg);
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 {
            margin: 0;
            color: var(--color-primary);
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--color-text-light);
        }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        .reporte-opciones {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .reporte-opcion {
            display: block;
            cursor: pointer;
        }
        .reporte-opcion input {
            display: none;
        }
        .reporte-opcion .opcion-content {
            display: flex;
            flex-direction: column;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .reporte-opcion .opcion-content i {
            font-size: 1.5rem;
            color: var(--color-primary);
            margin-bottom: 0.25rem;
        }
        .reporte-opcion .opcion-content strong {
            color: var(--color-text);
        }
        .reporte-opcion .opcion-content small {
            color: var(--color-text-light);
            font-size: 0.85rem;
        }
        .reporte-opcion input:checked + .opcion-content {
            border-color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
        }
        .reporte-opcion:hover .opcion-content {
            border-color: var(--color-primary);
        }
    </style>
    
    <script>
        // Variable global para rutas de API
        window.BASE_URL = '<?= getBaseUrl() ?>';
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js?v=<?= time(); ?>"></script>

</body>
</html>


