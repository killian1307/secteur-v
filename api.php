<?php
// api.php
header('Content-Type: application/json');
session_start();
require_once 'TeamManager.php';

// --- Simulation de connexion pour le test ---
// À remplacer par ton vrai système de login (ex: $_SESSION['user_id'])
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit; // On coupe le script ici
}
$userId = $_SESSION['user_id'];
// --------------------------------------------

$manager = new TeamManager($pdo);
$action = $_GET['action'] ?? '';

// ACTION 1 : Charger l'équipe
if ($action === 'get_team') {
    // Si le JS envoie un user_id (ce qu'on vient de corriger), on l'utilise.
    // Sinon, fallback sur la session.
    $targetId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
    
    // Petite sécurité : si l'ID est 0 ou invalide, on renvoie une erreur ou un tableau vide
    if ($targetId <= 0) {
        echo json_encode(['success' => false, 'team' => null]);
        exit;
    }

    $data = $manager->getOrCreateTeam($targetId);
    echo json_encode(['success' => true, 'team' => $data]);
    exit;
}

// ACTION 2 : Sauvegarder un joueur
if ($action === 'save_slot' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // On lit les données JSON envoyées par le JS
    $input = json_decode(file_get_contents('php://input'), true);
    
    $slot = $input['slot'];      // ex: "slot_10"
    $playerId = $input['player_id']; // ex: 452
    
    $result = $manager->updateSlot($userId, $slot, $playerId);
    
    echo json_encode(['success' => true, 'player' => $result]);
    exit;
}

// ACTION 3 : Rechercher des joueurs
if ($action === 'search_players') {
    $term = $_GET['term'] ?? '';
    // On évite les recherches trop courtes
    if (strlen($term) < 2) {
        echo json_encode([]); 
        exit;
    }
    $results = $manager->searchPlayers($term);
    echo json_encode($results);
    exit;
}
?>