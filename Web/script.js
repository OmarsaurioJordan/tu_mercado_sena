// JavaScript para funcionalidades del marketplace

/* =========================================================
   HELPERS GLOBALES
========================================================= */

function getApiBaseUrl() {
    const candidates = [
        window.LARAVEL_API_URL,
        window.API_BASE_URL
    ];

    for (const value of candidates) {
        if (typeof value === 'string' && value.trim() !== '') {
            return value.replace(/\/+$/, '');
        }
    }

    return '';
}

function getStorageBaseUrl() {
    const value = window.LARAVEL_STORAGE_URL || '';
    return typeof value === 'string' ? value.replace(/\/+$/, '') : '';
}

function hasLaravelApi() {
    return !!getApiBaseUrl();
}

function isLocalhost() {
    const host = window.location.hostname;
    return host === 'localhost' || host === '127.0.0.1';
}

/**
 * Para evitar CORS en desarrollo local, el navegador NO debe
 * pegarle directo a Laravel si la web corre en localhost.
 * En producción sí usa Laravel directo.
 */
function shouldUseLaravelApi() {
    return hasLaravelApi() && !isLocalhost();
}

function getApiHeaders(extra = {}) {
    const headers = {
        Accept: 'application/json',
        ...extra
    };

    const token = window.LARAVEL_API_TOKEN 
        || localStorage.getItem('api_token') 
        || '';

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    return headers;
}

function getApiUrl(endpoint = '') {
    const cleanEndpoint = String(endpoint || '').replace(/^\/+/, '');
    const apiBase = getApiBaseUrl();

    if (apiBase) {
        if (!cleanEndpoint) return apiBase;
        if (cleanEndpoint.startsWith('api/')) {
            return `${apiBase}/${cleanEndpoint.replace(/^api\//, '')}`;
        }
        return `${apiBase}/${cleanEndpoint}`;
    }

    return `${window.BASE_URL || ''}${cleanEndpoint}`;
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto == null ? '' : String(texto);
    return div.innerHTML;
}

function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

function formatMessageTime(timestamp) {
    if (!timestamp) return 'Ahora';

    const date = new Date(String(timestamp).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return 'Ahora';

    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);

    if (minutes < 1) return 'Ahora';
    if (minutes < 60) return `Hace ${minutes} min`;
    if (minutes < 1440) return `Hace ${Math.floor(minutes / 60)} h`;

    return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getDefaultProductImage() {
    return `${window.BASE_URL || ''}assets/images/default-product.jpg`;
}

function getDefaultAvatarImage() {
    return `${window.BASE_URL || ''}assets/images/default-avatar.jpg`;
}

function productImageFallback(imgEl) {
    const fallback = imgEl && imgEl.getAttribute('data-fallback-uploads');

    if (fallback && imgEl.getAttribute('data-fallback-tried') !== '1') {
        imgEl.setAttribute('data-fallback-tried', '1');
        imgEl.src = fallback;
        return;
    }

    imgEl.onerror = null;
    imgEl.src = getDefaultProductImage();
}
window.productImageFallback = productImageFallback;

/* =========================================================
   TOAST
========================================================= */

function showToast(message, type = 'info') {
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();

    const bg =
        type === 'success' ? '#28a745' :
        type === 'error' ? '#dc3545' :
        type === 'warning' ? '#f39c12' :
        'var(--color-primary)';

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: ${bg};
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 99999;
        animation: slideUp 0.3s ease;
        max-width: 90vw;
        text-align: center;
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            if (toast.parentElement) toast.remove();
        }, 300);
    }, 3000);
}

