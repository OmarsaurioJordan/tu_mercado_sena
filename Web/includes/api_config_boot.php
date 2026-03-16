<?php
/**
 * Inyecta la configuración de la API para el frontend.
 * Incluir ANTES de script.js en páginas que usen fetch a la API.
 */
if (!defined('LARAVEL_API_URL')) {
    if (!function_exists('getBaseUrl')) {
        require_once __DIR__ . '/../config.php';
    }
    require_once __DIR__ . '/../config_api.php';
}

if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../config.php';
}

$baseUrl = function_exists('getBaseUrl') ? getBaseUrl() : '/';
$apiUrl = defined('LARAVEL_API_URL') ? rtrim(LARAVEL_API_URL, '/') : '';
$storageUrl = defined('LARAVEL_STORAGE_URL') ? rtrim(LARAVEL_STORAGE_URL, '/') : '';

$currentUserId = null;
$notificaPush = 0;
$notificaCorreo = 0;
$apiToken = '';

if (function_exists('isLoggedIn') && isLoggedIn() && function_exists('getCurrentUser')) {
    $cu = getCurrentUser();
    $currentUserId = (int) ($cu['id'] ?? 0);
    $notificaPush = (int) ($cu['notifica_push'] ?? 0);
    $notificaCorreo = (int) ($cu['notifica_correo'] ?? 0);
    $apiToken = $_SESSION['api_token'] ?? '';
}
?>
<script>
(function () {
    const API_BASE_URL = <?= json_encode($apiUrl) ?>;
    const STORAGE_URL  = <?= json_encode($storageUrl) ?>;
    const BASE_URL     = <?= json_encode($baseUrl) ?>;
    const CURRENT_USER_ID = <?= json_encode($currentUserId) ?>;
    const NOTIFICA_PUSH = <?= json_encode($notificaPush) ?>;
    const NOTIFICA_CORREO = <?= json_encode($notificaCorreo) ?>;
    const API_TOKEN = <?= json_encode($apiToken) ?>;

    window.API_BASE_URL = API_BASE_URL ? API_BASE_URL.replace(/\/+$/, '') : '';
    window.LARAVEL_API_URL = window.API_BASE_URL;
    window.LARAVEL_STORAGE_URL = STORAGE_URL ? STORAGE_URL.replace(/\/+$/, '') : '';
    window.USE_LARAVEL_API = true;
    window.BASE_URL = BASE_URL;

    window.CURRENT_USER_ID = CURRENT_USER_ID;
    window.NOTIFICA_PUSH = NOTIFICA_PUSH;
    window.NOTIFICA_CORREO = NOTIFICA_CORREO;
    window.LARAVEL_API_TOKEN = API_TOKEN;

    // Objeto unificado para todo el frontend
    window.API_CONFIG = {
        API_BASE_URL: window.API_BASE_URL,
        LARAVEL_API_URL: window.LARAVEL_API_URL,
        LARAVEL_STORAGE_URL: window.LARAVEL_STORAGE_URL,
        BASE_URL: window.BASE_URL,
        CURRENT_USER_ID: window.CURRENT_USER_ID,
        NOTIFICA_PUSH: window.NOTIFICA_PUSH,
        NOTIFICA_CORREO: window.NOTIFICA_CORREO,
        LARAVEL_API_TOKEN: window.LARAVEL_API_TOKEN,
        USE_LARAVEL_API: true
    };
})();
</script>
<script src="<?= getBaseUrl() ?>js/api-config.js"></script>