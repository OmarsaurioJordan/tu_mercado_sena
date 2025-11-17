<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "tu_mercado_sena";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error DB: " . $conn->connect_error]));
}
$conn->set_charset("utf8");
?>
