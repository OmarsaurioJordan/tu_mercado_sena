<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$ventas = apiGetHistorialVentas();
$compras = apiGetHistorialCompras();
$ventas = is_array($ventas) ? $ventas : [];
$compras = is_array($compras) ? $compras : [];

$total_ventas = count($ventas);
$total_compras = count($compras);

$sum_cal = 0;
$total_calificaciones = 0;
foreach ($ventas as $v) {
    $c = $v['calificacion'] ?? null;
    if ($c !== null && $c !== '') {
        $sum_cal += (float)$c;
        $total_calificaciones++;
    }
}
$promedio = $total_calificaciones > 0 ? round($sum_cal / $total_calificaciones, 1) : 0;

function _h($arr, $key, $alt = '') {
    $v = $arr[$key] ?? $arr[lcfirst(str_replace('_', '', ucwords($key, '_')))] ?? $alt;
    return $v === null || $v === '' ? $alt : $v;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Transacciones - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
    <style>
        .historial-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white; /* Cambiado de var(--color-bg) */
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-primary);
        }
        .stat-card .label {
            color: var(--color-text-light);
            font-size: 0.9rem;
        }
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .tab-btn.active {
            background: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .transaccion-card {
            background: white; /* Cambiado de var(--color-bg) */
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            box-shadow: var(--shadow-sm);
        }
        .transaccion-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        .transaccion-info h3 {
            margin-bottom: 0.5rem;
            color: var(--color-primary);
        }
        .transaccion-info p {
            font-size: 0.9rem;
            color: var(--color-text-light);
            margin-bottom: 0.25rem;
        }
        .estrellas {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .comentario-box {
            background: var(--color-bg-secondary);
            padding: 0.75rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1>📊 Historial de Transacciones</h1>
            </div>
            
            <div class="historial-stats">
                <div class="stat-card">
                    <div class="number"><?= $total_ventas ?></div>
                    <div class="label">Ventas realizadas</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?= $total_compras ?></div>
                    <div class="label">Compras realizadas</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?= $promedio ?> ⭐</div>
                    <div class="label"><?= $total_calificaciones ?> calificaciones</div>
                </div>
            </div>
            
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('ventas')">Mis Ventas</button>
                <button class="tab-btn" onclick="showTab('compras')">Mis Compras</button>
            </div>
            
            <div id="ventas" class="tab-content active">
                <?php if ($total_ventas > 0): ?>
                    <?php foreach ($ventas as $v): ?>
                        <div class="transaccion-card">
                            <?php $pImg = _h($v, 'producto_imagen'); ?>
                            <img src="<?= $pImg ? getProductMainImage(_h($v, 'producto_id')) : 'https://picsum.photos/80' ?>" 
                                 class="transaccion-img" alt="Producto">
                            <div class="transaccion-info">
                                <h3><?= htmlspecialchars(_h($v, 'producto_nombre')) ?></h3>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <img src="<?= getAvatarUrl(_h($v, 'comprador_imagen')) ?>" 
                                         alt="<?= htmlspecialchars(_h($v, 'comprador_nombre')) ?>"
                                         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-primary);">
                                    <p style="margin: 0;"><strong>Comprador:</strong> <?= htmlspecialchars(_h($v, 'comprador_nombre')) ?></p>
                                </div>
                                <p><strong>Precio acordado:</strong> <?= formatPrice(_h($v, 'precio_acordado') ?: _h($v, 'precio_original')) ?></p>
                                <p><strong>Cantidad:</strong> <?= _h($v, 'cantidad', '1') ?></p>
                                <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime(_h($v, 'fecha_venta', 'now'))) ?></p>
                                <?php if (_h($v, 'calificacion')): ?>
                                    <div class="estrellas">
                                        <?= str_repeat('★', (int)_h($v, 'calificacion')) . str_repeat('☆', 5 - (int)_h($v, 'calificacion')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (_h($v, 'comentario')): ?>
                                    <div class="comentario-box">"<?= htmlspecialchars(_h($v, 'comentario')) ?>"</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products"><p>Aún no has realizado ventas.</p></div>

                <?php endif; ?>
            </div>
            
            <div id="compras" class="tab-content">
                <?php if ($total_compras > 0): ?>
                    <?php foreach ($compras as $c): ?>
                        <div class="transaccion-card">
                            <img src="<?= getProductMainImage(_h($c, 'producto_id')) ?: 'https://picsum.photos/80' ?>" 
                                 class="transaccion-img" alt="Producto">
                            <div class="transaccion-info">
                                <h3><?= htmlspecialchars(_h($c, 'producto_nombre')) ?></h3>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <img src="<?= getAvatarUrl(_h($c, 'vendedor_imagen')) ?>" 
                                         alt="<?= htmlspecialchars(_h($c, 'vendedor_nombre')) ?>"
                                         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-primary);">
                                    <p style="margin: 0;"><strong>Vendedor:</strong> <?= htmlspecialchars(_h($c, 'vendedor_nombre')) ?></p>
                                </div>
                                <p><strong>Precio:</strong> <?= formatPrice(_h($c, 'precio_acordado') ?: _h($c, 'precio_original')) ?></p>
                                <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime(_h($c, 'fecha_venta', 'now'))) ?></p>
                                <?php if (_h($c, 'calificacion')): ?>
                                    <div class="estrellas">Tu calificación: <?= str_repeat('★', (int)_h($c, 'calificacion')) ?></div>
                                <?php else: ?>
                                    <a href="calificar.php?chat_id=<?= (int)_h($c, 'chat_id') ?>" class="btn-small">Calificar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products"><p>Aún no has realizado compras.</p></div>

                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container"><p>&copy; 2025 Tu Mercado SENA.</p></div>
    </footer>
    
    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tab).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js"></script>
</body>
</html>
