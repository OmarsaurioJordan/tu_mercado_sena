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

$producto_id = (int) $_GET['id'];
$user = getCurrentUser();
$producto = null;
$chat_existente = null;
$fotos = [];
$isFavorite = false;

// Solo API (tumercadosena.shop); sin SQL
$raw = apiGetProducto($producto_id);
if (!$raw) {
    header('Location: ../index.php');
    exit;
}

$esDueno = $user && (($raw['vendedor_id'] ?? 0) == ($user['id'] ?? 0));
if (($raw['estado_id'] ?? 1) != 1 && !$esDueno) {
    header('Location: ../index.php');
    exit;
}

$v = $raw['vendedor'] ?? [];
$sc = $raw['subcategoria'] ?? [];
$cat = $sc['categoria'] ?? [];
$int = $raw['integridad'] ?? [];

$producto = [
    'id' => $raw['id'] ?? 0,
    'nombre' => $raw['nombre'] ?? '',
    'descripcion' => $raw['descripcion'] ?? '',
    'precio' => $raw['precio'] ?? 0,
    'disponibles' => $raw['disponibles'] ?? 0,
    'fecha_registro' => $raw['fecha_registro'] ?? '',
    'vendedor_id' => $raw['vendedor_id'] ?? 0,
    'vendedor_nombre' => $v['nickname'] ?? '',
    'vendedor_desc' => $v['descripcion'] ?? '',
    'vendedor_imagen' => $v['imagen'] ?? '',
    'subcategoria_nombre' => $sc['nombre'] ?? '',
    'categoria_nombre' => $cat['nombre'] ?? '',
    'integridad_nombre' => $int['nombre'] ?? '',
    'integridad_desc' => $int['descripcion'] ?? '',
];

foreach ($raw['fotos'] ?? [] as $f) {
    $fotos[] = $f['url'] ?? $f['imagen'] ?? '';
}

$chats = apiGetChats();
foreach (is_array($chats) ? $chats : [] as $c) {
    $pid = $c['producto_id'] ?? $c['producto']['id'] ?? 0;
    if ((int) $pid === (int) $producto_id) {
        $chat_existente = [
            'id' => $c['id'] ?? $c['chat_id'] ?? 0,
        ];
        break;
    }
}

$isFavorite = $user ? isSellerFavorite($user['id'], $producto['vendedor_id']) : false;

