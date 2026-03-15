<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_api.php';

session_unset();
session_destroy();

// Si usamos API Laravel, la página redirige con JS tras llamar a logout y limpiar token
if (isUsingLaravelApi()) {
    $laravelUrl = LARAVEL_API_URL;
    $loginUrl   = getBaseUrl() . 'auth/login.php';
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Cerrando sesión...</title></head>
<body>
<p>Cerrando sesión...</p>
<script>
(function() {
    var token = localStorage.getItem('api_token');
    var url = <?= json_encode($laravelUrl) ?> + 'auth/logout';
    var loginUrl = <?= json_encode($loginUrl) ?>;
    function go() {
        localStorage.removeItem('api_token');
        window.location.href = loginUrl;
    }
    if (token) {
        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
            body: JSON.stringify({ all_devices: false })
        }).then(go).catch(go);
    } else {
        go();
    }
})();
</script>
</body>
</html>
<?php
    exit;
}

header('Location: login.php');
exit;
