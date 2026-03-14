<?php
/**
 * Cliente API – Laravel local (API/public/api/), NO Hostinger
 * Tu Mercado SENA
 */
if (!defined('USE_LARAVEL_API')) {
    if (!function_exists('getBaseUrl')) require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../config_api.php';
}
if (!defined('API_BASE_URL')) {
    $base = (defined('USE_LARAVEL_API') && USE_LARAVEL_API && defined('LARAVEL_API_URL'))
        ? rtrim(LARAVEL_API_URL, '/')
        : (defined('HOSTINGER_API_URL') && HOSTINGER_API_URL ? rtrim(HOSTINGER_API_URL, '/') : '');
    define('API_BASE_URL', $base ?: 'https://omwekiatl.xyz/api');
}

/**
 * Realiza una petición HTTP a la API
 * 
 * @param string $endpoint Endpoint de la API (sin /api/)
 * @param string $method Método HTTP (GET, POST, PATCH, DELETE)
 * @param array $data Datos a enviar en el body
 * @param string|null $token Token JWT para autenticación
 * @return array Respuesta de la API
 */
function apiRequest($endpoint, $method = 'GET', $data = [], $token = null) {
    $url = API_BASE_URL . $endpoint;
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    // Agregar token de autenticación si existe
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        case 'GET':
        default:
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url);
            }
            break;
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Error de conexión: ' . $error,
            'http_code' => 0
        ];
    }
    
    $decoded = json_decode($response, true);
    
    // Detectar si la sesión fue reemplazada por otro login
    $sessionReplaced = false;
    if ($httpCode === 401 && isset($decoded['session_replaced']) && $decoded['session_replaced'] === true) {
        $sessionReplaced = true;
        // Limpiar la sesión local
        clearToken();
        if (isset($_SESSION['usuario_id'])) {
            session_destroy();
            session_start();
        }
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => $decoded,
        'http_code' => $httpCode,
        'session_replaced' => $sessionReplaced
    ];
}

/**
 * Guarda el token JWT en la sesión
 */
function saveToken($token, $expiresIn = 86400) {
    $_SESSION['jwt_token'] = $token;
    $_SESSION['token_expires'] = time() + $expiresIn;
}

/**
 * Obtiene el token JWT de la sesión
 * Usa api_token (login Laravel) o jwt_token
 */
function getToken() {
    if (!empty($_SESSION['api_token'])) {
        return $_SESSION['api_token'];
    }
    if (isset($_SESSION['jwt_token']) && isset($_SESSION['token_expires'])) {
        if (time() < $_SESSION['token_expires']) {
            return $_SESSION['jwt_token'];
        }
        unset($_SESSION['jwt_token']);
        unset($_SESSION['token_expires']);
    }
    return null;
}

/**
 * Verifica si hay un token válido
 */
function hasValidToken() {
    return getToken() !== null;
}

/**
 * Elimina el token de la sesión
 */
function clearToken() {
    unset($_SESSION['jwt_token']);
    unset($_SESSION['token_expires']);
}

/**
 * Verifica si la sesión actual sigue siendo válida en el servidor
 * Si la sesión fue reemplazada por otro login, limpia la sesión local y redirige
 * 
 * @param bool $redirectOnInvalid - Si es true, redirige al login automáticamente
 * @return bool - true si la sesión es válida, false si no
 */
function checkSessionValid($redirectOnInvalid = true) {
    $token = getToken();
    
    if (!$token) {
        if ($redirectOnInvalid) {
            header("Location: login.php?session_expired=1");
            exit();
        }
        return false;
    }
    
    // Hacer una petición al servidor para verificar el token
    $response = apiGetMe();
    
    // Si la sesión fue reemplazada
    if (isset($response['session_replaced']) && $response['session_replaced'] === true) {
        if ($redirectOnInvalid) {
            header("Location: login.php?session_replaced=1");
            exit();
        }
        return false;
    }
    
    // Si hay otro error de autenticación
    if (!$response['success'] && $response['http_code'] === 401) {
        clearToken();
        session_destroy();
        session_start();
        
        if ($redirectOnInvalid) {
            header("Location: login.php?session_expired=1");
            exit();
        }
        return false;
    }
    
    return $response['success'];
}

// ============================================
// FUNCIONES DE AUTENTICACIÓN
// ============================================

/**
 * Inicia el proceso de registro (envía código al correo)
 */
