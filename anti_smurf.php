<?php

// salt pour le hachage de l'IP, à définir dans le .env pour plus de sécurité
define('IP_SALT', $_ENV['IP_SALT']);

/**
 * Vérifie si l'utilisateur qui tente de se connecter/s'inscrire est un smurf.
 * Retourne TRUE si c'est une simple connexion d'un compte existant.
 * Retourne FALSE si un double compte est détecté et doit être bloqué.
 * Retourne le $device_id (string) si c'est un nouveau compte propre.
 */
function check_and_prevent_smurf($pdo, $discord_id) {
    // Mélange l'IP avec clé secrète, puis hache le tout en SHA-256
    $ip_address = hash('sha256', $_SERVER['REMOTE_ADDR'] . IP_SALT);
    
    // Récupère le "traceur" de l'appareil s'il existe, sinon on en crée un
    if (isset($_COOKIE['secteur_v_device_id'])) {
        $device_id = $_COOKIE['secteur_v_device_id'];
    } else {
        $device_id = bin2hex(random_bytes(32));
        // Pose le cookie pour 10 ans
        setcookie('secteur_v_device_id', $device_id, time() + (10 * 365 * 24 * 60 * 60), '/', '', true, true);
    }

    // Ce compte (ID Discord) existe-t-il déjà ?
    $stmt = $pdo->prepare("SELECT id FROM users WHERE discord_id = ?");
    $stmt->execute([$discord_id]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        // C'est un compte existant qui se connecte normalement.
        // Met à jour son IP et son Appareil
        $update = $pdo->prepare("UPDATE users SET ip_address = ?, device_id = ? WHERE discord_id = ?");
        $update->execute([$ip_address, $device_id, $discord_id]);
        return true; // Laissez passer !
    }

    // --- Création de compte ---

    // Y a-t-il déjà un AUTRE utilisateur avec cette IP ou cet appareil ?
    $stmtCheck = $pdo->prepare("SELECT username FROM users WHERE ip_address = ? OR device_id = ?");
    $stmtCheck->execute([$ip_address, $device_id]);
    $smurfMatch = $stmtCheck->fetch();

    if ($smurfMatch) {
        // C'est un smurf potentiel ! Bloque la création de compte et affiche un message d'erreur.
        return false;
    }

    // L'utilisateur est clean. Permet la création du compte et stocke l'IP + device_id pour les futurs contrôles.
    // Retourne le device_id pour pouvoir l'insérer dans la BDD.
    return $device_id;
}
?>