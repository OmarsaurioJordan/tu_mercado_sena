/**
 * Configuración de API para JavaScript
 * Usa la URL global definida en api_link.php (inyectada como window.API_BASE_URL)
 */

// ============================================
// URL GLOBAL DE LA API (api_link.php → api_config_boot.php)
// ============================================
var API_BASE_URL = (typeof window !== 'undefined' && window.API_BASE_URL) ? window.API_BASE_URL : (window.LARAVEL_API_URL || '');

var API_CONFIG = {
    USE_LARAVEL: true,
    LARAVEL_URL: API_BASE_URL,
    LARAVEL_STORAGE_URL: (typeof window !== 'undefined' && window.LARAVEL_STORAGE_URL) ? window.LARAVEL_STORAGE_URL : (API_BASE_URL ? API_BASE_URL.replace(/\/api\/?$/, '') + '/storage/' : ''),
    AUTH_ENDPOINTS: {
        iniciarRegistro: 'auth/iniciar-registro',
        register: 'auth/register',
        login: 'auth/login',
        logout: 'auth/logout',
        refresh: 'auth/refresh',
        me: 'auth/me',
        recuperarValidarCorreo: 'auth/recuperar-contrasena/validar-correo',
        recuperarValidarClave: 'auth/recuperar-contrasena/validar-clave-recuperacion',
        recuperarReestablecer: 'auth/recuperar-contrasena/reestablecer-contrasena'
    },
    get ACTIVE_URL() {
        return this.LARAVEL_URL || '';
    }
};
// Sincronizar token de sesión a localStorage (siempre usar el del servidor cuando existe)
if (typeof window.LARAVEL_API_TOKEN !== 'undefined' && window.LARAVEL_API_TOKEN && typeof localStorage !== 'undefined') {
    localStorage.setItem('api_token', window.LARAVEL_API_TOKEN);
}

// ============================================
// FUNCIONES HELPER
// ============================================

/**
 * Obtiene la URL de la API según la configuración
 * 
 * @returns {string} URL base de la API activa
 */
function getApiUrl() {
    return API_CONFIG.ACTIVE_URL;
}

/**
 * Obtiene la URL completa de un endpoint específico
 * 
 * @param {string} endpoint - Nombre del endpoint (ej: 'productos.php' o 'productos')
 * @returns {string} URL completa del endpoint
 */
function getApiEndpoint(endpoint) {
    endpoint = (endpoint || '').replace('.php', '');
    return (getApiUrl() || '') + endpoint;
}

/**
 * Verifica si se está usando la API de Laravel
 * 
 * @returns {boolean}
 */
function isUsingLaravelApi() {
    return true;
}

function isUsingPhpApi() {
    return false;
}

/**
 * Obtiene los headers necesarios para las peticiones a la API.
 * No incluye Content-Type para no romper FormData (el navegador lo fija con boundary).
 * Para JSON, añadir 'Content-Type': 'application/json' en la llamada fetch.
 *
 * @returns {Object} Headers para fetch
 */
function getApiHeaders() {
    const headers = {
        'Accept': 'application/json'
    };
    
    const token = typeof localStorage !== 'undefined' ? localStorage.getItem('api_token') : (window.LARAVEL_API_TOKEN || '');
    if (token) headers['Authorization'] = `Bearer ${token}`;
    
    return headers;
}

/**
 * Mapeo de endpoints entre PHP y Laravel (por nombre de archivo)
 */
