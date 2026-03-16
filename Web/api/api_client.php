<?php
/**
 * Cliente API – usa la URL global de api_link.php (Hostinger)
 * Tu Mercado SENA
 */
if (!defined('API_BASE_URL')) {
    require_once __DIR__ . '/../api_link.php';
}

/**
 * Realiza una petición HTTP a la API
 *
 * @param string $endpoint Endpoint de la API (sin /api/)
 * @param string $method Método HTTP (GET, POST, PATCH, PUT, DELETE)
 * @param array $data Datos a enviar en el body o query string
 * @param string|null $token Token JWT para autenticación
 * @return array Respuesta normalizada
 */
function apiRequest($endpoint, $method = 'GET', $data = [], $token = null) {
    $url = rtrim(API_BASE_URL, '/') . '/' . ltrim($endpoint, '/');
    $method = strtoupper($method);

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;

        case 'PATCH':
        case 'PUT':
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data) && $method !== 'DELETE') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
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
            'message' => 'Error de conexión: ' . $error,
            'errors' => null,
            'data' => null,
            'http_code' => 0,
            'session_replaced' => false,
        ];
    }

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        $decoded = [
            'message' => $response ?: 'La API no devolvió una respuesta JSON válida.'
        ];
    }

    $sessionReplaced = false;
    if ($httpCode === 401 && isset($decoded['session_replaced']) && $decoded['session_replaced'] === true) {
        $sessionReplaced = true;
        clearToken();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            session_start();
        }
    }

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'message' => $decoded['message'] ?? null,
        'errors' => $decoded['errors'] ?? null,
        'data' => $decoded,
        'http_code' => $httpCode,
        'session_replaced' => $sessionReplaced
    ];
}

/**
 * Realiza una petición multipart/form-data a la API
 */
function apiRequestMultipart($endpoint, $method = 'POST', $data = [], $files = null, $token = null) {
    $url = rtrim(API_BASE_URL, '/') . '/' . ltrim($endpoint, '/');
    $method = strtoupper($method);

    $headers = [
        'Accept: application/json'
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $postFields = [];

    foreach ($data as $key => $value) {
        $postFields[$key] = $value;
    }

    if ($files && isset($files['tmp_name']) && is_array($files['tmp_name'])) {
        $total = count($files['tmp_name']);

        for ($i = 0; $i < $total; $i++) {
            $tmpName = $files['tmp_name'][$i] ?? null;
            $fileError = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $fileName = $files['name'][$i] ?? ('archivo_' . $i);
            $fileType = $files['type'][$i] ?? 'application/octet-stream';

            if ($fileError === UPLOAD_ERR_OK && $tmpName && is_uploaded_file($tmpName)) {
                $postFields["imagenes[$i]"] = new CURLFile($tmpName, $fileType, $fileName);
            }
        }
    }

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 60,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return [
            'success' => false,
            'message' => 'Error de conexión: ' . $error,
            'errors' => null,
            'data' => null,
            'http_code' => 0,
            'session_replaced' => false,
        ];
    }

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        $decoded = [
            'message' => $response ?: 'La API no devolvió una respuesta JSON válida.'
        ];
    }

    $sessionReplaced = false;
    if ($httpCode === 401 && isset($decoded['session_replaced']) && $decoded['session_replaced'] === true) {
        $sessionReplaced = true;
        clearToken();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            session_start();
        }
    }

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'message' => $decoded['message'] ?? null,
        'errors' => $decoded['errors'] ?? null,
        'data' => $decoded,
        'http_code' => $httpCode,
        'session_replaced' => $sessionReplaced
    ];
}

function saveToken($token, $expiresIn = 86400) {
    $_SESSION['jwt_token'] = $token;
    $_SESSION['token_expires'] = time() + $expiresIn;
}

