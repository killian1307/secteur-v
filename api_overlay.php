<?php
require_once 'assets/init_session.php';
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "state" => "not_logged_in"]);
    exit;
}

$userId = $_SESSION['user_id'];

// 1. Fetch User Stats
$stmt = $pdo->prepare("SELECT username, elo, grade FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$state = "idle";
$matchData = null;
$queueData = null;

// 2. Are they in an active match?
$stmtMatch = $pdo->prepare("
    SELECT am.*, 
           u1.username as p1_name, u1.elo as p1_elo, 
           u2.username as p2_name, u2.elo as p2_elo
    FROM active_matches am
    JOIN users u1 ON am.player1_id = u1.id
    JOIN users u2 ON am.player2_id = u2.id
    WHERE (am.player1_id = ? OR am.player2_id = ?) 
    AND am.status = 'ongoing'
    LIMIT 1
");
$stmtMatch->execute([$userId, $userId]);
$match = $stmtMatch->fetch();

if ($match) {
    $state = "in_match";
    // Determine which player is the opponent
    $isPlayer1 = ($match['player1_id'] == $userId);
    $matchData = [
        "match_id" => $match['id'],
        "opponent_name" => $isPlayer1 ? $match['p2_name'] : $match['p1_name'],
        "opponent_elo" => $isPlayer1 ? $match['p2_elo'] : $match['p1_elo']
    ];
} else {
    // 3. If not in a match, are they in the queue?
    $stmtQueue = $pdo->prepare("SELECT mode FROM matchmaking_queue WHERE user_id = ?");
    $stmtQueue->execute([$userId]);
    $inQueue = $stmtQueue->fetch();
    
    if ($inQueue) {
        $state = "in_queue";
        // Count total players searching
        $stmtCount = $pdo->query("SELECT COUNT(*) as total FROM matchmaking_queue");
        $queueCount = $stmtCount->fetch();
        $queueData = ["players_searching" => $queueCount['total']];
    }
}

// 4. Return the JSON payload
echo json_encode([
    "status" => "success",
    "state" => $state,
    "user" => [
        "username" => $user['username'],
        "elo" => $user['elo']
    ],
    "queue" => $queueData,
    "match" => $matchData
]);
exit;