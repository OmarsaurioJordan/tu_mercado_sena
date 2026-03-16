<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$vendedor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vendedor_id <= 0) {
    header('Location: ../index.php');
    exit;
}

/* ================================================
   VERIFICAR SI ESTÁ BLOQUEADO
================================================ */
$r_bloq = apiGetBloqueados();
$bloqueados_ids = [];

if ($r_bloq['success'] && isset($r_bloq['data'])) {
    $list = $r_bloq['data']['data']
        ?? $r_bloq['data']['bloqueados']
        ?? (isset($r_bloq['data'][0]) ? $r_bloq['data'] : []);

    foreach (is_array($list) ? $list : [] as $b) {
        $u  = $b['usuario_bloqueado'] ?? $b['usuario'] ?? $b['bloqueado'] ?? $b;
        $bid = (int)($u['id'] ?? $b['bloqueado_id'] ?? 0);
        if ($bid > 0) $bloqueados_ids[] = $bid;
    }
}

if (in_array($vendedor_id, $bloqueados_ids, true)) {
    header('Location: ../index.php');
    exit;
}

/* ================================================
   PERFIL PÚBLICO DEL VENDEDOR
================================================ */
$r_perfil = apiGetPerfilPublico($vendedor_id);
$vendedor  = null;

if ($r_perfil['success'] && !empty($r_perfil['data'])) {
    $d = $r_perfil['data']['data'] ?? $r_perfil['data'];
    $vendedor = is_array($d) ? $d : null;
}

if (!$vendedor || empty($vendedor['id'])) {
    header('Location: ../index.php');
    exit;
}

$vendedor['total_productos']        = $vendedor['total_productos']        ?? $vendedor['productos_count'] ?? 0;
$vendedor['total_ventas']           = $vendedor['total_ventas']           ?? $vendedor['ventas_count']    ?? 0;
$vendedor['calificacion_promedio']  = $vendedor['calificacion_promedio']  ?? $vendedor['rating']          ?? null;

$esFavorito       = in_array($vendedor_id, apiGetFavoritos(), true);
$recienteConectado = !empty($vendedor['fecha_reciente'])
    && (time() - strtotime($vendedor['fecha_reciente'])) < 86400;

// La imagen ya viene como URL completa desde el accessor del modelo
$avatarUrl = $vendedor['imagen'] ?: getBaseUrl() . 'assets/images/default-avatar.jpg';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($vendedor['nickname']) ?> - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time() ?>">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .vendedor-header {
            background: var(--color-primary);
            padding: 2.5rem;
            border-radius: 20px;
            color: white;
            margin-bottom: 2rem;
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: var(--shadow-md);
        }
        .vendedor-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255,255,255,0.3);
            background: #eee;
        }
        .vendedor-info h1 {
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
        }
        .vendedor-stats {
            display: flex;
            gap: 1.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item .number {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .stat-item .label {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        .vendedor-descripcion {
            margin-top: 1rem;
            opacity: 0.95;
            max-width: 500px;
        }
        .vendedor-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .btn-social {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-social:hover {
            background: rgba(255,255,255,0.3);
        }
        .btn-bloquear {
            background: rgba(220,38,38,0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-bloquear:hover {
            background: rgba(220,38,38,1);
        }
        .reciente-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: rgba(46,204,113,0.3);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .reciente-badge i {
            color: #2ecc71;
        }
        .productos-section h2 {
            margin-bottom: 1.5rem;
            color: var(--color-primary);
        }
        .calificacion-stars {
            color: #f39c12;
        }
        .btn-favorite {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-favorite.active {
            background: rgba(239,68,68,0.8);
        }
        .btn-favorite:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">

            <!-- HEADER DEL VENDEDOR -->
            <div class="vendedor-header">

                <img src="<?= htmlspecialchars($avatarUrl) ?>"
                     alt="<?= htmlspecialchars($vendedor['nickname']) ?>"
                     class="vendedor-avatar"
                     onerror="this.src='<?= getBaseUrl() ?>assets/images/default-avatar.jpg'">

                <div class="vendedor-info">
                    <h1><?= htmlspecialchars($vendedor['nickname']) ?></h1>

                    <?php if ($recienteConectado): ?>
                        <span class="reciente-badge">
                            <i class="ri-checkbox-blank-circle-fill"></i>
                            Recientemente conectado
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($vendedor['descripcion'])): ?>
                        <p class="vendedor-descripcion">
                            <?= htmlspecialchars($vendedor['descripcion']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="vendedor-stats">
                        <div class="stat-item">
                            <div class="number"><?= (int)$vendedor['total_productos'] ?></div>
                            <div class="label">Productos</div>
                        </div>
                        <div class="stat-item">
                            <div class="number"><?= (int)$vendedor['total_ventas'] ?></div>
                            <div class="label">Ventas</div>
                        </div>
                        <div class="stat-item">
                            <div class="number calificacion-stars">
                                <?= $vendedor['calificacion_promedio']
                                    ? number_format((float)$vendedor['calificacion_promedio'], 1) . ' ★'
                                    : 'Sin calif.' ?>
                            </div>
                            <div class="label">Calificación</div>
                        </div>
                    </div>

                    <?php if ((int)($user['id'] ?? 0) !== $vendedor_id): ?>
                        <div class="vendedor-actions">

                            <!-- FAVORITO -->
                            <button type="button"
                                    id="btnFavorito"
                                    data-vendedor-id="<?= $vendedor_id ?>"
                                    class="btn-favorite <?= $esFavorito ? 'active' : '' ?>"
                                    onclick="toggleFavorito(this)">
                                <i class="fav-icon <?= $esFavorito ? 'ri-heart-3-fill' : 'ri-heart-3-line' ?>"></i>
                                <span class="fav-text">
                                    <?= $esFavorito ? 'En Favoritos' : 'Agregar Favorito' ?>
                                </span>
                            </button>

                            <!-- BLOQUEAR -->
                            <button type="button"
                                    class="btn-bloquear"
                                    onclick="toggleBloqueo(<?= $vendedor_id ?>)">
                                <i class="ri-forbid-line"></i>
                                Bloquear
                            </button>

                            <!-- RED SOCIAL -->
                            <?php if (!empty($vendedor['link'])): ?>
                                <a href="<?= htmlspecialchars($vendedor['link']) ?>"
                                   target="_blank"
                                   rel="noopener"
                                   class="btn-social">
                                    <i class="ri-links-line"></i> Red Social
                                </a>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PRODUCTOS DEL VENDEDOR -->
            <div class="productos-section">
                <h2>Productos de <?= htmlspecialchars($vendedor['nickname']) ?></h2>

                <div class="products-grid"
                     data-productos-vendedor
                     data-vendedor-id="<?= (int)$vendedor_id ?>">
                </div>

                <div class="no-products" style="display:none">
                    <p>Este vendedor no tiene productos disponibles.</p>
                </div>
            </div>

        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>

    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js?v=<?= time() ?>"></script>
</body>
</html>