const ENDPOINT_MAPPING = {
    // Autenticación
    'login.php': 'auth/login',
    'register.php': 'auth/register',
    'logout.php': 'auth/logout',
    
    // Productos
    'productos.php': 'productos',
    'crear_producto.php': 'productos',
    'editar_producto.php': 'productos',
    'eliminar_producto.php': 'productos',
    
    // Chats y mensajes
    'chats.php': 'chats',
    'get_messages.php': 'chats',
    'send_message.php': 'chats',
    'enviar_mensaje.php': 'mensajes/enviar',
    'obtener_mensajes.php': 'mensajes/obtener',
    'eliminar_chat.php': 'chats',
    'get_chats_notificaciones.php': 'chats',
    
    // Favoritos y bloqueados (Laravel: POST/DELETE favoritos/{id}, bloqueados/{id})
    'toggle_favorito.php': 'favoritos',
    'toggle_bloqueo.php': 'bloqueados',
    'toggle_visibilidad.php': 'productos',
    
    // Confirmaciones y Devoluciones
    'solicitar_confirmacion.php': 'transacciones/solicitar-confirmacion',
    'responder_confirmacion.php': 'transacciones/responder-confirmacion',
    'solicitar_devolucion.php': 'transacciones/solicitar-devolucion',
    'responder_devolucion.php': 'transacciones/responder-devolucion',
    
    // Denuncias
    'denunciar_usuario.php': 'denuncias/crear',
    'reportar_producto.php': 'denuncias',
    
    // Usuarios
    'perfil.php': 'usuarios/perfil',
    'editar_perfil.php': 'usuarios/editar',
    
    // Otros
    'toggle_silencio.php': 'chats/toggle-silencio',
    'cerrar_chats_automatico.php': 'chats/cerrar-automatico',
    'send_chat_image.php': 'chats',
    'finalizar_venta.php': 'chats',
};

/**
 * Obtiene el endpoint correcto según el sistema de API activo
 * 
 * @param {string} phpEndpoint - Nombre del endpoint PHP
 * @returns {string} Endpoint correcto según la configuración
 */
function mapEndpoint(phpEndpoint) {
    return ENDPOINT_MAPPING[phpEndpoint] || phpEndpoint;
}

/**
 * Obtiene la URL completa para una petición (path + query).
 * Así el front puede usar la misma llamada con PHP o Laravel según configuración.
 *
 * @param {string} pathWithQuery - Ej: 'api/productos.php?page=1&limit=12'
 * @returns {string} URL completa
 */
function getFullApiUrl(pathWithQuery) {
    if (!pathWithQuery || !pathWithQuery.startsWith('api/')) {
        return (window.BASE_URL || '') + (pathWithQuery || '');
    }
    const idx = pathWithQuery.indexOf('?');
    const path = idx >= 0 ? pathWithQuery.substring(0, idx) : pathWithQuery;
    const query = idx >= 0 ? pathWithQuery.substring(idx) : '';
    const fileName = path.replace('api/', '');
    const baseUrl = API_CONFIG.LARAVEL_URL || '';
    const mapped = ENDPOINT_MAPPING[fileName];
    const endpoint = mapped || fileName.replace('.php', '');
    return baseUrl + endpoint + query;
}

/**
 * Realiza una petición a la API con la configuración correcta
 * 
 * @param {string} endpoint - Endpoint a llamar
 * @param {Object} options - Opciones de fetch
 * @returns {Promise} Promesa con la respuesta
 */
async function apiRequest(endpoint, options = {}) {
    const url = getApiEndpoint(mapEndpoint(endpoint));
    
    // Configurar headers
    const headers = {
        ...getApiHeaders(),
        ...(options.headers || {})
    };
    
    let body = options.body;
    if (body && !(body instanceof FormData)) {
        if (typeof body === 'string' && body.includes('=')) {
            // Convertir URL encoded a JSON
            const params = new URLSearchParams(body);
            const jsonBody = {};
            for (const [key, value] of params) {
                jsonBody[key] = value;
            }
            body = JSON.stringify(jsonBody);
        } else if (typeof body === 'object') {
            body = JSON.stringify(body);
        }
    }
    
    // Realizar petición
    const response = await fetch(url, {
        ...options,
        headers,
        body
    });
    
    // Parsear respuesta
    const data = await response.json();
    
    return {
        ok: response.ok,
        status: response.status,
        data
    };
}

/**
 * Obtiene información sobre la configuración actual de la API
 * 
 * @returns {Object} Información de configuración
 */