(function injectToastStyles() {
    if (document.getElementById('toast-styles')) return;

    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.textContent = `
        @keyframes slideUp {
            from { transform: translateX(-50%) translateY(20px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
})();

/* =========================================================
   SISTEMA DE SONIDO DE NOTIFICACIÓN
========================================================= */

const NotificationSound = {
    audioContext: null,
    enabled: true,

    init() {
        const saved = localStorage.getItem('notificationSoundEnabled');
        this.enabled = saved !== 'false';
    },

    toggle() {
        this.enabled = !this.enabled;
        localStorage.setItem('notificationSoundEnabled', String(this.enabled));
        return this.enabled;
    },

    play() {
        if (!this.enabled) return;

        try {
            if (!this.audioContext) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }

            const ctx = this.audioContext;
            const now = ctx.currentTime;

            const oscillator = ctx.createOscillator();
            const gainNode = ctx.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(ctx.destination);

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, now);
            oscillator.frequency.setValueAtTime(1100, now + 0.1);

            gainNode.gain.setValueAtTime(0.3, now);
            gainNode.gain.exponentialRampToValueAtTime(0.01, now + 0.3);

            oscillator.start(now);
            oscillator.stop(now + 0.3);
        } catch (e) {
            console.log('Audio no soportado:', e);
        }
    }
};

NotificationSound.init();

function playNotificationSound() {
    NotificationSound.play();
}

function toggleNotificationSound() {
    return NotificationSound.toggle();
}

/* =========================================================
   TEMA OSCURO / CLARO
========================================================= */

function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.innerHTML = theme === 'dark'
            ? '<i class="ri-sun-line"></i>'
            : '<i class="ri-moon-line"></i>';
    }
}

function initTheme() {
    const pagesForceLight = ['welcome.php', 'login.php', 'register.php'];
    const currentPage = window.location.pathname.split('/').pop();

    if (pagesForceLight.includes(currentPage)) {
        localStorage.setItem('theme', 'light');
        document.documentElement.setAttribute('data-theme', 'light');
        updateThemeIcon('light');
        return;
    }

    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
}

/* =========================================================
   MENÚ HAMBURGUESA
========================================================= */

function closeHamburgerMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    const menuOverlay = document.getElementById('menuOverlay');

    if (menuToggle) menuToggle.classList.remove('active');
    if (mainNav) mainNav.classList.remove('active');
    if (menuOverlay) menuOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

function initHamburgerMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const menuClose = document.getElementById('menuClose');
    const mainNav = document.getElementById('mainNav');
    const menuOverlay = document.getElementById('menuOverlay');

    if (!menuToggle || !mainNav) return;

    menuToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        menuToggle.classList.toggle('active');
        mainNav.classList.toggle('active');
        if (menuOverlay) menuOverlay.classList.toggle('active');
        document.body.style.overflow = mainNav.classList.contains('active') ? 'hidden' : '';
    });

    if (menuClose) {
        menuClose.addEventListener('click', closeHamburgerMenu);
    }

    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeHamburgerMenu);
    }

    const navLinks = mainNav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            setTimeout(closeHamburgerMenu, 100);
        });
    });
}

/* =========================================================
   FAVORITOS
========================================================= */

function toggleFavorito(btn) {
    const vendedorId = btn.getAttribute('data-vendedor-id');
    const icon = btn.querySelector('.fav-icon');
    const textSpan = btn.querySelector('.fav-text');

    if (!vendedorId) return;

    btn.disabled = true;
    btn.style.opacity = '0.7';

    const isFavorite = btn.classList.contains('active');

    if (shouldUseLaravelApi() &&
        typeof window.getLaravelAddFavoritoUrl === 'function' &&
        typeof window.getLaravelDeleteFavoritoUrl === 'function') {

        const url = isFavorite
            ? window.getLaravelDeleteFavoritoUrl(vendedorId)
            : window.getLaravelAddFavoritoUrl(vendedorId);

        const method = isFavorite ? 'DELETE' : 'POST';

        fetch(url, {
            method,
            headers: getApiHeaders()
        })
            .then(response => response.json())
            .then(data => {
                const ok = data.success || data.status === 'success';

                if (!ok) {
                    const msg = data.message || data.error || 'No se pudo actualizar';
                    throw new Error(msg);
                }

                if (isFavorite) {
                    btn.classList.remove('active');
                    if (icon) icon.className = 'fav-icon ri-heart-3-line';
                    if (textSpan) textSpan.textContent = 'Añadir a Favoritos';
                } else {
                    btn.classList.add('active');
                    if (icon) icon.className = 'fav-icon ri-heart-3-fill';
                    if (textSpan) textSpan.textContent = 'En Favoritos';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message || 'Error de conexión', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.style.opacity = '1';
            });

        return;
    }

    const formData = new FormData();
    formData.append('vendedor_id', vendedorId);

    fetch(getApiUrl('api/toggle_favorito.php'), {
        method: 'POST',
        headers: getApiHeaders(),
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'No se pudo actualizar');
            }

            if (data.is_favorite) {
                btn.classList.add('active');
                if (icon) icon.className = 'fav-icon ri-heart-3-fill';
                if (textSpan) textSpan.textContent = 'En Favoritos';
            } else {
                btn.classList.remove('active');
                if (icon) icon.className = 'fav-icon ri-heart-3-line';
                if (textSpan) textSpan.textContent = 'Añadir a Favoritos';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Error de conexión', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.style.opacity = '1';
        });
}

/* =========================================================
   CHAT EN TIEMPO REAL
========================================================= */

let chatPollingInterval = null;
let lastMessageId = 0;
let currentChatModal = null;

function scrollChatToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

function normalizeLaravelChatDetail(raw) {
    const payload = raw?.data || raw?.chat_detalle || raw || {};
    const compradorId = payload.comprador_id != null ? Number(payload.comprador_id) : null;
    const currentUserId = window.CURRENT_USER_ID != null ? Number(window.CURRENT_USER_ID) : null;
    const mensajes = Array.isArray(payload.mensajes) ? payload.mensajes : [];

    const messages = mensajes.map(m => ({
        id: Number(m.id || 0),
        mensaje: m.mensaje || '',
        es_mio:
            currentUserId != null && compradorId != null
                ? (m.es_comprador === (compradorId === currentUserId) ? 1 : 0)
                : (m.es_comprador ? 1 : 0),
        imagen: m.imagen || null,
        fecha_registro: m.fecha_registro || m.created_at || ''
    }));

    return {
        success: raw?.status === 'success' || raw?.success === true || !!payload,
        messages
    };
}

function addMessageToChat(message, container) {
    if (!message || !container || document.getElementById(`message-${message.id}`)) {
        return;
    }

    const messageDiv = document.createElement('div');
    messageDiv.id = `message-${message.id}`;
    messageDiv.className = `message ${String(message.es_mio) === '1' ? 'message-sent' : 'message-received'}`;

    const messageText = document.createElement('p');
    messageText.innerHTML = String(message.mensaje || '').replace(/\n/g, '<br>');

    const messageTime = document.createElement('span');
    messageTime.className = 'message-time';
    messageTime.textContent = formatMessageTime(message.fecha_registro);

    messageDiv.appendChild(messageText);

    if (message.imagen) {
        const image = document.createElement('img');
        image.src = message.imagen;
        image.alt = 'Imagen del mensaje';
        image.style.maxWidth = '220px';
        image.style.borderRadius = '12px';
        image.style.display = 'block';
        image.style.marginTop = '0.75rem';
        messageDiv.appendChild(image);
    }

    messageDiv.appendChild(messageTime);
    container.appendChild(messageDiv);
}

function initChatRealTime(chatId) {
    if (!chatId) return;

    loadNewMessages(chatId);

    chatPollingInterval = setInterval(() => {
        loadNewMessages(chatId);
    }, 4000);

    window.addEventListener('beforeunload', () => {
        if (chatPollingInterval) {
            clearInterval(chatPollingInterval);
        }
    });
}

function loadNewMessages(chatId) {
    if (!chatId) return;

    if (shouldUseLaravelApi() && typeof window.getLaravelChatDetailUrl === 'function') {
        fetch(window.getLaravelChatDetailUrl(chatId), {
            headers: getApiHeaders()
        })
            .then(response => response.json())
            .then(raw => {
                const data = normalizeLaravelChatDetail(raw);

                if (data.success && Array.isArray(data.messages)) {
                    const chatMessages = document.getElementById('chatMessages');
                    if (!chatMessages) return;

                    data.messages.forEach(message => {
                        addMessageToChat(message, chatMessages);
                        lastMessageId = Math.max(lastMessageId, Number(message.id) || 0);
                    });

                    scrollChatToBottom();
                }
            })
            .catch(error => {
                console.error('Error al cargar mensajes:', error);
            });

        return;
    }

    fetch(getApiUrl(`api/get_messages.php?chat_id=${encodeURIComponent(chatId)}&last_id=${encodeURIComponent(lastMessageId)}`), {
        headers: getApiHeaders()
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.messages)) {
                const chatMessages = document.getElementById('chatMessages');
                if (!chatMessages) return;

                data.messages.forEach(message => {
                    addMessageToChat(message, chatMessages);
                    lastMessageId = Math.max(lastMessageId, Number(message.id) || 0);
                });

                scrollChatToBottom();
            }
        })
        .catch(error => {
            console.error('Error al cargar mensajes:', error);
        });
}

function applySendMessageResponse(chatId, data, callback) {
    if (!(data && data.success)) {
        showToast('Error al enviar mensaje: ' + (data?.error || 'Error desconocido'), 'error');
        return;
    }

    const payloadMessage = data.message || data.mensaje || null;

    if (window.currentModalChatId === chatId) {
        const messagesContainer = document.getElementById('chatModalMessages');
        if (messagesContainer && payloadMessage) {
            addMessageToModal(payloadMessage, messagesContainer);
            window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, Number(payloadMessage.id) || 0);
            scrollModalToBottom();
        } else {
            loadModalMessages(chatId);
        }
    } else {
        const textarea = document.getElementById('messageInput');
        if (textarea) textarea.value = '';

        if (payloadMessage) {
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                addMessageToChat(payloadMessage, chatMessages);
                lastMessageId = Math.max(lastMessageId, Number(payloadMessage.id) || 0);
                scrollChatToBottom();
            }
        } else {
            loadNewMessages(chatId);
        }
    }

    loadNotifications(true);
    if (typeof callback === 'function') callback();
}

function sendMessage(chatId, messageText, callback) {
    if (!String(messageText || '').trim() || !chatId) return;

    if (shouldUseLaravelApi() && typeof window.getLaravelSendMessageUrl === 'function') {
        fetch(window.getLaravelSendMessageUrl(chatId), {
            method: 'POST',
            headers: getApiHeaders({
                'Content-Type': 'application/json'
            }),
            body: JSON.stringify({
                mensaje: messageText
            })
        })
            .then(response => response.json())
            .then(raw => {
                const normalized = raw?.status === 'success' && raw?.nuevo_mensaje
                    ? {
                        success: true,
                        message: {
                            id: raw.nuevo_mensaje.id,
                            mensaje: raw.nuevo_mensaje.mensaje,
                            es_mio: 1,
                            fecha_registro: raw.nuevo_mensaje.fecha_registro || raw.nuevo_mensaje.created_at || ''
                        }
                    }
                    : {
                        success: !!raw?.success,
                        message: raw?.message,
                        error: raw?.message || raw?.error
                    };

                applySendMessageResponse(chatId, normalized, callback);
            })
            .catch(error => {
                console.error('Error al enviar mensaje:', error);
                showToast('Error al enviar mensaje. Por favor intenta de nuevo.', 'error');
            });

        return;
    }

    const formData = new FormData();
    formData.append('chat_id', chatId);
    formData.append('mensaje', messageText);

    fetch(getApiUrl('api/send_message.php'), {
        method: 'POST',
        headers: getApiHeaders(),
        body: formData
    })
        .then(response => response.json())
        .then(data => applySendMessageResponse(chatId, data, callback))
        .catch(error => {
            console.error('Error al enviar mensaje:', error);
            showToast('Error al enviar mensaje. Por favor intenta de nuevo.', 'error');
        });
}

/* =========================================================
   NOTIFICACIONES
========================================================= */

let notificationsPollingInterval = null;

function normalizeLaravelChatsList(chats) {
    const currentUserId = window.CURRENT_USER_ID != null ? Number(window.CURRENT_USER_ID) : null;
    let totalNoLeidos = 0;

    const out = (chats || []).map(c => {
        const compradorId = c.comprador_id != null ? Number(c.comprador_id) : null;
        let mensajesNoLeidos = 0;

        if (compradorId != null && currentUserId != null) {
            const soyComprador = compradorId === currentUserId;
            mensajesNoLeidos = soyComprador
                ? (!c.visto_comprador ? 1 : 0)
                : (!c.visto_vendedor ? 1 : 0);
        }

        totalNoLeidos += mensajesNoLeidos;

        return {
            chat_id: c.id,
            producto_nombre: c?.producto?.nombre || 'Chat',
            otro_usuario: c?.usuario?.nickname || c?.usuario?.apodo || '',
            ultimo_mensaje: {
                mensaje: c.ultimoMensajeTexto || '',
                fecha_registro: c.fechaUltimoMensaje || ''
            },
            mensajes_no_leidos: mensajesNoLeidos
        };
    });

    return {
        chats: out,
        total_no_leidos: totalNoLeidos
    };
}

function updateNotificationCount(count) {
    const notificationCount = document.getElementById('notificationCount');
    if (!notificationCount) return;

    if (count > 0) {
        notificationCount.textContent = count > 99 ? '99+' : String(count);
        notificationCount.classList.remove('hidden');
    } else {
        notificationCount.classList.add('hidden');
    }
}

function updateChatsList(chats) {
    const chatsList = document.getElementById('chatsList');
    if (!chatsList) return;

    if (!Array.isArray(chats) || chats.length === 0) {
        chatsList.innerHTML = `
            <div class="chat-item">
                <p style="padding: 1rem; text-align: center; color: var(--color-text-light);">
                    No tienes chats activos
                </p>
            </div>
        `;
        return;
    }

    chatsList.innerHTML = chats.map(chat => {
        const unread = parseInt(chat.mensajes_no_leidos, 10) || 0;
        const lastMsg = chat.ultimo_mensaje?.mensaje || 'Sin mensajes';
        const lastMsgPreview = lastMsg.length > 50 ? `${lastMsg.substring(0, 50)}...` : lastMsg;

        return `
            <div class="chat-item" data-chat-id="${chat.chat_id}"
                 onclick="openChatModal(${chat.chat_id}, '${escapeHtml(chat.producto_nombre)}', '${escapeHtml(chat.otro_usuario)}')">
                <div class="chat-item-info">
                    <div class="chat-item-title">${escapeHtml(chat.producto_nombre)}</div>
                    <div class="chat-item-message">${escapeHtml(chat.otro_usuario)}: ${escapeHtml(lastMsgPreview)}</div>
                </div>
                ${unread > 0
                    ? `<span class="chat-item-badge">${unread > 99 ? '99+' : unread}</span>`
                    : `<span class="chat-item-badge hidden">0</span>`}
            </div>
        `;
    }).join('');
}

function showBrowserNotification(title, message, chatId) {
    const existing = document.querySelectorAll('.browser-notification');
    existing.forEach(n => n.remove());

    const notification = document.createElement('div');
    notification.className = 'browser-notification';
    notification.onclick = () => {
        openChatModal(chatId, title, '');
        notification.remove();
    };

    notification.style.cssText = `
        position: fixed;
        right: 16px;
        bottom: 110px;
        width: min(360px, calc(100vw - 32px));
        background: var(--color-bg, #1e1e1e);
        color: var(--color-text, #fff);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        box-shadow: 0 10px 25px rgba(0,0,0,.25);
        z-index: 99999;
        padding: 0.9rem 1rem;
        cursor: pointer;
    `;

    notification.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.4rem;">
            <div style="font-weight:700;">${escapeHtml(title)}</div>
            <button type="button"
                    style="background:none;border:none;color:inherit;font-size:20px;cursor:pointer;"
                    onclick="event.stopPropagation(); this.closest('.browser-notification').remove()">×</button>
        </div>
        <div style="font-size:.95rem;opacity:.95;">${escapeHtml(message)}</div>
        <div style="font-size:.8rem;opacity:.65;margin-top:.5rem;">Hace un momento</div>
    `;

    document.body.appendChild(notification);
    playNotificationSound();

    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function requestBrowserNotification(title, body) {
    if (!('Notification' in window)) return;

    if (Notification.permission === 'granted') {
        new Notification(title, {
            body,
            icon: `${window.BASE_URL || ''}favicon.ico`,
            tag: 'chat-notification'
        });
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                new Notification(title, {
                    body,
                    icon: `${window.BASE_URL || ''}favicon.ico`,
                    tag: 'chat-notification'
                });
            }
        });
    }
}

function checkNewMessages(chats) {
    const storedLastCheck = localStorage.getItem('lastNotificationCheck');
    const lastCheckTime = storedLastCheck ? parseInt(storedLastCheck, 10) : Date.now();
    const sessionStart = window.sessionStartTime || Date.now();
    const minimalTime = Math.max(lastCheckTime, sessionStart);

    (chats || []).forEach(chat => {
        if (chat.ultimo_mensaje && Number(chat.mensajes_no_leidos) > 0) {
            try {
                const fechaStr = chat.ultimo_mensaje.fecha_registro || '';
                const fecha = new Date(fechaStr.replace(' ', 'T'));
                const lastMsgTime = fecha.getTime();

                if (lastMsgTime > minimalTime) {
                    const notificaPush = window.NOTIFICA_PUSH !== undefined ? Number(window.NOTIFICA_PUSH) : 1;
                    if (notificaPush) {
                        const rawMessage = chat.ultimo_mensaje.mensaje || '';
                        const messagePreview = rawMessage.length > 50
                            ? `${rawMessage.substring(0, 50)}...`
                            : rawMessage;

                        showBrowserNotification(
                            chat.producto_nombre,
                            `${chat.otro_usuario}: ${messagePreview}`,
                            chat.chat_id
                        );

                        requestBrowserNotification(
                            chat.producto_nombre,
                            `${chat.otro_usuario} te escribió`
                        );
                    }
                }
            } catch (e) {
                console.error('Error al procesar mensaje:', e);
            }
        }
    });

    localStorage.setItem('lastNotificationCheck', Date.now().toString());
}

function applyNotificationsData(data, silent) {
    if (!data || !data.success) return;

    updateNotificationCount(data.total_no_leidos || 0);

    const chatsList = document.getElementById('chatsList');
    if (chatsList && (chatsList.classList.contains('active') || silent)) {
        updateChatsList(data.chats || []);
    }

    if (!silent && !window.isFirstNotificationLoad && data.chats && !window.currentModalChatId) {
        checkNewMessages(data.chats);
    }
}

function loadNotifications(silent = false) {
    if (window.CURRENT_USER_ID == null || Number(window.CURRENT_USER_ID) === 0) return;

    if (shouldUseLaravelApi() && typeof window.getLaravelChatsListUrl === 'function') {
        fetch(window.getLaravelChatsListUrl(), {
            method: 'GET',
            headers: getApiHeaders()
        })
            .then(response => response.json())
            .then(raw => {
                const chats = Array.isArray(raw) ? raw : (raw.data || raw.chats || []);
                const normalized = normalizeLaravelChatsList(chats);

                applyNotificationsData({
                    success: true,
                    total_no_leidos: normalized.total_no_leidos,
                    chats: normalized.chats
                }, silent);
            })
            .catch(() => {});

        return;
    }

    fetch(getApiUrl('api/get_chats_notificaciones.php'), {
        headers: getApiHeaders()
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                applyNotificationsData(data, silent);
            }
        })
        .catch(() => {});
}

