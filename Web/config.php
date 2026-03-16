<?php

// =========================================================
// CONFIGURACIÓN DE LA BASE DE DATOS Y TIEMPO
// =========================================================

// Configuración de la base de datos (algunas páginas como perfil aún la usan; categorías/productos van a la API de Hostinger)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tu_mercado_sena');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================================================
// CONFIGURACIÓN DE RUTAS BASE
// =========================================================

// Función helper para obtener la URL base relativa
function getBaseUrl() {
    $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $web_pos = strpos($script_name, '/Web/');
    $frontend_pos = strpos($script_name, '/Frontend/');
    if ($web_pos !== false) {
        $after = substr($script_name, $web_pos + 5);
        $slashes = substr_count($after, '/');
        return $slashes == 0 ? './' : str_repeat('../', $slashes);
    } elseif ($frontend_pos !== false) {
        $after = substr($script_name, $frontend_pos + 10);
        $slashes = substr_count($after, '/');
        return $slashes == 0 ? './' : str_repeat('../', $slashes);
    } else {
        return './'; // por defecto
    }
}

// Ruta base absoluta desde la raíz del servidor (ej: /ensayo_link/Web/) para enlaces de navegación
function getBasePath() {
    $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    if (preg_match('#^(.*/Web)/#', $script_name, $m)) {
        return $m[1] . '/';
    }
    if (preg_match('#^(.*/Frontend)/#', $script_name, $m)) {
        return $m[1] . '/';
    }
    return '/';
}

// Cargar config_api para saber si usamos solo Laravel (después de getBaseUrl)
if (!defined('USE_LARAVEL_API')) {
    require_once __DIR__ . '/config_api.php';
}

// =========================================================
// FUNCIONES DE CONEXIÓN Y UTILIDAD
// =========================================================

/**
 * Conexión a la base de datos
 */
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        // Establece la zona horaria de la conexión SQL para que coincida con PHP
        $conn->query("SET time_zone = '-05:00'");
        return $conn;
    } catch (Exception $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}
/**
 * Obtiene la ruta completa del avatar o el avatar por defecto
 */
/**
 * Obtiene la ruta completa del avatar o el avatar por defecto
 */
function getAvatarUrl($imagen) {
    $baseUrl = getBaseUrl();
    
    if (empty($imagen)) {
        return $baseUrl . 'assets/images/default-avatar.jpg';
    }
    
    // Si ya trae la ruta completa con http/https, devolverla tal cual
    if (strpos($imagen, 'http') === 0) {
        return $imagen;
    }
    
    // Solo API Laravel: usar storage de Laravel
    if (defined('USE_LARAVEL_API') && USE_LARAVEL_API && defined('LARAVEL_STORAGE_URL')) {
        $path = (strpos($imagen, 'usuarios/') === 0 || strpos($imagen, 'uploads/') === 0) ? $imagen : 'usuarios/' . ltrim($imagen, '/');
        return rtrim(LARAVEL_STORAGE_URL, '/') . '/' . ltrim($path, '/');
    }
    
    // PHP local: uploads/usuarios/
    if (strpos($imagen, 'uploads/usuarios/') === 0) {
        $fullPath = $imagen;
    } else {
        $fullPath = 'uploads/usuarios/' . $imagen;
    }
    $serverPath = $_SERVER['DOCUMENT_ROOT'] . str_replace('//', '/', $baseUrl . $fullPath);
    if (file_exists($serverPath)) {
        return $baseUrl . $fullPath;
    }
    
    return $baseUrl . 'assets/images/default-avatar.jpg';
}
/**
 * Verifica si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Obtener información del usuario actual
 * Con USE_LARAVEL_API: solo usa sesión (sin BD).
 * Sin Laravel: consulta BD local.
 */
function getCurrentUser() {
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }

    // Solo API (tumercadosena.shop): datos de sesión; sin SQL
    return [
        'id' => $_SESSION['usuario_id'],
        'nickname' => $_SESSION['usuario_nombre'] ?? $_SESSION['nickname'] ?? 'Usuario',
        'imagen' => $_SESSION['usuario_imagen'] ?? $_SESSION['imagen'] ?? '',
        'descripcion' => $_SESSION['descripcion'] ?? '',
        'link' => $_SESSION['link'] ?? '',
        'estado_id' => (int)($_SESSION['estado_id'] ?? 1),
        'fecha_reciente' => $_SESSION['fecha_reciente'] ?? null,
        'email' => $_SESSION['email'] ?? '',
        'notifica_correo' => (int)($_SESSION['notifica_correo'] ?? 0),
        'notifica_push' => (int)($_SESSION['notifica_push'] ?? 0),
        'uso_datos' => (int)($_SESSION['uso_datos'] ?? 0)
    ];
}


