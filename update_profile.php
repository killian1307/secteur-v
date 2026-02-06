<?php
session_start();
require 'db.php';

// 1. Sécurité : Si pas connecté, dehors !
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 2. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On nettoie l'entrée (supprime les espaces inutiles au début/fin)
    $newBio = trim($_POST['bio']);

    // Vérification de la longueur (Sécurité côté serveur)
    if (strlen($newBio) > 150) {
        // Si c'est trop long, on coupe brutalement ou on renvoie une erreur
        // Ici on coupe pour simplifier
        $newBio = substr($newBio, 0, 150);
    }

    // 3. Mise à jour en BDD
    try {
        $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->execute([$newBio, $_SESSION['user_id']]);
        
        // Succès : on retourne au profil
        header("Location: profile.php");
        exit;

    } catch (PDOException $e) {
        // En cas d'erreur technique
        die("Erreur lors de la mise à jour : " . $e->getMessage());
    }
}