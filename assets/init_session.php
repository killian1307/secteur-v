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

// Gestion de la langue
$allowed_langs = ['fr', 'en', 'es', 'it', 'de', 'ja', 'ar'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$current_lang = $_SESSION['lang'] ?? 'fr'; // Français par défaut

// Chargement du dictionnaire
$translations = require __DIR__ . "/../lang/{$current_lang}.php";

// La fonction magique qu'on va utiliser partout
function __($key, ...$vars) {
    global $translations;
    $text = $translations[$key] ?? $key; // Si la trad n'existe pas, affiche au moins la clé
    
    // S'il y a des variables (comme le nombre de joueurs), on les injecte
    if (!empty($vars)) {
        return sprintf($text, ...$vars);
    }
    return $text;
}
?>