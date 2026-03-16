<?php
require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user = getCurrentUser();
$usuario_id = $user['id'] ?? 0;
if ($usuario_id <= 0) {
    header('Location: ../auth/login.php');
    exit;
}

if (isset($_GET['desbloquear'])) {
    $desbloquear_id = (int)$_GET['desbloquear'];
    apiDesbloquearUsuario($desbloquear_id);
    header("Location: perfil.php?section=privacidad&status=unblock_success");
    exit;
}

$r_bloq = apiGetBloqueados();
$lista_bloqueados = [];
if ($r_bloq['success'] && isset($r_bloq['data'])) {
    $list = $r_bloq['data']['data'] ?? $r_bloq['data']['bloqueados'] ?? (isset($r_bloq['data'][0]) ? $r_bloq['data'] : []);
    foreach (is_array($list) ? $list : [] as $b) {
        $u = $b['usuario'] ?? $b['bloqueado'] ?? $b;
        $lista_bloqueados[] = [
            'bloqueado_id' => (int)($u['id'] ?? $b['bloqueado_id'] ?? 0),
            'nickname' => $u['nickname'] ?? '',
            'imagen' => $u['imagen'] ?? '',
        ];
    }
}

$cuenta_id = $user['cuenta_id'] ?? 0;
$error          = '';
$success        = '';
$active_section = $_GET['section'] ?? 'perfil';

// 👇 AGREGA ESTO
$statusMap = [
    'ok'              => 'Configuración guardada correctamente.',
    'password_ok'     => 'Contraseña cambiada correctamente.',
    'avatar_success'  => 'Foto de perfil actualizada.',
    'unblock_success' => 'Usuario desbloqueado correctamente.',
];
if (!empty($_GET['status'])) {
    $success = $statusMap[$_GET['status']] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? 'perfil';

    if ($section === 'perfil') {
        $nickname = sanitize($_POST['nickname'] ?? '');
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        $link = sanitize($_POST['link'] ?? '');

        if ($nickname === '') {
            $error = 'El nombre es obligatorio';
        } elseif (strlen($nickname) < 3 || strlen($nickname) > 32) {
            $error = 'El nombre debe tener entre 3 y 32 caracteres';
        } elseif (strlen($descripcion) > 512) {
            $error = 'La Descripción no puede exceder 512 caracteres';
        } elseif (!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
            $error = 'El enlace debe ser una URL válida (comenzar con http:// o https://)';
        } else {
            $res = apiEditarPerfil($usuario_id, [
                'nickname' => $nickname,
                'descripcion' => $descripcion,
                'link' => $link,
            ]);
            if ($res['success']) {
                $success = 'Perfil actualizado correctamente';
                $user = getCurrentUser();
            } else {
                $error = $res['message'] ?? 'Error al actualizar el perfil';
            }
        }
    }

    elseif ($section === 'avatar' && !empty($_FILES['avatar_file']['tmp_name'])) {
        $file = $_FILES['avatar_file'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowedExt, true) && $file['size'] <= 2097152) {
            $res = apiUpdateAvatar($usuario_id, 'avatar_file');
            if ($res['success']) {
                header("Location: perfil.php?section=perfil&status=avatar_success");
                exit;
            }
            $error = $res['message'] ?? 'Error al subir la imagen';
        } else {
            $error = 'Formato o tamaño inválido (máx. 2MB)';
        }
    }

    elseif ($section === 'seguridad') {
    $actual  = $_POST['password_actual']  ?? '';
    $nueva   = $_POST['password_nueva']   ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if ($actual === '') {
        $error = 'Ingresa tu contraseña actual.';
    } elseif ($nueva !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($nueva) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $nueva)) {
        $error = 'Debe contener al menos una mayúscula.';
    } elseif (!preg_match('/[0-9]/', $nueva)) {
        $error = 'Debe contener al menos un número.';
    } else {
    $res = apiRequest('/auth/cambiar-password', 'PATCH', [
        'password_actual'       => $actual,
        'password'              => $nueva,
        'password_confirmation' => $confirm,
    ], getToken());

    if ($res['success']) {
        header("Location: perfil.php?section=seguridad&status=password_ok");
        exit;
    }

    if (!empty($res['errors']) && is_array($res['errors'])) {
        $msgs = [];
        foreach ($res['errors'] as $errores) {
            foreach ((array)$errores as $msg) {
                $msgs[] = htmlspecialchars($msg);
            }
        }
        $error = implode('<br>', $msgs);
    } else {
        $error = $res['message'] ?? 'Error al cambiar la contraseña.';
    }
}
}

    $active_section = $section;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Tu Mercado SENA</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>styles.css?v=<?= time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <?php include '../includes/bottom_nav.php'; ?>

    <main class="main">
        <div class="container">
            <div class="settings-container">
                <div class="settings-sidebar">
                    <ul>
                        <li><a href="#" data-section="perfil" class="<?php echo $active_section === 'perfil' ? 'active' : ''; ?>">Información Personal</a></li>
                        <li><a href="#" data-section="configuracion" class="<?php echo $active_section === 'configuracion' ? 'active' : ''; ?>">Configuración</a></li>
                        <li><a href="#" data-section="seguridad" class="<?php echo $active_section === 'seguridad' ? 'active' : ''; ?>">Seguridad</a></li>
                        <li><a href="#" data-section="ayuda" class="<?php echo $active_section === 'ayuda' ? 'active' : ''; ?>">Ayuda</a></li>
                        <li><a href="../perfil/historial.php">Historial de Transacciones</a></li>
                        
                        <li>
                            <a href="../auth/logout.php" onclick="return confirmarLogout();" style="color: var(--color-danger);">Cerrar sesión</a>
                            <script>
                                function confirmarLogout() {
                                    return confirm("¿Estás seguro de que deseas cerrar sesión?");
                                }
                            </script>
                        </li>
                    </ul>
                </div>
                
                <div class="settings-content">
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success-message"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <div id="perfil" class="settings-section <?php echo $active_section === 'perfil' ? 'active' : ''; ?>">
    <h2>Información Personal</h2>

    <!-- ===========================
         FORMULARIO DE AVATAR
    ============================ -->
    <div class="profile-avatar-wrapper">
