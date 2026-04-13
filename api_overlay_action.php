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
        // Prevent double-queueing
        $check = $pdo->prepare("SELECT user_id FROM matchmaking_queue WHERE user_id = ?");
        $check->execute([$userId]);
        if (!$check->fetch()) {
            $insert = $pdo->prepare("INSERT INTO matchmaking_queue (user_id, mode) VALUES (?, 'ranked')");
            $insert->execute([$userId]);
        }
        echo json_encode(["success" => true]);
        
    } elseif ($action === 'leave_queue') {
        $delete = $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id = ?");
        $delete->execute([$userId]);
        echo json_encode(["success" => true]);
        
    } else {
        echo json_encode(["success" => false, "error" => "Unknown action"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
exit;