function getToken() {
    if (!empty($_SESSION['api_token'])) {
        return $_SESSION['api_token'];
    }

    if (isset($_SESSION['jwt_token']) && isset($_SESSION['token_expires'])) {
        if (time() < $_SESSION['token_expires']) {
            return $_SESSION['jwt_token'];
        }

        unset($_SESSION['jwt_token'], $_SESSION['token_expires']);
    }

    return null;
}

function hasValidToken() {
    return getToken() !== null;
}

function clearToken() {
    unset($_SESSION['jwt_token'], $_SESSION['token_expires'], $_SESSION['api_token']);
}

function checkSessionValid($redirectOnInvalid = true) {
    $token = getToken();

    if (!$token) {
        if ($redirectOnInvalid) {
            header("Location: login.php?session_expired=1");
            exit();
        }
        return false;
    }

    $response = apiGetMe();

    if (isset($response['session_replaced']) && $response['session_replaced'] === true) {
        if ($redirectOnInvalid) {
            header("Location: login.php?session_replaced=1");
            exit();
        }
        return false;
    }

    if (!$response['success'] && $response['http_code'] === 401) {
        clearToken();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            session_start();
        }

        if ($redirectOnInvalid) {
            header("Location: login.php?session_expired=1");
            exit();
        }
        return false;
    }

    return $response['success'];
}

/* =========================
   AUTENTICACIÓN
========================= */

function apiIniciarRegistro($email, $password, $passwordConfirmation, $nickname, $descripcion = '', $link = '', $imagen = '') {
    $data = [
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $passwordConfirmation,
        'nickname' => $nickname,
        'rol_id' => 3,
        'estado_id' => 1,
        'device_name' => 'web'
    ];

    if (!empty($descripcion)) $data['descripcion'] = $descripcion;
    if (!empty($link)) $data['link'] = $link;
    if (!empty($imagen)) $data['imagen'] = $imagen;

    return apiRequest('/auth/iniciar-registro', 'POST', $data);
}

function apiCompletarRegistro($cuentaId, $clave, $datosEncriptados) {
    return apiRequest('/auth/register', 'POST', [
        'cuenta_id' => $cuentaId,
        'clave' => $clave,
        'datosEncriptados' => $datosEncriptados,
        'device_name' => 'web'
    ]);
}

function apiLogin($email, $password) {
    return apiRequest('/auth/login', 'POST', [
        'email' => $email,
        'password' => $password,
        'device_name' => 'web'
    ]);
}

function apiLogout($allDevices = false) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }

    return apiRequest('/auth/logout', 'POST', ['all_devices' => $allDevices], $token);
}

function apiGetMe() {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }

    return apiRequest('/auth/me', 'GET', [], $token);
}

function apiGetPerfilPublico($id) {
    $token = getToken();
    return apiRequest("/vendedores/{$id}", 'GET', [], $token);
}

function apiRefreshToken() {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }

    return apiRequest('/auth/refresh', 'POST', [], $token);
}

/* =========================
   RECUPERACIÓN
========================= */

function apiValidarCorreo($email) {
    return apiRequest('/auth/recuperar-contrasena/validar-correo', 'POST', ['email' => $email]);
}

function apiValidarClaveRecuperacion($cuentaId, $clave) {
    return apiRequest('/auth/recuperar-contrasena/validar-clave-recuperacion', 'POST', [
        'cuenta_id' => $cuentaId,
        'clave' => $clave
    ]);
}

function apiReestablecerPassword($cuentaId, $password, $passwordConfirmation) {
    return apiRequest('/auth/recuperar-contrasena/reestablecer-contrasena', 'PATCH', [
        'cuenta_id' => $cuentaId,
        'password' => $password,
        'password_confirmation' => $passwordConfirmation
    ]);
}

/* =========================
   PERFIL
========================= */

function apiEditarPerfil($userId, $data) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }

    return apiRequest('/editar-perfil/' . $userId, 'PATCH', $data, $token);
}

/**
 * Actualizar avatar (multipart). Endpoint según API: PATCH editar-perfil con imagen o POST avatar.
 */
