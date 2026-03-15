/**
 * Configuración de APIs para JavaScript
 * 
 * Este archivo controla qué sistema de API se utiliza en el frontend
 * Debe estar sincronizado con config_api.php
 */

// ============================================
// CONFIGURACIÓN PRINCIPAL
// ============================================

const API_CONFIG = {
    // Usar API de Laravel (true) o PHP nativa (false). Se puede sobrescribir con window.USE_LARAVEL_API desde PHP.
    USE_LARAVEL: false,
    
    // URL base de la API de Laravel (se puede sobrescribir con window.LARAVEL_API_URL desde PHP)
    LARAVEL_URL: 'http://localhost:8000/api/',
    
    // Endpoints de autenticación Laravel (solo auth)
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
    
    // URL base de la API de PHP (se obtiene de window.BASE_URL)
    get PHP_URL() {
        return (window.BASE_URL || '') + 'api/';
    },
    
    // Obtener URL activa según configuración
    get ACTIVE_URL() {
        return this.USE_LARAVEL ? this.LARAVEL_URL : this.PHP_URL;
    }
};

// Sincronizar con PHP: un solo lugar (config_api.php) controla si se usa Laravel o PHP
if (typeof window.USE_LARAVEL_API !== 'undefined') {
    API_CONFIG.USE_LARAVEL = !!window.USE_LARAVEL_API;
}
if (typeof window.LARAVEL_API_URL !== 'undefined' && window.LARAVEL_API_URL) {
    API_CONFIG.LARAVEL_URL = window.LARAVEL_API_URL;
}
if (typeof window.LARAVEL_STORAGE_URL !== 'undefined' && window.LARAVEL_STORAGE_URL) {
    API_CONFIG.LARAVEL_STORAGE_URL = window.LARAVEL_STORAGE_URL;
} else if (API_CONFIG.LARAVEL_URL) {
    API_CONFIG.LARAVEL_STORAGE_URL = API_CONFIG.LARAVEL_URL.replace(/\/api\/?$/, '/') + 'storage/';
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
    const baseUrl = getApiUrl();
    
    // Si usa Laravel, remover la extensión .php si existe
    if (API_CONFIG.USE_LARAVEL) {
        endpoint = endpoint.replace('.php', '');
    }
    
    return baseUrl + endpoint;
}

/**
 * Verifica si se está usando la API de Laravel
 * 
 * @returns {boolean}
 */
function isUsingLaravelApi() {
    return API_CONFIG.USE_LARAVEL;
}

/**
 * Verifica si se está usando la API de PHP
 * 
 * @returns {boolean}
 */
function isUsingPhpApi() {
    return !API_CONFIG.USE_LARAVEL;
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
    
    if (API_CONFIG.USE_LARAVEL) {
        const token = localStorage.getItem('api_token');
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
    }
    
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
    
    // Bloqueados
    'toggle_bloqueo.php': 'bloqueados',
    
    // Confirmaciones y Devoluciones
    'solicitar_confirmacion.php': 'transacciones/solicitar-confirmacion',
    'responder_confirmacion.php': 'transacciones/responder-confirmacion',
    'solicitar_devolucion.php': 'transacciones/solicitar-devolucion',
    'responder_devolucion.php': 'transacciones/responder-devolucion',
    
    // Denuncias
    'denunciar_usuario.php': 'denuncias/crear',
    
    // Usuarios
    'perfil.php': 'usuarios/perfil',
    'editar_perfil.php': 'usuarios/editar',
    
    // Otros
    'toggle_silencio.php': 'chats/toggle-silencio',
    'cerrar_chats_automatico.php': 'chats/cerrar-automatico',
};

/**
 * Obtiene el endpoint correcto según el sistema de API activo
 * 
 * @param {string} phpEndpoint - Nombre del endpoint PHP
 * @returns {string} Endpoint correcto según la configuración
 */