function getApiInfo() {
    return {
        api_url: API_CONFIG.ACTIVE_URL,
        api_type: 'Hostinger (tumercadosena.shop)',
    };
}

// ============================================
// LOGGING (solo en desarrollo)
// ============================================

if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    console.log('🔧 API Configuration:', getApiInfo());
}

/**
 * URLs específicas para Chats/Mensajes cuando se usa API Laravel
 * Rutas según documentación: GET/POST/DELETE /api/chats, /api/chats/{id}, /api/chats/{id}/mensajes
 */
/**
 * URLs de Productos cuando se usa API Laravel
 * GET /api/productos (filtros), GET /api/productos/buscar?q=, GET /api/productos/vendedor/{id}
 */
function getLaravelProductosUrl(params) {
    const qs = params && typeof params === 'object' && Object.keys(params).length
        ? '?' + new URLSearchParams(params).toString()
        : '';
    return API_CONFIG.LARAVEL_URL + 'productos' + qs;
}
function getLaravelProductosBuscarUrl(busqueda, page, perPage) {
    const params = new URLSearchParams({ q: busqueda, per_page: perPage || 12 });
    if (page) params.set('page', page);
    return API_CONFIG.LARAVEL_URL + 'productos/buscar?' + params.toString();
}
function getLaravelProductosVendedorUrl(vendedorId) {
    return API_CONFIG.LARAVEL_URL + 'productos/vendedor/' + encodeURIComponent(vendedorId);
}

function getLaravelChatsListUrl() {
    return API_CONFIG.LARAVEL_URL + 'chats';
}
function getLaravelChatDetailUrl(chatId) {
    return API_CONFIG.LARAVEL_URL + 'chats/' + encodeURIComponent(chatId);
}
function getLaravelSendMessageUrl(chatId) {
    return API_CONFIG.LARAVEL_URL + 'chats/' + encodeURIComponent(chatId) + '/mensajes';
}
function getLaravelDeleteChatUrl(chatId) {
    return API_CONFIG.LARAVEL_URL + 'chats/' + encodeURIComponent(chatId);
}
function getLaravelStartChatUrl(productId) {
    return API_CONFIG.LARAVEL_URL + 'productos/' + encodeURIComponent(productId) + '/chats';
}
function getLaravelDeleteMessageUrl(mensajeId) {
    return API_CONFIG.LARAVEL_URL + 'mensajes/' + encodeURIComponent(mensajeId);
}
// Transferencias y compraventa (según documentación)
function getLaravelIniciarCompraventaUrl(chatId) {
    return API_CONFIG.LARAVEL_URL + 'chats/' + encodeURIComponent(chatId) + '/iniciar-compraventas';
}
function getLaravelTerminarCompraventaUrl(chatId) {
    return API_CONFIG.LARAVEL_URL + 'chats/' + encodeURIComponent(chatId) + '/terminar-compraventas';
}
function getLaravelIniciarDevolucionUrl(chatId) {
    return API_CONFIG.LARAVEL_URL + 'chats/' + encodeURIComponent(chatId) + '/iniciar-devoluciones';
}
function getLaravelTerminarDevolucionUrl(chatId) {
    return API_CONFIG.LARAVEL_URL + 'chats/' + encodeURIComponent(chatId) + '/terminar-devoluciones';
}
function getLaravelEstadosUrl() {
    return API_CONFIG.LARAVEL_URL + 'estados';
}
function getLaravelTransferenciasUrl() {
    return API_CONFIG.LARAVEL_URL + 'transferencias';
}
function getLaravelTransferenciasFiltrosUrl(estados) {
    const qs = estados && estados.length ? '?' + estados.map(e => 'estados[]=' + e).join('&') : '';
    return API_CONFIG.LARAVEL_URL + 'transferencias-filtros' + qs;
}
// Favoritos: GET /api/favoritos, POST /api/favoritos/{id}, DELETE /api/favoritos/{id}
function getLaravelFavoritosUrl() {
    return API_CONFIG.LARAVEL_URL + 'favoritos';
}
function getLaravelAddFavoritoUrl(usuarioId) {
    return API_CONFIG.LARAVEL_URL + 'favoritos/' + encodeURIComponent(usuarioId);
}
function getLaravelDeleteFavoritoUrl(usuarioId) {
    return API_CONFIG.LARAVEL_URL + 'favoritos/' + encodeURIComponent(usuarioId);
}
// Motivos: GET /api/motivos?tipo=denuncia|pqrs|notificacion
function getLaravelMotivosUrl(tipo) {
    return API_CONFIG.LARAVEL_URL + 'motivos' + (tipo ? '?tipo=' + encodeURIComponent(tipo) : '');
}
// Denuncias: POST /api/denuncias
function getLaravelDenunciasUrl() {
    return API_CONFIG.LARAVEL_URL + 'denuncias';
}
// PQRS: GET /api/pqrs, POST /api/pqrs
function getLaravelPqrsUrl() {
    return API_CONFIG.LARAVEL_URL + 'pqrs';
}
// Notificaciones: GET /api/notificaciones, GET /api/notificaciones/no-vistas, GET/DELETE /api/notificaciones/{id}
function getLaravelNotificacionesUrl() {
    return API_CONFIG.LARAVEL_URL + 'notificaciones';
}
function getLaravelNotificacionesNoVistasUrl() {
    return API_CONFIG.LARAVEL_URL + 'notificaciones/no-vistas';
}
function getLaravelNotificacionUrl(notificacionId) {
    return API_CONFIG.LARAVEL_URL + 'notificaciones/' + encodeURIComponent(notificacionId);
}

