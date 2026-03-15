<?php
/**
 * Inyecta la configuración de API (Laravel vs PHP) para que api-config.js y script.js usen la misma.
 * Incluir ANTES de cargar script.js en cualquier página que haga fetch a la API.
 */
if (!defined('USE_LARAVEL_API')) {
    if (!function_exists('getBaseUrl')) {
        require_once __DIR__ . '/../config.php';
    }
    require_once __DIR__ . '/../config_api.php';
}
?>
<script>
window.USE_LARAVEL_API = <?= json_encode(defined('USE_LARAVEL_API') && USE_LARAVEL_API) ?>;
window.LARAVEL_API_URL = <?= json_encode(defined('LARAVEL_API_URL') ? LARAVEL_API_URL : 'http://localhost:8000/api/') ?>;
window.LARAVEL_STORAGE_URL = <?= json_encode(defined('LARAVEL_STORAGE_URL') ? LARAVEL_STORAGE_URL : 'http://localhost:8000/storage/') ?>;
<?php
if (!function_exists('isLoggedIn')) {
    if (!function_exists('getBaseUrl')) require_once __DIR__ . '/../config.php';
}
if (function_exists('isLoggedIn') && isLoggedIn() && function_exists('getCurrentUser')) {
    $cu = getCurrentUser();
    echo 'window.CURRENT_USER_ID = ' . json_encode((int)($cu['id'] ?? 0)) . ';';
} else {
    echo 'window.CURRENT_USER_ID = null;';
}
?>
</script>
<script src="<?= getBaseUrl() ?>js/api-config.js"></script>