function apiIniciarRegistro($email, $password, $passwordConfirmation, $nickname, $descripcion = '', $link = '', $imagen = '') {
    $data = [
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $passwordConfirmation,
        'nickname' => $nickname,
        'rol_id' => 3,  // prosumer
        'estado_id' => 1,  // activo
        'device_name' => 'web'
    ];
    
    // Solo agregar campos opcionales si tienen valor
    if (!empty($descripcion)) {
        $data['descripcion'] = $descripcion;
    }
    if (!empty($link)) {
        $data['link'] = $link;
    }
    if (!empty($imagen)) {
        $data['imagen'] = $imagen;
    }
    
    return apiRequest('/auth/iniciar-registro', 'POST', $data);
}

/**
 * Completa el registro con el código de verificación
 */
function apiCompletarRegistro($cuentaId, $clave, $datosEncriptados) {
    $data = [
        'cuenta_id' => $cuentaId,
        'clave' => $clave,
        'datosEncriptados' => $datosEncriptados,
        'device_name' => 'web'
    ];
    
    return apiRequest('/auth/register', 'POST', $data);
}

/**
 * Inicia sesión
 */
function apiLogin($email, $password) {
    $data = [
        'email' => $email,
        'password' => $password,
        'device_name' => 'web'
    ];
    
    return apiRequest('/auth/login', 'POST', $data);
}

/**
 * Cierra sesión
 */
function apiLogout($allDevices = false) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'error' => 'No hay sesión activa'];
    }
    
    $data = ['all_devices' => $allDevices];
    return apiRequest('/auth/logout', 'POST', $data, $token);
}

/**
 * Obtiene el usuario autenticado
 */
function apiGetMe() {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'error' => 'No hay sesión activa'];
    }
    
    return apiRequest('/auth/me', 'GET', [], $token);
}

/**
 * Obtiene el perfil público de un usuario
 */
function apiGetPerfilPublico($id) {
    return apiRequest("/auth/perfil-publico/{$id}", 'GET');
}

/**
 * Refresca el token
 */
function apiRefreshToken() {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'error' => 'No hay sesión activa'];
    }
    
    return apiRequest('/auth/refresh', 'POST', [], $token);
}

// ============================================
// FUNCIONES DE RECUPERACIÓN DE CONTRASEÑA
// ============================================

/**
 * Inicia el proceso de recuperación de contraseña
 */
function apiValidarCorreo($email) {
    $data = ['email' => $email];
    return apiRequest('/auth/recuperar-contrasena/validar-correo', 'POST', $data);
}

/**
 * Valida el código de recuperación
 */
function apiValidarClaveRecuperacion($cuentaId, $clave) {
    $data = [
        'cuenta_id' => $cuentaId,
        'clave' => $clave
    ];
    return apiRequest('/auth/recuperar-contrasena/validar-clave-recuperacion', 'POST', $data);
}

/**
 * Restablece la contraseña
 */
function apiReestablecerPassword($cuentaId, $password, $passwordConfirmation) {
    $data = [
        'cuenta_id' => $cuentaId,
        'password' => $password,
        'password_confirmation' => $passwordConfirmation
    ];
    return apiRequest('/auth/recuperar-contrasena/reestablecer-contrasena', 'PATCH', $data);
}

// ============================================
// FUNCIONES DE PERFIL
// ============================================

/**
 * Edita el perfil del usuario
 */
function apiEditarPerfil($userId, $data) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'error' => 'No hay sesión activa'];
    }
    
    return apiRequest('/editar-perfil/' . $userId, 'PATCH', $data, $token);
}

// ============================================
// FUNCIONES DE BLOQUEADOS
// ============================================

/**
 * Obtiene los usuarios bloqueados
 */
function apiGetBloqueados() {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'error' => 'No hay sesión activa'];
    }
    
    return apiRequest('/bloqueados', 'GET', [], $token);
}

/**
 * Bloquea un usuario
 */
function apiBloquearUsuario($bloqueadorId, $bloqueadoId) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'error' => 'No hay sesión activa'];
    }
    
    $data = [
        'bloqueador_id' => $bloqueadorId,
        'bloqueado_id' => $bloqueadoId
    ];
    
    return apiRequest('/bloqueados', 'POST', $data, $token);
}

/**
 * Desbloquea un usuario
 */
