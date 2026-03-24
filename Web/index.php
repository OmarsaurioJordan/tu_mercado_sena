<?php
require_once 'config.php';
require_once __DIR__ . '/config_api.php';
require_once __DIR__ . '/api/api_client.php';

// Redirigir a login si no está autenticado
if (!isLoggedIn()) {
    header('Location: /auth/welcome.php');
    exit;
}

$user = getCurrentUser();

// Filtros (se pasan a JavaScript para la API)
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';

// Categorías e integridad solo desde API (tumercadosena.shop); sin SQL
$categorias_list = [];
$integridad_list = [];
$cats = apiGetCategorias();
$categorias_list = is_array($cats) ? $cats : [];
$integridad_list = apiGetIntegridad();
if (!is_array($integridad_list)) $integridad_list = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Mercado SENA - Marketplace</title>
    <link rel="stylesheet" href="/styles.css">

</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <?php include 'includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <!-- Cabecera: tu búsqueda y filtros existentes + botón actualizar -->
            <div class="list-header">
            <div class="filters-section">
                <div class="filters-form" id="filtersForm">
                    <div class="filter-group filter-group-search">
                        <input type="text" id="searchInput" placeholder="Buscar productos..." 
                               value="<?php echo htmlspecialchars($busqueda); ?>" class="search-input">
                    </div>
                    <div class="filter-group filter-group-refresh">
                        <button type="button" id="refreshProductsBtn" class="btn-icon-refresh" title="Actualizar lista" aria-label="Actualizar">
                            <i class="ri-refresh-line" id="refreshIcon"></i>
                        </button>
                    </div>
                    <div class="filter-group">
                        <select id="categoryFilter" class="select-input">
                            <option value="0">Categorías</option>
                            <?php foreach ($categorias_list as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select id="integridadFilter" class="select-input">
                            <option value="0">Condición</option>
                            <?php foreach ($integridad_list as $int): ?>
                                <option value="<?php echo $int['id']; ?>">
                                    <?php echo htmlspecialchars($int['nombre'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group filter-price">
                        <input type="number" id="precioMin" placeholder="$ Mín" class="price-input" min="0">
                        <span>-</span>
                        <input type="number" id="precioMax" placeholder="$ Máx" class="price-input" min="0">
                    </div>
                    <div class="filter-group">
                        <select id="sortFilter" class="select-input">
                            <option value="newest">Más recientes</option>
                            <option value="oldest">Más antiguos</option>
                            <option value="price_low">Menor precio</option>
                            <option value="price_high">Mayor precio</option>
                            <option value="available">Más disponibles</option>
                        </select>
                    </div>
                    <button type="button" id="clearFiltersBtn" class="btn-link" style="display: none;">Limpiar filtros</button>
                </div>
            </div>
            </div>

            <!-- Contenido del listado (equivalente a FlatList contentContainerStyle paddingBottom) -->
            <div class="products-list-content">
            <div class="products-grid" id="productsGrid">
                <!-- Los productos se cargarán dinámicamente via JavaScript -->
            </div>
            
            <!-- Skeleton Loaders (se muestran mientras carga) -->
            <div class="products-grid skeleton-grid" id="skeletonGrid">
                <?php for ($i = 0; $i < 8; $i++): ?>
                <div class="product-card skeleton-card">
                    <div class="skeleton skeleton-image"></div>
                    <div class="skeleton-info">
                        <div class="skeleton skeleton-title"></div>
                        <div class="skeleton skeleton-price"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text-short"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            
            <!-- Indicador de carga para infinite scroll -->
            <div class="loading-more" id="loadingMore" style="display: none;">
                <div class="loading-spinner"></div>
                <span>Cargando más productos...</span>
            </div>
            
            <!-- Mensaje cuando no hay más productos -->
            <div class="no-more-products" id="noMoreProducts" style="display: none;">
                <p>✨ Has visto todos los productos disponibles</p>
            </div>
            
            <!-- Mensaje cuando no hay productos -->
            <div class="no-products" id="noProducts" style="display: none;">
                <p>No se encontraron productos. ¡Sé el primero en publicar!</p>
                <?php if ($user): ?>
                    <a href="<?= getAbsoluteBaseUrl() ?>productos/publicar.php" class="btn-primary">Publicar Producto</a>
                <?php endif; ?>
            </div>
            </div>

            <!-- Pasar filtros actuales a JavaScript -->
            <script>
                // Variable global para rutas de API
                window.BASE_URL = '<?= getAbsoluteBaseUrl() ?>';
                
                window.productFilters = {
                    categoria: <?php echo json_encode($categoria_id); ?>,
                    busqueda: <?php echo json_encode($busqueda); ?>,
                    orden: 'newest'
                };
                window.currentUsoDatos = <?php echo (int)$user['uso_datos']; ?>;
            </script>

        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>
    <?php include 'includes/api_config_boot.php'; ?>
    <script src="/script.js?v=<?= time(); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /*
                * Carga inicial de productos al abrir la página.
                * Se llama a loadProducts con page=1 y 
                * reset=true para cargar la primera página de productos
                * Función del script.js que hace la llamada a la API y renderiza 
                * los productos.
            */
            loadProducts(1, true);  
        });

    </script>
</body>
</html>