<img id="avatarPhoto" 
     src="<?php echo getAvatarUrl($user['imagen']); ?>"
     class="avatar-photo"
     alt="Avatar">


        <form method="POST" action="perfil.php" id="avatarUploadForm" enctype="multipart/form-data">
            <input type="hidden" name="section" value="avatar">
            <input type="file" id="avatarInputHidden" name="avatar_file" accept="image/*" style="display:none;">

            <button type="button" id="avatarEditButton" class="avatar-edit-btn" title="Cambiar foto de perfil">
                <img src="<?= getBaseUrl() ?>assets/icons/icono-lapiz.png" alt="Editar">
            </button>
        </form>
    </div>

    <!-- Script de avatar -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const avatarEditBtn = document.getElementById('avatarEditButton');
        const avatarInput = document.getElementById('avatarInputHidden');
        const avatarForm = document.getElementById('avatarUploadForm');
        const avatarPhoto = document.getElementById('avatarPhoto');

        avatarEditBtn.addEventListener('click', () => {
            avatarInput.click();
        });

        avatarInput.addEventListener('change', () => {
            if (avatarInput.files && avatarInput.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    avatarPhoto.src = e.target.result;
                };
                reader.readAsDataURL(avatarInput.files[0]);
                avatarForm.submit();
            }
        });
    });
    </script>

    <!-- ===========================
         FORMULARIO DE DATOS PERSONALES
    ============================ -->
    <form method="POST" action="perfil.php">
        <input type="hidden" name="section" value="perfil">

        <div class="settings-group">
            <h3>Datos Básicos</h3>

            <div class="form-group">
                <label for="nickname">Nombre de Usuario *</label>
                <input type="text" id="nickname" name="nickname"
                    value="<?php echo htmlspecialchars($user['nickname']); ?>" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled autocomplete="email">
                <small>El correo no se puede cambiar</small>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="5" maxlength="512"><?php echo htmlspecialchars($user['descripcion']); ?></textarea>
            </div>

          <div class="form-group">
            <label for="link">Enlace (Redes sociales, sitio web, etc.)</label>
            <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($user['link']); ?>" maxlength="128" placeholder="https://...">
            <small>Comparte tus redes sociales o sitio web</small>
            </div>
        </div>

        <button type="submit" class="btn-primary">Guardar Cambios</button>
    </form>

</div>
                    