function apiDesbloquearUsuario($bloqueadoId) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'error' => 'No hay sesión activa'];
    }
    
    return apiRequest('/bloqueados/' . $bloqueadoId, 'DELETE', [], $token);
}

// ============================================
// PRODUCTOS (solo API Laravel)
// ============================================

/**
 * Normaliza respuesta de la API: puede ser array directo o { data: [...] }
 */
function apiNormalizeList($r) {
    if (!$r['success'] || !isset($r['data'])) return [];
    $d = $r['data'];
    if (is_array($d) && isset($d['data']) && is_array($d['data'])) return $d['data'];
    return is_array($d) ? $d : [];
}

/**
 * Obtiene categorías de la API (Hostinger). Envía token por si el endpoint requiere auth.
 */
function apiGetCategorias() {
    $token = getToken();
    $r = apiRequest('/categorias', 'GET', [], $token);
    return apiNormalizeList($r);
}

/**
 * Obtiene integridad (condiciones) de la API (Hostinger). Envía token por si el endpoint requiere auth.
 */
function apiGetIntegridad() {
    $token = getToken();
    $r = apiRequest('/integridades', 'GET', [], $token);
    return apiNormalizeList($r);
}

/**
 * Obtiene los productos del usuario autenticado
 */
function apiGetMisProductos() {
    $token = getToken();
    if (!$token) return [];
    $r = apiRequest('/mis-productos', 'GET', [], $token);
    if (!$r['success']) return [];
    $data = $r['data'] ?? [];
    return $data['data'] ?? (is_array($data) ? $data : []);
}

/**
 * Obtiene un producto por ID
 */
function apiGetProducto($id) {
    $token = getToken();
    $r = apiRequest('/productos/' . (int)$id, 'GET', [], $token);
    if (!$r['success'] || empty($r['data']['data'])) {
        return null;
    }
    return $r['data']['data'];
}

/**
 * Obtiene los chats del usuario
 */
function apiGetChats() {
    $token = getToken();
    if (!$token) return [];
    $r = apiRequest('/chats', 'GET', [], $token);
    if (!$r['success']) return [];
    $data = $r['data'] ?? [];
    return $data['data'] ?? $data['chats'] ?? (is_array($data) ? $data : []);
}

/**
 * Obtiene los favoritos del usuario (lista de votado_id)
 */
function apiGetFavoritos() {
    $token = getToken();
    if (!$token) return [];
    $r = apiRequest('/favoritos', 'GET', [], $token);
    $list = $r['data']['favoritos'] ?? $r['data']['data'] ?? [];
    if (!is_array($list)) return [];
    $ids = [];
    foreach ($list as $item) {
        $vid = $item['usuario_votado']['id'] ?? $item['votado_id'] ?? $item['id'] ?? null;
        if ($vid) $ids[] = (int)$vid;
    }
    return $ids;
}

/**
 * Cambia el estado de un producto (activo/invisible)
 */
function apiCambiarEstadoProducto($productoId, $estadoId) {
    $token = getToken();
    if (!$token) return ['success' => false];
    return apiRequest('/productos/' . (int)$productoId . '/estado', 'PATCH', ['estado_id' => (int)$estadoId], $token);
}

/**
 * Elimina un producto
 */
function apiEliminarProducto($productoId) {
    $token = getToken();
    if (!$token) return ['success' => false];
    return apiRequest('/productos/' . (int)$productoId, 'DELETE', [], $token);
}

/**
 * Crea un producto vía API (multipart con imágenes)
 */
function apiCrearProducto($data, $imagenes = []) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'data' => ['message' => 'No hay sesión activa'], 'http_code' => 401];
    }
    
    $url = API_BASE_URL . '/productos';
    $postData = [
        'nombre' => $data['nombre'],
        'descripcion' => $data['descripcion'],
        'subcategoria_id' => $data['subcategoria_id'],
        'integridad_id' => $data['integridad_id'],
        'precio' => $data['precio'],
        'disponibles' => (int)($data['disponibles'] ?? 1)
    ];
    
    foreach ($imagenes as $i => $file) {
        if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $postData["imagenes[$i]"] = new \CURLFile($file['tmp_name'], $file['type'] ?? 'image/jpeg', $file['name'] ?? 'image.jpg');
        }
    }
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $decoded = $response ? json_decode($response, true) : null;
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => $decoded,
        'http_code' => $httpCode
    ];
}
?>