function initNotifications() {
    if (window.CURRENT_USER_ID == null || Number(window.CURRENT_USER_ID) === 0) return;

    window.sessionStartTime = Date.now();
    window.isFirstNotificationLoad = true;

    loadNotifications(true);

    setTimeout(() => {
        window.isFirstNotificationLoad = false;
    }, 2000);

    const notifyInterval = shouldUseLaravelApi() ? 15000 : 10000;

    notificationsPollingInterval = setInterval(() => {
        loadNotifications(false);
    }, notifyInterval);

    window.addEventListener('beforeunload', () => {
        if (notificationsPollingInterval) {
            clearInterval(notificationsPollingInterval);
        }
    });
}

/* =========================================================
   MODAL DE CHAT
========================================================= */

function scrollModalToBottom() {
    const messagesContainer = document.getElementById('chatModalMessages');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

function addMessageToModal(message, container) {
    if (!message || !container || document.getElementById(`modal-message-${message.id}`)) {
        return;
    }

    const messageDiv = document.createElement('div');
    messageDiv.id = `modal-message-${message.id}`;
    messageDiv.className = `message ${String(message.es_mio) === '1' ? 'message-sent' : 'message-received'}`;

    const messageText = document.createElement('p');
    messageText.innerHTML = String(message.mensaje || '').replace(/\n/g, '<br>');

    const messageTime = document.createElement('span');
    messageTime.className = 'message-time';
    messageTime.textContent = formatMessageTime(message.fecha_registro);

    messageDiv.appendChild(messageText);

    if (message.imagen) {
        const image = document.createElement('img');
        image.src = message.imagen;
        image.alt = 'Imagen del mensaje';
        image.style.maxWidth = '220px';
        image.style.borderRadius = '12px';
        image.style.display = 'block';
        image.style.marginTop = '0.75rem';
        messageDiv.appendChild(image);
    }

    messageDiv.appendChild(messageTime);
    container.appendChild(messageDiv);
}

function loadModalMessages(chatId, silent = false) {
    if (shouldUseLaravelApi() && typeof window.getLaravelChatDetailUrl === 'function') {
        fetch(window.getLaravelChatDetailUrl(chatId), {
            headers: getApiHeaders()
        })
            .then(r => r.json())
            .then(raw => {
                const data = normalizeLaravelChatDetail(raw);

                if (data.success && data.messages && data.messages.length > 0) {
                    const messagesContainer = document.getElementById('chatModalMessages');
                    if (!messagesContainer) return;

                    data.messages.forEach(message => {
                        addMessageToModal(message, messagesContainer);
                        window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, Number(message.id) || 0);
                    });

                    scrollModalToBottom();
                    loadNotifications(true);
                } else if (!silent) {
                    loadAllModalMessages(chatId);
                }
            })
            .catch(error => console.error('Error al cargar mensajes del modal:', error));

        return;
    }

    const lastId = window.currentModalLastMessageId || 0;

    fetch(getApiUrl(`api/get_messages.php?chat_id=${encodeURIComponent(chatId)}&last_id=${encodeURIComponent(lastId)}`), {
        headers: getApiHeaders()
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                const messagesContainer = document.getElementById('chatModalMessages');
                if (!messagesContainer) return;

                data.messages.forEach(message => {
                    addMessageToModal(message, messagesContainer);
                    window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, Number(message.id) || 0);
                });

                scrollModalToBottom();
                loadNotifications(true);
            } else if (!silent) {
                loadAllModalMessages(chatId);
            }
        })
        .catch(error => console.error('Error al cargar mensajes del modal:', error));
}

function loadAllModalMessages(chatId) {
    if (shouldUseLaravelApi() && typeof window.getLaravelChatDetailUrl === 'function') {
        fetch(window.getLaravelChatDetailUrl(chatId), {
            headers: getApiHeaders()
        })
            .then(r => r.json())
            .then(raw => {
                const data = normalizeLaravelChatDetail(raw);

                if (data.success && data.messages) {
                    const messagesContainer = document.getElementById('chatModalMessages');
                    if (!messagesContainer) return;

                    messagesContainer.innerHTML = '';
                    data.messages.forEach(message => {
                        addMessageToModal(message, messagesContainer);
                        window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, Number(message.id) || 0);
                    });

                    scrollModalToBottom();
                    loadNotifications(true);
                }
            })
            .catch(error => console.error('Error al cargar todos los mensajes:', error));

        return;
    }

    fetch(getApiUrl(`api/get_messages.php?chat_id=${encodeURIComponent(chatId)}&last_id=0`), {
        headers: getApiHeaders()
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages) {
                const messagesContainer = document.getElementById('chatModalMessages');
                if (!messagesContainer) return;

                messagesContainer.innerHTML = '';
                data.messages.forEach(message => {
                    addMessageToModal(message, messagesContainer);
                    window.currentModalLastMessageId = Math.max(window.currentModalLastMessageId || 0, Number(message.id) || 0);
                });

                scrollModalToBottom();
                loadNotifications(true);
            }
        })
        .catch(error => console.error('Error al cargar todos los mensajes:', error));
}

function sendModalMessage(chatId) {
    const textarea = document.getElementById('chatModalInput');
    if (!textarea || !textarea.value.trim()) return;

    const messageText = textarea.value;
    textarea.value = '';
    textarea.disabled = true;

    sendMessage(chatId, messageText, () => {
        textarea.disabled = false;
        textarea.focus();
        loadModalMessages(chatId);
        loadNotifications(true);
    });
}

function closeChatModal() {
    if (currentChatModal) {
        currentChatModal.remove();
        currentChatModal = null;
        window.currentModalChatId = null;
        window.currentModalLastMessageId = 0;
    }

    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
        chatPollingInterval = null;
    }

    setTimeout(() => {
        loadNotifications(true);
    }, 500);
}

