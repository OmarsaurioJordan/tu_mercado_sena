<?php
/**
 * Funciones para productos vía API Laravel
 * Usa LARAVEL_API_URL y api_token de config_api.php
 */
if (!defined('LARAVEL_API_URL')) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../config_api.php';
}

function apiFetch($endpoint, $token = null) {
    $url = rtrim(LARAVEL_API_URL, '/') . '/' . ltrim($endpoint, '/');
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n" . ($token ? "Authorization: Bearer $token\r\n" : ''),
            'timeout' => 15
        ]
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    return $resp ? json_decode($resp, true) : null;
}

function apiCrearProducto($data, $imagenes = [], $token = null) {
    $url = rtrim(LARAVEL_API_URL, '/') . '/productos';
    $token = $token ?? ($_SESSION['api_token'] ?? '');
    
    $postData = [
        'nombre' => $data['nombre'],
        'descripcion' => $data['descripcion'],
        'subcategoria_id' => $data['subcategoria_id'],
        'integridad_id' => $data['integridad_id'],
        'precio' => $data['precio'],
        'disponibles' => $data['disponibles'] ?? 1
    ];
    
    $ch = curl_init($url);
    $headers = ['Accept: application/json'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    
    $curlFiles = [];
    foreach ($imagenes as $i => $file) {
        if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $curlFiles["imagenes[$i]"] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        }
    }
    
    $postData = array_merge($postData, $curlFiles);
    
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $decoded = $response ? json_decode($response, true) : null;
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => $decoded,
        'http_code' => $httpCode
    ];
}
