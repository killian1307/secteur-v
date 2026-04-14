<?php
require_once 'assets/init_session.php';
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['action'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'];

try {
    if ($action === 'join_queue') {
        // Grab the mode, default to ranked
        $mode = (isset($_POST['mode']) && $_POST['mode'] === 'normal') ? 'normal' : 'ranked';

        // Prevent double-queueing
        $check = $pdo->prepare("SELECT user_id FROM matchmaking_queue WHERE user_id = ?");
        $check->execute([$userId]);
        if (!$check->fetch()) {
            // Insert with the selected mode!
            $insert = $pdo->prepare("INSERT INTO matchmaking_queue (user_id, mode) VALUES (?, ?)");
            $insert->execute([$userId, $mode]);
        }
        echo json_encode(["success" => true]);
        
    } elseif ($action === 'leave_queue') {
        $delete = $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id = ?");
        $delete->execute([$userId]);
        echo json_encode(["success" => true]);
        
    } elseif ($action === 'send_chat') {
        $matchId = (int)$_POST['match_id'];
        $message = trim($_POST['message']);
        
        if (!empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO match_chat (match_id, sender_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$matchId, $userId, $message]);
        }
        echo json_encode(["success" => true]);

    } elseif ($action === 'submit_score') {
        $matchId = (int)$_POST['match_id'];
        $myScore = (int)$_POST['my_score'];
        $oppScore = (int)$_POST['opp_score'];

        // Determine if they are P1 or P2
        $stmt = $pdo->prepare("SELECT player1_id FROM active_matches WHERE id = ?");
        $stmt->execute([$matchId]);
        $match = $stmt->fetch();

        if ($match) {
            $isP1 = ($match['player1_id'] == $userId);
            if ($isP1) {
                $upd = $pdo->prepare("UPDATE active_matches SET p1_score_claim = ?, p1_opp_score_claim = ? WHERE id = ?");
            } else {
                $upd = $pdo->prepare("UPDATE active_matches SET p2_score_claim = ?, p2_opp_score_claim = ? WHERE id = ?");
            }
            $upd->execute([$myScore, $oppScore, $matchId]);
        }
        echo json_encode(["success" => true]);
    
    } else {
        echo json_encode(["success" => false, "error" => "Unknown action"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
exit;