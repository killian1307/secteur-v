<?php
// api.php
header('Content-Type: application/json');
session_start();
require_once 'TeamManager.php';


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}
$userId = $_SESSION['user_id'];

$manager = new TeamManager($pdo);
$action = $_GET['action'] ?? '';

// Charger 'l'équipe
if ($action === 'get_team') {
    // Si user id, on regarde l'équipe de cet utilisateur, sinon on prend celle du connecté
    $targetId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
    
    // si l'id est invalide, renvoie une erreur
    if ($targetId <= 0) {
        echo json_encode(['success' => false, 'team' => null]);
        exit;
    }

    $data = $manager->getOrCreateTeam($targetId);
    echo json_encode(['success' => true, 'team' => $data]);
    exit;
}

// Sauvegarder un joueur
if ($action === 'save_slot' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lit les données JSON envoyées par le JS
    $input = json_decode(file_get_contents('php://input'), true);
    
    $slot = $input['slot'];
    $playerId = $input['player_id'];
    
    $result = $manager->updateSlot($userId, $slot, $playerId);
    
    echo json_encode(['success' => true, 'player' => $result]);
    exit;
}

// Rechercher des joueurs
if ($action === 'search_players') {
    $term = $_GET['term'] ?? '';
    // Évite les recherches trop courtes
    if (strlen($term) < 2) {
        echo json_encode([]); 
        exit;
    }
    $results = $manager->searchPlayers($term);
    echo json_encode($results);
    exit;
}
?>