function openChatModal(chatId, productoNombre, otroUsuario) {
    const chatsList = document.getElementById('chatsList');
    if (chatsList) {
        chatsList.classList.remove('active');
    }

    if (currentChatModal) {
        closeChatModal();
    }

    const modal = document.createElement('div');
    modal.className = 'chat-modal active';
    modal.id = 'chatModal';

    modal.innerHTML = `
        <div class="chat-modal-content">
            <div class="chat-modal-header">
                <div>
                    <h3 style="margin:0;">${escapeHtml(productoNombre)}</h3>
                    ${otroUsuario ? `<small style="opacity:.75;">${escapeHtml(otroUsuario)}</small>` : ''}
                </div>
                <button class="chat-modal-close" type="button" onclick="closeChatModal()">×</button>
            </div>
            <div class="chat-modal-body">
                <div class="chat-modal-messages" id="chatModalMessages"></div>
                <div class="chat-modal-input">
                    <form id="chatModalForm" onsubmit="event.preventDefault(); sendModalMessage(${Number(chatId)})">
                        <textarea id="chatModalInput" placeholder="Escribe un mensaje..." required rows="2"></textarea>
                        <button type="submit" class="btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    currentChatModal = modal;
    window.currentModalChatId = Number(chatId);
    window.currentModalLastMessageId = 0;

    loadAllModalMessages(chatId);

    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
    }

    chatPollingInterval = setInterval(() => {
        if (window.currentModalChatId === Number(chatId)) {
            loadModalMessages(chatId, true);
            loadNotifications(true);
        }
    }, 4000);

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

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeChatModal();
        }
    });
}

/* =========================================================
   VALIDACIÓN GENÉRICA DE FORMULARIOS Y PREVIEWS
========================================================= */

function initGenericForms() {
    const forms = document.querySelectorAll('form:not(#messageForm):not(#chatModalForm)');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                const value = typeof field.value === 'string' ? field.value.trim() : field.value;
                if (!value) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '';
                }
            });

            if (!isValid) {
                e.preventDefault();
                showToast('Por favor complete todos los campos requeridos', 'error');
            }
        });
    });

    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]:not(#imagen):not(.avatar-input)');
    imageInputs.forEach(input => {
        input.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (event) {
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
                preview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    });

    const priceInputs = document.querySelectorAll('input[type="number"][name="precio"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function (e) {
            if (Number(e.target.value) < 0) {
                e.target.value = '0';
            }
        });
    });
}

/* =========================================================
   AVATAR
========================================================= */

function initAvatarLogic() {
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
            if (!this.files || this.files.length === 0) return;

            const file = this.files[0];
            const reader = new FileReader();

            reader.onload = (e) => {
                if (avatarPhoto) avatarPhoto.src = e.target.result;
                if (headerAvatar) headerAvatar.src = e.target.result;
            };

            reader.readAsDataURL(file);
            avatarUploadForm.submit();
        });
    }

    if (deleteAvatarBtn) {
        deleteAvatarBtn.addEventListener('click', () => {
            if (confirm('¿Deseas eliminar tu foto de perfil?')) {
                window.location.href = 'perfil.php?section=avatar&action=delete';
            }
        });
    }
}

/* =========================================================
   VISTA PREVIA DE IMÁGENES MÚLTIPLES
========================================================= */

function initMultipleImagesUpload() {
    const input = document.getElementById('imagenes');
    const previewContainer = document.getElementById('previsualizaciones');
    const dropArea = document.getElementById('dropArea');

    if (!input || !previewContainer || !dropArea) return;

    let selectedFiles = [];

    function syncInputFiles() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        input.files = dt.files;
    }

    function renderPreviews() {
        previewContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'prev-container';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Vista previa ${index + 1}">
                    <button type="button" class="btn-remove-prev" data-index="${index}">×</button>
                `;

                previewContainer.appendChild(div);

                const btnRemove = div.querySelector('.btn-remove-prev');
                if (btnRemove) {
                    btnRemove.addEventListener('click', () => {
                        selectedFiles.splice(index, 1);
                        syncInputFiles();
                        renderPreviews();
                    });
                }
            };

            reader.readAsDataURL(file);
        });
    }

    input.addEventListener('change', function (e) {
        const files = Array.from(e.target.files || []);

        selectedFiles = files.slice(0, 5);
        if (files.length > 5) {
            showToast('Solo puedes subir un máximo de 5 imágenes. Se tomarán las primeras 5.', 'warning');
        }

        syncInputFiles();
        renderPreviews();
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('drag-over'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('drag-over'), false);
    });

    dropArea.addEventListener('drop', (e) => {
        const files = Array.from((e.dataTransfer && e.dataTransfer.files) || []);
        selectedFiles = files.slice(0, 5);

        if (files.length > 5) {
            showToast('Solo puedes subir un máximo de 5 imágenes.', 'warning');
        }

        syncInputFiles();
        renderPreviews();
    }, false);
}

/* =========================================================
   ZOOM DE IMAGEN
========================================================= */

let zoomModal = null;
let currentZoomLevel = 1;
let isDragging = false;
let startX = 0;
let startY = 0;
let translateX = 0;
let translateY = 0;

function updateZoomTransform() {
    const zoomImage = document.getElementById('zoomImage');
    if (zoomImage) {
        zoomImage.style.transform = `translate(${translateX}px, ${translateY}px) scale(${currentZoomLevel})`;
    }
}

function zoomIn() {
    currentZoomLevel = Math.min(currentZoomLevel + 0.5, 4);
    updateZoomTransform();
}
window.zoomIn = zoomIn;

function zoomOut() {
    currentZoomLevel = Math.max(currentZoomLevel - 0.5, 0.5);
    updateZoomTransform();
}
window.zoomOut = zoomOut;

function zoomReset() {
    currentZoomLevel = 1;
    translateX = 0;
    translateY = 0;
    updateZoomTransform();
}
window.zoomReset = zoomReset;

function closeZoomModal() {
    if (!zoomModal) return;

    zoomModal.classList.remove('active');
    document.body.style.overflow = '';
    currentZoomLevel = 1;
    translateX = 0;
    translateY = 0;
}
window.closeZoomModal = closeZoomModal;

function getDistance(touch1, touch2) {
    const dx = touch1.clientX - touch2.clientX;
    const dy = touch1.clientY - touch2.clientY;
    return Math.sqrt(dx * dx + dy * dy);
}

function initTouchZoom() {
    const zoomImage = document.getElementById('zoomImage');
    if (!zoomImage) return;

    let initialDistance = 0;
    let initialZoom = 1;

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

    zoomImage.addEventListener('wheel', function (e) {
        e.preventDefault();
        if (e.deltaY < 0) zoomIn();
        else zoomOut();
    }, { passive: false });
}

function openZoomModal(imageSrc) {
    if (!zoomModal) {
        zoomModal = document.createElement('div');
        zoomModal.className = 'zoom-modal';
        zoomModal.id = 'zoomModal';

        zoomModal.innerHTML = `
            <span class="zoom-modal-close" onclick="closeZoomModal()">×</span>
            <img class="zoom-modal-content" id="zoomImage" src="" alt="Vista ampliada">
            <div class="zoom-controls">
                <button class="zoom-btn" type="button" onclick="zoomIn()">+</button>
                <button class="zoom-btn" type="button" onclick="zoomReset()">⟲</button>
                <button class="zoom-btn" type="button" onclick="zoomOut()">−</button>
            </div>
            <div class="zoom-hint">Pellizca para hacer zoom • Arrastra para mover</div>
        `;

        document.body.appendChild(zoomModal);

        zoomModal.addEventListener('click', function (e) {
            if (e.target === zoomModal) {
                closeZoomModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && zoomModal && zoomModal.classList.contains('active')) {
                closeZoomModal();
            }
        });

        initTouchZoom();
    }

    const zoomImage = document.getElementById('zoomImage');
    if (!zoomImage) return;

    zoomImage.src = imageSrc;

    currentZoomLevel = 1;
    translateX = 0;
    translateY = 0;
    updateZoomTransform();

    zoomModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function initImageZoom() {
    const zoomableImages = document.querySelectorAll('.zoomable, .product-detail-image');
    zoomableImages.forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', function () {
            openZoomModal(this.src);
        });
    });
}

/* =========================================================
   INFINITE SCROLL
========================================================= */

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

window.infiniteScrollState = infiniteScrollState;

function clearScrollState() {
    try {
        sessionStorage.removeItem('infiniteScrollState');
    } catch (_) {}
}

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
    } catch (_) {}
}

function getScrollState() {
    try {
        const raw = sessionStorage.getItem('infiniteScrollState');
        if (!raw) return null;

        const state = JSON.parse(raw);
        if (Date.now() - state.timestamp > 10 * 60 * 1000) {
            clearScrollState();
            return null;
        }

        return state;
    } catch (_) {
        return null;
    }
}

function restoreScrollState(state) {
    const productsGrid = document.getElementById('productsGrid');
    const skeletonGrid = document.getElementById('skeletonGrid');
    const noMoreProducts = document.getElementById('noMoreProducts');

    if (!productsGrid || !state || !state.productsHTML) {
        loadProducts(1, true);
        return;
    }

    if (skeletonGrid) skeletonGrid.style.display = 'none';
    productsGrid.innerHTML = state.productsHTML;
    productsGrid.style.display = 'grid';

    infiniteScrollState.currentPage = state.currentPage;
    infiniteScrollState.hasMore = state.hasMore;
    infiniteScrollState.totalProducts = state.totalProducts;

    if (!state.hasMore && noMoreProducts) {
        noMoreProducts.style.display = 'block';
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            window.scrollTo({ top: state.scrollY, behavior: 'instant' });
        });
    });

    clearScrollState();
}

function updateClearFiltersButton() {
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (!clearFiltersBtn) return;

    const f = infiniteScrollState.filters;
    const hasFilters =
        Number(f.categoria) > 0 ||
        String(f.busqueda || '').length > 0 ||
        String(f.orden || 'newest') !== 'newest' ||
        Number(f.integridad) > 0 ||
        Number(f.precioMin) > 0 ||
        Number(f.precioMax) > 0;

    clearFiltersBtn.style.display = hasFilters ? 'inline-block' : 'none';
}

