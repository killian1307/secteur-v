<?php
require 'db.php';

// Charge le .env
try {
    loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    die("Erreur de configuration : " . $e->getMessage());
}

// Clé secrète
$secretKey = $_ENV['DB_SECRET_KEY'];

if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    http_response_code(403);
    die("Accès refusé.");
}

// CRÉATION DU DOSSIER CACHÉ
$backupFolder = __DIR__ . '/backups/';
if (!file_exists($backupFolder)) {
    mkdir($backupFolder, 0755, true);
}
// On empêche quiconque de télécharger les sauvegardes depuis le navigateur
file_put_contents($backupFolder . '.htaccess', "Deny from all");

// EXÉCUTION DE LA SAUVEGARDE
$date = date('Y-m-d_H-i-s');
$filePath = $backupFolder . "secteur_v_backup_{$date}.sql";

// On récupère tes infos depuis le .env
$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

// La commande Linux magique pour exporter la BDD
$command = "mysqldump -h " . escapeshellarg($host) . " -u " . escapeshellarg($user) . " -p" . escapeshellarg($pass) . " " . escapeshellarg($dbname) . " > " . escapeshellarg($filePath);
exec($command, $output, $returnVar);

if ($returnVar !== 0) {
    die("Erreur lors de la création de la sauvegarde.");
}

// NETTOYAGE : Garder uniquement les 10 dernières
$files = glob($backupFolder . 'secteur_v_backup_*.sql');
rsort($files);

if (count($files) > 10) {
    $filesToDelete = array_slice($files, 10);
    foreach ($filesToDelete as $file) {
        unlink($file);
    }
}

echo "✅ Sauvegarde générée avec succès ! (" . count($files) . " fichiers en mémoire)";
?>