<?php
require_once 'assets/init_session.php';
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['action'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

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
    
// ==========================================
// OVERLAY SOCIAL ENGINE ENDPOINTS
// ==========================================

    } elseif ($action === 'poll_social') {
        // Fetch accepted friends using 'friends' table and 'sender_id' / 'receiver_id'
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.elo, u.avatar
            FROM friends f
            JOIN users u ON (u.id = f.sender_id OR u.id = f.receiver_id) AND u.id != ?
            WHERE (f.sender_id = ? OR f.receiver_id = ?) AND f.status = 'accepted'
        ");
        $stmt->execute([$userId, $userId, $userId]);
        $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["friends" => $friends]);
        exit;

    } elseif ($action === 'poll_messages') {
        // Fetch recent DM conversations using 'private_messages' and 'sent_at'
        $stmt = $pdo->prepare("
            SELECT u.id as user_id, u.username, u.avatar,
                   (SELECT message FROM private_messages WHERE (sender_id = ? AND receiver_id = u.id) OR (sender_id = u.id AND receiver_id = ?) ORDER BY sent_at DESC LIMIT 1) as last_message,
                   (SELECT COUNT(*) FROM private_messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread
            FROM users u
            WHERE u.id IN (
                SELECT sender_id FROM private_messages WHERE receiver_id = ?
                UNION
                SELECT receiver_id FROM private_messages WHERE sender_id = ?
            )
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["conversations" => $conversations]);
        exit;

    } elseif ($action === 'get_chat') {
        // Fetch a specific conversation between the user and a friend
        $targetId = (int)$_POST['target_id'];
        
        // Mark their messages as read
        $upd = $pdo->prepare("UPDATE private_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
        $upd->execute([$targetId, $userId]);

        // Fetch the message history
        $stmt = $pdo->prepare("
            SELECT sender_id, message, DATE_FORMAT(sent_at, '%H:%i') as time 
            FROM private_messages 
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
            ORDER BY sent_at ASC
        ");
        $stmt->execute([$userId, $targetId, $targetId, $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["messages" => $messages]);
        exit;

    } elseif ($action === 'send_message') {
        // Send a new DM to a friend
        $targetId = (int)$_POST['target_id'];
        $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');
        
        if (!empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO private_messages (sender_id, receiver_id, message, is_read) VALUES (?, ?, ?, 0)");
            $stmt->execute([$userId, $targetId, $message]);
        }
        
        echo json_encode(["success" => true]);
        exit;

    } elseif ($action === 'poll_notifications') {
        // Notifications are actually PENDING friend requests received by the user
        $stmt = $pdo->prepare("
            SELECT u.id as sender_id, u.username, u.avatar, DATE_FORMAT(f.created_at, '%m/%d %H:%i') as created_at 
            FROM friends f
            JOIN users u ON f.sender_id = u.id
            WHERE f.receiver_id = ? AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["notifications" => $requests]);
        exit;
        } else {
        echo json_encode(["success" => false, "error" => "Unknown action"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
exit;