function applyFilters() {
    infiniteScrollState.currentPage = 1;
    infiniteScrollState.hasMore = true;
    clearScrollState();

    const noMoreProducts = document.getElementById('noMoreProducts');
    if (noMoreProducts) noMoreProducts.style.display = 'none';

    updateClearFiltersButton();
    loadProducts(1, true);
}

function setupAjaxFilters() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortFilter = document.getElementById('sortFilter');
    const integridadFilter = document.getElementById('integridadFilter');
    const precioMin = document.getElementById('precioMin');
    const precioMax = document.getElementById('precioMax');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const refreshBtn = document.getElementById('refreshProductsBtn');

    let searchTimeout;
    let priceTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                infiniteScrollState.filters.busqueda = e.target.value.trim();
                applyFilters();
            }, 400);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                infiniteScrollState.filters.busqueda = e.target.value.trim();
                applyFilters();
            }
        });
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', (e) => {
            infiniteScrollState.filters.categoria = parseInt(e.target.value, 10) || 0;
            applyFilters();
        });
    }

    if (integridadFilter) {
        integridadFilter.addEventListener('change', (e) => {
            infiniteScrollState.filters.integridad = parseInt(e.target.value, 10) || 0;
            applyFilters();
        });
    }

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

    if (sortFilter) {
        sortFilter.addEventListener('change', (e) => {
            infiniteScrollState.filters.orden = e.target.value;
            applyFilters();
        });
    }

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (categoryFilter) categoryFilter.value = '0';
            if (sortFilter) sortFilter.value = 'newest';
            if (integridadFilter) integridadFilter.value = '0';
            if (precioMin) precioMin.value = '';
            if (precioMax) precioMax.value = '';

            infiniteScrollState.filters = {
                categoria: 0,
                busqueda: '',
                orden: 'newest',
                integridad: 0,
                precioMin: 0,
                precioMax: 0
            };

            applyFilters();
        });
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            if (refreshBtn.classList.contains('refreshing')) return;
            refreshBtn.classList.add('refreshing');
            refreshBtn.disabled = true;
            reloadProducts();
            setTimeout(function () {
                refreshBtn.classList.remove('refreshing');
                refreshBtn.disabled = false;
            }, 800);
        });
    }

    updateClearFiltersButton();
}

function setupInfiniteScrollObserver() {
    const loadingMore = document.getElementById('loadingMore');
    if (!loadingMore || !('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !infiniteScrollState.isLoading && infiniteScrollState.hasMore) {
                loadMoreProducts();
            }
        });
    }, {
        rootMargin: '200px',
        threshold: 0.1
    });

    observer.observe(loadingMore);
}

function setupScrollListener() {
    let scrollTimeout;

    window.addEventListener('scroll', () => {
        if (scrollTimeout) clearTimeout(scrollTimeout);

        scrollTimeout = setTimeout(() => {
            if (infiniteScrollState.isLoading || !infiniteScrollState.hasMore) return;

            const scrollPosition = window.innerHeight + window.scrollY;
            const documentHeight = document.documentElement.scrollHeight;

            if (scrollPosition >= documentHeight * 0.8) {
                loadMoreProducts();
            }
        }, 100);
    }, { passive: true });
}

function loadMoreProducts() {
    if (infiniteScrollState.isLoading || !infiniteScrollState.hasMore) return;
    infiniteScrollState.currentPage += 1;
    loadProducts(infiniteScrollState.currentPage, false);
}

function normalizeProductsResponse(data) {
    // Si ya viene normalizado, retornar tal cual
    if (data.productos) return data;

    // Estructura Laravel: { success, data: [...], pagination: {...} }
    if (data.data && Array.isArray(data.data)) {
        const pagination = data.pagination || {};
        return {
            success: data.success ?? true,
            productos: data.data,
            paginacion: {
                total:        pagination.total        ?? data.data.length,
                per_page:     pagination.per_page     ?? 12,
                current_page: pagination.current_page ?? 1,
                last_page:    pagination.last_page    ?? 1,
                has_more:     (pagination.current_page ?? 1) < (pagination.last_page ?? 1)
            },
            uso_datos: data.uso_datos ?? 0
        };
    }

    return data;
}

function createProductCard(producto) {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.setAttribute('data-id', String(producto.id != null ? producto.id : ''));

    const integridadVal = producto.integridad;
    const integridadStr =
        typeof integridadVal === 'string'
            ? integridadVal
            : (integridadVal && integridadVal.nombre)
                ? integridadVal.nombre
                : (producto.integridad_nombre || '');

    const integridadLower = String(integridadStr || '').toLowerCase();

    let conditionClass = '';
    if (integridadLower === 'nuevo') conditionClass = 'condition-new';
    else if (integridadLower === 'usado') conditionClass = 'condition-used';

    const nombre = producto.nombre || '';
    const precioFormateado = producto.precio_formateado ||
        (producto.precio != null ? `${Number(producto.precio).toLocaleString('es-CO')} COP` : '');

    const vendedorNombre =
        producto.vendedor_nombre ||
        producto?.vendedor?.nickname ||
        producto?.vendedor?.apodo ||
        producto?.usuario?.nickname ||
        producto?.usuario?.apodo ||
        '';

    let vendedorAvatar =
        producto.vendedor_avatar ||
        producto?.vendedor?.imagen ||
        producto?.vendedor?.avatar ||
        producto?.usuario?.imagen ||
        '';

    if (vendedorAvatar && typeof window.getAvatarUrl === 'function' && !/^https?:\/\//i.test(vendedorAvatar)) {
        vendedorAvatar = window.getAvatarUrl(vendedorAvatar);
    }

    if (!vendedorAvatar) {
        vendedorAvatar = getDefaultAvatarImage();
    }

    const categoriaNombre =
        producto.categoria_nombre ||
        producto?.categoria?.nombre ||
        producto?.subcategoria?.categoria?.nombre ||
        '';

    const subcategoriaNombre =
        producto.subcategoria_nombre ||
        producto?.subcategoria?.nombre ||
        '';

    const foto = Array.isArray(producto.fotos) && producto.fotos.length ? producto.fotos[0] : null;
    const rawImagen = producto.imagen || (foto && (foto.url || foto.imagen)) || producto.producto_imagen || '';
    const rawFilename = (foto && foto.imagen)
        || (typeof rawImagen === 'string' && rawImagen ? rawImagen.replace(/^.*[\\/]/, '') : '')
        || '';

    let imgSrc = rawImagen;

    if (imgSrc && typeof window.getProductImageUrl === 'function') {
        if (/^https?:\/\//i.test(imgSrc)) {
            imgSrc = window.getProductImageUrl(imgSrc) || imgSrc;
        } else if (foto && foto.imagen && producto.id) {
            imgSrc = window.getProductImageUrl(foto.imagen, producto.id);
        } else {
            imgSrc = window.getProductImageUrl(imgSrc, producto.id);
        }
    } else if (imgSrc && imgSrc.startsWith('/') && hasLaravelApi()) {
        const origin = getApiBaseUrl().replace(/\/api\/?$/, '');
        imgSrc = origin + imgSrc;
    }

    const uploadsFallbackUrl = rawFilename
        ? ((typeof window.getProductImageUrl === 'function')
            ? window.getProductImageUrl(rawFilename, producto.id)
            : `${window.BASE_URL || ''}uploads/productos/${rawFilename}`)
        : '';

    const defaultProductImg = getDefaultProductImage();
    const productUrl = `${window.BASE_URL || ''}productos/producto.php?id=${encodeURIComponent(producto.id || '')}`;

    card.innerHTML = `
        <a href="${escapeHtml(productUrl)}" class="product-card-link" style="display:block;width:100%;height:100%;">
            <img src="${escapeHtml(imgSrc || defaultProductImg)}"
                 alt="${escapeHtml(nombre)}"
                 class="product-image"
                 loading="lazy"
                 data-fallback-uploads="${escapeHtml(uploadsFallbackUrl)}"
                 onerror="window.productImageFallback(this)">
            <div class="product-info">
                <h3 class="product-name">${escapeHtml(nombre)}</h3>
                <p class="product-price">${escapeHtml(precioFormateado)}</p>
                <div class="product-seller-info">
                    <img src="${escapeHtml(vendedorAvatar)}"
                         alt="${escapeHtml(vendedorNombre)}"
                         class="seller-avatar-small"
                         onerror="this.src='${escapeHtml(getDefaultAvatarImage())}'">
                    <span>Vendedor: ${escapeHtml(vendedorNombre)}</span>
                </div>
                <p class="product-category">${escapeHtml(categoriaNombre)} - ${escapeHtml(subcategoriaNombre)}</p>
                <span class="product-condition ${conditionClass}">${escapeHtml(integridadStr)}</span>
                <span class="product-stock">Disponibles: ${producto.disponibles != null ? escapeHtml(producto.disponibles) : ''}</span>
            </div>
        </a>
    `;

    return card;
}

function renderProducts(productos, container, isNewLoad = false) {
    productos.forEach((producto, index) => {
        const card = createProductCard(producto);

        if (isNewLoad) {
            card.classList.add('new-load');
            card.style.animationDelay = `${index * 0.05}s`;
        } else {
            card.classList.add('fade-in');
        }

        container.appendChild(card);
    });
}