$imagen_url = !empty($fotos)
    ? (strpos($fotos[0], 'http') === 0 ? $fotos[0] : '../uploads/productos/' . $fotos[0])
    : "https://picsum.photos/seed/{$producto_id}/600/450";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto['nombre']); ?> - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getAbsoluteBaseUrl() ?>styles.css?v=<?= time(); ?>">
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
                            <img
                                src="<?= htmlspecialchars($principal); ?>"
                                alt="<?= htmlspecialchars($producto['nombre']); ?>"
                                id="mainProductImage"
                                class="product-detail-image"
                                onerror="this.onerror=null; this.src='https://picsum.photos/seed/error/600/450?blur=5'"
                            >
                        </div>

                        <?php if (count($fotosForThumbs) > 1): ?>
                            <div class="thumbnails-grid">
                                <?php foreach ($fotosForThumbs as $index => $fotoItem): ?>
                                    <?php
                                    $thumbUrl = (strpos($fotoItem, 'http') === 0)
                                        ? $fotoItem
                                        : '../uploads/productos/' . $fotoItem;
                                    ?>
                                    <div
                                        class="thumbnail <?= $index === 0 ? 'active' : ''; ?>"
                                        onclick="changeMainImage('<?= htmlspecialchars($thumbUrl, ENT_QUOTES); ?>', this)"
                                    >
                                        <img
                                            src="<?= htmlspecialchars($thumbUrl); ?>"
                                            alt="Miniatura <?= $index + 1; ?>"
                                            onerror="this.onerror=null; this.src='https://picsum.photos/seed/error/100/100?blur=5'"
                                        >
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="product-detail-info">
                    <h1 class="product-detail-title"><?= htmlspecialchars($producto['nombre']); ?></h1>
                    <p class="product-detail-price"><?= formatPrice($producto['precio']); ?></p>

                    <div class="product-meta">
                        <p>
                            <strong>Categoría:</strong>
                            <?= htmlspecialchars($producto['categoria_nombre']); ?> -
                            <?= htmlspecialchars($producto['subcategoria_nombre']); ?>
                        </p>
                        <p><strong>Condición:</strong> <?= htmlspecialchars($producto['integridad_nombre']); ?></p>
                        <p><strong>Disponibles:</strong> <?= (int) $producto['disponibles']; ?></p>
                        <p>
                            <strong>Publicado:</strong>
                            <?= !empty($producto['fecha_registro']) ? date('d/m/Y', strtotime($producto['fecha_registro'])) : 'Sin fecha'; ?>
                        </p>
                    </div>

                    <div class="product-description">
                        <h3>Descripción</h3>
                        <p><?= nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                    </div>

                    <div class="seller-info">
                        <h3>Vendedor</h3>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <img
                                src="<?= getAvatarUrl($producto['vendedor_imagen']); ?>"
                                alt="<?= htmlspecialchars($producto['vendedor_nombre']); ?>"
                                style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-primary);"
                            >
                            <div>
                                <p style="margin: 0;">
                                    <strong>
                                        <a href="<?= getBasePath(); ?>perfil/vendedor.php?id=<?= (int) ($producto['vendedor_id'] ?? 0); ?>">
                                            <?= htmlspecialchars($producto['vendedor_nombre']); ?>
                                        </a>
                                    </strong>
                                </p>
                                <?php if (!empty($producto['vendedor_desc'])): ?>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: var(--color-text-light);">
                                        <?= htmlspecialchars($producto['vendedor_desc']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="<?= getBasePath(); ?>perfil/vendedor.php?id=<?= (int) ($producto['vendedor_id'] ?? 0); ?>" class="btn-small">
                            Ver perfil del vendedor
                        </a>
                    </div>

                    <div class="product-actions">
                        <?php if (($user['id'] ?? 0) == $producto['vendedor_id']): ?>
                            <a href="../productos/editar_producto.php?id=<?= (int) $producto['id']; ?>" class="btn-secondary">
                                Editar Producto
                            </a>

                            <a
                                href="../productos/eliminar_producto.php?id=<?= (int) $producto['id']; ?>"
                                class="btn-secondary"
                                onclick="return confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.');"
                            >
                                Eliminar Producto
                            </a>
                        <?php else: ?>
                            <button
                                type="button"
                                id="btnFavorito"
                                data-vendedor-id="<?= (int) $producto['vendedor_id']; ?>"
                                class="btn-favorite <?= $isFavorite ? 'active' : ''; ?>"
                                title="<?= $isFavorite ? 'Quitar de Favoritos' : 'Añadir a Favoritos'; ?>"
                                onclick="toggleFavorito(this)"
                            >
                                <i class="fav-icon <?= $isFavorite ? 'ri-heart-3-fill' : 'ri-heart-3-line'; ?>"></i>
                                <span class="fav-text"><?= $isFavorite ? 'En Favoritos' : 'Añadir a Favoritos'; ?></span>
                            </button>

                            <button
                                type="button"
                                id="btnBloquear"
                                data-usuario-id="<?= (int) $producto['vendedor_id']; ?>"
                                class="btn-small btn-danger"
                                title="Bloquear a este usuario"
                                onclick="toggleBloqueo(<?= (int) $producto['vendedor_id']; ?>)"
                            >
                                <i class="ri-forbid-line"></i> Bloquear
                            </button>

                            <button
                                type="button"
                                id="btnReportar"
                                class="btn-small btn-warning"
                                title="Reportar este producto"
                                onclick="abrirModalReporte(<?= (int) $producto['id']; ?>, <?= (int) $producto['vendedor_id']; ?>)"
                            >
                                <i class="ri-flag-line"></i> Reportar
                            </button>

                            <?php if ($chat_existente): ?>
                                <a href="../chat/chat.php?id=<?= (int) $chat_existente['id']; ?>" class="btn-primary">
                                    Ver Conversación
                                </a>
                            <?php else: ?>
                                <a href="../chat/contactar.php?producto_id=<?= (int) $producto['id']; ?>" class="btn-primary">
                                    Contactar Vendedor
                                </a>
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

                <div class="reporte-opciones" id="motivosDenuncia">
                    <div class="loading-motivos">Cargando motivos...</div>
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

        .loading-motivos,
        .error-motivos {
            padding: 1rem;
            border: 1px dashed var(--border-color);
            border-radius: 12px;
            text-align: center;
            color: var(--color-text-light);
            background: rgba(255,255,255,0.02);
        }
    </style>

    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>

    <script src="<?= getAbsoluteBaseUrl(); ?>script.js?v=<?= time(); ?>"></script>

    <script>
        let motivosDenunciaCache = [];

        function getApiBaseUrl() {
            const candidates = [
                window.API_CONFIG?.API_BASE_URL,
                window.LARAVEL_API_URL,
                window.API_BASE_URL
            ];

            for (const value of candidates) {
                if (typeof value === 'string' && value.trim() !== '') {
                    return value.replace(/\/+$/, '');
                }
            }

            return '';
        }

        function getApiHeaders(extra = {}) {
            const headers = {
                'Accept': 'application/json',
                ...extra
            };

            const token =
                window.API_CONFIG?.LARAVEL_API_TOKEN ||
                window.LARAVEL_API_TOKEN ||
                '';

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            return headers;
        }

        function changeMainImage(src, thumbElement) {
            const mainImage = document.getElementById('mainProductImage');
            if (mainImage) {
                mainImage.src = src;
            }

            document.querySelectorAll('.thumbnail').forEach(el => el.classList.remove('active'));
            if (thumbElement) {
                thumbElement.classList.add('active');
            }
        }

        function abrirModalReporte(productoId, usuarioId) {
            const modal = document.getElementById('modalReporte');
            const productoInput = document.getElementById('reporteProductoId');
            const usuarioInput = document.getElementById('reporteUsuarioId');
            const comentarioInput = document.getElementById('comentarioReporte');

            if (productoInput) productoInput.value = productoId;
            if (usuarioInput) usuarioInput.value = usuarioId;
            if (comentarioInput) comentarioInput.value = '';

            document.querySelectorAll('input[name="motivo_reporte"]').forEach(radio => {
                radio.checked = false;
            });

            if (modal) {
                modal.style.display = 'flex';
            }

            cargarMotivosDenuncia();
        }

        function cerrarModalReporte() {
            const modal = document.getElementById('modalReporte');
            if (modal) {
                modal.style.display = 'none';
            }

            const comentarioInput = document.getElementById('comentarioReporte');
            if (comentarioInput) {
                comentarioInput.value = '';
            }

            document.querySelectorAll('input[name="motivo_reporte"]').forEach(radio => {
                radio.checked = false;
            });
        }

        async function cargarMotivosDenuncia() {
            const contenedor = document.getElementById('motivosDenuncia');
            if (!contenedor) return;

            if (motivosDenunciaCache.length > 0) {
                renderizarMotivosDenuncia(motivosDenunciaCache);
                return;
            }

            contenedor.innerHTML = '<div class="loading-motivos">Cargando motivos...</div>';

            try {
                const apiBase = getApiBaseUrl();
                if (!apiBase) {
                    throw new Error('No se encontró la URL base de la API');
                }

                const url = `${apiBase}/motivos?tipo=denuncia`;
                console.log('Consultando motivos en:', url);
                console.log('API_CONFIG:', window.API_CONFIG);
                console.log('LARAVEL_API_URL:', window.LARAVEL_API_URL);

                const response = await fetch(url, {
                    method: 'GET',
                    headers: getApiHeaders()
                });

                const rawText = await response.text();
                console.log('Respuesta cruda motivos:', rawText);

                let result = {};
                try {
                    result = rawText ? JSON.parse(rawText) : {};
                } catch (jsonError) {
                    throw new Error('La respuesta no es JSON válido');
                }

                if (!response.ok) {
                    throw new Error(result.message || `Error HTTP ${response.status}`);
                }

                const motivos =
                    Array.isArray(result) ? result :
                    Array.isArray(result.data) ? result.data :
                    Array.isArray(result.data?.items) ? result.data.items :
                    Array.isArray(result.motivos) ? result.motivos :
                    [];

                if (!motivos.length) {
                    throw new Error('La API respondió, pero no devolvió motivos');
                }

                motivosDenunciaCache = motivos;
                renderizarMotivosDenuncia(motivos);
            } catch (error) {
                console.error('Error cargando motivos de denuncia:', error);
                contenedor.innerHTML = `
                    <div class="error-motivos">
                        No se pudieron cargar los motivos de denuncia.<br>
                        <small>${escapeHtml(error.message || 'Error desconocido')}</small>
                    </div>
                `;
            }
        }

        function renderizarMotivosDenuncia(motivos) {
            const contenedor = document.getElementById('motivosDenuncia')
                            || document.querySelector('.reporte-opciones');
            if (!contenedor) return;

            const iconoMap = (nombre) => {
                const t = String(nombre).toLowerCase();
                if (t.includes('ilegal') || t.includes('prohibido')) return 'ri-spam-line';
                if (t.includes('precio'))   return 'ri-money-dollar-circle-line';
                if (t.includes('descrip'))  return 'ri-file-warning-line';
                if (t.includes('imagen') || t.includes('foto')) return 'ri-image-line';
                if (t.includes('estafa') || t.includes('fraude')) return 'ri-error-warning-line';
                if (t.includes('acoso'))    return 'ri-user-unfollow-line';
                if (t.includes('bulling') || t.includes('bully')) return 'ri-emotion-unhappy-line';
                if (t.includes('troll'))    return 'ri-ghost-line';
                if (t.includes('spam'))     return 'ri-spam-2-line';
                if (t.includes('sexual'))   return 'ri-alert-line';
                if (t.includes('fake'))     return 'ri-file-warning-line';
                if (t.includes('violencia'))return 'ri-error-warning-line';
                return 'ri-flag-line';
            };

            contenedor.innerHTML = motivos.map(m => `
                <label class="reporte-opcion">
                    <input type="radio" name="motivo_reporte" value="${m.id}">
                    <span class="opcion-content">
                        <i class="${iconoMap(m.nombre)}"></i>
                        <strong>${escapeHtml(m.nombre ?? '')}</strong>
                    </span>
                </label>
            `).join('');
        }

        function obtenerIconoMotivo(nombre, index = 0) {
            const texto = String(nombre).toLowerCase();

            if (texto.includes('prohibido') || texto.includes('ilegal')) return 'ri-spam-line';
            if (texto.includes('precio')) return 'ri-money-dollar-circle-line';
            if (texto.includes('descrip')) return 'ri-file-warning-line';
            if (texto.includes('imagen') || texto.includes('foto')) return 'ri-image-line';
            if (texto.includes('estafa') || texto.includes('fraude')) return 'ri-error-warning-line';
            if (texto.includes('ofensivo')) return 'ri-alert-line';

            const iconosFallback = [
                'ri-flag-line',
                'ri-alert-line',
                'ri-error-warning-line',
                'ri-information-line',
                'ri-spam-2-line'
            ];

            return iconosFallback[index % iconosFallback.length];
        }

        function escapeHtml(texto) {
            return String(texto)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        async function enviarReporte() {
            const productoId = document.getElementById('reporteProductoId')?.value || '';
            const usuarioId = document.getElementById('reporteUsuarioId')?.value || '';
            const motivoInput = document.querySelector('input[name="motivo_reporte"]:checked');

            if (!motivoInput) {
                showToast('Selecciona un motivo para el reporte', 'error');
                return;
            }

            const body = {
                motivo_id:  Number(motivoInput.value),
                usuario_id: Number(usuarioId)
            };

            if (productoId) {
        body.producto_id = Number(productoId);
    }

    try {
        const response = await fetch(`${getApiBaseUrl()}/denuncias`, {
            method: 'POST',
            headers: getApiHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(body)
        });

        const data = await response.json();
        const ok = data.success || data.status === 'success';

        if (ok) {
            showToast(data.message || 'Reporte enviado correctamente', 'success');
            cerrarModalReporte();
        } else {
            const errores = data.errors
                ? Object.values(data.errors).flat().join(', ')
                : (data.message || 'Error al enviar el reporte');
            showToast(errores, 'error');
        }
    } catch (error) {
        console.error('Error enviando reporte:', error);
        showToast('Error de conexión', 'error');
    }
}
window.enviarReporte = enviarReporte;
    </script>
</body>
</html>