function mapEndpoint(phpEndpoint) {
    if (!API_CONFIG.USE_LARAVEL) {
        return phpEndpoint;
    }
    
    return ENDPOINT_MAPPING[phpEndpoint] || phpEndpoint;
}

/**
 * Obtiene la URL completa para una petición (path + query).
 * Así el front puede usar la misma llamada con PHP o Laravel según configuración.
 *
 * @param {string} pathWithQuery - Ej: 'api/productos.php?page=1&limit=12'
 * @returns {string} URL completa (PHP o Laravel según USE_LARAVEL)
 */
function getFullApiUrl(pathWithQuery) {
    // Usar directamente ACTIVE_URL para evitar cualquier recursión con getApiUrl de script.js
    const baseUrl = API_CONFIG.USE_LARAVEL ? API_CONFIG.LARAVEL_URL : (window.BASE_URL || '') + 'api/';
    if (!pathWithQuery || !pathWithQuery.startsWith('api/')) {
        return (window.BASE_URL || '') + (pathWithQuery || '');
    }
    const idx = pathWithQuery.indexOf('?');
    const path = idx >= 0 ? pathWithQuery.substring(0, idx) : pathWithQuery;
    const query = idx >= 0 ? pathWithQuery.substring(idx) : '';
    const fileName = path.replace('api/', '');
    if (!API_CONFIG.USE_LARAVEL) {
        return (window.BASE_URL || '') + pathWithQuery;
    }
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
    
    // Si usa Laravel y el body no es FormData, convertir a JSON
    let body = options.body;
    if (API_CONFIG.USE_LARAVEL && body && !(body instanceof FormData)) {
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
        using_laravel: API_CONFIG.USE_LARAVEL,
        api_url: getApiUrl(),
        api_type: API_CONFIG.USE_LARAVEL ? 'Laravel' : 'PHP Nativo',
        laravel_url: API_CONFIG.LARAVEL_URL,
        php_url: API_CONFIG.PHP_URL,
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

/**
 * URL completa para una imagen de producto (Laravel storage o PHP uploads)
 * @param {string} path - Ruta relativa: "productos/xxx.webp", "uploads/productos/xxx.jpg", o URL absoluta
 * @returns {string}
 */
function getProductImageUrl(path) {
    if (!path || typeof path !== 'string') return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    // Laravel asset() puede devolver ruta relativa /storage/productos/xxx.webp
    if (path.startsWith('/')) {
        const origin = API_CONFIG.LARAVEL_URL ? API_CONFIG.LARAVEL_URL.replace(/\/api\/?$/, '') : '';
        return origin ? (origin + path) : path;
    }
    if (API_CONFIG.USE_LARAVEL) {
        const base = API_CONFIG.LARAVEL_STORAGE_URL || (API_CONFIG.LARAVEL_URL.replace(/\/api\/?$/, '/') + 'storage/');
        const clean = path.replace(/^uploads\/productos\//, 'productos/').replace(/^productos\//, 'productos/');
        return base + (clean.startsWith('productos/') ? clean : 'productos/' + clean);
    }
    return (window.BASE_URL || '') + (path.startsWith('uploads/') ? path : 'uploads/productos/' + path.replace(/^productos\//, ''));
}

// Exponer en window para que script.js y otras páginas usen la misma configuración
window.getFullApiUrl = getFullApiUrl;
window.getApiHeaders = getApiHeaders;
window.getProductImageUrl = getProductImageUrl;
window.API_CONFIG = API_CONFIG;
window.getLaravelChatsListUrl = getLaravelChatsListUrl;
window.getLaravelChatDetailUrl = getLaravelChatDetailUrl;
window.getLaravelSendMessageUrl = getLaravelSendMessageUrl;
window.getLaravelDeleteChatUrl = getLaravelDeleteChatUrl;
window.getLaravelStartChatUrl = getLaravelStartChatUrl;
window.getLaravelDeleteMessageUrl = getLaravelDeleteMessageUrl;

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
