<?php
require_once 'assets/init_session.php';
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "state" => "not_logged_in"]);
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch User Stats
$stmt = $pdo->prepare("SELECT username, elo, grade, avatar FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// --- ANTI-AFK FIX ---
// Tell the server this user is actively looking at the overlay so they don't get forfeited
$currentPhpTime = date('Y-m-d H:i:s');
$updateActivity = $pdo->prepare("UPDATE users SET last_activity = ? WHERE id = ?");
$updateActivity->execute([$currentPhpTime, $userId]);
// --------------------

$state = "idle";
$matchData = null;
$queueData = null;
$inQueue = null;

// Are they in an active match
$stmtMatch = $pdo->prepare("
    SELECT am.*, 
           u1.username as p1_name, u1.elo as p1_elo, u1.avatar as p1_avatar,
           u2.username as p2_name, u2.elo as p2_elo, u2.avatar as p2_avatar
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
    $matchId = $match['id'];

    // Grab the live chat history
    $stmtChat = $pdo->prepare("
        SELECT mc.message, u.username 
        FROM match_chat mc 
        JOIN users u ON mc.sender_id = u.id 
        WHERE mc.match_id = ? 
        ORDER BY mc.sent_at ASC
    ");
    $stmtChat->execute([$matchId]);
    $chatHistory = $stmtChat->fetchAll(PDO::FETCH_ASSOC);

    $matchData = [
        "match_id" => $matchId,
        "opponent_name" => $isPlayer1 ? $match['p2_name'] : $match['p1_name'],
        "opponent_elo" => $isPlayer1 ? $match['p2_elo'] : $match['p1_elo'],
        "opponent_avatar" => $isPlayer1 ? $match['p2_avatar'] : $match['p1_avatar'],
        "my_score_claim" => $isPlayer1 ? $match['p1_score_claim'] : $match['p2_score_claim'],
        "chat" => $chatHistory
    ];
} else {
    // If not in a match, are they in the queue
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

// Return the JSON payload
echo json_encode([
    "status" => "success",
    "state" => $state,
    "user" => [
        "username" => $user['username'],
        "elo" => $user['elo'],
        "avatar" => $user['avatar']
    ],
    "queue" => $queueData,
    "match" => $matchData,
    "queueMode" => $inQueue ? $inQueue['mode'] : null
]);
exit;