<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once("config.php");

if (!isset($_GET["tabla"])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta nombre de tabla"]);
    exit;
}
$tabla = $_GET["tabla"];

$extra = "0 AS extra";
switch ($tabla) {
    case "sucesos":
    case "estados":
    case "integridad":
        $extra = "descripcion AS extra";
        break;
    case "motivos":
        $extra = "tipo AS extra";
        break;
    case "subcategorias":
        $extra = "categoria_id AS extra";
        break;
    case "roles":
    case "categorias":
        break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Tabla inválida"]);
        exit;
}

$sql = "SELECT id, nombre, $extra FROM $tabla";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$res = $result->fetch_all(MYSQLI_ASSOC);

if (!$res) {
    http_response_code(404);
    echo json_encode(["error" => "Datos no encontrados"]);
    return;
}

echo json_encode($res);
?>