function apiUpdateAvatar($userId, $fileKey = 'avatar_file') {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }
    if (empty($_FILES[$fileKey]['tmp_name']) || !is_uploaded_file($_FILES[$fileKey]['tmp_name'])) {
        return ['success' => false, 'message' => 'No se envió ninguna imagen'];
    }
    $url = rtrim(API_BASE_URL, '/') . '/editar-perfil/' . (int)$userId . '/avatar';
    $headers = ['Accept: application/json', 'Authorization: Bearer ' . $token];
    $cfile = new CURLFile($_FILES[$fileKey]['tmp_name'], $_FILES[$fileKey]['type'], $_FILES[$fileKey]['name']);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['imagen' => $cfile],
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $decoded = json_decode($response, true) ?: [];
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'message' => $decoded['message'] ?? null,
        'errors' => $decoded['errors'] ?? null,
        'data' => $decoded,
        'http_code' => $httpCode,
    ];
}

/* =========================
   BLOQUEADOS
========================= */

function apiGetBloqueados() {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }

    return apiRequest('/bloqueados', 'GET', [], $token);
}

function apiBloquearUsuario($bloqueadorId, $bloqueadoId) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }

    return apiRequest('/bloqueados/' . (int)$bloqueadoId, 'POST', [
        'bloqueador_id' => (int)$bloqueadorId
    ], $token);
}

function apiDesbloquearUsuario($bloqueadoId) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }

    return apiRequest('/bloqueados/' . (int)$bloqueadoId, 'DELETE', [], $token);
}

/* =========================
   PRODUCTOS
========================= */

function apiNormalizeList($r) {
    if (!is_array($r) || empty($r['success'])) {
        return [];
    }

    $body = $r['data'] ?? null;
    if (!is_array($body)) {
        return [];
    }

    if (isset($body['data']) && is_array($body['data'])) {
        return $body['data'];
    }

    if (array_keys($body) === range(0, count($body) - 1)) {
        return $body;
    }

    if (isset($body['categorias']) && is_array($body['categorias'])) {
        return $body['categorias'];
    }

    if (isset($body['integridades']) && is_array($body['integridades'])) {
        return $body['integridades'];
    }

    if (isset($body['productos']) && is_array($body['productos'])) {
        return $body['productos'];
    }

    return [];
}

/**
 * Categorías protegidas por jwtVerify
 */
function apiGetCategorias() {
    $token = getToken();
    if (!$token) return [];

    $r = apiRequest('/categorias', 'GET', [], $token);
    return apiNormalizeList($r);
}

/**
 * Integridades pública
 */
function apiGetIntegridad() {
    $r = apiRequest('/integridades', 'GET');
    return apiNormalizeList($r);
}

/**
 * Mis productos protegida por jwtVerify
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
 * Show producto. Ruta protegida según tus rutas actuales.
 */
function apiGetProducto($id) {
    $token = getToken();
    if (!$token) return null;

    $r = apiRequest('/productos/' . (int)$id, 'GET', [], $token);

    if (!$r['success']) {
        return null;
    }

    $data = $r['data'] ?? [];
    return $data['data'] ?? $data ?? null;
}

/**
 * PATCH /api/productos/{id}/estado
 * 1=activo, 2=invisible, 3=eliminado
 */
function apiCambiarEstadoProducto($productoId, $estadoId) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }

    return apiRequest('/productos/' . (int)$productoId . '/estado', 'PATCH', [
        'estado_id' => (int)$estadoId
    ], $token);
}

/**
 * Eliminación lógica usando estado_id = 3
 */
function apiEliminarProducto($productoId) {
    return apiCambiarEstadoProducto($productoId, 3);
}

