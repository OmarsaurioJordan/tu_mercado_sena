<?php
session_start();


require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';
forceLightTheme();

$error = '';

if (isLoggedIn()) {
    header("Location: /index.php");
    exit();
}

// Login solo vía API (tumercadosena.shop); sin SQL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $result = apiLogin($email, $password);
        $data = $result['data'] ?? [];
        $user = $data['data']['user'] ?? $data['user'] ?? [];
        $token = $data['data']['token'] ?? $data['token'] ?? '';

        if ($result['success'] && !empty($token) && !empty($user['id'])) {
            $uid = (int)$user['id'];
            $estado_id = (int)($user['estado_id'] ?? 1);
            if ($estado_id == 4) {
                $error = 'Tu cuenta ha sido bloqueada. Contacta al administrador.';
            } elseif ($estado_id == 3) {
                $error = 'Esta cuenta ya no existe.';
            } else {
                $_SESSION['usuario_id'] = $uid;
                $_SESSION['usuario_nombre'] = $user['nickname'] ?? $user['name'] ?? '';
                $_SESSION['usuario_imagen'] = $user['imagen'] ?? $user['avatar'] ?? '';
                $_SESSION['usuario_rol'] = (int)($user['rol_id'] ?? 1);
                $_SESSION['cuenta_id'] = (int)($user['cuenta_id'] ?? 0);
                $_SESSION['api_token'] = $token;
                $_SESSION['nickname'] = $_SESSION['usuario_nombre'];
                $_SESSION['imagen'] = $_SESSION['usuario_imagen'];
                $_SESSION['descripcion'] = $user['descripcion'] ?? '';
                $_SESSION['link'] = $user['link'] ?? '';
                $_SESSION['estado_id'] = $estado_id;
                $_SESSION['email'] = $user['email'] ?? $email;
                $_SESSION['notifica_correo'] = (int)($user['notifica_correo'] ?? 0);
                $_SESSION['notifica_push'] = (int)($user['notifica_push'] ?? 0);
                $_SESSION['uso_datos'] = (int)($user['uso_datos'] ?? 0);
                header("Location: /index.php");
                exit();
            }
        } else {
            $error = $result['message'] ?? 'Correo o contraseña incorrectos.';
            if (!empty($result['errors']) && is_array($result['errors'])) {
                $error = implode(' ', array_map(function ($e) { return is_array($e) ? implode(' ', $e) : $e; }, $result['errors']));
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Tu Mercado SENA</title>
    <link rel="stylesheet" href="../styles.css?v=<?= time(); ?>">
</head>
<script>
    const savedTheme = localStorage.getItem("theme") || "light";
    document.documentElement.setAttribute("data-theme", savedTheme);
</script>

<body>
    <!-- Header superior -->
    <header class="header">
        <div class="header-content" style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: flex-start; gap: 20px; padding: 0 20px;">
            <img src="../logo_new.png" alt="SENA" style="height: 70px; width: auto;">
            <span style="font-size: 1.5rem; font-weight: 800; color: white;">Tu Mercado SENA</span>
        </div>
    </header>

    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">
                Iniciar Sesión
            </h1>
            <?php if (isset($_GET['session_expired'])): ?>
                <div class="error-message">Tu sesión ha expirado. Por favor inicia sesión nuevamente.</div>
            <?php endif; ?>
            <?php if (isset($_GET['registered'])): ?>
                <div class="success-message">¡Registro completado! Ahora puedes iniciar sesión.</div>
            <?php endif; ?>
            <?php if (isset($_GET['password_changed'])): ?>
                <div class="success-message">Contraseña cambiada correctamente. Ya puedes iniciar sesión.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form id="loginForm" method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" style="margin-bottom: 5px;">
                </div>

                <button type="submit" class="btn-primary" id="btnLogin">Iniciar Sesión</button>
                <p class="auth-link"><a href="../auth/forgot_password.php">¿Olvidaste tu contraseña?</a></p>
            </form>
            <p class="auth-link">¿No tienes cuenta? <a href="../auth/register.php">Regístrate aquí</a></p>
            <p class="auth-link"><small>Debes tener un correo @soy.sena.edu.co para registrarte</small></p>
        </div>
    </div>
    
    <!-- Modal de Políticas (Oculto por defecto) -->
    <div id="policiesModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Políticas y Privacidad</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <h3>Código de Comportamiento</h3>
                <p>Tu Mercado SENA es una comunidad exclusiva. Se espera respeto, honestidad y responsabilidad.</p>
                
                <h3>Conductas Prohibidas</h3>
                <ul>
                    <li>Acoso, discriminación o bullying</li>
                    <li>Contenido ofensivo o violento</li>
                    <li>Suplantación de identidad o fraude</li>
                </ul>

                <h3>Política de Privacidad</h3>
                <p>Recopilamos datos básicos (correo, nombre) y actividad en la plataforma. Tus contraseñas están encriptadas y no compartimos datos sin consentimiento.</p>

                <h3>Productos Prohibidos</h3>
                <ul>
                    <li>Alcohol, tabaco y sustancias controladas</li>
                    <li>Armas o elementos peligrosos</li>
                    <li>Material pirateado o robado</li>
                </ul>

                <h3>Sanciones</h3>
                <p>El incumplimiento puede llevar a advertencias, suspensiones temporales o permanentes de la cuenta.</p>
            </div>
            <div class="modal-footer">
                <button class="btn-primary close-modal-btn">Entendido</button>
            </div>
        </div>
    </div>

    <style>
        /* Estilos del Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--color-white);
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--color-primary);
        }

        .close-modal {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            line-height: 1;
        }

        .close-modal:hover {
            color: var(--color-text);
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--color-text);
        }

        .modal-body h3 {
            color: var(--color-secondary);
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .modal-body ul {
            padding-left: 20px;
            margin-bottom: 10px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            text-align: right;
        }

        .close-modal-btn {
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>

    <script>
        // Lógica del Modal
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('policiesModal');
            const openBtn = document.getElementById('openModal');
            const closeBtn = document.querySelector('.close-modal');
            const closeBtnFooter = document.querySelector('.close-modal-btn');

            function openModal(e) {
                e.preventDefault();
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Evitar scroll de fondo
            }

            function closeModal() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto'; // Restaurar scroll
            }

            if(openBtn) openBtn.addEventListener('click', openModal);
            if(closeBtn) closeBtn.addEventListener('click', closeModal);
            if(closeBtnFooter) closeBtnFooter.addEventListener('click', closeModal);

            // Cerrar al hacer clic fuera
            window.addEventListener('click', function(e) {
                if (e.target == modal) {
                    closeModal();
                }
            });
        });
    </script>
<?php if (isUsingLaravelApi()): ?>
    <script>
        window.USE_LARAVEL_API = true;
        window.LARAVEL_API_URL = <?= json_encode(defined('LARAVEL_API_URL') ? LARAVEL_API_URL : '') ?>;
    </script>
    <script src="/js/api-config.js"></script>
    <script>
        window.BASE_URL = <?= json_encode(getAbsoluteBaseUrl()) ?>;
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            var btn = document.getElementById('btnLogin');
            var email = document.getElementById('email').value.trim();
            var password = document.getElementById('password').value;
            var errEl = document.querySelector('.error-message');
            if (!email || !password) {
                if (errEl) errEl.textContent = 'Completa correo y contraseña.';
                return;
            }
            console.log("Boton de login presionado. Iniciando proceso de autenticación...");
            btn.disabled = true;
            if (errEl) errEl.remove();
            try {
                var base = (typeof API_CONFIG !== 'undefined' && API_CONFIG.LARAVEL_URL) ? API_CONFIG.LARAVEL_URL : (window.API_BASE_URL || window.LARAVEL_API_URL || '<?= rtrim(LARAVEL_API_URL, "/") ?>/');
                var r = await fetch(base + 'auth/login', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, password: password, device_name: 'web' })
                });
                var data = await r.json();
                var user = (data.data && data.data.user) ? data.data.user : data.user;
                var token = (data.data && data.data.token) ? data.data.token : data.token;
                if (!user || !token) {
                    var msg = (data.message || data.error || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Correo o contraseña incorrectos.');
                    var box = document.querySelector('.auth-box');
                    var div = document.createElement('div');
                    div.className = 'error-message';
                    div.textContent = msg;
                    box.insertBefore(div, document.getElementById('loginForm'));
                    btn.disabled = false;
                    return;
                }
                localStorage.setItem('api_token', token);
                var setSessionUrl = (window.BASE_URL || '') + 'auth/set_session.php';
                await fetch(setSessionUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        usuario_id:     user.id,
                        nickname:       user.nickname       || '',
                        imagen:         user.imagen         || '',
                        rol_id:         user.rol_id         || 1,
                        cuenta_id:      user.cuenta_id      || 0,
                        api_token:      token,
                        // 👇 campos que faltaban
                        descripcion:    user.descripcion    || '',
                        link:           user.link           || '',
                        estado_id:      user.estado_id      || 1,
                        email:          user.email          || email,
                        notifica_correo: user.notifica_correo || 0,
                        notifica_push:   user.notifica_push   || 0,
                        uso_datos:       user.uso_datos       || 0,
                    })
                });
                window.location.href = '/index.php';
            } catch (err) {
                var box = document.querySelector('.auth-box');
                var div = document.createElement('div');
                div.className = 'error-message';
                div.textContent = 'Error de conexión. Comprueba que la API Laravel esté en marcha.';
                box.insertBefore(div, document.getElementById('loginForm'));
                btn.disabled = false;
            }
        });
    </script>
<?php endif; ?>

    <!-- Barra inferior -->
    <footer style="background-color: var(--color-primary); color: white; text-align: center; padding: 15px; font-size: 0.9rem; font-weight: 500;">
        © 2025 Tu Mercado SENA. Todos los derechos reservados.
    </footer>
</body>

</html>
