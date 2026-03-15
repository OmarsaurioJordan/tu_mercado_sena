<?php
/**
 * Inyecta la configuración para que el front use la API Laravel local.
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
window.USE_HOSTINGER_API = false;
window.USE_LARAVEL_API = true;
window.HOSTINGER_API_URL = "";
window.HOSTINGER_STORAGE_URL = "";
window.LARAVEL_API_URL = <?= json_encode(defined('LARAVEL_API_URL') ? rtrim(LARAVEL_API_URL, '/') . '/' : '') ?>;
window.LARAVEL_STORAGE_URL = <?= json_encode(defined('LARAVEL_STORAGE_URL') ? rtrim(LARAVEL_STORAGE_URL, '/') . '/' : '') ?>;
<?php
if (!function_exists('isLoggedIn')) {
    if (!function_exists('getBaseUrl')) require_once __DIR__ . '/../config.php';
}
if (function_exists('isLoggedIn') && isLoggedIn() && function_exists('getCurrentUser')) {
    $cu = getCurrentUser();
    echo 'window.CURRENT_USER_ID = ' . json_encode((int)($cu['id'] ?? 0)) . ';';
    echo 'window.NOTIFICA_PUSH = ' . json_encode((int)($cu['notifica_push'] ?? 0)) . ';';
    echo 'window.NOTIFICA_CORREO = ' . json_encode((int)($cu['notifica_correo'] ?? 0)) . ';';
    if (defined('USE_LARAVEL_API') && USE_LARAVEL_API && !empty($_SESSION['api_token'])) {
        echo 'window.LARAVEL_API_TOKEN = ' . json_encode($_SESSION['api_token']) . ';';
    } else {
        echo 'window.LARAVEL_API_TOKEN = "";';
    }
} else {
    echo 'window.CURRENT_USER_ID = null;';
    echo 'window.NOTIFICA_PUSH = 0;';
    echo 'window.NOTIFICA_CORREO = 0;';
    echo 'window.LARAVEL_API_TOKEN = "";';
}
?>
</script>
<script src="<?= getBaseUrl() ?>js/api-config.js"></script>