/**
 * Origen del storage en Hostinger (para reemplazar localhost en URLs que vengan de la API)
 */
function getStorageOrigin() {
    return (API_CONFIG.LARAVEL_STORAGE_URL || (API_CONFIG.LARAVEL_URL || '').replace(/\/api\/?$/, '') + 'storage/').replace(/\/$/, '');
}

/**
 * URL completa para avatar (Laravel storage o PHP uploads)
 * @param {string} path - Ruta: "usuarios/xxx.webp", "avatar_xx/yy.png", o URL absoluta
 * @returns {string}
 */
function getAvatarUrl(path) {
    if (!path || typeof path !== 'string') return (window.BASE_URL || '') + 'assets/images/default-avatar.jpg';
    if (path.startsWith('http://') || path.startsWith('https://')) {
        if (path.indexOf('localhost') !== -1) {
            var storageOrigin = getStorageOrigin();
            var match = path.match(/\/(?:storage\/)?(usuarios\/[^?#]+|avatar[^?#]*)/i);
            return match ? storageOrigin + '/' + match[1].replace(/^\/storage\//, '') : (window.BASE_URL || '') + 'assets/images/default-avatar.jpg';
        }
        return path;
    }
    if (API_CONFIG.LARAVEL_URL) {
        var base = API_CONFIG.LARAVEL_STORAGE_URL || (API_CONFIG.LARAVEL_URL.replace(/\/api\/?$/, '/') + 'storage/');
        var clean = path.replace(/^uploads\/usuarios\//, 'usuarios/').replace(/^usuarios\//, 'usuarios/');
        return base + (clean.startsWith('usuarios/') ? clean : 'usuarios/' + clean);
    }
    return (window.BASE_URL || '') + (path.startsWith('uploads/') ? path : 'uploads/usuarios/' + path);
}

/**
 * URL completa para una imagen de producto (Laravel storage o PHP uploads)
 * @param {string} path - Ruta: "productos/8/xxx.webp", "xxx.webp" (solo filename), o URL absoluta
 * @param {number} [productId] - ID del producto (obligatorio si path es solo el nombre del archivo)
 * @returns {string}
 */
function getProductImageUrl(path, productId) {
    if (!path || typeof path !== 'string') return '';
    if (path.startsWith('http://') || path.startsWith('https://')) {
        if (path.indexOf('localhost') !== -1) {
            var storageOrigin = getStorageOrigin();
            var match = path.match(/\/(?:storage\/)?(productos\/[^?#]+)/i);
            return match ? storageOrigin + '/' + match[1].replace(/^\/storage\//, '') : path;
        }
        return path;
    }
    if (path.startsWith('/')) {
        var origin = API_CONFIG.LARAVEL_URL ? API_CONFIG.LARAVEL_URL.replace(/\/api\/?$/, '') : '';
        return origin ? (origin + path) : path;
    }
    if (API_CONFIG.LARAVEL_URL) {
        var base = API_CONFIG.LARAVEL_STORAGE_URL || (API_CONFIG.LARAVEL_URL.replace(/\/api\/?$/, '/') + 'storage/');
        var clean = path.replace(/^uploads\/productos\//, 'productos/').replace(/^productos\//, 'productos/');
        // Si path es solo filename (sin /) y tenemos productId, usar productos/{id}/{filename}
        if (productId && clean.indexOf('/') === -1) {
            clean = 'productos/' + productId + '/' + clean;
        } else if (!clean.startsWith('productos/')) {
            clean = 'productos/' + clean;
        }
        return base + clean;
    }
    return (window.BASE_URL || '') + (path.startsWith('uploads/') ? path : 'uploads/productos/' + path.replace(/^productos\//, ''));
}

// Exponer en window para que script.js y otras páginas usen la misma configuración
window.getFullApiUrl = getFullApiUrl;
window.getApiHeaders = getApiHeaders;
window.getProductImageUrl = getProductImageUrl;
window.getAvatarUrl = getAvatarUrl;
window.API_CONFIG = API_CONFIG;
window.getLaravelProductosUrl = getLaravelProductosUrl;
window.getLaravelProductosBuscarUrl = getLaravelProductosBuscarUrl;
window.getLaravelProductosVendedorUrl = getLaravelProductosVendedorUrl;
window.getLaravelChatsListUrl = getLaravelChatsListUrl;
window.getLaravelChatDetailUrl = getLaravelChatDetailUrl;
window.getLaravelSendMessageUrl = getLaravelSendMessageUrl;
window.getLaravelDeleteChatUrl = getLaravelDeleteChatUrl;
window.getLaravelStartChatUrl = getLaravelStartChatUrl;
window.getLaravelDeleteMessageUrl = getLaravelDeleteMessageUrl;
window.getLaravelIniciarCompraventaUrl = getLaravelIniciarCompraventaUrl;
window.getLaravelTerminarCompraventaUrl = getLaravelTerminarCompraventaUrl;
window.getLaravelIniciarDevolucionUrl = getLaravelIniciarDevolucionUrl;
window.getLaravelTerminarDevolucionUrl = getLaravelTerminarDevolucionUrl;
window.getLaravelEstadosUrl = getLaravelEstadosUrl;
window.getLaravelTransferenciasUrl = getLaravelTransferenciasUrl;
window.getLaravelTransferenciasFiltrosUrl = getLaravelTransferenciasFiltrosUrl;
window.getLaravelFavoritosUrl = getLaravelFavoritosUrl;
window.getLaravelAddFavoritoUrl = getLaravelAddFavoritoUrl;
window.getLaravelDeleteFavoritoUrl = getLaravelDeleteFavoritoUrl;
window.getLaravelMotivosUrl = getLaravelMotivosUrl;
window.getLaravelDenunciasUrl = getLaravelDenunciasUrl;
window.getLaravelPqrsUrl = getLaravelPqrsUrl;
window.getLaravelNotificacionesUrl = getLaravelNotificacionesUrl;
window.getLaravelNotificacionesNoVistasUrl = getLaravelNotificacionesNoVistasUrl;
window.getLaravelNotificacionUrl = getLaravelNotificacionUrl;

// ============================================
// EXPORTAR (si se usa como módulo)
// ============================================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        API_CONFIG,
        getApiUrl,
        getApiEndpoint,
        isUsingLaravelApi,
        isUsingPhpApi,
        getApiHeaders,
        mapEndpoint,
        apiRequest,
        getApiInfo
    };
}