function apiCrearProducto($data, $imagenes = null) {
    $token = getToken();

    if (!$token) {
        return [
            'success' => false,
            'message' => 'No hay sesión activa',
            'errors' => null,
            'data' => null,
            'http_code' => 401
        ];
    }

    $payload = [
        'nombre' => $data['nombre'] ?? '',
        'descripcion' => $data['descripcion'] ?? '',
        'subcategoria_id' => (int)($data['subcategoria_id'] ?? 0),
        'integridad_id' => (int)($data['integridad_id'] ?? 0),
        'precio' => $data['precio'] ?? 0,
        'disponibles' => (int)($data['disponibles'] ?? 1)
    ];

    return apiRequestMultipart('/productos', 'POST', $payload, $imagenes, $token);
}

function apiActualizarProducto($productoId, $data, $imagenes = null) {
    $token = getToken();

    if (!$token) {
        return [
            'success' => false,
            'message' => 'No hay sesión activa',
            'errors' => null,
            'data' => null,
            'http_code' => 401
        ];
    }

    $payload = [
        '_method' => 'PATCH',
        'nombre' => $data['nombre'] ?? '',
        'descripcion' => $data['descripcion'] ?? '',
        'subcategoria_id' => (int)($data['subcategoria_id'] ?? 0),
        'integridad_id' => (int)($data['integridad_id'] ?? 0),
        'estado_id' => (int)($data['estado_id'] ?? 1),
        'precio' => $data['precio'] ?? 0,
        'disponibles' => (int)($data['disponibles'] ?? 1)
    ];

    return apiRequestMultipart('/productos/' . (int)$productoId, 'POST', $payload, $imagenes, $token);
}

/* =========================
   CHATS / FAVORITOS
========================= */

/**
 * Crear o abrir chat con vendedor por producto (POST /productos/{id}/chats).
 * Devuelve ['success' => bool, 'chat_id' => int|null, 'message' => string].
 */
function apiCrearChatPorProducto($productoId) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'chat_id' => null, 'message' => 'No hay sesión activa'];
    }
    $r = apiRequest('/productos/' . (int)$productoId . '/chats', 'POST', [], $token);
    $chatId = null;
    if ($r['success'] && isset($r['data'])) {
        $d = $r['data']['data'] ?? $r['data']['chat'] ?? $r['data'];
        $chatId = (int)($d['id'] ?? $d['chat_id'] ?? 0);
    }
    return [
        'success' => $r['success'] || ($r['http_code'] ?? 0) === 201,
        'chat_id' => $chatId,
        'message' => $r['message'] ?? '',
    ];
}

function apiGetChats() {
    $token = getToken();
    if (!$token) return [];

    $r = apiRequest('/chats', 'GET', [], $token);
    if (!$r['success']) return [];

    $data = $r['data'] ?? [];
    return $data['data'] ?? $data['chats'] ?? (is_array($data) ? $data : []);
}

/**
 * Mensajes de un chat.
 */
function apiGetMensajes($chatId) {
    $token = getToken();
    if (!$token) return [];
    $r = apiRequest('/chats/' . (int)$chatId . '/mensajes', 'GET', [], $token);
    if (!$r['success']) return [];
    $d = $r['data'] ?? [];
    return $d['data'] ?? $d['mensajes'] ?? (is_array($d) ? $d : []);
}

/**
 * Marcar chat como visto (comprador o vendedor según quien abre).
 */
function apiMarcarVistoChat($chatId) {
    $token = getToken();
    if (!$token) return ['success' => false];
    return apiRequest('/chats/' . (int)$chatId . '/visto', 'PATCH', [], $token);
}

function apiGetFavoritos() {
    $token = getToken();
    if (!$token) return [];

    $r = apiRequest('/favoritos', 'GET', [], $token);
    $list = $r['data']['favoritos'] ?? $r['data']['data'] ?? [];

    if (!is_array($list)) return [];

    $ids = [];
    foreach ($list as $item) {
        $vid = $item['usuario_votado']['id'] ?? $item['votado_id'] ?? $item['id'] ?? null;
        if ($vid) {
            $ids[] = (int)$vid;
        }
    }

    return $ids;
}

