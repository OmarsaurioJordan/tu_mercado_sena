<?php
/**
 * Configuración de API – usa solo la URL publicada en api_link.php (Hostinger).
 * Toda la app consume https://tumercadosena.shop/api/ – sin artisan ni servidor local.
 */

require_once __DIR__ . '/api_link.php';

/** URL base de la API (siempre la de api_link.php) */
define('LARAVEL_API_URL', rtrim(API_BASE_URL, '/') . '/');
define('LARAVEL_STORAGE_URL', rtrim(API_STORAGE_URL, '/') . '/');

/** Siempre usamos la API externa (Hostinger) */
define('USE_LARAVEL_API', true);

// ============================================
// FUNCIONES HELPER
// ============================================

function getApiUrl() {
    return LARAVEL_API_URL;
}

function getApiEndpoint($endpoint) {
    $endpoint = str_replace('.php', '', $endpoint);
    return LARAVEL_API_URL . ltrim($endpoint, '/');
}

function isUsingLaravelApi() {
    return true;
}

function isUsingPhpApi() {
    return false;
}

function getApiHeaders() {
    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];
    if (isset($_SESSION['api_token'])) {
        $headers['Authorization'] = 'Bearer ' . $_SESSION['api_token'];
    }
    return $headers;
}

function getEndpointMapping() {
    return [
        'login.php' => 'auth/login',
        'register.php' => 'auth/register',
        'logout.php' => 'auth/logout',
        'productos.php' => 'productos',
        'crear_producto.php' => 'productos/crear',
        'editar_producto.php' => 'productos/editar',
        'eliminar_producto.php' => 'productos/eliminar',
        'chats.php' => 'chats',
        'enviar_mensaje.php' => 'mensajes/enviar',
        'obtener_mensajes.php' => 'mensajes/obtener',
        'eliminar_chat.php' => 'chats/eliminar',
        'solicitar_confirmacion.php' => 'transacciones/solicitar-confirmacion',
        'responder_confirmacion.php' => 'transacciones/responder-confirmacion',
        'solicitar_devolucion.php' => 'transacciones/solicitar-devolucion',
        'responder_devolucion.php' => 'transacciones/responder-devolucion',
        'denunciar_usuario.php' => 'denuncias/crear',
        'perfil.php' => 'usuarios/perfil',
        'editar_perfil.php' => 'usuarios/editar',
        'toggle_silencio.php' => 'chats/toggle-silencio',
        'cerrar_chats_automatico.php' => 'chats/cerrar-automatico',
    ];
}

function mapEndpoint($phpEndpoint) {
    $mapping = getEndpointMapping();
    return isset($mapping[$phpEndpoint]) ? $mapping[$phpEndpoint] : $phpEndpoint;
}

function getApiInfo() {
    return [
        'api_url' => LARAVEL_API_URL,
        'api_type' => 'Hostinger (tumercadosena.shop)',
    ];
}
