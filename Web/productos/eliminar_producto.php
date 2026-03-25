

<?php 

require_once '../config.php';
require_once __DIR__ . '/../config_api.php';
require_once __DIR__ . '/../api/api_client.php';

if(!isLoggedIn()) {
    header('Location:login.php');
    exit;
}

// Usuario autenticado

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header('Location:index.php');
    exit;
}

$producto_id = (int)$_GET['id'];
$user = getCurrentUser();

// Solo API (tumercadosena.shop); sin SQL
$prod = apiGetProducto($producto_id);
if (!$prod || ($prod['vendedor_id'] ?? 0) != ($user['id'] ?? 0)) {
    header('Location: mis_productos.php?error=sin_permiso');
    exit;
}
$r = apiEliminarProducto($producto_id);
header('Location: mis_productos.php?' . ($r['success'] ? 'mensaje=producto_eliminado' : 'error=eliminacion_fallida'));
exit;


