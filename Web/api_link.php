<?php
/**
 * URL global de la API – Tu Mercado SENA (Hostinger, ya publicada)
 * Todo el sistema usa solo esta API. No hace falta php artisan ni servidor local.
 * Para cambiar de entorno, solo edita este archivo.
 */

if (!defined('API_BASE_URL')) {
    define('API_BASE_URL', 'https://tumercadosena.shop/api/api/');
}

if (!defined('API_STORAGE_URL')) {
    define('API_STORAGE_URL', 'https://tumercadosena.shop/storage/');
}
