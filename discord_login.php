<?php
// discord_login.php

session_start();

// Si l'utilisateur est déjà connecté, renvoie vers l'accueil
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Charge le .env
try {
    loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    die("Erreur de configuration : " . $e->getMessage());
}

// Récupère les infos depuis le .env
$client_id = $_ENV['DISCORD_CLIENT_ID'];
$client_secret   = $_ENV['DISCORD_CLIENT_SECRET'];
$redirect_uri = $_ENV['DISCORD_REDIRECT_URI'];

// Redirection vers Discord
if (!isset($_GET['code'])) {
    $params = [
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'identify email'
    ];
    header('Location: https://discord.com/api/oauth2/authorize?' . http_build_query($params));
    exit;
}

// Échange du Code contre Token
if (isset($_GET['code'])) {
    $token_url = "https://discord.com/api/oauth2/token";
    $post_data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $redirect_uri
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Désactive la vérification SSL (A COMMENTER EN PRODUCTION)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        die('Erreur CURL : ' . curl_error($ch));
    }
    
    curl_close($ch);

    $results = json_decode($response, true);
    
    // Message d'erreur de Discord
    if (!isset($results['access_token'])) {
        echo "<h3>Erreur Discord détectée :</h3>";
        echo "<pre>";
        print_r($results);
        echo "</pre>";
        exit;
    }

    $access_token = $results['access_token'];

    // Récupération User
    $user_url = "https://discord.com/api/users/@me";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $user_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactive SSL ici aussi (A COMMENTER EN PRODUCTION)
    $user_data = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // Préparation des données
    $discord_id = $user_data['id'];
    $email = isset($user_data['email']) ? $user_data['email'] : null;

    $raw_username = $user_data['username'];

    // Si le nom est trop long (> 12)
    if (mb_strlen($raw_username) > 12) {
        // On garde les 8 premiers caractères et on ajoute 4 chiffres aléatoires
        // Ex: "SuperLongPseudo" devient "SuperLon4829"
        $username = mb_substr($raw_username, 0, 8) . rand(1000, 9999);
    } else {
        $username = $raw_username;
}
    
    if (isset($user_data['avatar'])) {
        $avatar_url = "https://cdn.discordapp.com/avatars/$discord_id/" . $user_data['avatar'] . ".png";
    } else {
        $avatar_url = null;
    }

    // Ecriture dans la base de Données
    
    // Vérif existant
    $stmt = $pdo->prepare("SELECT * FROM users WHERE discord_id = ?");
    $stmt->execute([$discord_id]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['avatar'] = $avatar_url;
    } else {
        // Inscription
        $stmt = $pdo->prepare("INSERT INTO users (discord_id, username, email, avatar, elo) VALUES (?, ?, ?, ?, 1200)");
        $stmt->execute([$discord_id, $username, $email, $avatar_url]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['avatar'] = $avatar_url;
    }

    header('Location: index.php');
    exit;
}
?>