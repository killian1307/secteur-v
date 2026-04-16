<?php
// discord_login.php

require_once 'assets/init_session.php';

if (isset($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = $_GET['redirect'];
}

// Si l'utilisateur est déjà connecté, renvoie vers l'accueil
if (isset($_SESSION['user_id'])) {
    $destination = (isset($_SESSION['redirect_after_login']) && $_SESSION['redirect_after_login'] === 'overlay') ? '/overlay.php' : '/';
    unset($_SESSION['redirect_after_login']);
    header("Location: " . $destination);
    exit;
}

require 'db.php';

// Charge le .env
try {
    loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    die("Erreur de configuration : " . $e->getMessage());
}

require_once 'anti_smurf.php';

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
        'scope' => 'identify email guilds.join'
    ];
    header('Location: https://discord.com/api/oauth2/authorize?' . http_build_query($params));
    exit;
}

// Échange du Code contre Token
if (isset($_GET['code'])) {

    // --- THE ANTI-DOUBLE-FIRE FIX ---
    $oauth_code = $_GET['code'];
    if (isset($_SESSION['last_oauth_code']) && $_SESSION['last_oauth_code'] === $oauth_code) {
        // Kill the duplicate background request instantly
        die("Duplicate OAuth request intercepted.");
    }
    $_SESSION['last_oauth_code'] = $oauth_code;
    // --------------------------------

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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !IS_LOCAL); // Vérification SSL selon l'environnement
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, IS_LOCAL ? 0 : 2); // 0 en local, 2 en prod
    $user_data = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // --- AJOUT AUTOMATIQUE AU SERVEUR DISCORD ---
    $botToken = $_ENV['DISCORD_BOT_TOKEN'];
    $guildId = $_ENV['DISCORD_GUILD_ID'];
    $discordId = $user_data['id'];
    $accessToken = $access_token;

    $joinUrl = "https://discord.com/api/v10/guilds/$guildId/members/$discordId";
    $joinData = json_encode(['access_token' => $accessToken]);

    $chJoin = curl_init($joinUrl);
    curl_setopt($chJoin, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($chJoin, CURLOPT_POSTFIELDS, $joinData);
    curl_setopt($chJoin, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chJoin, CURLOPT_HTTPHEADER, [
        "Authorization: Bot $botToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($chJoin, CURLOPT_SSL_VERIFYPEER, !IS_LOCAL); // Vérification SSL selon l'environnement
    curl_setopt($chJoin, CURLOPT_SSL_VERIFYHOST, IS_LOCAL ? 0 : 2); // 0 en local, 2 en prod
    curl_exec($chJoin);
    curl_close($chJoin);

    // Préparation des données
    $discord_id = $user_data['id'];
    $email = isset($user_data['email']) ? $user_data['email'] : null;

    $raw_username = $user_data['username'];

    // Vérification anti-smurf
    $smurfCheck = check_and_prevent_smurf($pdo, $discord_id);

    if ($smurfCheck === false) {
    // Si c'est un smurf, détruit la session dégage avec un message d'erreur
    session_destroy();
    die("<h1>Access Denied : Potential Smurf Detected</h1>
         <p>An account has already been created using this device of network on Secteur V.<br>
         Smurfs and multi-accounts are strictly prohibited and will result in a definive ban from the platform.</p>
         <a href='/'>Back to Home</a>");
    }

    // Si le nom est trop long (> 12)
    if (mb_strlen($raw_username) > 12) {
        // On garde les 8 premiers caractères et on ajoute 4 chiffres aléatoires
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
        if ($user['avatar'] !== $avatar_url) {
            $updateStmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $updateStmt->execute([$avatar_url, $user['id']]);
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['avatar'] = $avatar_url;
    } else {

        // Récupération de l'adresse IP et de l'empreinte de l'appareil pour checker les smurfs
        $ip_address = hash('sha256', $_SERVER['REMOTE_ADDR'] . IP_SALT);
        $device_id = $smurfCheck;

        // Inscription
        $stmt = $pdo->prepare("INSERT INTO users (discord_id, username, email, avatar, elo, ip_address, device_id) VALUES (?, ?, ?, ?, 1200, ?, ?)");
        $stmt->execute([$discord_id, $username, $email, $avatar_url, $ip_address, $device_id]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['avatar'] = $avatar_url;
    }

    // Redirect them back to where they came from
    $destination = (isset($_SESSION['redirect_after_login']) && $_SESSION['redirect_after_login'] === 'overlay') ? '/overlay.php' : '/';
    unset($_SESSION['redirect_after_login']);
    
    header("Location: " . $destination);
    exit;
}
?>