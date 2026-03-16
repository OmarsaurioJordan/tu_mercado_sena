// JavaScript para funcionalidades del marketplace

// ==================== HELPER PARA RUTAS ====================
// Siempre usa el link global de la API (api_link.php → API_BASE_URL)
function getApiUrl(endpoint) {
    if (typeof window.getFullApiUrl === 'function') {
        return window.getFullApiUrl(endpoint);
    }
    var base = (typeof window !== 'undefined' && (window.API_BASE_URL || (window.API_CONFIG && window.API_CONFIG.LARAVEL_URL))) ? (window.API_BASE_URL || window.API_CONFIG.LARAVEL_URL) : '';
    if (!base) return (window.BASE_URL || '') + (endpoint || '');
    var e = (endpoint || '');
    var q = e.indexOf('?') >= 0 ? e.substring(e.indexOf('?')) : '';
    var path = e.replace(/^api\//, '').replace(/\.php.*$/, '').replace(/\?.*$/, '');
    return base + path + q;
}

// ==================== SISTEMA DE SONIDO DE NOTIFICACIÓN ====================
const NotificationSound = {
    audioContext: null,
    enabled: true,

    init() {
        // Cargar preferencia guardada
        const saved = localStorage.getItem('notificationSoundEnabled');
        this.enabled = saved !== 'false';
    },

    toggle() {
        this.enabled = !this.enabled;
        localStorage.setItem('notificationSoundEnabled', this.enabled);
        return this.enabled;
    },

    play() {
        if (!this.enabled) return;

        try {
            // Crear AudioContext si no existe
            if (!this.audioContext) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }

            const ctx = this.audioContext;
            const now = ctx.currentTime;

            // Crear oscilador para tono de notificación
            const oscillator = ctx.createOscillator();
            const gainNode = ctx.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(ctx.destination);

            // Tono tipo "mensaje" (dos notas cortas)
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, now); // La nota A5
            oscillator.frequency.setValueAtTime(1100, now + 0.1); // Sube

            // Volumen suave
            gainNode.gain.setValueAtTime(0.3, now);
            gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3);

            oscillator.start(now);
            oscillator.stop(now + 0.3);

        } catch (e) {
            console.log('Audio no soportado:', e);
        }
    }
};

// Inicializar sistema de sonido
NotificationSound.init();

// Función global para reproducir sonido
function playNotificationSound() {
    NotificationSound.play();
}

// Función global para toggle
function toggleNotificationSound() {
    return NotificationSound.toggle();
}

// ==================== TEMA OSCURO/CLARO ====================
function initTheme() {
    const pagesForceLight = ['welcome.php', 'login.php', 'register.php'];
    const currentPage = window.location.pathname.split("/").pop();

    if (pagesForceLight.includes(currentPage)) {
        localStorage.setItem('theme', 'light');
        document.documentElement.setAttribute('data-theme', 'light');
        return;
    }

    const savedTheme = localStorage.getItem("theme") || "light";
    document.documentElement.setAttribute("data-theme", savedTheme);
    updateThemeIcon(savedTheme);
}

document.addEventListener('DOMContentLoaded', initTheme);

// ==================== MENÚ HAMBURGUESA ====================
function initHamburgerMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const menuClose = document.getElementById('menuClose');
    const mainNav = document.getElementById('mainNav');
    const menuOverlay = document.getElementById('menuOverlay');

    if (!menuToggle || !mainNav) return;

    // Abrir menú
    menuToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        menuToggle.classList.toggle('active');
        mainNav.classList.toggle('active');
        if (menuOverlay) menuOverlay.classList.toggle('active');
        document.body.style.overflow = mainNav.classList.contains('active') ? 'hidden' : '';
    });

    // Cerrar menú con botón X
    if (menuClose) {
        menuClose.addEventListener('click', function () {
            closeHamburgerMenu();
        });
    }

    // Cerrar menú con overlay
    if (menuOverlay) {
        menuOverlay.addEventListener('click', function () {
            closeHamburgerMenu();
        });
    }

    // Cerrar menú al hacer clic en un enlace
    const navLinks = mainNav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Pequeño delay para ver el efecto antes de navegar
            setTimeout(closeHamburgerMenu, 100);
        });
    });
}

function closeHamburgerMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    const menuOverlay = document.getElementById('menuOverlay');

    if (menuToggle) menuToggle.classList.remove('active');
    if (mainNav) mainNav.classList.remove('active');
    if (menuOverlay) menuOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.innerHTML = theme === 'dark' ? '<i class="ri-sun-line"></i>' : '<i class="ri-moon-line"></i>';
    }
}

// ==================== FAVORITOS ====================
function toggleFavorito(btn) {
    const vendedorId = btn.getAttribute('data-vendedor-id');
    const icon = btn.querySelector('.fav-icon');
    const textSpan = btn.querySelector('.fav-text');

    if (!vendedorId) return;

    btn.disabled = true;
    btn.style.opacity = '0.7';

    const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelAddFavoritoUrl === 'function';
    const isFavorite = btn.classList.contains('active');

    if (useLaravel) {
        const url = isFavorite ? window.getLaravelDeleteFavoritoUrl(vendedorId) : window.getLaravelAddFavoritoUrl(vendedorId);
        const method = isFavorite ? 'DELETE' : 'POST';
        fetch(url, {
            method: method,
            headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) }
        })
            .then(response => response.json())
            .then(data => {
                const ok = data.success || data.status === 'success';
                if (ok) {
                    if (isFavorite) {
                        btn.classList.remove('active');
                        if (icon) icon.className = 'fav-icon ri-heart-3-line';
                        if (textSpan) textSpan.textContent = 'Añadir a Favoritos';
                    } else {
                        btn.classList.add('active');
                        if (icon) icon.className = 'fav-icon ri-heart-3-fill';
                        if (textSpan) textSpan.textContent = 'En Favoritos';
                    }
                } else {
                    const msg = data.message || data.error || 'No se pudo actualizar';
                    if (msg.indexOf('ya se encuentra') !== -1) {
                        btn.classList.add('active');
                        if (icon) icon.className = 'fav-icon ri-heart-3-fill';
                        if (textSpan) textSpan.textContent = 'En Favoritos';
                    } else {
                        alert('Error: ' + msg);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            })
            .finally(() => {
                btn.disabled = false;
                btn.style.opacity = '1';
            });
        return;
    }

    const formData = new FormData();
    formData.append('vendedor_id', vendedorId);
    const favoritosUrl = getApiUrl('api/toggle_favorito.php');
    fetch(favoritosUrl, {
        method: 'POST',
        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.is_favorite) {
                    btn.classList.add('active');
                    if (icon) icon.className = 'fav-icon ri-heart-3-fill';
                    if (textSpan) textSpan.textContent = 'En Favoritos';
                } else {
                    btn.classList.remove('active');
                    if (icon) icon.className = 'fav-icon ri-heart-3-line';
                    if (textSpan) textSpan.textContent = 'Añadir a Favoritos';
                }
            } else {
                alert('Error: ' + (data.error || 'No se pudo actualizar'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        })
        .finally(() => {
            btn.disabled = false;
            btn.style.opacity = '1';
        });
}

// ==================== CHAT EN TIEMPO REAL ====================
let chatPollingInterval = null;
let lastMessageId = 0;

function initChatRealTime(chatId) {
    if (!chatId) return;

    // Cargar últimos mensajes al iniciar
    loadNewMessages(chatId);

    // Polling cada 4 segundos para nuevos mensajes (reduce carga en /api/chats)
    chatPollingInterval = setInterval(() => {
        loadNewMessages(chatId);
    }, 4000);

    // Limpiar intervalo al salir de la página
    window.addEventListener('beforeunload', () => {
        if (chatPollingInterval) {
            clearInterval(chatPollingInterval);
        }
    });
}

function loadNewMessages(chatId) {
    if (!chatId) return;

    const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelChatDetailUrl === 'function';
    if (useLaravel) {
        fetch(window.getLaravelChatDetailUrl(chatId), {
            headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) }
        })
            .then(response => response.json())
            .then(raw => {
                const data = normalizeLaravelChatDetail(raw);
                if (data.success && data.messages && data.messages.length > 0) {
                    const chatMessages = document.getElementById('chatMessages');
                    if (chatMessages) {
                        data.messages.forEach(message => {
                            addMessageToChat(message, chatMessages);
                            lastMessageId = Math.max(lastMessageId, message.id);
                        });
                        scrollChatToBottom();
                    }
                }
            })
            .catch(error => console.error('Error al cargar mensajes:', error));
        return;
    }

    fetch(getApiUrl(`api/get_messages.php?chat_id=${chatId}&last_id=${lastMessageId}`), {
        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    data.messages.forEach(message => {
                        addMessageToChat(message, chatMessages);
                        lastMessageId = Math.max(lastMessageId, message.id);
                    });
                    scrollChatToBottom();
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar mensajes:', error);
        });
}

function normalizeLaravelChatDetail(raw) {
    const payload = raw.data || raw.chat_detalle || raw;
    const compradorId = payload.comprador_id;
    const currentUserId = window.CURRENT_USER_ID;
    const mensajes = payload.mensajes || [];
    const messages = mensajes.map(m => ({
        id: m.id,
        mensaje: m.mensaje || '',
        es_mio: (currentUserId != null && compradorId != null)
            ? (m.es_comprador === (compradorId === currentUserId) ? 1 : 0)
            : (m.es_comprador ? 1 : 0),
        imagen: m.imagen || null,
        fecha_registro: m.fecha_registro || m.created_at || ''
    }));
    return { success: (raw.status === 'success' || !!payload), messages };
}