function initLazyLoadImages() {
    const images = document.querySelectorAll('.product-image:not(.observed)');
    if (!('IntersectionObserver' in window)) return;

    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.classList.add('observed');

                if (img.complete) {
                    img.classList.add('loaded');
                } else {
                    img.addEventListener('load', () => {
                        img.classList.add('loaded');
                    }, { once: true });
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

async function loadProducts(page, isInitial = false) {
    const productsGrid = document.getElementById('productsGrid');
    const skeletonGrid = document.getElementById('skeletonGrid');
    const loadingMore = document.getElementById('loadingMore');
    const noProducts = document.getElementById('noProducts');
    const noMoreProducts = document.getElementById('noMoreProducts');

    if (!productsGrid) return;
    if (isInitial && infiniteScrollState.isLoading) return;

    infiniteScrollState.isLoading = true;

    if (isInitial) {
        if (skeletonGrid) skeletonGrid.style.display = 'grid';
        productsGrid.style.display = 'none';
        if (noProducts) noProducts.style.display = 'none';
    } else if (loadingMore) {
        loadingMore.style.display = 'flex';
    }

    try {
        const filters = infiniteScrollState.filters;
        const orden = filters.orden || 'newest';
        let response;

        if (shouldUseLaravelApi() && typeof window.getLaravelProductosUrl === 'function') {
            const perPage = 12;

            if (filters.busqueda && filters.busqueda.trim().length >= 2 && typeof window.getLaravelProductosBuscarUrl === 'function') {
                const url = window.getLaravelProductosBuscarUrl(filters.busqueda.trim(), page, perPage);
                response = await fetch(url, {
                    headers: {
                        ...getApiHeaders(),
                        'Cache-Control': 'no-cache'
                    },
                    cache: 'no-store'
                });
            } else {
                const orderMap = {
                    newest: ['fecha_registro', 'desc'],
                    oldest: ['fecha_registro', 'asc'],
                    price_asc: ['precio', 'asc'],
                    price_desc: ['precio', 'desc']
                };

                const [orderBy, orderDirection] = orderMap[orden] || orderMap.newest;

                const params = {
                    page,
                    per_page: perPage,
                    order_by: orderBy,
                    order_direction: orderDirection
                };

                if (filters.categoria > 0) params.categoria_id = filters.categoria;
                if (filters.integridad > 0) params.integridad_id = filters.integridad;

                const url = window.getLaravelProductosUrl(params);

                response = await fetch(url, {
                    headers: {
                        ...getApiHeaders(),
                        'Cache-Control': 'no-cache'
                    },
                    cache: 'no-store'
                });
            }
        } else {
            const params = new URLSearchParams({
                page: String(page),
                limit: '12',
                orden: String(orden)
            });

            if (filters.categoria > 0) params.append('categoria', String(filters.categoria));
            if (filters.busqueda) params.append('busqueda', String(filters.busqueda));
            if (filters.integridad > 0) params.append('integridad', String(filters.integridad));
            if (filters.precioMin > 0) params.append('precio_min', String(filters.precioMin));
            if (filters.precioMax > 0) params.append('precio_max', String(filters.precioMax));

            response = await fetch(getApiUrl(`api/productos.php?${params.toString()}`), {
                headers: {
                    ...getApiHeaders(),
                    'Cache-Control': 'no-cache'
                },
                cache: 'no-store'
            });
        }

        if (response.status === 401) {
            if (isInitial && noProducts) {
                if (skeletonGrid) skeletonGrid.style.display = 'none';
                productsGrid.style.display = 'grid';
                noProducts.style.display = 'block';
                const msg = noProducts.querySelector('p');
                if (msg) {
                    msg.textContent = 'Sesión no autorizada con la API. Cierra sesión e inicia sesión de nuevo para ver los productos.';
                }
            }
            return;
        }

        let data = await response.json();
        data = normalizeProductsResponse(data);
    
        // Depuración: Mostrar la estructura de datos recibida
        console.log('Data normalizada:', data);
        console.log('Productos:', data.productos);
        console.log('Primer producto:', data.productos?.[0]);
        
        if (data.success) {
            const productos = data.productos || [];
            const paginacion = data.paginacion || { has_more: false, total: 0 };

            infiniteScrollState.hasMore = !!paginacion.has_more;
            infiniteScrollState.totalProducts = Number(paginacion.total || 0);
            window.currentUsoDatos = data.uso_datos || 0;

            if (isInitial) {
                productsGrid.innerHTML = '';
                if (skeletonGrid) skeletonGrid.style.display = 'none';
                productsGrid.style.display = 'grid';
            }

            if (productos.length > 0) {
                renderProducts(productos, productsGrid, !isInitial);
                if (noProducts) noProducts.style.display = 'none';
            } else if (isInitial && noProducts) {
                noProducts.style.display = 'block';
            }

            if (!infiniteScrollState.hasMore && infiniteScrollState.totalProducts > 0 && noMoreProducts) {
                noMoreProducts.style.display = 'block';
            } else if (noMoreProducts) {
                noMoreProducts.style.display = 'none';
            }

            initLazyLoadImages();
        } else {
            console.error('Error al cargar productos:', data.error);
            if (isInitial) {
                if (skeletonGrid) skeletonGrid.style.display = 'none';
                if (noProducts) noProducts.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error de conexión al cargar productos:', error);
        if (isInitial) {
            if (skeletonGrid) skeletonGrid.style.display = 'none';
            if (noProducts) noProducts.style.display = 'block';
        }
    } finally {
        infiniteScrollState.isLoading = false;
        if (loadingMore) loadingMore.style.display = 'none';
    }
}

function reloadProducts() {
    infiniteScrollState.currentPage = 1;
    infiniteScrollState.hasMore = true;

    const productsGrid = document.getElementById('productsGrid');
    const noMoreProducts = document.getElementById('noMoreProducts');

    if (productsGrid) productsGrid.innerHTML = '';
    if (noMoreProducts) noMoreProducts.style.display = 'none';

    loadProducts(1, true);
}
window.reloadProducts = reloadProducts;

function initInfiniteScroll() {
    if (window.productFilters) {
        infiniteScrollState.filters = {
            ...infiniteScrollState.filters,
            ...window.productFilters
        };
    }

    setupInfiniteScrollObserver();
    setupScrollListener();
    setupAjaxFilters();

    const savedState = getScrollState();
    if (savedState) {
        restoreScrollState(savedState);
    } else {
        loadProducts(1, true);
    }

    document.addEventListener('click', (e) => {
        const productLink = e.target.closest('a[href*="productos/producto.php"]');
        if (productLink) {
            saveScrollState();
        }
    });

    window.addEventListener('pagehide', saveScrollState);
}

/* =========================================================
   PRODUCTOS POR VENDEDOR
========================================================= */

async function loadProductosVendedorLaravel(container, vendedorId) {
    if (!container || !vendedorId || typeof window.getLaravelProductosVendedorUrl !== 'function') return;

    const url = window.getLaravelProductosVendedorUrl(String(vendedorId));
    const noProductsEl = container.parentElement && container.parentElement.querySelector('.no-products');

    try {
        const response = await fetch(url, {
            headers: getApiHeaders()
        });

        const data = await response.json();
        const productos = (data && data.success && data.data) ? data.data : [];

        container.innerHTML = '';

        if (productos.length > 0) {
            renderProducts(productos, container, false);
            if (noProductsEl) noProductsEl.style.display = 'none';
        } else if (noProductsEl) {
            noProductsEl.style.display = 'block';
            const p = noProductsEl.querySelector('p');
            if (p) p.textContent = 'Este vendedor no tiene productos disponibles.';
        }

        initLazyLoadImages();
    } catch (e) {
        console.error('Error al cargar productos del vendedor:', e);
        if (noProductsEl) {
            noProductsEl.style.display = 'block';
            const p = noProductsEl.querySelector('p');
            if (p) p.textContent = 'No se pudieron cargar los productos.';
        }
    }
}

(function initProductosVendedorLaravel() {
    function run() {
        if (!shouldUseLaravelApi() || typeof window.getLaravelProductosVendedorUrl !== 'function') return;

        const el = document.querySelector('[data-productos-vendedor]');
        if (!el) return;

        const vendedorId = el.getAttribute('data-vendedor-id');
        if (!vendedorId) return;

        loadProductosVendedorLaravel(el, vendedorId);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();

/* =========================================================
   GALERÍA DE PRODUCTO
========================================================= */

const galleryState = {
    currentIndex: 0,
    totalImages: 0,
    images: [],
    touchStartX: 0,
    touchEndX: 0,
    isLightboxOpen: false
};

function navigateGallery(direction) {
    const newIndex = galleryState.currentIndex + direction;

    if (newIndex >= 0 && newIndex < galleryState.totalImages) {
        goToSlide(newIndex);
    } else if (newIndex < 0) {
        goToSlide(galleryState.totalImages - 1);
    } else {
        goToSlide(0);
    }
}

function updateLightboxImage(index) {
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxCounter = document.getElementById('lightboxCurrentSlide');
    const dots = document.querySelectorAll('.lightbox-dot');

    if (lightboxImage && galleryState.images[index]) {
        lightboxImage.src = galleryState.images[index];
        lightboxImage.alt = `Imagen ${index + 1}`;
    }

    if (lightboxCounter) {
        lightboxCounter.textContent = String(index + 1);
    }

    dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
    });
}

function goToSlide(index) {
    galleryState.currentIndex = index;

    const slides = document.querySelectorAll('.gallery-slide');
    slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
    });

    const thumbs = document.querySelectorAll('.gallery-thumb');
    thumbs.forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });

    const counter = document.getElementById('currentSlide');
    if (counter) {
        counter.textContent = String(index + 1);
    }

    if (galleryState.isLightboxOpen) {
        updateLightboxImage(index);
    }
}

function setupGalleryNavigation() {
    const prevBtn = document.getElementById('galleryPrev');
    const nextBtn = document.getElementById('galleryNext');

    if (prevBtn) prevBtn.addEventListener('click', () => navigateGallery(-1));
    if (nextBtn) nextBtn.addEventListener('click', () => navigateGallery(1));
}

function setupGalleryThumbnails() {
    const thumbs = document.querySelectorAll('.gallery-thumb');

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const index = parseInt(thumb.getAttribute('data-index'), 10);
            goToSlide(index);
        });

        thumb.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const index = parseInt(thumb.getAttribute('data-index'), 10);
                goToSlide(index);
            }
        });
    });
}

