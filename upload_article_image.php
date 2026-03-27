<?php
require_once 'assets/init_session.php';
require 'db.php';

// Seuls les membres autorisés peuvent uploader des images ici
$stmtUser = $pdo->prepare("SELECT grade FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$user = $stmtUser->fetch();
$allowed_grades = ['Modérateur', 'Administrateur', 'Créateur'];

if (!$user || !in_array($user['grade'], $allowed_grades)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

// Vérification du fichier reçu
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $slug = $_POST['slug'] ?? 'brouillon';
    // On nettoie le slug au cas où
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug)));

    $uploadDir = __DIR__ . '/articles/img/' . $slug . '/';
    
    // Créer le dossier s'il n'existe pas encore
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    
    // Sécuriser le nom de l'image
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'webp', 'jpeg');

    if (in_array($fileExtension, $allowedfileExtensions)) {
        // On génère un nom unique pour éviter d'écraser une autre image
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $dest_path = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // On renvoie l'URL de l'image à l'éditeur TinyMCE (format JSON attendu)
            $imageUrl = 'articles/img/' . $slug . '/' . $newFileName;
            echo json_encode(['location' => $imageUrl]);
            exit;
        }
    }
}

// Si on arrive ici, c'est qu'il y a eu une erreur
header("HTTP/1.1 400 Invalid extension or upload error.");
?>