function addMessageToChat(message, container) {
    // Verificar si el mensaje ya existe
    if (document.getElementById(`message-${message.id}`)) {
        return;
    }

    const messageDiv = document.createElement('div');
    messageDiv.id = `message-${message.id}`;
    messageDiv.className = `message ${message.es_mio == 1 ? 'message-sent' : 'message-received'}`;
    const messageText = document.createElement('p');
    messageText.innerHTML = message.mensaje.replace(/\n/g, '<br>');

    const messageTime = document.createElement('span');
    messageTime.className = 'message-time';
    messageTime.textContent = formatMessageTime(message.fecha_registro);

    messageDiv.appendChild(messageText);
    messageDiv.appendChild(messageTime);
    
    // Detectar tipo de mensaje
    const esSolicitudConfirmacion = message.mensaje.includes('SOLICITUD DE CONFIRMACIÓN');
    const esSolicitudDevolucion = message.mensaje.includes('SOLICITUD DE DEVOLUCIÓN');
    const esCompraConfirmada = message.mensaje.includes('✅ COMPRA CONFIRMADA');
    const esRespuesta = message.mensaje.includes('✅') || message.mensaje.includes('❌') || 
                        message.mensaje.includes('CONFIRMADA') || message.mensaje.includes('RECHAZADA') ||
                        message.mensaje.includes('ACEPTADA');
    
    // Si es una compra confirmada, ocultar botón Confirmar y mostrar botón Devolver
    if (esCompraConfirmada) {
        const btnConfirmarCompra = document.getElementById('btnConfirmarCompra');
        const btnDevolver = document.getElementById('btnDevolver');
        
        if (btnConfirmarCompra) {
            btnConfirmarCompra.style.display = 'none';
        }
        if (btnDevolver) {
            btnDevolver.style.display = 'flex';
        }
    }
    
    // Mostrar botones si es una solicitud que no ha sido respondida
    // Ambas personas pueden hacer clic en los botones
    const mostrarBotones = (esSolicitudConfirmacion || esSolicitudDevolucion) && !esRespuesta;
    
    if (mostrarBotones) {
        const buttonsDiv = document.createElement('div');
        buttonsDiv.style.cssText = 'display: flex; gap: 0.5rem; margin-top: 1rem;';
        buttonsDiv.id = `buttons-${message.id}`;
        
        if (esSolicitudConfirmacion) {
            buttonsDiv.innerHTML = `
                <button onclick="responderConfirmacion('confirmar', ${message.id})" 
                        style="flex: 1; padding: 0.75rem; background: #28a745; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="ri-check-line"></i> Confirmar
                </button>
                <button onclick="responderConfirmacion('rechazar', ${message.id})" 
                        style="flex: 1; padding: 0.75rem; background: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="ri-close-line"></i> Rechazar
                </button>
            `;
        } else if (esSolicitudDevolucion) {
            buttonsDiv.innerHTML = `
                <button onclick="responderDevolucion('aceptar', ${message.id})" 
                        style="flex: 1; padding: 0.75rem; background: #28a745; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="ri-check-line"></i> Aceptar
                </button>
                <button onclick="responderDevolucion('rechazar', ${message.id})" 
                        style="flex: 1; padding: 0.75rem; background: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="ri-close-line"></i> Rechazar
                </button>
            `;
        }
        
        messageDiv.appendChild(buttonsDiv);
    }
    
    container.appendChild(messageDiv);
}

function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);

    if (minutes < 1) return 'Ahora';
    if (minutes < 60) return `Hace ${minutes} min`;
    if (minutes < 1440) return `Hace ${Math.floor(minutes / 60)} h`;

    return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

// Auto-scroll en chat
function scrollChatToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Enviar mensaje con AJAX
function sendMessage(chatId, messageText, callback) {
    if (!messageText.trim() || !chatId) return;

    const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelSendMessageUrl === 'function';
    if (useLaravel) {
        const headers = { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Content-Type': 'application/json' };
        fetch(window.getLaravelSendMessageUrl(chatId), {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ mensaje: messageText })
        })
            .then(response => response.json())
            .then(raw => {
                const data = raw.status === 'success' && raw.nuevo_mensaje
                    ? { success: true, message: { id: raw.nuevo_mensaje.id, mensaje: raw.nuevo_mensaje.mensaje, es_mio: 1, fecha_registro: raw.nuevo_mensaje.fecha_registro || raw.nuevo_mensaje.created_at } }
                    : { success: false, error: raw.message || raw.error };
                applySendMessageResponse(chatId, data, callback);
            })
            .catch(error => {
                console.error('Error al enviar mensaje:', error);
                alert('Error al enviar mensaje. Por favor intenta de nuevo.');
            });
        return;
    }

    const formData = new FormData();
    formData.append('chat_id', chatId);
    formData.append('mensaje', messageText);

    fetch(getApiUrl('api/send_message.php'), {
        method: 'POST',
        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) },
        body: formData
    })
        .then(response => response.json())
        .then(data => applySendMessageResponse(chatId, data, callback))
        .catch(error => {
            console.error('Error al enviar mensaje:', error);
            alert('Error al enviar mensaje. Por favor intenta de nuevo.');
        });
}

function applySendMessageResponse(chatId, data, callback) {
    if (data.success) {
        if (window.currentModalChatId === chatId) {
            const messagesContainer = document.getElementById('chatModalMessages');
            if (messagesContainer && data.message) {
                addMessageToModal(data.message, messagesContainer);
                window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, data.message.id);
                scrollModalToBottom();
            } else {
                loadModalMessages(chatId);
            }
        } else {
            const textarea = document.getElementById('messageInput');
            if (textarea) textarea.value = '';
            if (data.message) {
                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    addMessageToChat(data.message, chatMessages);
                    lastMessageId = Math.max(lastMessageId, data.message.id);
                    scrollChatToBottom();
                }
            } else {
                loadNewMessages(chatId);
            }
        }
        loadNotifications();
        if (callback) callback();
    } else {
        alert('Error al enviar mensaje: ' + (data.error || 'Error desconocido'));
    }
}

// ==================== SISTEMA DE NOTIFICACIONES ====================
let notificationsPollingInterval = null;
let currentChatModal = null;
let lastNotificationCheck = Date.now();

function initNotifications() {
    // No cargar chats/notificaciones si el usuario no está autenticado (evita 401 en login/register)
    if (window.CURRENT_USER_ID == null || window.CURRENT_USER_ID === 0) return;
    window.sessionStartTime = Date.now();
    window.isFirstNotificationLoad = true;

    // Cargar notificaciones al iniciar (silencioso - no mostrar popups de mensajes existentes)
    loadNotifications(true);

    // Después de 2 segundos, empezar a escuchar nuevos mensajes reales
    setTimeout(() => {
        window.isFirstNotificationLoad = false;
    }, 2000);

    // Polling cada 15 s (Laravel) o 10 s (PHP) para no saturar el servidor ni /api/chats
    const notifyInterval = (window.API_CONFIG && window.API_CONFIG.USE_LARAVEL) ? 15000 : 10000;
    notificationsPollingInterval = setInterval(() => {
        loadNotifications(false); // false = puede mostrar notificaciones de mensajes NUEVOS
    }, notifyInterval);

    // Limpiar intervalo al salir
    window.addEventListener('beforeunload', () => {
        if (notificationsPollingInterval) {
            clearInterval(notificationsPollingInterval);
        }
    });
}

function loadNotifications(silent = false) {
    if (window.CURRENT_USER_ID == null || window.CURRENT_USER_ID === 0) return;
    const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL;

    if (useLaravel && typeof window.getLaravelChatsListUrl === 'function') {
        fetch(window.getLaravelChatsListUrl(), {
            method: 'GET',
            headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) }
        })
            .then(response => response.json())
            .then(raw => {
                const chats = Array.isArray(raw) ? raw : (raw.data || raw.chats || []);
                const normalized = normalizeLaravelChatsList(chats);
                const data = {
                    success: true,
                    total_no_leidos: normalized.total_no_leidos,
                    chats: normalized.chats
                };
                applyNotificationsData(data, silent);
            })
            .catch(() => { /* CORS o red: no mostrar error en consola en localhost */ });
        return;
    }

    fetch(getApiUrl('api/get_chats_notificaciones.php'), {
        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) applyNotificationsData(data, silent);
        })
        .catch(() => {});
}

function normalizeLaravelChatsList(chats) {
    const currentUserId = window.CURRENT_USER_ID != null ? Number(window.CURRENT_USER_ID) : null;
    let total_no_leidos = 0;
    const out = (chats || []).map(c => {
        const compradorId = c.comprador_id != null ? Number(c.comprador_id) : null;
        let mensajes_no_leidos = 0;
        if (compradorId != null && currentUserId != null && !Number.isNaN(compradorId) && !Number.isNaN(currentUserId)) {
            const soyComprador = compradorId === currentUserId;
            mensajes_no_leidos = soyComprador ? (!c.visto_comprador ? 1 : 0) : (!c.visto_vendedor ? 1 : 0);
        } else {
            mensajes_no_leidos = 0;
        }
        total_no_leidos += mensajes_no_leidos;
        return {
            chat_id: c.id,
            producto_nombre: (c.producto && c.producto.nombre) ? c.producto.nombre : 'Chat',
            otro_usuario: (c.usuario && c.usuario.nickname) ? c.usuario.nickname : '',
            ultimo_mensaje: {
                mensaje: c.ultimoMensajeTexto || '',
                fecha_registro: c.fechaUltimoMensaje || ''
            },
            mensajes_no_leidos: mensajes_no_leidos
        };
    });
    return { chats: out, total_no_leidos };
}

function applyNotificationsData(data, silent) {
    if (!data || !data.success) return;
    updateNotificationCount(data.total_no_leidos);
    const chatsList = document.getElementById('chatsList');
    if (chatsList && (chatsList.classList.contains('active') || silent)) {
        updateChatsList(data.chats);
    }
    if (!silent && !window.isFirstNotificationLoad && data.chats && !window.currentModalChatId) {
        checkNewMessages(data.chats);
    }
}

function updateNotificationCount(count) {
    const notificationCount = document.getElementById('notificationCount');
    if (notificationCount) {
        if (count > 0) {
            notificationCount.textContent = count > 99 ? '99+' : count;
            notificationCount.classList.remove('hidden');
        } else {
            notificationCount.classList.add('hidden');
        }
    }
}

function updateChatsList(chats) {
    const chatsList = document.getElementById('chatsList');
    if (!chatsList) return;

    if (!chats || chats.length === 0) {
        chatsList.innerHTML = '<div class="chat-item"><p style="padding: 1rem; text-align: center; color: var(--color-text-light);">No tienes chats activos</p></div>';
        return;
    }

    chatsList.innerHTML = chats.map(chat => {
        const unread = parseInt(chat.mensajes_no_leidos) || 0;
        const lastMsg = chat.ultimo_mensaje ? chat.ultimo_mensaje.mensaje : 'Sin mensajes';
        const lastMsgPreview = lastMsg.length > 50 ? lastMsg.substring(0, 50) + '...' : lastMsg;

        return `
            <div class="chat-item" data-chat-id="${chat.chat_id}" onclick="openChatModal(${chat.chat_id}, '${escapeHtml(chat.producto_nombre)}', '${escapeHtml(chat.otro_usuario)}')">
                <div class="chat-item-info">
                    <div class="chat-item-title">${escapeHtml(chat.producto_nombre)}</div>
                    <div class="chat-item-message">${escapeHtml(chat.otro_usuario)}: ${escapeHtml(lastMsgPreview)}</div>
                </div>
                ${unread > 0 ? `<span class="chat-item-badge">${unread > 99 ? '99+' : unread}</span>` : '<span class="chat-item-badge hidden">0</span>'}
            </div>
        `;
    }).join('');
}