function openLightbox() {
    const lightbox = document.getElementById('galleryLightbox');
    if (!lightbox) return;

    galleryState.isLightboxOpen = true;
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';

    updateLightboxImage(galleryState.currentIndex);
    setupLightboxTouch();
}

function closeLightbox() {
    const lightbox = document.getElementById('galleryLightbox');
    if (!lightbox) return;

    galleryState.isLightboxOpen = false;
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
}

function setupGalleryFullscreen() {
    const fullscreenBtn = document.getElementById('galleryFullscreenBtn');
    const lightbox = document.getElementById('galleryLightbox');
    const closeBtn = document.getElementById('lightboxClose');
    const prevBtn = document.getElementById('lightboxPrev');
    const nextBtn = document.getElementById('lightboxNext');
    const galleryImages = document.querySelectorAll('.gallery-image');

    if (fullscreenBtn) fullscreenBtn.addEventListener('click', openLightbox);
    galleryImages.forEach(img => img.addEventListener('click', openLightbox));

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });
    }

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

    const dots = document.querySelectorAll('.lightbox-dot');
    dots.forEach(dot => {
        dot.addEventListener('click', (e) => {
            e.stopPropagation();
            const index = parseInt(dot.getAttribute('data-index'), 10);
            goToSlide(index);
        });
    });
}

function setupGalleryKeyboard() {
    document.addEventListener('keydown', (e) => {
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
        if (diff > 0) navigateGallery(1);
        else navigateGallery(-1);
    }

    galleryState.touchStartX = 0;
    galleryState.touchEndX = 0;
}

function setupGalleryTouch() {
    const galleryMain = document.querySelector('.gallery-main');
    if (!galleryMain) return;

    galleryMain.addEventListener('touchstart', handleTouchStart, { passive: true });
    galleryMain.addEventListener('touchmove', handleTouchMove, { passive: true });
    galleryMain.addEventListener('touchend', handleTouchEnd, { passive: true });
}

function setupLightboxTouch() {
    const lightboxContent = document.querySelector('.lightbox-content');
    if (!lightboxContent) return;

    lightboxContent.addEventListener('touchstart', handleTouchStart, { passive: true });
    lightboxContent.addEventListener('touchmove', handleTouchMove, { passive: true });
    lightboxContent.addEventListener('touchend', handleTouchEnd, { passive: true });
}

function initProductGallery() {
    const galleryContainer = document.getElementById('galleryContainer');
    if (!galleryContainer) return;

    if (Array.isArray(window.galleryImages) && window.galleryImages.length > 0) {
        galleryState.images = window.galleryImages;
        galleryState.totalImages = window.galleryImages.length;
    } else {
        return;
    }

    setupGalleryNavigation();
    setupGalleryThumbnails();
    setupGalleryFullscreen();
    setupGalleryKeyboard();
    setupGalleryTouch();

    if (galleryState.totalImages > 1 && window.innerWidth <= 600) {
        const galleryMain = document.querySelector('.gallery-main');
        if (galleryMain) {
            galleryMain.classList.add('show-swipe-hint');
            setTimeout(() => galleryMain.classList.remove('show-swipe-hint'), 3000);
        }
    }
}

function changeMainImage(src, thumb) {
    const mainImg = document.getElementById('mainProductImage');
    if (mainImg) mainImg.src = src;

    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(t => t.classList.remove('active'));

    if (thumb) {
        thumb.classList.add('active');
    }
}
window.changeMainImage = changeMainImage;

/* =========================================================
   BLOQUEAR USUARIO
========================================================= */

async function toggleBloqueo(usuarioId) {
    const esBloqueado = document.querySelector(`[data-usuario-id="${usuarioId}"]`)
        ?.classList.contains('bloqueado') ?? false;

    const mensaje = esBloqueado
        ? '¿Quieres desbloquear a este usuario?'
        : '¿Estás seguro de que deseas bloquear a este usuario? No podrás ver sus productos ni recibir mensajes de él.';

    if (!confirm(mensaje)) return;

    try {
        if (!shouldUseLaravelApi()) {
            showToast('Bloqueo no disponible en este entorno.', 'error');
            return;
        }

        const url = `${getApiBaseUrl()}/bloqueados/${encodeURIComponent(usuarioId)}`;
        const method = esBloqueado ? 'DELETE' : 'POST';

        const response = await fetch(url, {
            method,
            headers: getApiHeaders()
        });

        const data = await response.json();
        const ok = data.success || data.status === 'success';

        if (!ok) {
            throw new Error(data.message || 'Error al procesar la solicitud.');
        }

        if (esBloqueado) {
            showToast('Usuario desbloqueado correctamente.', 'success');
            const btn = document.querySelector(`[data-usuario-id="${usuarioId}"]`);
            if (btn) {
                btn.classList.remove('bloqueado');
                btn.innerHTML = '<i class="ri-forbid-line"></i> Bloquear';
            }
        } else {
            showToast('Usuario bloqueado correctamente.', 'success');
            setTimeout(() => {
                window.location.href = `${window.BASE_URL || ''}index.php`;
            }, 1500);
        }
    } catch (error) {
        console.error('Error:', error);
        showToast(error.message || 'Error de conexión.', 'error');
    }
}
window.toggleBloqueo = toggleBloqueo;

/* =========================================================
   ENVIAR IMAGEN EN CHAT
========================================================= */

async function sendChatImage(chatId, file, mensaje = '') {
    if (!file) return null;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showToast('Solo se permiten imágenes JPG, PNG, GIF o WebP', 'error');
        return null;
    }

    if (file.size > 5 * 1024 * 1024) {
        showToast('La imagen no puede superar los 5 MB', 'error');
        return null;
    }

    try {
        const formData = new FormData();
        formData.append('chat_id', chatId);
        formData.append('imagen', file);
        formData.append('mensaje', mensaje);

        const sendUrl =
            shouldUseLaravelApi() && typeof window.getLaravelSendMessageUrl === 'function'
                ? window.getLaravelSendMessageUrl(chatId)
                : getApiUrl('api/send_chat_image.php');

        const response = await fetch(sendUrl, {
            method: 'POST',
            headers: getApiHeaders(),
            body: formData
        });

        const data = await response.json();

        if (data.success || data.status === 'success') {
            if (window.currentModalChatId === Number(chatId)) {
                loadModalMessages(chatId);
            } else {
                loadNewMessages(chatId);
            }
            return data;
        }

        showToast(data.error || data.message || 'Error al enviar imagen', 'error');
        return null;
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
        return null;
    }
}
window.sendChatImage = sendChatImage;

/* =========================================================
   FINALIZAR / TERMINAR COMPRAVENTA
========================================================= */