function isSellerFavorite($votante_id, $vendedor_id) {
    require_once __DIR__ . '/api/api_client.php';
    $favoritos = apiGetFavoritos();
    return in_array((int)$vendedor_id, $favoritos, true);
}
function forceLightTheme() {
    echo "<script>
        localStorage.setItem('theme', 'light');
        document.documentElement.setAttribute('data-theme', 'light');
    </script>";
}

/**
 * Sanitizar entrada
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Formatear precio (Ej: 1.234.567 COP)
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' COP';
}


// =========================================================
// FUNCIONES DE FECHA Y AVATAR
// =========================================================

/**
 * Formatea un timestamp de base de datos a tiempo relativo (Ej: hace 5 minutos)
 */
function formato_tiempo_relativo($timestamp_db) {
    // Configurar la zona horaria del servidor (¡MUY IMPORTANTE!)
    date_default_timezone_set('America/Bogota'); 
    
    $tiempo_mensaje = strtotime($timestamp_db);
    $tiempo_actual = time();
    $diferencia = $tiempo_actual - $tiempo_mensaje;

    $segundos_por_minuto = 60;
    $segundos_por_hora = 3600;
    $segundos_por_dia = 86400;

    if ($diferencia < 30) {
        return "Ahora";
    } elseif ($diferencia < $segundos_por_minuto) {
        return "hace " . $diferencia . " segundos";
    } elseif ($diferencia < ($segundos_por_minuto * 60)) {
        // Minutos
        $minutos = round($diferencia / $segundos_por_minuto);
        if ($minutos == 1) {
            return "hace 1 minuto";
        }
        return "hace " . $minutos . " minutos";
    } elseif ($diferencia < $segundos_por_dia) {
        // Horas
        $horas = round($diferencia / $segundos_por_hora);
        if ($horas == 1) {
            return "hace 1 hora";
        }
        return "hace " . $horas . " horas";
    } else {
        // Si es más de un día, mostramos la fecha corta
        return date('d M', $tiempo_mensaje); // Ej: 14 Nov
    }
}

function getProductImage($productId) {
    $base = getBaseUrl();
    return $base . 'assets/images/default-product.jpg';
}

function getProductMainImage($producto_id) {
    $base = getBaseUrl();
    return $base . 'assets/images/default-product.jpg';
}

/**
 * URL de imagen de producto (Hostinger storage o uploads locales)
 * @param string $path Ruta: "productos/xxx.jpg", "uploads/productos/xxx.jpg" o URL completa
 * @return string URL completa para usar en <img src="">
 */
function getProductImageUrlPHP($path) {
    if (empty($path) || !is_string($path)) {
        return getBaseUrl() . 'assets/images/default-product.jpg';
    }
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        if (defined('USE_LARAVEL_API') && USE_LARAVEL_API && defined('LARAVEL_STORAGE_URL') && (strpos($path, 'localhost') !== false || strpos($path, 'storage/') !== false)) {
            $m = [];
            if (preg_match('@/(?:storage/)?(productos/[^\?&#]+)@i', $path, $m)) {
                return rtrim(LARAVEL_STORAGE_URL, '/') . '/' . $m[1];
            }
        }
        return $path;
    }
    if (defined('USE_LARAVEL_API') && USE_LARAVEL_API && defined('LARAVEL_STORAGE_URL')) {
        $clean = preg_replace('#^uploads/productos/#', 'productos/', $path);
        if (strpos($clean, 'productos/') !== 0) $clean = 'productos/' . ltrim($clean, '/');
        return rtrim(LARAVEL_STORAGE_URL, '/') . '/' . $clean;
    }
    $base = getBaseUrl();
    return (strpos($path, 'uploads/') === 0) ? ($base . $path) : ($base . 'uploads/productos/' . ltrim($path, '/'));
}

/**
 * Obtiene la URL del avatar del usuario.
 * (Actualizado para usar una imagen por defecto)
 * @param int $userId El ID del usuario.
 * @return string La URL del avatar (o un placeholder).
 */

/**
 * Envía un correo de notificación (usa mail() de PHP).
 * Para producción puede sustituirse por SMTP (ej. PHPMailer).
 * @param string $para_email Destinatario
 * @param string $asunto Asunto del correo
 * @param string $cuerpo_plain Cuerpo en texto plano
 * @return bool true si se envió, false en caso contrario
 */
function enviar_correo_notificacion($para_email, $asunto, $cuerpo_plain) {
    if (empty($para_email) || !filter_var($para_email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        'From: Tu Mercado SENA <noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>',
        'X-Mailer: PHP/' . phpversion()
    ];
    return @mail($para_email, $asunto, $cuerpo_plain, implode("\r\n", $headers));
}

?>