function checkNewMessages(chats) {
    const storedLastCheck = localStorage.getItem('lastNotificationCheck');
    const lastCheckTime = storedLastCheck ? parseInt(storedLastCheck) : Date.now();

    // Tiempo mínimo: el más reciente entre el último check y el inicio de la sesión
    const sessionStart = window.sessionStartTime || Date.now();
    const minimumTime = Math.max(lastCheckTime, sessionStart);

    let newMessagesFound = false;

    chats.forEach(chat => {
        if (chat.ultimo_mensaje && chat.mensajes_no_leidos > 0) {
            try {
                // Convertir fecha de MySQL a timestamp
                const fechaStr = chat.ultimo_mensaje.fecha_registro;
                const fecha = new Date(fechaStr.replace(' ', 'T'));
                const lastMsgTime = fecha.getTime();

                // Si el mensaje es nuevo (después de la última verificación Y después del inicio de sesión)
                if (lastMsgTime > minimumTime) {
                    newMessagesFound = true;
                    const notificaPush = window.NOTIFICA_PUSH !== undefined ? window.NOTIFICA_PUSH : 1;
                    if (notificaPush) {
                        const messagePreview = chat.ultimo_mensaje.mensaje.length > 50
                            ? chat.ultimo_mensaje.mensaje.substring(0, 50) + '...'
                            : chat.ultimo_mensaje.mensaje;

                        showBrowserNotification(
                            chat.producto_nombre,
                            `${chat.otro_usuario}: ${messagePreview}`,
                            chat.chat_id
                        );

                        requestBrowserNotification(chat.producto_nombre, `${chat.otro_usuario} te escribió`);
                    }
                }
            } catch (e) {
                console.error('Error al procesar mensaje:', e);
            }
        }
    });

    // Actualizar último check
    localStorage.setItem('lastNotificationCheck', Date.now().toString());
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Fallback para imagen de producto: si falla la URL principal (ej. Laravel storage), intenta uploads/productos/
 * y solo entonces el placeholder borroso.
 */
function productImageFallback(imgEl) {
    const fallback = imgEl && imgEl.getAttribute('data-fallback-uploads');
    if (fallback && imgEl.getAttribute('data-fallback-tried') !== '1') {
        imgEl.setAttribute('data-fallback-tried', '1');
        imgEl.src = fallback;
    } else {
        imgEl.onerror = null;
        imgEl.src = (window.BASE_URL || '') + 'assets/images/default-product.jpg';
    }
}
window.productImageFallback = productImageFallback;

function showBrowserNotification(title, message, chatId) {
    // Eliminar notificaciones anteriores
    const existing = document.querySelectorAll('.browser-notification');
    existing.forEach(n => n.remove());

    const notification = document.createElement('div');
    notification.className = 'browser-notification';
    notification.onclick = () => {
        openChatModal(chatId, title, '');
        notification.remove();
    };

    notification.innerHTML = `
        <div class="browser-notification-header">
            <div class="browser-notification-title">${escapeHtml(title)}</div>
            <button class="browser-notification-close" onclick="event.stopPropagation(); this.parentElement.parentElement.remove()">×</button>
        </div>
        <div class="browser-notification-message">${escapeHtml(message)}</div>
        <div class="browser-notification-time">Hace un momento</div>
    `;

    document.body.appendChild(notification);

    // 🔔 Reproducir sonido de notificación
    playNotificationSound();

    // Auto-eliminar después de 5 segundos
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function requestBrowserNotification(title, body) {
    if (!('Notification' in window)) {
        return;
    }

    if (Notification.permission === 'granted') {
        new Notification(title, {
            body: body,
            icon: '/favicon.ico',
            tag: 'chat-notification'
        });
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                new Notification(title, {
                    body: body,
                    icon: '/favicon.ico',
                    tag: 'chat-notification'
                });
            }
        });
    }
}