/**
 * Lista de vendedores favoritos con datos para listar (nickname, imagen, etc.)
 */
function apiGetFavoritosVendedores() {
    $token = getToken();
    if (!$token) return [];

    $r = apiRequest('/favoritos', 'GET', [], $token);
    if (!$r['success']) return [];

    $list = $r['data']['favoritos'] ?? $r['data']['data'] ?? [];
    if (!is_array($list)) return [];

    $out = [];
    foreach ($list as $item) {
        $user = $item['usuario_votado'] ?? $item['user'] ?? $item;
        $out[] = [
            'id' => (int)($user['id'] ?? 0),
            'nickname' => $user['nickname'] ?? '',
            'descripcion' => $user['descripcion'] ?? '',
            'link' => $user['link'] ?? '',
            'imagen' => $user['imagen'] ?? '',
        ];
    }
    return $out;
}

/**
 * Añadir o quitar favorito (toggle). POST = añadir, DELETE = quitar.
 */
function apiToggleFavorito($vendedorId, $añadir = true) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }
    $id = (int)$vendedorId;
    if ($añadir) {
        return apiRequest('/favoritos/' . $id, 'POST', [], $token);
    }
    return apiRequest('/favoritos/' . $id, 'DELETE', [], $token);
}

/**
 * Productos de un vendedor (público).
 */
function apiGetProductosVendedor($vendedorId) {
    $r = apiRequest('/productos', 'GET', ['vendedor_id' => (int)$vendedorId]);
    if (!$r['success']) return [];
    $data = $r['data'] ?? [];
    return $data['data'] ?? (isset($data['productos']) ? $data['productos'] : []);
}

/**
 * Historial: ventas y compras. Endpoints según API Laravel.
 */
function apiGetHistorialVentas() {
    $token = getToken();
    if (!$token) return [];
    $r = apiRequest('/historial/ventas', 'GET', [], $token);
    if (!$r['success']) return [];
    $d = $r['data'] ?? [];
    return $d['data'] ?? $d['ventas'] ?? (is_array($d) ? $d : []);
}

function apiGetHistorialCompras() {
    $token = getToken();
    if (!$token) return [];
    $r = apiRequest('/historial/compras', 'GET', [], $token);
    if (!$r['success']) return [];
    $d = $r['data'] ?? [];
    return $d['data'] ?? $d['compras'] ?? (is_array($d) ? $d : []);
}

/**
 * Detalle de un chat (para calificar, etc.).
 */
function apiGetChat($chatId) {
    $token = getToken();
    if (!$token) return null;
    $r = apiRequest('/chats/' . (int)$chatId, 'GET', [], $token);
    if (!$r['success']) return null;
    $d = $r['data']['data'] ?? $r['data']['chat'] ?? $r['data'];
    return is_array($d) ? $d : null;
}

/**
 * Calificar chat/compra (como comprador).
 */
/**
 * PQRS: crear y listar.
 */
function apiCrearPqrs($mensaje, $motivoId) {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }
    return apiRequest('/pqrs', 'POST', [
        'mensaje' => $mensaje,
        'motivo_id' => (int)$motivoId,
    ], $token);
}

function apiGetPqrs() {
    $token = getToken();
    if (!$token) return [];
    $r = apiRequest('/pqrs', 'GET', [], $token);
    if (!$r['success']) return [];
    $d = $r['data'] ?? [];
    $list = $d['data'] ?? (isset($d[0]) ? $d : []);
    return is_array($list) ? $list : [];
}

/**
 * Calificar chat/compra (como comprador).
 */
function apiCalificarChat($chatId, $calificacion, $comentario = '') {
    $token = getToken();
    if (!$token) {
        return ['success' => false, 'message' => 'No hay sesión activa'];
    }
    return apiRequest('/chats/' . (int)$chatId . '/calificar', 'PATCH', [
        'calificacion' => (int)$calificacion,
        'comentario' => $comentario,
    ], $token);
}
?>