<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
forceLightTheme();

$error = '';
$success = '';

// Si ya tiene sesión, redirigir
if (isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Registro solo vía API (Hostinger). Sin SQL.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
    <style>
        .avatar-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #ffffff; /* Fondo blanco */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px dashed var(--color-primary); /* Borde discontinuo verde */
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        .avatar-preview .avatar-icon {
            font-size: 48px;
            color: var(--color-primary); /* Icono verde */
        }
        .avatar-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-preview .overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.5);
            padding: 8px;
            text-align: center;
            color: white;
            font-size: 11px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .avatar-preview:hover .overlay {
            opacity: 1;
        }
        .avatar-input {
            display: none !important;
            visibility: hidden;
            position: absolute;
            left: -9999px;
        }
        /* Ocultar cualquier imagen fuera del preview */
        .avatar-upload > img,
        .avatar-upload input[type="file"] + img {
            display: none !important;
        }
        .avatar-label {
            margin-top: 10px;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <!-- Header superior -->
    <header class="header">
        <div class="header-content" style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: flex-start; gap: 20px; padding: 0 20px;">
            <img src="<?= getBaseUrl() ?>logo_new.png" alt="SENA" style="height: 70px; width: auto;">
            <span style="font-size: 1.5rem; font-weight: 800; color: white;">Tu Mercado SENA</span>
        </div>
    </header>

    <div class="auth-container" style="margin-top: 20px;">
        <div class="auth-box" style="width: 500px; margin: 40px 0;">
            <h1 class="auth-title">Registro</h1>
            <div id="registerLaravelWrap">
                <div id="registerStep1">
                    <p class="auth-link" style="margin-bottom: 15px;">Correo @soy.sena.edu.co. Recibirás un código por correo.</p>
                    <div id="regLaravelError" class="error-message" style="display:none;"></div>
                    <form id="regLaravelForm1">
                        <div class="form-group">
                            <label for="r1_nombre">Nombre de Usuario *</label>
                            <input type="text" id="r1_nombre" maxlength="24" required>
                        </div>
                        <div class="form-group">
                            <label for="r1_email">Correo (@soy.sena.edu.co) *</label>
                            <input type="email" id="r1_email" placeholder="usuario@soy.sena.edu.co" required>
                        </div>
                        <div class="form-group">
                            <label for="r1_password">Contraseña *</label>
                            <input type="password" id="r1_password" required minlength="8">
                        </div>
                        <div class="form-group">
                            <label for="r1_password_confirm">Confirmar Contraseña *</label>
                            <input type="password" id="r1_password_confirm" required minlength="8">
                        </div>
                        <div class="form-group">
                            <label for="r1_descripcion">Descripción (opcional)</label>
                            <textarea id="r1_descripcion" maxlength="300" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="r1_link">Link red social (opcional)</label>
                            <input type="url" id="r1_link" placeholder="https://instagram.com/...">
                        </div>
                        <!-- Checkbox de términos -->
                        <div class="form-group" style="margin-bottom: 25px;">
                            <div style="display: flex; align-items: flex-start; gap: 8px;">
                                <input type="checkbox" id="terms" name="terms" required style="width: 16px; height: 16px; min-width: 16px; margin-top: 3px; cursor: pointer;">
                                <label for="terms" style="font-size: 0.85rem; color: #666; cursor: pointer; line-height: 1.3;">
                                    Acepto los <a href="#" id="openModal" style="color: var(--color-primary); font-weight: bold; text-decoration: underline;">Términos y Condiciones</a> y la Política de Privacidad.
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">Enviar código al correo</button>
                    </form>
                </div>
                <div id="registerStep2" style="display:none;">
                    <p class="success-message">Revisa tu correo e ingresa el código de 6 caracteres.</p>
                    <div id="regLaravelError2" class="error-message" style="display:none;"></div>
                    <form id="regLaravelForm2">
                        <div class="form-group">
                            <label for="r2_clave">Código de verificación *</label>
                            <input type="text" id="r2_clave" maxlength="6" pattern="[A-Za-z0-9]{6}" placeholder="XXXXXX" required>
                        </div>
                        <button type="submit" class="btn-primary">Completar registro</button>
                    </form>
                </div>
            </div>
            <p class="auth-link">¿Ya tienes cuenta? <a href="login.php" style="color: var(--color-primary);">Inicia sesión aquí</a></p>
            <p class="auth-link"><small>Debes tener un correo @sena.edu.co para registrarte</small></p>
        </div>
    </div>

    <!-- Barra inferior -->
    <footer style="background-color: var(--color-primary); color: white; text-align: center; padding: 15px; font-size: 0.9rem; font-weight: 500;">
        © 2025 Tu Mercado SENA. Todos los derechos reservados.
    </footer>
</body>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview de imagen de perfil (solo cuando existe el formulario PHP con campo imagen)
            const imagenInput = document.getElementById('imagen');
            const avatarPreview = document.getElementById('avatarPreview');
            if (imagenInput && avatarPreview) {
            imagenInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validar tamaño (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('La imagen es muy grande. Máximo 5MB.');
                        this.value = '';
                        return;
                    }
                    
                    // Validar tipo
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        alert('Formato no válido. Use JPG, PNG, GIF o WEBP.');
                        this.value = '';
                        return;
                    }
                    
                    // Mostrar preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <div class="overlay">Cambiar foto</div>
                        `;
                    };
                    reader.readAsDataURL(file);
                }
            });
            }
            // Validación del dominio en tiempo real (solo formulario PHP)
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value.trim().toLowerCase();
                    if (email && !(email.endsWith('@soy.sena.edu.co') || email.endsWith('@gmail.com'))) {
                        this.setCustomValidity('El correo debe ser del dominio @soy.sena.edu.co');
                        this.style.borderColor = '#e74c3c';
                    } else {
                        this.setCustomValidity('');
                        this.style.borderColor = '#ddd';
                    }
                });
                
                emailInput.addEventListener('input', function() {
                    if (this.style.borderColor === 'rgb(231, 76, 60)') {
                        const email = this.value.trim().toLowerCase();
                        if (email.endsWith('@soy.sena.edu.co') || email.endsWith('@gmail.com')) {
                            this.setCustomValidity('');
                            this.style.borderColor = '#ddd';
                        }
                    }
                });
            }
        });
    </script>
    <script>
        window.BASE_URL = <?= json_encode(getBaseUrl()) ?>;
        var regCuentaId = null, regDatosEncriptados = null;
        var apiBase = typeof API_CONFIG !== 'undefined' ? API_CONFIG.LARAVEL_URL : <?= json_encode(defined('LARAVEL_API_URL') ? LARAVEL_API_URL : 'https://tumercadosena.shop/api/') ?>;
        var setSessionUrl = (window.BASE_URL || '') + 'auth/set_session.php';

        document.getElementById('regLaravelForm1').addEventListener('submit', async function(e) {
            e.preventDefault();
            var err = document.getElementById('regLaravelError');
            var nombre = document.getElementById('r1_nombre').value.trim();
            var email = document.getElementById('r1_email').value.trim().toLowerCase();
            var password = document.getElementById('r1_password').value;
            var password_confirm = document.getElementById('r1_password_confirm').value;
            var descripcion = document.getElementById('r1_descripcion').value.trim();
            var link = document.getElementById('r1_link').value.trim();
            if (password !== password_confirm) { err.style.display='block'; err.textContent = 'Las contraseñas no coinciden'; return; }
            if (!(email.endsWith('@soy.sena.edu.co') || email.endsWith('@gmail.com'))) { err.style.display='block'; err.textContent = 'Solo correos @soy.sena.edu.co'; return; }
            err.style.display = 'none';
            try {
                var r = await fetch(apiBase + 'auth/iniciar-registro', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: email, password: password, password_confirmation: password_confirm,
                        rol_id: 1, estado_id: 1, nickname: nombre, descripcion: descripcion || '',
                        link: (link && link.trim()) ? link.trim() : undefined, device_name: 'web'
                    })
                });
                var data = await r.json();
                var payload = data.data || data;
                if (payload.cuenta_id && payload.datosEncriptados) {
                    regCuentaId = payload.cuenta_id;
                    regDatosEncriptados = payload.datosEncriptados;
                    document.getElementById('registerStep1').style.display = 'none';
                    document.getElementById('registerStep2').style.display = 'block';
                } else {
                    err.style.display = 'block';
                    var detalle = data.errors && Object.values(data.errors).flat().join('. ');
                    err.textContent = detalle || data.message || 'Error al enviar el código';
                }
            } catch (x) {
                err.style.display = 'block';
                err.textContent = 'Error de conexión. Comprueba que la API Laravel esté en marcha.';
            }
        });

        document.getElementById('regLaravelForm2').addEventListener('submit', async function(e) {
            e.preventDefault();
            var err = document.getElementById('regLaravelError2');
            var clave = document.getElementById('r2_clave').value.trim();
            if (!regCuentaId || !regDatosEncriptados) { err.style.display='block'; err.textContent = 'Sesión de registro expirada. Vuelve a empezar.'; return; }
            if (clave.length !== 6) { err.style.display='block'; err.textContent = 'El código debe tener 6 caracteres'; return; }
            err.style.display = 'none';
            try {
                var r = await fetch(apiBase + 'auth/register', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        cuenta_id: regCuentaId, clave: clave, datosEncriptados: regDatosEncriptados, device_name: 'web'
                    })
                });
                var data = await r.json();
                var payload = data.data || data;
                var user = payload.user;
                var token = payload.token;
                if (user && token) {
                    if (typeof localStorage !== 'undefined') localStorage.setItem('api_token', token);
                    window.location.href = (window.BASE_URL || '') + 'auth/login.php?registered=1';
                } else {
                    err.style.display = 'block';
                    err.textContent = data.message || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Error al completar el registro';
                }
            } catch (x) {
                err.style.display = 'block';
                err.textContent = 'Error de conexión.';
            }
        });
    </script>
</body>
</html>