// ==================== MODAL DE CHAT ====================
function openChatModal(chatId, productoNombre, otroUsuario) {
    // Cerrar lista de chats
    const chatsList = document.getElementById('chatsList');
    if (chatsList) {
        chatsList.classList.remove('active');
    }

    // Si ya hay un modal abierto, cerrarlo
    if (currentChatModal) {
        closeChatModal();
    }

    // Crear modal
    const modal = document.createElement('div');
    modal.className = 'chat-modal active';
    modal.id = 'chatModal';

    modal.innerHTML = `
        <div class="chat-modal-content">
            <div class="chat-modal-header">
                <h3>${escapeHtml(productoNombre)}</h3>
                <button class="chat-modal-close" onclick="closeChatModal()">×</button>
            </div>
            <div class="chat-modal-body">
                <div class="chat-modal-messages" id="chatModalMessages"></div>
                <div class="chat-modal-input">
                    <form id="chatModalForm" onsubmit="event.preventDefault(); sendModalMessage(${chatId})">
                        <textarea id="chatModalInput" placeholder="Escribe un mensaje..." required rows="2"></textarea>
                        <button type="submit" class="btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    currentChatModal = modal;

    // Inicializar variables antes de cargar mensajes
    window.currentModalChatId = chatId;
    window.currentModalLastMessageId = 0;

    // Cargar todos los mensajes al abrir el modal
    loadAllModalMessages(chatId);

    // Inicializar polling para este chat
    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
    }

    // Polling para mensajes del modal cada 4 s (reduce peticiones a /api/chats)
    chatPollingInterval = setInterval(() => {
        if (window.currentModalChatId === chatId) {
            loadModalMessages(chatId, true);
            loadNotifications(true);
        }
    }, 4000);

    // Enviar con Enter
    const textarea = document.getElementById('chatModalInput');
    if (textarea) {
        textarea.focus();
        textarea.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendModalMessage(chatId);
            }
        });
    }

    // Cerrar al hacer clic fuera
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeChatModal();
        }
    });
}

function closeChatModal() {
    if (currentChatModal) {
        currentChatModal.remove();
        currentChatModal = null;
        const closedChatId = window.currentModalChatId;
        window.currentModalChatId = null;
        window.currentModalLastMessageId = null;

        if (chatPollingInterval) {
            clearInterval(chatPollingInterval);
            chatPollingInterval = null;
        }

        // Recargar notificaciones al cerrar el modal
        setTimeout(() => {
            loadNotifications();
        }, 500);
    }
}

function loadModalMessages(chatId, silent = false) {
    const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelChatDetailUrl === 'function';
    if (useLaravel) {
        fetch(window.getLaravelChatDetailUrl(chatId), { headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) } })
            .then(r => r.json())
            .then(raw => {
                const data = normalizeLaravelChatDetail(raw);
                if (data.success && data.messages && data.messages.length > 0) {
                    const messagesContainer = document.getElementById('chatModalMessages');
                    if (messagesContainer) {
                        data.messages.forEach(message => {
                            addMessageToModal(message, messagesContainer);
                            window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, message.id);
                        });
                        scrollModalToBottom();
                        loadNotifications(true);
                    }
                } else if (!silent) loadAllModalMessages(chatId);
            })
            .catch(e => console.error('Error al cargar mensajes del modal:', e));
        return;
    }
    const lastId = window.currentModalLastMessageId || 0;
    fetch(getApiUrl(`api/get_messages.php?chat_id=${chatId}&last_id=${lastId}`), {
        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                const messagesContainer = document.getElementById('chatModalMessages');
                if (messagesContainer) {
                    data.messages.forEach(message => {
                        addMessageToModal(message, messagesContainer);
                        window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, message.id);
                    });
                    scrollModalToBottom();
                    loadNotifications(true);
                }
            } else if (!silent) loadAllModalMessages(chatId);
        })
        .catch(error => console.error('Error al cargar mensajes del modal:', error));
}

function loadAllModalMessages(chatId) {
    const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelChatDetailUrl === 'function';
    if (useLaravel) {
        fetch(window.getLaravelChatDetailUrl(chatId), { headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) } })
            .then(r => r.json())
            .then(raw => {
                const data = normalizeLaravelChatDetail(raw);
                if (data.success && data.messages) {
                    const messagesContainer = document.getElementById('chatModalMessages');
                    if (messagesContainer) {
                        messagesContainer.innerHTML = '';
                        data.messages.forEach(message => {
                            addMessageToModal(message, messagesContainer);
                            window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, message.id);
                        });
                        scrollModalToBottom();
                        loadNotifications(true);
                    }
                }
            })
            .catch(e => console.error('Error al cargar todos los mensajes:', e));
        return;
    }
    fetch(getApiUrl(`api/get_messages.php?chat_id=${chatId}&last_id=0`), {
        headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages) {
                const messagesContainer = document.getElementById('chatModalMessages');
                if (messagesContainer) {
                    messagesContainer.innerHTML = '';
                    data.messages.forEach(message => {
                        addMessageToModal(message, messagesContainer);
                        window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, message.id);
                    });
                    scrollModalToBottom();
                    loadNotifications(true);
                }
            }
        })
        .catch(error => console.error('Error al cargar todos los mensajes:', error));
}

function addMessageToModal(message, container) {
    if (document.getElementById(`modal-message-${message.id}`)) {
        return;
    }

    const messageDiv = document.createElement('div');
    messageDiv.id = `modal-message-${message.id}`;
    messageDiv.className = `message ${message.es_mio == 1 ? 'message-sent' : 'message-received'}`;

    const messageText = document.createElement('p');
    messageText.innerHTML = message.mensaje.replace(/\n/g, '<br>');

    const messageTime = document.createElement('span');
    messageTime.className = 'message-time';
    messageTime.textContent = formatMessageTime(message.fecha_registro);

    messageDiv.appendChild(messageText);
    messageDiv.appendChild(messageTime);
    container.appendChild(messageDiv);
}

function sendModalMessage(chatId) {
    const textarea = document.getElementById('chatModalInput');
    if (!textarea || !textarea.value.trim()) return;

    const messageText = textarea.value;
    textarea.value = '';
    textarea.disabled = true;

    sendMessage(chatId, messageText, () => {
        // Callback después de enviar
        textarea.disabled = false;
        textarea.focus();
        // Recargar mensajes
        loadModalMessages(chatId);
        // Recargar notificaciones
        loadNotifications();
    });
}

function scrollModalToBottom() {
    const messagesContainer = document.getElementById('chatModalMessages');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', function () {
    // El tema ya se inicializó arriba
    // initTheme(); // Eliminado redundante

    // Inicializar menú hamburguesa
    initHamburgerMenu();

    // Alternar tema
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    // Sistema de notificaciones
    initNotifications();

    // Galería (si existe)
    initProductGallery();

    // Subida de imágenes y Zoom
    initMultipleImagesUpload();
    initImageZoom();

    // Infinite Scroll (si aplica)
    if (document.getElementById('productsGrid')) {
        initInfiniteScroll();
    }

    // Toggle lista de chats
    const notificationIcon = document.getElementById('notificationIcon');
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function (e) {
            e.stopPropagation();
            const chatsList = document.getElementById('chatsList');
            if (chatsList) {
                chatsList.classList.toggle('active');
                if (chatsList.classList.contains('active')) {
                    loadNotifications();
                }
            }
        });

        // Cerrar lista al hacer clic fuera
        document.addEventListener('click', function (e) {
            const chatsList = document.getElementById('chatsList');
            if (chatsList && !chatsList.contains(e.target) && !notificationIcon.contains(e.target)) {
                chatsList.classList.remove('active');
            }
        });
    }

    // Solicitar permiso para notificaciones del navegador
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Chat en tiempo real
    const chatId = window.chatId || getUrlParameter('id');
    if (chatId && document.getElementById('chatMessages')) {
        // Obtener el último ID de mensaje
        lastMessageId = window.lastMessageId || 0;
        if (lastMessageId === 0) {
            const messages = document.querySelectorAll('.message');
            if (messages.length > 0) {
                const lastMessage = messages[messages.length - 1];
                const lastId = lastMessage.id.replace('message-', '');
                lastMessageId = parseInt(lastId) || 0;
            }
        }
        initChatRealTime(chatId);
    }

    // Auto-scroll en chat
    scrollChatToBottom();

    // Formulario de mensaje mejorado con AJAX
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const textarea = document.getElementById('messageInput');
            const chatId = window.chatId || getUrlParameter('id');
            if (textarea && chatId && textarea.value.trim()) {
                sendMessage(chatId, textarea.value);
            }
        });

        // Enviar con Enter (Shift+Enter para nueva línea)
        const textarea = document.getElementById('messageInput');
        if (textarea) {
            textarea.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    messageForm.dispatchEvent(new Event('submit'));
                }
            });
        }
    }
    // Solo validar formularios que no sean chat
    const forms = document.querySelectorAll('form:not(#messageForm)');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor completa todos los campos requeridos');
            }
        });
    });

    // Preview de imagen antes de subir (excluyendo el avatar que tiene su propio preview)
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]:not(#imagen):not(.avatar-input)');
    imageInputs.forEach(input => {
        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    let preview = input.parentElement.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview';
                        preview.style.maxWidth = '200px';
                        preview.style.height = 'auto';
                        preview.style.marginTop = '0.5rem';
                        preview.style.borderRadius = '4px';
                        input.parentElement.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Formateo automático de precios
    const priceInputs = document.querySelectorAll('input[type="number"][name="precio"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value;
            if (value < 0) {
                e.target.value = 0;
            }
        });
    });

    // Navegación de settings
    const settingsLinks = document.querySelectorAll('.settings-sidebar a');
    settingsLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            if (!this.hasAttribute('data-section')) {
                return;
            }
            e.preventDefault();
            const target = this.getAttribute('data-section');
            if (target) {
                // Ocultar todas las secciones
                document.querySelectorAll('.settings-section').forEach(section => {
                    section.classList.remove('active');
                });
                // Remover active de todos los links
                settingsLinks.forEach(l => l.classList.remove('active'));
                // Mostrar sección seleccionada
                const targetElement = document.getElementById(target);
                if (targetElement) {
                    targetElement.classList.add('active');
                    this.classList.add('active');
                }
            }
        });
    });

    // --- LÓGICA DE AVATAR (Perfil) ---
    const avatarEditButton = document.getElementById('avatarEditButton');
    const avatarInputHidden = document.getElementById('avatarInputHidden');
    const avatarUploadForm = document.getElementById('avatarUploadForm');
    const avatarPhoto = document.getElementById('avatarPhoto');
    const headerAvatar = document.getElementById('headerAvatar');
    const deleteAvatarBtn = document.getElementById('deleteAvatarBtn');

    if (avatarEditButton && avatarInputHidden && avatarUploadForm) {
        avatarEditButton.addEventListener('click', () => {
            avatarInputHidden.click();
        });

        avatarInputHidden.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const reader = new FileReader();

                reader.onload = (e) => {
                    if (avatarPhoto) avatarPhoto.src = e.target.result;
                    if (headerAvatar) headerAvatar.src = e.target.result;
                };

                reader.readAsDataURL(file);
                avatarUploadForm.submit();
            }
        });
    }

    if (deleteAvatarBtn) {
        deleteAvatarBtn.addEventListener('click', () => {
            if (confirm("¿Deseas eliminar tu foto de perfil?")) {
                window.location.href = 'perfil.php?section=avatar&action=delete';
            }
        });
    }
});

// Función auxiliar para obtener parámetros de URL
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Lógica de avatar consolidada en el bloque principal de inicialización.
// La lógica de favoritos se ha movido a la función global toggleFavorito(btn)
// para ser compatible con el atributo onclick de producto.php.

// ==================== MÚLTIPLES IMÁGENES PREVIEW ====================
function initMultipleImagesUpload() {
    const input = document.getElementById('imagenes');
    const previewContainer = document.getElementById('previsualizaciones');
    const dropArea = document.getElementById('dropArea');

    if (!input || !previewContainer) return;

    let selectedFiles = [];

    input.addEventListener('change', function (e) {
        const files = Array.from(e.target.files);

        // Limitar a los primeros 5 archivos si seleccionan más
        if (files.length > 5) {
            alert('Solo puedes subir un máximo de 5 imágenes. Se tomarán las primeras 5.');
            selectedFiles = files.slice(0, 5);
        } else {
            selectedFiles = files;
        }

        renderPreviews();
    });

    function renderPreviews() {
        previewContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'prev-container';
                div.innerHTML = `
                    <img src="${e.target.result}">
                    <button type="button" class="btn-remove-prev" data-index="${index}">×</button>
                `;
                previewContainer.appendChild(div);

                div.querySelector('.btn-remove-prev').addEventListener('click', () => {
                    removeFile(index);
                });
            };
            reader.readAsDataURL(file);
        });
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);

        // Actualizar el input real (Esto es complejo porque FileList es readonly)
        // Por simplificación en este paso, si borran, el input mantendrá los originales 
        // pero el visual ayudará al usuario. Lo ideal es usar DataTransfer.
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        input.files = dt.files;

        renderPreviews();
    }

    // Drag and Drop básico
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('drag-over'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('drag-over'), false);
    });

    dropArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = Array.from(dt.files);

        if (files.length > 5) {
            alert('Solo puedes subir un máximo de 5 imágenes.');
            input.files = dt.files; // El navegador filtrará o podemos usar DataTransfer
        }

        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    }, false);
}

// ==================== GALERÍA DE PRODUCTO ====================
function changeMainImage(src, thumbnail) {
    const mainImg = document.getElementById('mainProductImage');
    if (mainImg) {
        mainImg.src = src;

        // Actualizar clase activa en miniaturas
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        thumbnail.classList.add('active');
    }
}

// ==================== ZOOM DE IMAGEN ====================
let zoomModal = null;
let currentZoomLevel = 1;
let isDragging = false;
let startX, startY, translateX = 0, translateY = 0;

function initImageZoom() {
    // Buscar imágenes con clase zoomable
    const zoomableImages = document.querySelectorAll('.zoomable, .product-detail-image');

    zoomableImages.forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', function () {
            openZoomModal(this.src);
        });
    });
}

function openZoomModal(imageSrc) {
    // Crear modal si no existe
    if (!zoomModal) {
        zoomModal = document.createElement('div');
        zoomModal.className = 'zoom-modal';
        zoomModal.id = 'zoomModal';

        zoomModal.innerHTML = `
            <span class="zoom-modal-close" onclick="closeZoomModal()">×</span>
            <img class="zoom-modal-content" id="zoomImage" src="">
            <div class="zoom-controls">
                <button class="zoom-btn" onclick="zoomIn()">+</button>
                <button class="zoom-btn" onclick="zoomReset()">⟲</button>
                <button class="zoom-btn" onclick="zoomOut()">−</button>
            </div>
            <div class="zoom-hint">Pellizca para hacer zoom • Arrastra para mover</div>
        `;

        document.body.appendChild(zoomModal);

        // Cerrar al hacer clic fuera de la imagen
        zoomModal.addEventListener('click', function (e) {
            if (e.target === zoomModal) {
                closeZoomModal();
            }
        });

        // Cerrar con tecla Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && zoomModal.classList.contains('active')) {
                closeZoomModal();
            }
        });

        // Inicializar gestos táctiles
        initTouchZoom();
    }

    // Configurar imagen
    const zoomImage = document.getElementById('zoomImage');
    zoomImage.src = imageSrc;

    // Reset zoom
    currentZoomLevel = 1;
    translateX = 0;
    translateY = 0;
    updateZoomTransform();

    // Mostrar modal
    zoomModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeZoomModal() {
    if (zoomModal) {
        zoomModal.classList.remove('active');
        document.body.style.overflow = '';
        currentZoomLevel = 1;
        translateX = 0;
        translateY = 0;
    }
}

function zoomIn() {
    currentZoomLevel = Math.min(currentZoomLevel + 0.5, 4);
    updateZoomTransform();
}

function zoomOut() {
    currentZoomLevel = Math.max(currentZoomLevel - 0.5, 0.5);
    updateZoomTransform();
}

function zoomReset() {
    currentZoomLevel = 1;
    translateX = 0;
    translateY = 0;
    updateZoomTransform();
}

function updateZoomTransform() {
    const zoomImage = document.getElementById('zoomImage');
    if (zoomImage) {
        zoomImage.style.transform = `scale(${currentZoomLevel}) translate(${translateX}px, ${translateY}px)`;
    }
}

function initTouchZoom() {
    const zoomImage = document.getElementById('zoomImage');
    if (!zoomImage) return;

    let initialDistance = 0;
    let initialZoom = 1;

    // Pinch zoom
    zoomImage.addEventListener('touchstart', function (e) {
        if (e.touches.length === 2) {
            initialDistance = getDistance(e.touches[0], e.touches[1]);
            initialZoom = currentZoomLevel;
        } else if (e.touches.length === 1 && currentZoomLevel > 1) {
            isDragging = true;
            startX = e.touches[0].clientX - translateX;
            startY = e.touches[0].clientY - translateY;
        }
    }, { passive: true });

    zoomImage.addEventListener('touchmove', function (e) {
        if (e.touches.length === 2) {
            e.preventDefault();
            const currentDistance = getDistance(e.touches[0], e.touches[1]);
            const scale = currentDistance / initialDistance;
            currentZoomLevel = Math.min(Math.max(initialZoom * scale, 0.5), 4);
            updateZoomTransform();
        } else if (e.touches.length === 1 && isDragging && currentZoomLevel > 1) {
            translateX = e.touches[0].clientX - startX;
            translateY = e.touches[0].clientY - startY;
            updateZoomTransform();
        }
    }, { passive: false });

    zoomImage.addEventListener('touchend', function () {
        isDragging = false;
    }, { passive: true });

    // Mouse drag para desktop
    zoomImage.addEventListener('mousedown', function (e) {
        if (currentZoomLevel > 1) {
            isDragging = true;
            startX = e.clientX - translateX;
            startY = e.clientY - translateY;
            zoomImage.style.cursor = 'grabbing';
        }
    });

    zoomImage.addEventListener('mousemove', function (e) {
        if (isDragging && currentZoomLevel > 1) {
            translateX = e.clientX - startX;
            translateY = e.clientY - startY;
            updateZoomTransform();
        }
    });

    zoomImage.addEventListener('mouseup', function () {
        isDragging = false;
        zoomImage.style.cursor = currentZoomLevel > 1 ? 'grab' : 'zoom-out';
    });

    zoomImage.addEventListener('mouseleave', function () {
        isDragging = false;
    });

    // Zoom con rueda del mouse
    zoomImage.addEventListener('wheel', function (e) {
        e.preventDefault();
        if (e.deltaY < 0) {
            zoomIn();
        } else {
            zoomOut();
        }
    }, { passive: false });
}

function getDistance(touch1, touch2) {
    const dx = touch1.clientX - touch2.clientX;
    const dy = touch1.clientY - touch2.clientY;
    return Math.sqrt(dx * dx + dy * dy);
}

// Inicializar zoom y otras funcionalidades en DOMContentLoaded
// Los inicializadores se han movido a la sección de INICIALIZACIÓN principal.

// ==================== INFINITE SCROLL SYSTEM ====================

/**
 * Estado global del Infinite Scroll
 */
const infiniteScrollState = {
    currentPage: 1,
    isLoading: false,
    hasMore: true,
    totalProducts: 0,
    filters: {
        categoria: 0,
        busqueda: '',
        orden: 'newest',
        integridad: 0,
        precioMin: 0,
        precioMax: 0
    }
};

/**
 * Inicializar el sistema de Infinite Scroll
 */
function initInfiniteScroll() {
    // Obtener filtros desde PHP (pasados via window.productFilters)
    if (window.productFilters) {
        infiniteScrollState.filters = window.productFilters;
    }

    // Limpiar cualquier estado guardado anterior para asegurar datos frescos
    clearScrollState();

    // Cargar la primera página de productos siempre desde el servidor
    loadProducts(1, true);

    // Configurar el observer para infinite scroll
    setupInfiniteScrollObserver();

    // También escuchar el evento de scroll como fallback
    setupScrollListener();

    // 🔍 Configurar filtros AJAX
    setupAjaxFilters();
}

/**
 * Configurar filtros AJAX (sin recargar página)
 */
function setupAjaxFilters() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortFilter = document.getElementById('sortFilter');
    const integridadFilter = document.getElementById('integridadFilter');
    const precioMin = document.getElementById('precioMin');
    const precioMax = document.getElementById('precioMax');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    let searchTimeout;
    let priceTimeout;

    // Búsqueda con debounce (espera a que el usuario deje de escribir)
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                infiniteScrollState.filters.busqueda = e.target.value.trim();
                applyFilters();
            }, 400); // Espera 400ms después de dejar de escribir
        });

        // También buscar al presionar Enter
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                infiniteScrollState.filters.busqueda = e.target.value.trim();
                applyFilters();
            }
        });
    }

    // Filtro de categoría
    if (categoryFilter) {
        categoryFilter.addEventListener('change', (e) => {
            infiniteScrollState.filters.categoria = parseInt(e.target.value) || 0;
            applyFilters();
        });
    }

    // Filtro de integridad (condición)
    if (integridadFilter) {
        integridadFilter.addEventListener('change', (e) => {
            infiniteScrollState.filters.integridad = parseInt(e.target.value) || 0;
            applyFilters();
        });
    }

    // Filtros de precio con debounce
    if (precioMin) {
        precioMin.addEventListener('input', (e) => {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(() => {
                infiniteScrollState.filters.precioMin = parseFloat(e.target.value) || 0;
                applyFilters();
            }, 500);
        });
    }

    if (precioMax) {
        precioMax.addEventListener('input', (e) => {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(() => {
                infiniteScrollState.filters.precioMax = parseFloat(e.target.value) || 0;
                applyFilters();
            }, 500);
        });
    }

    // Filtro de ordenamiento
    if (sortFilter) {
        sortFilter.addEventListener('change', (e) => {
            infiniteScrollState.filters.orden = e.target.value;
            applyFilters();
        });
    }

    // Botón limpiar filtros
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            // Resetear inputs
            if (searchInput) searchInput.value = '';
            if (categoryFilter) categoryFilter.value = '0';
            if (sortFilter) sortFilter.value = 'newest';
            if (integridadFilter) integridadFilter.value = '0';
            if (precioMin) precioMin.value = '';
            if (precioMax) precioMax.value = '';

            // Resetear estado
            infiniteScrollState.filters = {
                categoria: 0,
                busqueda: '',
                orden: 'newest',
                integridad: 0,
                precioMin: 0,
                precioMax: 0
            };

            // Recargar productos
            applyFilters();
        });
    }

    // Mostrar/ocultar botón de limpiar según si hay filtros activos
    updateClearFiltersButton();

    // Refresh (equivalente a RefreshControl onRefresh)
    const refreshBtn = document.getElementById('refreshProductsBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            if (refreshBtn.classList.contains('refreshing')) return;
            refreshBtn.classList.add('refreshing');
            refreshBtn.disabled = true;
            if (typeof reloadProducts === 'function') {
                reloadProducts();
            }
            setTimeout(function () {
                refreshBtn.classList.remove('refreshing');
                refreshBtn.disabled = false;
            }, 800);
        });
    }
}

/**
 * Aplicar filtros y recargar productos
 */
function applyFilters() {
    // Resetear paginación
    infiniteScrollState.currentPage = 1;
    infiniteScrollState.hasMore = true;

    // Limpiar estado guardado
    clearScrollState();

    // Ocultar mensaje de fin
    const noMoreProducts = document.getElementById('noMoreProducts');
    if (noMoreProducts) noMoreProducts.style.display = 'none';

    // Actualizar botón de limpiar
    updateClearFiltersButton();

    // Recargar productos
    loadProducts(1, true);
}

/**
 * Actualizar visibilidad del botón "Limpiar filtros"
 */
function updateClearFiltersButton() {
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (!clearFiltersBtn) return;

    const hasFilters = infiniteScrollState.filters.categoria > 0 ||
        infiniteScrollState.filters.busqueda.length > 0 ||
        infiniteScrollState.filters.orden !== 'newest' ||
        infiniteScrollState.filters.integridad > 0 ||
        infiniteScrollState.filters.precioMin > 0 ||
        infiniteScrollState.filters.precioMax > 0;

    clearFiltersBtn.style.display = hasFilters ? 'inline-block' : 'none';
}

/**
 * Verificar si los filtros actuales son los mismos que los guardados
 */
function isSameFilters(savedFilters) {
    if (!savedFilters) return false;
    return savedFilters.categoria === infiniteScrollState.filters.categoria &&
        savedFilters.busqueda === infiniteScrollState.filters.busqueda &&
        savedFilters.orden === infiniteScrollState.filters.orden &&
        savedFilters.integridad === infiniteScrollState.filters.integridad &&
        savedFilters.precioMin === infiniteScrollState.filters.precioMin &&
        savedFilters.precioMax === infiniteScrollState.filters.precioMax;
}

/**
 * Guardar estado del scroll antes de salir
 */
function setupBeforeUnload() {
    // Guardar al hacer clic en cualquier enlace de producto
    document.addEventListener('click', (e) => {
        const productLink = e.target.closest('a[href*="productos/producto.php"]');
        if (productLink) {
            saveScrollState();
        }
    });

    // También guardar al usar el botón atrás/adelante
    window.addEventListener('pagehide', () => {
        saveScrollState();
    });
}

/**
 * Guardar el estado actual del scroll
 */
function saveScrollState() {
    const productsGrid = document.getElementById('productsGrid');
    if (!productsGrid) return;

    const state = {
        scrollY: window.scrollY,
        currentPage: infiniteScrollState.currentPage,
        hasMore: infiniteScrollState.hasMore,
        totalProducts: infiniteScrollState.totalProducts,
        filters: { ...infiniteScrollState.filters },
        productsHTML: productsGrid.innerHTML,
        timestamp: Date.now()
    };

    try {
        sessionStorage.setItem('infiniteScrollState', JSON.stringify(state));
    } catch (e) {
        console.warn('No se pudo guardar el estado del scroll:', e);
    }
}

/**
 * Obtener el estado guardado del scroll
 */
function getScrollState() {
    try {
        const saved = sessionStorage.getItem('infiniteScrollState');
        if (!saved) return null;

        const state = JSON.parse(saved);

        // Expirar después de 10 minutos
        if (Date.now() - state.timestamp > 10 * 60 * 1000) {
            clearScrollState();
            return null;
        }

        return state;
    } catch (e) {
        return null;
    }
}

/**
 * Restaurar el estado guardado
 */
function restoreScrollState(state) {
    const productsGrid = document.getElementById('productsGrid');
    const skeletonGrid = document.getElementById('skeletonGrid');
    const noMoreProducts = document.getElementById('noMoreProducts');

    if (!productsGrid || !state.productsHTML) {
        loadProducts(1, true);
        return;
    }

    // Ocultar skeletons
    if (skeletonGrid) skeletonGrid.style.display = 'none';

    // Restaurar productos
    productsGrid.innerHTML = state.productsHTML;
    productsGrid.style.display = 'grid';

    // Restaurar estado
    infiniteScrollState.currentPage = state.currentPage;
    infiniteScrollState.hasMore = state.hasMore;
    infiniteScrollState.totalProducts = state.totalProducts;

    // Mostrar mensaje de fin si aplica
    if (!state.hasMore && noMoreProducts) {
        noMoreProducts.style.display = 'block';
    }

    // Restaurar posición del scroll después de que el DOM se actualice
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            window.scrollTo({
                top: state.scrollY,
                behavior: 'instant'
            });
        });
    });

    // Limpiar el estado guardado para evitar restauraciones múltiples
    // (se volverá a guardar al hacer clic en un producto)
    clearScrollState();
}

/**
 * Limpiar el estado guardado
 */
function clearScrollState() {
    try {
        sessionStorage.removeItem('infiniteScrollState');
    } catch (e) {
        // Ignorar errores
    }
}

/**
 * Configurar Intersection Observer para detectar cuando el usuario llega al final
 */
function setupInfiniteScrollObserver() {
    const loadingMore = document.getElementById('loadingMore');
    if (!loadingMore) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !infiniteScrollState.isLoading && infiniteScrollState.hasMore) {
                loadMoreProducts();
            }
        });
    }, {
        rootMargin: '200px', // Cargar antes de llegar al final
        threshold: 0.1
    });

    observer.observe(loadingMore);
}

/**
 * Fallback: Listener de scroll tradicional
 */
function setupScrollListener() {
    let scrollTimeout;

    window.addEventListener('scroll', () => {
        if (scrollTimeout) clearTimeout(scrollTimeout);

        scrollTimeout = setTimeout(() => {
            if (infiniteScrollState.isLoading || !infiniteScrollState.hasMore) return;

            const scrollPosition = window.innerHeight + window.scrollY;
            const documentHeight = document.documentElement.scrollHeight;

            // Cargar más cuando estamos al 80% del scroll
            if (scrollPosition >= documentHeight * 0.8) {
                loadMoreProducts();
            }
        }, 100);
    }, { passive: true });
}

/**
 * Cargar más productos (siguiente página)
 */
function loadMoreProducts() {
    if (infiniteScrollState.isLoading || !infiniteScrollState.hasMore) return;

    infiniteScrollState.currentPage++;
    loadProducts(infiniteScrollState.currentPage, false);
}

/**
 * Cargar productos desde la API
 * @param {number} page - Número de página
 * @param {boolean} isInitial - Si es la carga inicial (muestra skeletons)
 */
async function loadProducts(page, isInitial = false) {
    const productsGrid = document.getElementById('productsGrid');
    const skeletonGrid = document.getElementById('skeletonGrid');
    const loadingMore = document.getElementById('loadingMore');
    const noProducts = document.getElementById('noProducts');
    const noMoreProducts = document.getElementById('noMoreProducts');

    if (!productsGrid) return;
    // Evitar doble carga inicial (p. ej. si se llama dos veces al iniciar)
    if (isInitial && infiniteScrollState.isLoading) return;

    // Marcar como cargando
    infiniteScrollState.isLoading = true;

    // Mostrar indicadores de carga
    if (isInitial) {
        skeletonGrid.style.display = 'grid';
        productsGrid.style.display = 'none';
    } else {
        loadingMore.style.display = 'flex';
    }

    try {
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelProductosUrl === 'function';
        const filters = infiniteScrollState.filters;
        const orden = filters.orden || 'newest';

        let response;
        if (useLaravel) {
            // Laravel: GET /api/productos (con filtros) o GET /api/productos/buscar?q=...
            const perPage = 12;
            if (filters.busqueda && filters.busqueda.trim().length >= 2) {
                const url = window.getLaravelProductosBuscarUrl(filters.busqueda.trim(), page, perPage);
                response = await fetch(url, {
                    headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Cache-Control': 'no-cache' },
                    cache: 'no-store'
                });
            } else {
                const orderMap = { newest: ['fecha_registro', 'desc'], oldest: ['fecha_registro', 'asc'], price_asc: ['precio', 'asc'], price_desc: ['precio', 'desc'] };
                const [orderBy, orderDirection] = orderMap[orden] || orderMap.newest;
                const params = { page: page, per_page: perPage, order_by: orderBy, order_direction: orderDirection };
                if (filters.categoria > 0) params.categoria_id = filters.categoria;
                if (filters.integridad > 0) params.integridad_id = filters.integridad;
                const url = window.getLaravelProductosUrl(params);
                response = await fetch(url, {
                    headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Cache-Control': 'no-cache' },
                    cache: 'no-store'
                });
            }
        } else {
            const params = new URLSearchParams({ page: page, limit: 12, orden: orden });
            if (filters.categoria > 0) params.append('categoria', filters.categoria);
            if (filters.busqueda) params.append('busqueda', filters.busqueda);
            if (filters.integridad > 0) params.append('integridad', filters.integridad);
            if (filters.precioMin > 0) params.append('precio_min', filters.precioMin);
            if (filters.precioMax > 0) params.append('precio_max', filters.precioMax);
            response = await fetch(getApiUrl(`api/productos.php?${params.toString()}`), {
                headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}), 'Cache-Control': 'no-cache' },
                cache: 'no-store'
            });
        }

        if (response.status === 401) {
            if (isInitial && noProducts) {
                skeletonGrid.style.display = 'none';
                productsGrid.style.display = 'grid';
                noProducts.style.display = 'block';
                const msg = noProducts.querySelector('p');
                if (msg) msg.textContent = 'Sesión no autorizada con la API. Cierra sesión e inicia sesión de nuevo para ver los productos.';
            }
            infiniteScrollState.isLoading = false;
            loadingMore.style.display = 'none';
            return;
        }

        let data = await response.json();

        // Normalizar respuesta de Laravel (data.data + data.pagination)
        if (window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && data && !data.productos && Array.isArray(data.data)) {
            const pag = data.pagination || data.meta || {};
            const total = pag.total ?? data.data.length;
            const currentPage = pag.current_page ?? 1;
            const lastPage = pag.last_page ?? 1;
            data = {
                success: true,
                productos: data.data,
                pagination: { has_more: currentPage < lastPage, total: total },
                uso_datos: data.uso_datos || 0
            };
        }

        if (data.success) {
            const productos = data.productos || [];
            const pagination = data.pagination || { has_more: false, total: 0 };

            // Actualizar estado
            infiniteScrollState.hasMore = pagination.has_more;
            infiniteScrollState.totalProducts = pagination.total;
            window.currentUsoDatos = data.uso_datos || 0;


            if (isInitial) {
                // Limpiar grid y ocultar skeletons
                productsGrid.innerHTML = '';
                skeletonGrid.style.display = 'none';
                productsGrid.style.display = 'grid';
            }

            // Renderizar productos
            if (productos.length > 0) {
                renderProducts(productos, productsGrid, !isInitial);
                noProducts.style.display = 'none';
            } else if (isInitial) {
                // No hay productos
                noProducts.style.display = 'block';
            }

            // Mostrar mensaje de fin si no hay más productos
            if (!infiniteScrollState.hasMore && infiniteScrollState.totalProducts > 0) {
                noMoreProducts.style.display = 'block';
            }

            // Inicializar lazy loading para las nuevas imágenes
            initLazyLoadImages();

        } else {
            console.error('Error al cargar productos:', data.error);
            if (isInitial) {
                skeletonGrid.style.display = 'none';
                noProducts.style.display = 'block';
            }
        }

    } catch (error) {
        console.error('Error de conexión al cargar productos:', error);
        if (isInitial) {
            skeletonGrid.style.display = 'none';
            noProducts.style.display = 'block';
        }
    } finally {
        infiniteScrollState.isLoading = false;
        loadingMore.style.display = 'none';
    }
}

/**
 * Renderizar productos en el grid
 * @param {Array} productos - Array de productos
 * @param {HTMLElement} container - Contenedor del grid
 * @param {boolean} isNewLoad - Si son productos nuevos (para animación)
 */
function renderProducts(productos, container, isNewLoad = false) {
    productos.forEach((producto, index) => {
        const card = createProductCard(producto);

        // Agregar clases de animación
        if (isNewLoad) {
            card.classList.add('new-load');
            card.style.animationDelay = `${index * 0.05}s`;
        } else {
            card.classList.add('fade-in');
        }

        container.appendChild(card);
    });
}

/**
 * Crear el HTML de una tarjeta de producto
 * @param {Object} producto - Datos del producto
 * @returns {HTMLElement} - Elemento de la tarjeta
 */
function createProductCard(producto) {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.setAttribute('data-id', String(producto.id != null ? producto.id : ''));

    // Normalizar campos que pueden venir como string (PHP) o objeto (Laravel)
    const integridadVal = producto.integridad;
    const integridadStr = typeof integridadVal === 'string'
        ? integridadVal
        : (integridadVal && integridadVal.nombre) ? integridadVal.nombre : (producto.integridad_nombre || '');
    const integridadLower = (integridadStr + '').toLowerCase();

    let conditionClass = '';
    if (integridadLower === 'nuevo') conditionClass = 'condition-nuevo';
    else if (integridadLower === 'usado') conditionClass = 'condition-usado';

    const nombre = producto.nombre || '';
    const precioFormateado = producto.precio_formateado || (producto.precio != null ? Number(producto.precio).toLocaleString('es-CO') + ' COP' : '');
    const vendedorNombre = producto.vendedor_nombre || (producto.vendedor && producto.vendedor.nickname) || (producto.usuario && producto.usuario.nickname) || '';
    let vendedorAvatar = producto.vendedor_avatar || (producto.vendedor && producto.vendedor.imagen) || (producto.vendedor && producto.vendedor.avatar) || (producto.usuario && producto.usuario.imagen) || '';
    if (vendedorAvatar && typeof window.getAvatarUrl === 'function' && !vendedorAvatar.startsWith('http')) {
        vendedorAvatar = window.getAvatarUrl(vendedorAvatar);
    }
    if (!vendedorAvatar) vendedorAvatar = (window.BASE_URL || '') + 'assets/images/default-avatar.jpg';
    const categoriaNombre = producto.categoria_nombre || (producto.categoria && producto.categoria.nombre) || '';
    const subcategoriaNombre = producto.subcategoria_nombre || (producto.subcategoria && producto.subcategoria.nombre) || '';
    // Imagen: prioridad foto.url (URL completa de Laravel), luego foto.imagen con producto_id
    const foto = producto.fotos && producto.fotos[0];
    const rawImagen = producto.imagen || (foto && (foto.url || foto.imagen)) || producto.producto_imagen || '';
    const rawFilename = (foto && foto.imagen) || (typeof rawImagen === 'string' && rawImagen ? rawImagen.replace(/^.*[/\\]/, '') : '') || '';
    let imgSrc = rawImagen;
    if (imgSrc && typeof window.getProductImageUrl === 'function') {
        if (imgSrc.startsWith('http') || imgSrc.startsWith('https')) {
            imgSrc = window.getProductImageUrl(imgSrc) || imgSrc;
        } else if (foto && foto.imagen && producto.id) {
            imgSrc = window.getProductImageUrl(foto.imagen, producto.id);
        } else {
            imgSrc = window.getProductImageUrl(imgSrc);
        }
    } else if (imgSrc && imgSrc.startsWith('/') && window.API_CONFIG && window.API_CONFIG.LARAVEL_URL) {
        const origin = window.API_CONFIG.LARAVEL_URL.replace(/\/api\/?$/, '');
        imgSrc = origin + imgSrc;
    }
    const uploadsFallbackUrl = rawFilename && (window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getProductImageUrl === 'function')
        ? window.getProductImageUrl(rawFilename, producto.id)
        : (rawFilename && (window.BASE_URL || '') ? (window.BASE_URL + 'uploads/productos/' + rawFilename) : '');

    var defaultProductImg = (window.BASE_URL || '') + 'assets/images/default-product.jpg';
    var imgHTML = `
        <img src="${escapeHtml(imgSrc || defaultProductImg)}"
             alt="${escapeHtml(nombre)}"
             class="product-image"
             loading="lazy"
             data-fallback-uploads="${escapeHtml(uploadsFallbackUrl)}"
             onerror="window.productImageFallback(this)">
    `;

    var productUrl = (window.BASE_URL || '') + 'productos/producto.php?id=' + (producto.id || '');
    card.innerHTML = `
        <a href="${escapeHtml(productUrl)}" class="product-card-link" style="display:block;width:100%;height:100%;min-height:200px;">
            ${imgHTML}
            <div class="product-info">
                <h3 class="product-name">${escapeHtml(nombre)}</h3>
                <p class="product-price">${precioFormateado}</p>
                <div class="product-seller-info">
                    <img src="${escapeHtml(vendedorAvatar)}" 
                         alt="${escapeHtml(vendedorNombre)}" 
                         class="seller-avatar-small"
                         onerror="this.src='${window.BASE_URL || ''}assets/images/default-avatar.jpg'">
                    <span>Vendedor: ${escapeHtml(vendedorNombre)}</span>
                </div>
                <p class="product-category">${escapeHtml(categoriaNombre)} - ${escapeHtml(subcategoriaNombre)}</p>
                <span class="product-condition ${conditionClass}">${escapeHtml(integridadStr)}</span>
                <span class="product-stock">Disponibles: ${producto.disponibles != null ? producto.disponibles : ''}</span>
            </div>
        </a>
    `;

    return card;
}

/**
 * Inicializar lazy loading para imágenes (usando Intersection Observer)
 */
function initLazyLoadImages() {
    // Usamos el atributo native loading="lazy" que ya está en las imágenes
    // Pero agregamos un observer para animar cuando se carguen

    const images = document.querySelectorAll('.product-image:not(.observed)');

    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.classList.add('observed');

                // Cuando la imagen carga, agregar clase para animación
                if (img.complete) {
                    img.classList.add('loaded');
                } else {
                    img.addEventListener('load', () => {
                        img.classList.add('loaded');
                    });
                }

                imageObserver.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px',
        threshold: 0.01
    });

    images.forEach(img => {
        imageObserver.observe(img);
    });
}

/**
 * Recargar productos (por ejemplo, al cambiar filtros via JavaScript)
 */
function reloadProducts() {
    infiniteScrollState.currentPage = 1;
    infiniteScrollState.hasMore = true;

    const productsGrid = document.getElementById('productsGrid');
    const noMoreProducts = document.getElementById('noMoreProducts');

    if (productsGrid) productsGrid.innerHTML = '';
    if (noMoreProducts) noMoreProducts.style.display = 'none';

    loadProducts(1, true);
}

// Exponer funciones globalmente si es necesario
window.reloadProducts = reloadProducts;
window.infiniteScrollState = infiniteScrollState;

/**
 * Cargar productos de un vendedor desde la API Laravel (GET /api/productos/vendedor/{id})
 * @param {HTMLElement} container - Contenedor con clase tipo products-grid
 * @param {string|number} vendedorId - ID del vendedor
 */
async function loadProductosVendedorLaravel(container, vendedorId) {
    if (!container || !vendedorId || typeof window.getLaravelProductosVendedorUrl !== 'function') return;
    const url = window.getLaravelProductosVendedorUrl(String(vendedorId));
    const noProductsEl = container.parentElement && container.parentElement.querySelector('.no-products');
    try {
        const response = await fetch(url, { headers: window.getApiHeaders ? window.getApiHeaders() : {} });
        const data = await response.json();
        const productos = (data && data.success && data.data) ? data.data : [];
        container.innerHTML = '';
        if (productos.length > 0) {
            renderProducts(productos, container, false);
            if (noProductsEl) noProductsEl.style.display = 'none';
        } else {
            if (noProductsEl) { noProductsEl.style.display = 'block'; noProductsEl.querySelector('p').textContent = 'Este vendedor no tiene productos disponibles.'; }
        }
        if (typeof initLazyLoadImages === 'function') initLazyLoadImages();
    } catch (e) {
        console.error('Error al cargar productos del vendedor:', e);
        if (noProductsEl) { noProductsEl.style.display = 'block'; noProductsEl.querySelector('p').textContent = 'No se pudieron cargar los productos.'; }
    }
}

// Auto-cargar productos por vendedor cuando la página tiene [data-productos-vendedor] y se usa Laravel
(function initProductosVendedorLaravel() {
    function run() {
        if (!window.API_CONFIG || !window.API_CONFIG.USE_LARAVEL || typeof window.getLaravelProductosVendedorUrl !== 'function') return;
        var el = document.querySelector('[data-productos-vendedor]');
        if (!el) return;
        var vendedorId = el.getAttribute('data-vendedor-id');
        if (!vendedorId) return;
        loadProductosVendedorLaravel(el, vendedorId);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
    else run();
})();

/* ==================== GALERÍA DE IMÁGENES MEJORADA ==================== */

/**
 * Estado de la galería
 */
const galleryState = {
    currentIndex: 0,
    totalImages: 0,
    images: [],
    touchStartX: 0,
    touchEndX: 0,
    isLightboxOpen: false
};

/**
 * Inicializar la galería de imágenes
 */
function initProductGallery() {
    // Verificar si existe la galería
    const galleryContainer = document.getElementById('galleryContainer');
    if (!galleryContainer) return;

    // Obtener imágenes del array global
    if (window.galleryImages && window.galleryImages.length > 0) {
        galleryState.images = window.galleryImages;
        galleryState.totalImages = window.galleryImages.length;
    } else {
        return;
    }

    // Configurar event listeners
    setupGalleryNavigation();
    setupGalleryThumbnails();
    setupGalleryFullscreen();
    setupGalleryKeyboard();
    setupGalleryTouch();

    // Mostrar indicador de swipe en móvil
    if (galleryState.totalImages > 1 && window.innerWidth <= 600) {
        const galleryMain = document.querySelector('.gallery-main');
        if (galleryMain) {
            galleryMain.classList.add('show-swipe-hint');
            setTimeout(() => galleryMain.classList.remove('show-swipe-hint'), 3000);
        }
    }
}

/**
 * Configurar navegación con flechas
 */
function setupGalleryNavigation() {
    const prevBtn = document.getElementById('galleryPrev');
    const nextBtn = document.getElementById('galleryNext');

    if (prevBtn) {
        prevBtn.addEventListener('click', () => navigateGallery(-1));
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => navigateGallery(1));
    }
}

/**
 * Navegar entre imágenes
 */
function navigateGallery(direction) {
    const newIndex = galleryState.currentIndex + direction;

    if (newIndex >= 0 && newIndex < galleryState.totalImages) {
        goToSlide(newIndex);
    } else if (newIndex < 0) {
        goToSlide(galleryState.totalImages - 1); // Loop al final
    } else {
        goToSlide(0); // Loop al inicio
    }
}

/**
 * Ir a una imagen específica
 */
function goToSlide(index) {
    galleryState.currentIndex = index;

    // Actualizar slides
    const slides = document.querySelectorAll('.gallery-slide');
    slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
    });

    // Actualizar miniaturas
    const thumbs = document.querySelectorAll('.gallery-thumb');
    thumbs.forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });

    // Actualizar contador
    const counter = document.getElementById('currentSlide');
    if (counter) {
        counter.textContent = index + 1;
    }

    // Si el lightbox está abierto, actualizar también
    if (galleryState.isLightboxOpen) {
        updateLightboxImage(index);
    }
}

/**
 * Configurar clics en miniaturas
 */
function setupGalleryThumbnails() {
    const thumbs = document.querySelectorAll('.gallery-thumb');

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const index = parseInt(thumb.getAttribute('data-index'));
            goToSlide(index);
        });

        // Soporte de teclado
        thumb.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const index = parseInt(thumb.getAttribute('data-index'));
                goToSlide(index);
            }
        });
    });
}

/**
 * Configurar pantalla completa (Lightbox)
 */
function setupGalleryFullscreen() {
    const fullscreenBtn = document.getElementById('galleryFullscreenBtn');
    const lightbox = document.getElementById('galleryLightbox');
    const closeBtn = document.getElementById('lightboxClose');
    const prevBtn = document.getElementById('lightboxPrev');
    const nextBtn = document.getElementById('lightboxNext');
    const galleryImages = document.querySelectorAll('.gallery-image');

    // Abrir lightbox con botón
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', openLightbox);
    }

    // Abrir lightbox al hacer clic en imagen principal
    galleryImages.forEach(img => {
        img.addEventListener('click', openLightbox);
    });

    // Cerrar lightbox
    if (closeBtn) {
        closeBtn.addEventListener('click', closeLightbox);
    }

    // Cerrar con clic fuera
    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
    }

    // Navegación en lightbox
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            navigateGallery(-1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            navigateGallery(1);
        });
    }

    // Puntos del lightbox
    const dots = document.querySelectorAll('.lightbox-dot');
    dots.forEach(dot => {
        dot.addEventListener('click', (e) => {
            e.stopPropagation();
            const index = parseInt(dot.getAttribute('data-index'));
            goToSlide(index);
        });
    });
}

/**
 * Abrir lightbox
 */
function openLightbox() {
    const lightbox = document.getElementById('galleryLightbox');
    if (!lightbox) return;

    galleryState.isLightboxOpen = true;
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';

    updateLightboxImage(galleryState.currentIndex);

    // Setup touch para lightbox
    setupLightboxTouch();
}

/**
 * Cerrar lightbox
 */
function closeLightbox() {
    const lightbox = document.getElementById('galleryLightbox');
    if (!lightbox) return;

    galleryState.isLightboxOpen = false;
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
}

/**
 * Actualizar imagen del lightbox
 */
function updateLightboxImage(index) {
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxCounter = document.getElementById('lightboxCurrentSlide');
    const dots = document.querySelectorAll('.lightbox-dot');

    if (lightboxImage && galleryState.images[index]) {
        lightboxImage.src = galleryState.images[index];
        lightboxImage.alt = `Imagen ${index + 1}`;
    }

    if (lightboxCounter) {
        lightboxCounter.textContent = index + 1;
    }

    dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
    });
}

/**
 * Configurar navegación con teclado
 */
function setupGalleryKeyboard() {
    document.addEventListener('keydown', (e) => {
        // Solo si el lightbox está abierto o estamos en la página del producto
        const galleryContainer = document.getElementById('galleryContainer');
        if (!galleryContainer) return;

        switch (e.key) {
            case 'ArrowLeft':
                navigateGallery(-1);
                break;
            case 'ArrowRight':
                navigateGallery(1);
                break;
            case 'Escape':
                if (galleryState.isLightboxOpen) {
                    closeLightbox();
                }
                break;
        }
    });
}

/**
 * Configurar gestos táctiles (swipe)
 */
function setupGalleryTouch() {
    const galleryMain = document.querySelector('.gallery-main');
    if (!galleryMain) return;

    galleryMain.addEventListener('touchstart', handleTouchStart, { passive: true });
    galleryMain.addEventListener('touchmove', handleTouchMove, { passive: true });
    galleryMain.addEventListener('touchend', handleTouchEnd, { passive: true });
}

/**
 * Configurar touch para lightbox
 */
function setupLightboxTouch() {
    const lightboxContent = document.querySelector('.lightbox-content');
    if (!lightboxContent) return;

    lightboxContent.addEventListener('touchstart', handleTouchStart, { passive: true });
    lightboxContent.addEventListener('touchmove', handleTouchMove, { passive: true });
    lightboxContent.addEventListener('touchend', handleTouchEnd, { passive: true });
}

function handleTouchStart(e) {
    galleryState.touchStartX = e.changedTouches[0].screenX;
}

function handleTouchMove(e) {
    galleryState.touchEndX = e.changedTouches[0].screenX;
}

function handleTouchEnd() {
    const swipeThreshold = 50;
    const diff = galleryState.touchStartX - galleryState.touchEndX;

    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            // Swipe izquierda -> siguiente
            navigateGallery(1);
        } else {
            // Swipe derecha -> anterior
            navigateGallery(-1);
        }
    }

    // Reset
    galleryState.touchStartX = 0;
    galleryState.touchEndX = 0;
}

// La galería se inicializa en la sección principal de INICIALIZACIÓN.

/**
 * Cambiar la imagen principal en la página de detalle de producto
 */
function changeMainImage(src, thumb) {
    const mainImg = document.getElementById('mainProductImage');
    if (mainImg) {
        mainImg.src = src;
    }

    // Actualizar clase activa en miniaturas
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(t => t.classList.remove('active'));
    if (thumb) {
        thumb.classList.add('active');
    }
}

// ==================== BLOQUEAR USUARIOS (RF09-001) ====================
/**
 * Toggle bloqueo de usuario
 */
async function toggleBloqueo(usuarioId) {
    const esBloqueado = document.querySelector(`[data-vendedor-id="${usuarioId}"]`)
        ?.classList.contains('bloqueado') ?? false;

    const mensaje = esBloqueado
        ? '¿Deseas desbloquear a este usuario?'
        : '¿Estás seguro de que deseas bloquear a este usuario? No podrás ver sus productos ni recibir mensajes de él.';

    if (!confirm(mensaje)) return;

    try {
        const url = window.API_CONFIG.LARAVEL_URL + 'bloqueados/' + usuarioId;
        const method = esBloqueado ? 'DELETE' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) }
        });

        const data = await response.json();
        const ok = data.success || data.status === 'success';

        if (ok) {
            if (esBloqueado) {
                showToast('Usuario desbloqueado correctamente.', 'success');
                // Actualizar botón
                const btn = document.querySelector(`[data-vendedor-id="${usuarioId}"]`);
                if (btn) {
                    btn.classList.remove('bloqueado');
                    btn.innerHTML = '<i class="ri-forbid-line"></i> Bloquear';
                    btn.style.background = '#dc2626';
                }
            } else {
                showToast('Usuario bloqueado correctamente.', 'success');
                setTimeout(() => {
                    window.location.href = (window.BASE_URL || '') + 'index.php';
                }, 1500);
            }
        } else {
            showToast(data.message || 'Error al procesar la solicitud.', 'error');
        }

    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión.', 'error');
    }
}

// ==================== ENVIAR IMAGEN EN CHAT (RF04-009) ====================
/**
 * Enviar imagen en el chat
 */
async function sendChatImage(chatId, file, mensaje = '') {
    if (!file) return;

    // Validar tipo de archivo
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showToast('Solo se permiten imágenes JPG, PNG, GIF o WebP', 'error');
        return;
    }

    // Validar tamaño (máximo 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showToast('La imagen no puede superar los 5MB', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('chat_id', chatId);
        formData.append('imagen', file);
        formData.append('mensaje', mensaje);

        const sendUrl = (typeof window.getLaravelSendMessageUrl === 'function' && window.API_CONFIG && window.API_CONFIG.LARAVEL_URL)
            ? window.getLaravelSendMessageUrl(chatId)
            : getApiUrl('api/send_chat_image.php');
        const response = await fetch(sendUrl, {
            method: 'POST',
            headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Recargar mensajes del chat
            if (typeof loadChatMessages === 'function') {
                loadChatMessages(chatId);
            }
            return data;
        } else {
            showToast(data.error || 'Error al enviar imagen', 'error');
            return null;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
        return null;
    }
}

// ==================== FINALIZAR VENTA (RF04-011) ====================
/**
 * Finalizar una venta/transacción.
 * Con Laravel: PATCH iniciar-compraventas (vendedor) o terminar-compraventas (comprador).
 */
async function finalizarVenta(chatId, precio = 0, cantidad = 1) {
    if (!confirm('¿Confirmas que esta transacción se ha completado?')) {
        return;
    }

    try {
        let response, data;
        const url = (window.getLaravelIniciarCompraventaUrl && window.API_CONFIG && window.API_CONFIG.LARAVEL_URL)
            ? window.getLaravelIniciarCompraventaUrl(chatId)
            : getApiUrl('api/finalizar_venta.php');
        response = await fetch(url, {
            method: (window.API_CONFIG && window.API_CONFIG.LARAVEL_URL) ? 'PATCH' : 'POST',
            headers: {
                ...(window.getApiHeaders ? window.getApiHeaders() : {}),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cantidad: cantidad || 1, precio: precio || 0 })
        });

        data = await response.json();
        const ok = data.success || data.status === 'success';
        if (ok) {
            showToast(data.message || '¡Transacción finalizada correctamente!', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message || data.error || 'Error al finalizar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    }
}

// ==================== ELIMINAR CHAT (RF04-010) ====================
/**
 * Eliminar un chat de la lista (siempre API PHP: usa tabla chats_eliminados que mis_chats.php filtra)
 */
async function eliminarChat(chatId) {
    if (!confirm('¿Eliminar esta conversación? El otro usuario aún podrá ver los mensajes.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('chat_id', chatId);
        const eliminarUrl = (typeof window.getLaravelDeleteChatUrl === 'function' && window.API_CONFIG && window.API_CONFIG.LARAVEL_URL)
            ? window.getLaravelDeleteChatUrl(chatId)
            : getApiUrl('api/eliminar_chat.php');
        const response = await fetch(eliminarUrl, {
            method: (window.API_CONFIG && window.API_CONFIG.LARAVEL_URL) ? 'DELETE' : 'POST',
            headers: { ...(window.getApiHeaders ? window.getApiHeaders() : {}) },
            body: (window.API_CONFIG && window.API_CONFIG.LARAVEL_URL) ? undefined : formData
        });
        const data = await response.json();
        const ok = data.success || data.status === 'success';
        if (ok) {
            showToast(data.message || 'Chat eliminado', 'success');
            const card = document.querySelector(`button[onclick*="eliminarChat(${chatId})"]`)?.closest('.chat-item');
            if (card) card.remove();
            setTimeout(() => { window.location.href = (window.BASE_URL || '') + 'chat/mis_chats.php'; }, 800);
        } else {
            showToast(data.message || data.error || 'Error al eliminar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    }
}

// ==================== TERMINAR COMPRAVENTA (comprador acepta/rechaza) ====================
/**
 * Terminar proceso de compraventa (solo comprador).
 * confirmacion: true = acepta, false = rechaza.
 * Si acepta: comentario y calificacion opcionales.
 */
async function terminarCompraventa(chatId, confirmacion, comentario, calificacion) {
    try {
        const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelTerminarCompraventaUrl === 'function';
        if (!useLaravel) return;
        const body = { confirmacion: !!confirmacion };
        if (confirmacion && (comentario || calificacion != null)) {
            if (comentario) body.comentario = comentario;
            if (calificacion != null) body.calificacion = parseInt(calificacion, 10) || 0;
        }
        const response = await fetch(window.getLaravelTerminarCompraventaUrl(chatId), {
            method: 'PATCH',
            headers: {
                ...(window.getApiHeaders ? window.getApiHeaders() : {}),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });
        const data = await response.json();
        const ok = data.success || data.status === 'success';
        if (ok) {
            showToast(data.message || (confirmacion ? 'Venta concretada' : 'Proceso cancelado'), 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message || data.error || 'Error', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    }
}

// ==================== UTILIDADES ====================
/**
 * Mostrar toast de notificación
 */
function showToast(message, type = 'info') {
    // Remover toast existente
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : 'var(--color-primary)'};
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 9999;
        animation: slideUp 0.3s ease;
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Estilos para animaciones de toast
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    @keyframes slideUp {
        from { transform: translateX(-50%) translateY(20px); opacity: 0; }
        to { transform: translateX(-50%) translateY(0); opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
document.head.appendChild(toastStyles);

// ==================== SISTEMA DE REPORTE DE PRODUCTOS ====================

/**
 * Abre el modal de reporte de producto
 * @param {number} productoId - ID del producto
 * @param {number} [vendedorId] - ID del vendedor (requerido para API Laravel denuncias)
 */
function abrirModalReporte(productoId, vendedorId) {
    const modal = document.getElementById('modalReporte');
    if (modal) {
        document.getElementById('reporteProductoId').value = productoId;
        const reporteUsuario = document.getElementById('reporteUsuarioId');
        if (reporteUsuario) reporteUsuario.value = vendedorId || '';
        document.querySelectorAll('input[name="motivo_reporte"]').forEach(r => r.checked = false);
        document.getElementById('comentarioReporte').value = '';
        modal.style.display = 'flex';
    }
}

/**
 * Cierra el modal de reporte
 */
function cerrarModalReporte() {
    const modal = document.getElementById('modalReporte');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Envía el reporte del producto
 * Laravel: POST /api/denuncias con {motivo_id, usuario_id, producto_id}
 */
async function enviarReporte() {
    const productoId = document.getElementById('reporteProductoId').value;
    const usuarioIdEl = document.getElementById('reporteUsuarioId');
    const vendedorId = usuarioIdEl ? usuarioIdEl.value : '';
    const motivoInput = document.querySelector('input[name="motivo_reporte"]:checked');
    const comentario = document.getElementById('comentarioReporte').value;

    if (!motivoInput) {
        showToast('Selecciona un motivo para el reporte', 'error');
        return;
    }

    const motivoId = parseInt(motivoInput.value, 10);

    const useLaravel = window.API_CONFIG && window.API_CONFIG.USE_LARAVEL && typeof window.getLaravelDenunciasUrl === 'function';
    try {
        let response;
        if (useLaravel && vendedorId) {
            response = await fetch(window.getLaravelDenunciasUrl(), {
                method: 'POST',
                headers: {
                    ...(window.getApiHeaders ? window.getApiHeaders() : {}),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    motivo_id: motivoId,
                    usuario_id: parseInt(vendedorId, 10),
                    producto_id: parseInt(productoId, 10)
                })
            });
        } else {
            response = await fetch(getApiUrl('api/reportar_producto.php'), {
                method: 'POST',
                headers: {
                    ...(window.getApiHeaders ? window.getApiHeaders() : {}),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    producto_id: productoId,
                    motivo: motivoId,
                    comentario: comentario
                })
            });
        }

        const data = await response.json();
        const ok = data.success || data.status === 'success';
        if (ok) {
            showToast(data.message || 'Reporte enviado correctamente', 'success');
            cerrarModalReporte();
        } else {
            showToast(data.message || data.error || 'Error al enviar el reporte', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    }
}

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function (e) {
    const modal = document.getElementById('modalReporte');
    if (modal && e.target === modal) {
        cerrarModalReporte();
    }
});

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModalReporte();
    }
});