async function finalizarVenta(chatId, precio = 0, cantidad = 1) {
    if (!confirm('¿Confirmas que esta transacción se ha completado?')) {
        return;
    }

    try {
        let url;
        let method;

        if (shouldUseLaravelApi() && typeof window.getLaravelIniciarCompraventaUrl === 'function') {
            url = window.getLaravelIniciarCompraventaUrl(chatId);
            method = 'PATCH';
        } else {
            url = getApiUrl('api/finalizar_venta.php');
            method = 'POST';
        }

        const response = await fetch(url, {
            method,
            headers: getApiHeaders({
                'Content-Type': 'application/json'
            }),
            body: JSON.stringify({
                cantidad: cantidad || 1,
                precio: precio || 0
            })
        });

        const data = await response.json();
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
window.finalizarVenta = finalizarVenta;

async function terminarCompraventa(chatId, confirmacion, comentario, calificacion) {
    try {
        if (!(shouldUseLaravelApi() && typeof window.getLaravelTerminarCompraventaUrl === 'function')) {
            return;
        }

        const body = {
            confirmacion: !!confirmacion
        };

        if (confirmacion && (comentario || calificacion != null)) {
            if (comentario) body.comentario = comentario;
            if (calificacion != null) body.calificacion = parseInt(calificacion, 10) || 0;
        }

        const response = await fetch(window.getLaravelTerminarCompraventaUrl(chatId), {
            method: 'PATCH',
            headers: getApiHeaders({
                'Content-Type': 'application/json'
            }),
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
window.terminarCompraventa = terminarCompraventa;

/* =========================================================
   ELIMINAR CHAT
========================================================= */

async function eliminarChat(chatId) {
    if (!confirm('¿Eliminar esta conversación? El otro usuario aún podrá ver los mensajes.')) {
        return;
    }

    try {
        let eliminarUrl;
        let method;
        let body;

        if (shouldUseLaravelApi() && typeof window.getLaravelDeleteChatUrl === 'function') {
            eliminarUrl = window.getLaravelDeleteChatUrl(chatId);
            method = 'DELETE';
        } else {
            eliminarUrl = getApiUrl('api/eliminar_chat.php');
            method = 'POST';
            body = new FormData();
            body.append('chat_id', chatId);
        }

        const response = await fetch(eliminarUrl, {
            method,
            headers: getApiHeaders(),
            body
        });

        const data = await response.json();
        const ok = data.success || data.status === 'success';

        if (ok) {
            showToast(data.message || 'Chat eliminado', 'success');
            const card = document.querySelector(`button[onclick*="eliminarChat(${chatId})"]`)?.closest('.chat-item');
            if (card) card.remove();

            setTimeout(() => {
                window.location.href = `${window.BASE_URL || ''}chat/mis_chats.php`;
            }, 800);
        } else {
            showToast(data.message || data.error || 'Error al eliminar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    }
}
window.eliminarChat = eliminarChat;

/* =========================================================
   REPORTE DE PRODUCTOS
========================================================= */

function abrirModalReporte(productoId, vendedorId) {
    const modal = document.getElementById('modalReporte');
    if (!modal) return;

    const productoInput = document.getElementById('reporteProductoId');
    const usuarioInput  = document.getElementById('reporteUsuarioId');
    const comentarioInput = document.getElementById('comentarioReporte');

    if (productoInput)   productoInput.value  = productoId || '';
    if (usuarioInput)    usuarioInput.value   = vendedorId || '';
    if (comentarioInput) comentarioInput.value = '';

    document.querySelectorAll('input[name="motivo_reporte"]').forEach(r => {
        r.checked = false;
    });

    // Cargar motivos de tipo denuncia dinámicamente
    const contenedor = document.querySelector('.reporte-opciones');
    if (contenedor && shouldUseLaravelApi()) {
        contenedor.innerHTML = '<p style="color:var(--color-text-light);text-align:center;">Cargando motivos...</p>';

        fetch(`${getApiBaseUrl()}/motivos?tipo=denuncia`, {
            headers: getApiHeaders()
        })
        .then(r => r.json())
        .then(data => {
            const motivos = data.data ?? data ?? [];
            if (!Array.isArray(motivos) || motivos.length === 0) {
                contenedor.innerHTML = '<p style="color:var(--color-danger)">No se pudieron cargar los motivos.</p>';
                return;
            }

            contenedor.innerHTML = motivos.map(m => `
                <label class="reporte-opcion">
                    <input type="radio" name="motivo_reporte" value="${m.id}">
                    <span class="opcion-content">
                        <strong>${escapeHtml(m.nombre)}</strong>
                    </span>
                </label>
            `).join('');
        })
        .catch(() => {
            contenedor.innerHTML = '<p style="color:var(--color-danger)">Error al cargar los motivos.</p>';
        });
    }

    modal.style.display = 'flex';
}
window.abrirModalReporte = abrirModalReporte;

function cerrarModalReporte() {
    const modal = document.getElementById('modalReporte');
    if (modal) modal.style.display = 'none';
}
window.cerrarModalReporte = cerrarModalReporte;
async function enviarReporte() {
    const productoId = document.getElementById('reporteProductoId')?.value || '';
    const vendedorId = document.getElementById('reporteUsuarioId')?.value || '';
    const motivoInput = document.querySelector('input[name="motivo_reporte"]:checked');

    if (!motivoInput) {
        showToast('Selecciona un motivo para el reporte', 'error');
        return;
    }

    const motivoId = parseInt(motivoInput.value, 10);

    try {
        const body = {
            motivo_id:  motivoId,
            usuario_id: parseInt(vendedorId, 10)
        };

        // Agregar producto_id solo si existe
        if (productoId) {
            body.producto_id = parseInt(productoId, 10);
        }

        const response = await fetch(`${getApiBaseUrl()}/denuncias`, {
            method: 'POST',
            headers: getApiHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(body)
        });

        const data = await response.json();
        const ok = data.success || data.status === 'success';

        if (ok) {
            showToast(data.message || 'Reporte enviado correctamente', 'success');
            cerrarModalReporte();
        } else {
            showToast(data.message || data.error || 'Error al enviar el reporte', 'error');
        }
    } catch (error) {
        console.error('Error enviando reporte:', error);
        showToast('Error de conexión', 'error');
    }
}
window.enviarReporte = enviarReporte;

/* =========================================================
   CONFIGURACIÓN DE SETTINGS
========================================================= */

function initSettingsNavigation() {
    const settingsLinks = document.querySelectorAll('.settings-sidebar a');
    settingsLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            if (!this.hasAttribute('data-section')) return;

            e.preventDefault();
            const target = this.getAttribute('data-section');
            if (!target) return;

            document.querySelectorAll('.settings-section').forEach(section => {
                section.classList.remove('active');
            });

            settingsLinks.forEach(l => l.classList.remove('active'));

            const targetElement = document.getElementById(target);
            if (targetElement) {
                targetElement.classList.add('active');
                this.classList.add('active');
            }
        });
    });
}

/* =========================================================
   INICIALIZACIÓN GLOBAL
========================================================= */

document.addEventListener('DOMContentLoaded', function () {
    initTheme();
    initHamburgerMenu();
    initNotifications();
    initMultipleImagesUpload();
    initProductGallery();
    initImageZoom();
    initGenericForms();
    initSettingsNavigation();
    initAvatarLogic();

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    const notificationIcon = document.getElementById('notificationIcon');
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function (e) {
            e.stopPropagation();

            const chatsList = document.getElementById('chatsList');
            if (chatsList) {
                chatsList.classList.toggle('active');
                if (chatsList.classList.contains('active')) {
                    loadNotifications(true);
                }
            }
        });

        document.addEventListener('click', function (e) {
            const chatsList = document.getElementById('chatsList');
            if (chatsList && !chatsList.contains(e.target) && !notificationIcon.contains(e.target)) {
                chatsList.classList.remove('active');
            }
        });
    }

    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().catch(() => {});
    }

    const chatId = window.chatId || getUrlParameter('id');
    if (chatId && document.getElementById('chatMessages')) {
        lastMessageId = Number(window.lastMessageId || 0);

        if (lastMessageId === 0) {
            const messages = document.querySelectorAll('.message[id^="message-"]');
            if (messages.length > 0) {
                const lastMessage = messages[messages.length - 1];
                const lastId = String(lastMessage.id || '').replace('message-', '');
                lastMessageId = parseInt(lastId, 10) || 0;
            }
        }

        initChatRealTime(chatId);
        scrollChatToBottom();
    }

    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const textarea = document.getElementById('messageInput');
            const currentChatId = window.chatId || getUrlParameter('id');

            if (textarea && currentChatId && textarea.value.trim()) {
                sendMessage(currentChatId, textarea.value);
            }
        });

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

    if (document.getElementById('productsGrid')) {
        initInfiniteScroll();
    }
});

document.addEventListener('click', function (e) {
    const modal = document.getElementById('modalReporte');
    if (modal && e.target === modal) {
        cerrarModalReporte();
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModalReporte();
        if (currentChatModal) {
            closeChatModal();
        }
    }
});
/* =========================================================
   MODAL DE DENUNCIA - COMPARTIDO
========================================================= */

let motivosDenunciaCache = [];

async function cargarMotivosDenuncia() {
    const contenedor = document.getElementById('motivosDenuncia')
                    || document.querySelector('.reporte-opciones');
    if (!contenedor) return;

    if (motivosDenunciaCache.length > 0) {
        renderizarMotivosDenuncia(motivosDenunciaCache);
        return;
    }

    contenedor.innerHTML = '<p style="color:var(--color-text-light);text-align:center;">Cargando motivos...</p>';

    try {
        const response = await fetch(`${getApiBaseUrl()}/motivos?tipo=denuncia`, {
            headers: getApiHeaders()
        });
        const result = await response.json();
        const motivos = Array.isArray(result) ? result : (Array.isArray(result.data) ? result.data : []);

        if (!motivos.length) throw new Error('No hay motivos');

        motivosDenunciaCache = motivos;
        renderizarMotivosDenuncia(motivos);
    } catch (error) {
        const contenedor2 = document.getElementById('motivosDenuncia') || document.querySelector('.reporte-opciones');
        if (contenedor2) contenedor2.innerHTML = '<p style="color:red;padding:1rem;">Error al cargar los motivos.</p>';
    }
}
window.cargarMotivosDenuncia = cargarMotivosDenuncia;

function renderizarMotivosDenuncia(motivos) {
    const contenedor = document.getElementById('motivosDenuncia')
                    || document.querySelector('.reporte-opciones');
    if (!contenedor) return;

    const iconoMap = (nombre) => {
        const t = String(nombre).toLowerCase();
        if (t.includes('ilegal') || t.includes('prohibido')) return 'ri-spam-line';
        if (t.includes('precio'))    return 'ri-money-dollar-circle-line';
        if (t.includes('descrip'))   return 'ri-file-warning-line';
        if (t.includes('imagen') || t.includes('foto')) return 'ri-image-line';
        if (t.includes('estafa') || t.includes('fraude')) return 'ri-error-warning-line';
        if (t.includes('acoso'))     return 'ri-user-unfollow-line';
        if (t.includes('bulling') || t.includes('bully')) return 'ri-emotion-unhappy-line';
        if (t.includes('troll'))     return 'ri-ghost-line';
        if (t.includes('spam'))      return 'ri-spam-2-line';
        if (t.includes('sexual'))    return 'ri-alert-line';
        if (t.includes('fake'))      return 'ri-file-warning-line';
        if (t.includes('violencia')) return 'ri-error-warning-line';
        return 'ri-flag-line';
    };

    contenedor.innerHTML = motivos.map(m => `
        <label class="reporte-opcion">
            <input type="radio" name="motivo_reporte" value="${m.id}">
            <span class="opcion-content">
                <i class="${iconoMap(m.nombre)}"></i>
                <strong>${escapeHtml(m.nombre ?? '')}</strong>
            </span>
        </label>
    `).join('');
}
window.renderizarMotivosDenuncia = renderizarMotivosDenuncia;

function abrirModalDenunciaUsuario(usuarioId) {
    const modal = document.getElementById('modalReporte');
    if (!modal) return;

    const productoInput   = document.getElementById('reporteProductoId');
    const usuarioInput    = document.getElementById('reporteUsuarioId');
    const comentarioInput = document.getElementById('comentarioReporte');

    if (productoInput)   productoInput.value  = '';
    if (usuarioInput)    usuarioInput.value   = usuarioId;
    if (comentarioInput) comentarioInput.value = '';

    document.querySelectorAll('input[name="motivo_reporte"]').forEach(r => r.checked = false);

    modal.style.display = 'flex';
    cargarMotivosDenuncia();
}
window.abrirModalDenunciaUsuario = abrirModalDenunciaUsuario;