<div id="configuracion" class="settings-section <?php echo $active_section === 'configuracion' ? 'active' : ''; ?>">
    <h2>Configuración del Marketplace</h2>
    <form id="form-configuracion-estetico" class="profile-form" onsubmit="return false;">
        
        <div class="settings-group">
            <h3>Apariencia</h3>
            <div class="toggle-switch">
                <label for="themeToggle">Modo oscuro</label>
                <button class="theme-toggle settings-theme-toggle" id="themeToggle" title="Cambiar tema">
                    <i class="ri-moon-line"></i>
                </button>
            </div>
            <small>Personaliza la apariencia de tu interfaz</small>
        </div>

        <div class="settings-group">
            <h3>Privacidad y Visibilidad</h3>
            <div class="toggle-switch">
                <label for="perfil_visible">Mi perfil es visible</label>
                <label class="switch">
                    <input type="checkbox" id="perfil_visible" name="perfil_visible" 
                           <?php echo ($user['visible'] ?? 1) == 1 ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <small>Si desactivas esta opción, otros usuarios no podrán ver tu perfil ni tus productos.</small>
        </div>

        <div class="settings-group">
            <h3>Notificaciones</h3>
            <div class="toggle-switch">
                <label for="notifica_correo">Notificaciones por Correo</label>
                <label class="switch">
                    <input type="checkbox" id="notifica_correo" name="notifica_correo" 
                           <?php echo ($user['notifica_correo'] ?? 0) ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <small>Recibir notificaciones importantes por correo electrónico</small>
            
            <div class="toggle-switch">
                <label for="notifica_push">Notificaciones Push</label>
                <label class="switch">
                    <input type="checkbox" id="notifica_push" name="notifica_push" 
                           <?php echo ($user['notifica_push'] ?? 0) ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <small>Recibir notificaciones emergentes en tu dispositivo</small>
        </div>
        
        <div class="settings-group">
            <h3>Ahorro de Datos</h3>
            <div class="toggle-switch">
                <label for="uso_datos">Modo Ahorro de Datos</label>
                <label class="switch">
                    <input type="checkbox" id="uso_datos" name="uso_datos" 
                           <?php echo ($user['uso_datos'] ?? 0) ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>
            <small>Reduce el consumo de datos evitando cargar imágenes automáticamente</small>
        </div>
        
        <div class="settings-group">
            <h3>Gestión de Usuarios Bloqueados</h3>
            <p style="margin-bottom: 15px;">Gestiona la lista de usuarios que has bloqueado para que no puedan contactarte.</p>
            
            <a href="../perfil/bloqueados.php" class="btn-secondary" style="display: inline-block; text-align: center; width: 100%;">
                <i class="ri-user-forbid-line" style="vertical-align: middle; margin-right: 5px;"></i>
                Gestionar Usuarios Bloqueados
            </a>
        </div>

        <button type="button" class="btn-primary" style="margin-top: 20px;">Guardar Configuración</button>
    </form>
</div>
<!-- Sección: Privacidad -->

 <!-- Sección: Seguridad -->
                    <div id="seguridad" class="settings-section <?php echo $active_section === 'seguridad' ? 'active' : ''; ?>">
                        <h2>Seguridad</h2>
                        <form method="POST" action="perfil.php" class="profile-form">
                            <input type="hidden" name="section" value="seguridad">
                            
                            <div class="settings-group">
                                <h3>Cambiar contraseña</h3>
                                
                                <div class="form-group">
                                    <label for="password_actual">contraseña Actual *</label>
                                    <input type="password" id="password_actual" name="password_actual" required autocomplete="current-password">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_nueva">Nueva contraseña *</label>
                                    <input type="password" id="password_nueva" name="password_nueva" required minlength="8" autocomplete="new-password">
                                    <small>Mínimo 8 caracteres, incluir mayúscula, minúscula y número</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_confirm">Confirmar Nueva contraseña *</label>
                                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6" autocomplete="new-password">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-primary">Cambiar contraseña</button>
                        </form>
                    </div>

                    <!-- Sección: Ayuda -->
                    <div id="ayuda" class="settings-section <?php echo $active_section === 'ayuda' ? 'active' : ''; ?>">
                        <h2>Ayuda</h2>
                        <p style="margin-bottom: 1.5rem; color: var(--color-text-light);">Preguntas, quejas, reclamos o sugerencias. Envía una PQRS o consulta la información de contacto.</p>
                        <div class="settings-group">
                            <h3>PQRS</h3>
                            <p style="margin-bottom: 1rem;">Peticiones, quejas, reclamos y sugerencias. Te responderemos a la brevedad.</p>
                            <a href="../soporte/pqrs.php" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-file-text-line" aria-hidden="true"></i>
                                Enviar PQRS
                            </a>
                        </div>
                        <div class="settings-group" style="margin-top: 1.5rem;">
                            <h3>Contacto</h3>
                            <p style="margin-bottom: 1rem;">Canales de contacto y horarios de atención.</p>
                            <a href="../soporte/contacto.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="ri-mail-line" aria-hidden="true"></i>
                                Ver información de contacto
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Tu Mercado SENA. Todos los derechos reservados.</p>
        </div>
    </footer>
    <script>
        window.BASE_URL = '<?= getBaseUrl() ?>';
    </script>
    <?php include __DIR__ . '/../includes/api_config_boot.php'; ?>
    <script src="<?= getBaseUrl() ?>script.js"></script>
</body>
</html>
