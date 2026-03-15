<?php
/**
 * Configuración de API – Laravel local (NO Hostinger)
 * La Web usa la API Laravel del proyecto (API/public/api/) en localhost.
 */

// ============================================
// API LARAVEL LOCAL (carpeta API del proyecto)
// ============================================

/** Usar API Laravel local */
define('USE_LARAVEL_API', true);

/** Usar php artisan serve (puerto 8000). Si false, usa XAMPP/API/public */
define('LARAVEL_ARTISAN_SERVE', true);

/** No usar Hostinger */
define('HOSTINGER_API_URL', '');
define('HOSTINGER_STORAGE_URL', '');

/** URL de la API Laravel local */
if (!defined('LARAVEL_API_URL')) {
    // Si usas "php artisan serve" (puerto 8000), la API está aquí:
    if (defined('LARAVEL_ARTISAN_SERVE') && LARAVEL_ARTISAN_SERVE) {
        define('LARAVEL_API_URL', 'http://127.0.0.1:8000/api/');
        define('LARAVEL_STORAGE_URL', 'http://127.0.0.1:8000/storage/');
    } else {
        // Si usas XAMPP/Apache para servir la API:
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (preg_match('#^(.*?)/Web_omi/Web/#', $script, $m)) {
            $projectPath = rtrim($m[1], '/') . '/';
        } elseif (preg_match('#^(.*?)/Web/#', $script, $m)) {
            $projectPath = rtrim($m[1], '/') . '/';
        } else {
            $projectPath = '/tu_mercado_sena/';
        }
        define('LARAVEL_API_URL', $protocol . '://' . $host . $projectPath . 'API/public/api/');
        define('LARAVEL_STORAGE_URL', $protocol . '://' . $host . $projectPath . 'API/public/storage/');
    }
}

/**
 * URL base de la API de PHP
 * Por defecto usa la carpeta api del frontend
 */
define('PHP_API_URL', getBaseUrl() . 'api/');

// ============================================
// FUNCIONES HELPER
// ============================================

/**
 * Obtiene la URL de la API según la configuración
 * 
 * @return string URL base de la API activa
 */
function getApiUrl() {
    return USE_LARAVEL_API ? LARAVEL_API_URL : PHP_API_URL;
}

/**
 * Obtiene la URL completa de un endpoint específico
 * 
 * @param string $endpoint Nombre del endpoint (ej: 'productos.php' o 'productos')
 * @return string URL completa del endpoint
 */
function getApiEndpoint($endpoint) {
    $baseUrl = getApiUrl();
    
    // Si usa Laravel, remover la extensión .php si existe
    if (USE_LARAVEL_API) {
        $endpoint = str_replace('.php', '', $endpoint);
    }
    
    return $baseUrl . $endpoint;
}

/**
 * Verifica si se está usando la API de Laravel
 * 
 * @return bool
 */
function isUsingLaravelApi() {
    return USE_LARAVEL_API;
}

/**
 * Verifica si se está usando la API de PHP
 * 
 * @return bool
 */
function isUsingPhpApi() {
    return !USE_LARAVEL_API;
}

/**
 * Obtiene los headers necesarios para las peticiones a la API
 * 
 * @return array Headers para fetch/curl
 */
function getApiHeaders() {
    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];
    
    // Si usa Laravel y hay token de autenticación, agregarlo
    if (USE_LARAVEL_API && isset($_SESSION['api_token'])) {
        $headers['Authorization'] = 'Bearer ' . $_SESSION['api_token'];
    }
    
    return $headers;
}

/**
 * Mapeo de endpoints entre PHP y Laravel
 * Útil cuando los nombres de endpoints difieren entre sistemas
 */
function getEndpointMapping() {
    return [
        // Autenticación
        'login.php' => 'auth/login',
        'register.php' => 'auth/register',
        'logout.php' => 'auth/logout',
        
        // Productos
        'productos.php' => 'productos',
        'crear_producto.php' => 'productos/crear',
        'editar_producto.php' => 'productos/editar',
        'eliminar_producto.php' => 'productos/eliminar',
        
        // Chats
        'chats.php' => 'chats',
        'enviar_mensaje.php' => 'mensajes/enviar',
        'obtener_mensajes.php' => 'mensajes/obtener',
        'eliminar_chat.php' => 'chats/eliminar',
        
        // Confirmaciones y Devoluciones
        'solicitar_confirmacion.php' => 'transacciones/solicitar-confirmacion',
        'responder_confirmacion.php' => 'transacciones/responder-confirmacion',
        'solicitar_devolucion.php' => 'transacciones/solicitar-devolucion',
        'responder_devolucion.php' => 'transacciones/responder-devolucion',
        
        // Denuncias
        'denunciar_usuario.php' => 'denuncias/crear',
        
        // Usuarios
        'perfil.php' => 'usuarios/perfil',
        'editar_perfil.php' => 'usuarios/editar',
        
        // Otros
        'toggle_silencio.php' => 'chats/toggle-silencio',
        'cerrar_chats_automatico.php' => 'chats/cerrar-automatico',
    ];
}

/**
 * Obtiene el endpoint correcto según el sistema de API activo
 * 
 * @param string $phpEndpoint Nombre del endpoint PHP
 * @return string Endpoint correcto según la configuración
 */
function mapEndpoint($phpEndpoint) {
    if (!USE_LARAVEL_API) {
        return $phpEndpoint;
    }
    
    $mapping = getEndpointMapping();
    return isset($mapping[$phpEndpoint]) ? $mapping[$phpEndpoint] : $phpEndpoint;
}

// ============================================
// INFORMACIÓN DEL SISTEMA
// ============================================

/**
 * Obtiene información sobre la configuración actual de la API
 * 
 * @return array Información de configuración
 */
function getApiInfo() {
    return [
        'using_hostinger' => false,
        'api_url' => LARAVEL_API_URL,
        'api_type' => 'Laravel local',
        'php_url' => PHP_API_URL,
    ];
}

?>
