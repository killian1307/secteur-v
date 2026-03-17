<?php
$lifetime = 31536000; 

ini_set('session.gc_maxlifetime', $lifetime);

// Vérifie qu'on est pas en local pour éviter les problèmes de cookies en développement
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] === 'localhost';

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'secure' => !$isLocalhost,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
?>