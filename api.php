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
    // Si user id, regarde l'équipe de cet utilisateur, sinon prend celle du connecté
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

// Changer la formation
if ($action === 'save_formation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Non connecté']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $formation = $input['formation'] ?? '4-4-2';
    
    // Liste blanche des formations autorisées (Sécurité)
    $allowed = [
    '4-4-2 Diamant',
    '4-4-2 Boîte',
    '3-5-2 Liberté',
    '4-3-3 Triangle',
    '4-3-3 Delta',
    '4-5-1 Équilibré',
    '3-6-1 Hexa',
    '5-4-1 Double Volante'
    ];
    
    if (in_array($formation, $allowed)) {
        $manager->updateFormation($_SESSION['user_id'], $formation);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Formation invalide']);
    }
    exit;
}

// ============================================================
// PARTIE 2 : MATCHMAKING & TEMPS RÉEL
// ============================================================

// --- REJOINDRE LA FILE ---
if ($action === 'join_queue') {
    $mode = $_GET['mode'] ?? 'ranked';
    $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("INSERT INTO matchmaking_queue (user_id, mode) VALUES (?, ?)")->execute([$userId, $mode]);
    echo json_encode(['success' => true]);
    exit;
}

// --- QUITTER LA FILE OU FORFAIT ---
if ($action === 'leave_match') {
    $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id = ?")->execute([$userId]);
    
    // On récupère le match actif
    $stmt = $pdo->prepare("SELECT * FROM active_matches WHERE (player1_id = ? OR player2_id = ?) AND status != 'finished'");
    $stmt->execute([$userId, $userId]);
    $match = $stmt->fetch();
    
    if ($match) {
        $isP1 = ($match['player1_id'] == $userId);
        $oppId = $isP1 ? $match['player2_id'] : $match['player1_id'];
        $isForfeit = true;

        // Si on est en litige, on vérifie s'il a le droit de quitter
        if ($match['status'] === 'disputed') {
            $stmtLitige = $pdo->prepare("SELECT * FROM litiges WHERE match_id = ?");
            $stmtLitige->execute([$match['id']]);
            $litige = $stmtLitige->fetch();
            
            if ($litige) {
                $myEvidence = $isP1 ? $litige['p1_evidence_path'] : $litige['p2_evidence_path'];
                if ($myEvidence !== null) {
                    $isForfeit = false;
                } else {
                    // Forfeit pendant le litige
                    $pdo->prepare("DELETE FROM litiges WHERE match_id = ?")->execute([$match['id']]);
                }
            }
        }

        // Si c'est bien un forfait
        if ($isForfeit) {
            processMatchResult($pdo, $oppId, $userId, 3, 0, $match['mode']);
            $pdo->prepare("UPDATE active_matches SET status = 'forfeit' WHERE id = ?")->execute([$match['id']]);
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

// --- LE COEUR : POLLING ---
if ($action === 'poll_match') {
    $mode = $_GET['mode'] ?? 'ranked';
    
    $stmt = $pdo->prepare("SELECT * FROM active_matches WHERE player1_id = ? OR player2_id = ?");
    $stmt->execute([$userId, $userId]);
    $match = $stmt->fetch();

    if ($match) {
        $isP1 = ($match['player1_id'] == $userId);
        $oppId = $isP1 ? $match['player2_id'] : $match['player1_id'];
        
        // Vérifier si le match a été forfeit par l'adversaire
        if ($match['status'] === 'forfeit') {
            // Nettoyage complet
            $pdo->prepare("DELETE FROM litiges WHERE match_id = ?")->execute([$match['id']]);
            $pdo->prepare("DELETE FROM active_matches WHERE id = ?")->execute([$match['id']]);
            echo json_encode(['state' => 'opponent_left']);
            exit;
        }

        // Vérifier si le match a été terminé avec accord mutuel
        if ($match['status'] === 'finished') {
            $pdo->prepare("DELETE FROM active_matches WHERE id = ?")->execute([$match['id']]);
            echo json_encode(['state' => 'finished_agreement']);
            exit;
        }

        $myPingCol = $isP1 ? 'p1_last_ping' : 'p2_last_ping';
        $oppPingCol = $isP1 ? 'p2_last_ping' : 'p1_last_ping';

        // Mettre à jour le Ping
        $pdo->prepare("UPDATE active_matches SET $myPingCol = NOW() WHERE id = ?")->execute([$match['id']]);

        // Vérifier le Timeout
        if ($match['status'] !== 'disputed') {
            $stmtPing = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, $oppPingCol, NOW()) as diff FROM active_matches WHERE id = ?");
            $stmtPing->execute([$match['id']]);
            $pingDiff = $stmtPing->fetchColumn();

            if ($pingDiff > 15) {
                processMatchResult($pdo, $userId, $oppId, 3, 0, $match['mode']);
                $pdo->prepare("DELETE FROM active_matches WHERE id = ?")->execute([$match['id']]);
                echo json_encode(['state' => 'opponent_left']);
                exit;
            }
        } else {
            // Si en litige, vérification preuve et timeout
            $stmtLitige = $pdo->prepare("SELECT * FROM litiges WHERE match_id = ?");
            $stmtLitige->execute([$match['id']]);
            $litige = $stmtLitige->fetch();
            
            if ($litige) {
                $myEvidence = $isP1 ? $litige['p1_evidence_path'] : $litige['p2_evidence_path'];
                $oppEvidence = $isP1 ? $litige['p2_evidence_path'] : $litige['p1_evidence_path'];

                // Si preuve envoyée, peut partir
                if ($myEvidence !== null) {
                    echo json_encode(['state' => 'lobby']); 
                    exit;
                }

                // Si l'adversaire timeout, on gagne et le litige est supprimé
                $stmtPing = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, $oppPingCol, NOW()) as diff FROM active_matches WHERE id = ?");
                $stmtPing->execute([$match['id']]);
                $pingDiff = $stmtPing->fetchColumn();

                if ($pingDiff > 15 && $oppEvidence === null) {
                    processMatchResult($pdo, $userId, $oppId, 3, 0, $match['mode']); // Je gagne par forfait
                    $pdo->prepare("DELETE FROM litiges WHERE match_id = ?")->execute([$match['id']]);
                    $pdo->prepare("DELETE FROM active_matches WHERE id = ?")->execute([$match['id']]);
                    echo json_encode(['state' => 'opponent_left']);
                    exit;
                }
            }
        }

        // Récupérer infos et envoyer l'état
        $stmtOpp = $pdo->prepare("SELECT username, elo, avatar FROM users WHERE id = ?");
        $stmtOpp->execute([$oppId]);
        $oppInfo = $stmtOpp->fetch();

        $stmtChat = $pdo->prepare("SELECT c.message, c.sender_id, DATE_FORMAT(c.sent_at, '%H:%i') as time 
                                   FROM match_chat c WHERE match_id = ? ORDER BY sent_at ASC");
        $stmtChat->execute([$match['id']]);
        $chat = $stmtChat->fetchAll();

        echo json_encode([
            'state' => 'in_match',
            'status' => $match['status'], 
            'match_id' => $match['id'],
            'opponent' => ['username' => $oppInfo['username'], 'elo' => $oppInfo['elo'], 'avatar' => $oppInfo['avatar']],
            'chat' => $chat,
            'my_id' => $userId
        ]);
        exit;
    }

    // Si aucun match actif, on cherche dans la file
    $stmtQueue = $pdo->prepare("SELECT * FROM matchmaking_queue WHERE user_id = ?");
    $stmtQueue->execute([$userId]);
    
    if ($stmtQueue->fetch()) {
        $pdo->beginTransaction();
        $stmtFind = $pdo->prepare("SELECT user_id FROM matchmaking_queue WHERE mode = ? AND user_id != ? ORDER BY joined_at ASC LIMIT 1 FOR UPDATE");
        $stmtFind->execute([$mode, $userId]);
        $opponent = $stmtFind->fetch();

        if ($opponent) {
            $oppId = $opponent['user_id'];
            $pdo->prepare("INSERT INTO active_matches (player1_id, player2_id, mode) VALUES (?, ?, ?)")->execute([$userId, $oppId, $mode]);
            $matchId = $pdo->lastInsertId();
            $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id IN (?, ?)")->execute([$userId, $oppId]);
            $pdo->commit();

            $stmtOpp = $pdo->prepare("SELECT username, elo, avatar FROM users WHERE id = ?");
            $stmtOpp->execute([$oppId]);
            $oppInfo = $stmtOpp->fetch();

            echo json_encode([
                'state' => 'in_match',
                'status' => 'ongoing',
                'match_id' => $matchId,
                'opponent' => ['username' => $oppInfo['username'], 'elo' => $oppInfo['elo'], 'avatar' => $oppInfo['avatar']],
                'chat' => [],
                'my_id' => $userId
            ]);
            exit;
        } else {
            $pdo->commit();
            echo json_encode(['state' => 'searching']);
            exit;
        }
    }

    echo json_encode(['state' => 'lobby']);
    exit;
}

// --- TCHAT ---
if ($action === 'send_chat' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $pdo->prepare("INSERT INTO match_chat (match_id, sender_id, message) VALUES (?, ?, ?)")
        ->execute([$input['match_id'], $userId, htmlspecialchars($input['message'])]);
    echo json_encode(['success' => true]);
    exit;
}

// --- SOUMETTRE LE SCORE ---
if ($action === 'submit_score' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $myScore = (int)$input['my_score'];
    $oppScore = (int)$input['opp_score'];
    $matchId = (int)$input['match_id'];

    $stmt = $pdo->prepare("SELECT * FROM active_matches WHERE id = ?");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch();

    if (!$match) { echo json_encode(['success' => false]); exit; }

    $isP1 = ($match['player1_id'] == $userId);
    
    if ($isP1) {
        $pdo->prepare("UPDATE active_matches SET p1_score_claim = ?, p1_opp_score_claim = ?, status = 'resolving' WHERE id = ?")
            ->execute([$myScore, $oppScore, $matchId]);
    } else {
        $pdo->prepare("UPDATE active_matches SET p2_score_claim = ?, p2_opp_score_claim = ?, status = 'resolving' WHERE id = ?")
            ->execute([$myScore, $oppScore, $matchId]);
    }

    $stmt->execute([$matchId]);
    $match = $stmt->fetch();

    if ($match['p1_score_claim'] !== null && $match['p2_score_claim'] !== null) {
        if ($match['p1_score_claim'] === $match['p2_opp_score_claim'] && $match['p1_opp_score_claim'] === $match['p2_score_claim']) {
            // Accord, on traite le résultat
            $winId = ($match['p1_score_claim'] > $match['p1_opp_score_claim']) ? $match['player1_id'] : (($match['p1_score_claim'] < $match['p1_opp_score_claim']) ? $match['player2_id'] : null);
            $losId = ($winId == $match['player1_id']) ? $match['player2_id'] : $match['player1_id'];
            $winScore = max($match['p1_score_claim'], $match['p1_opp_score_claim']);
            $losScore = min($match['p1_score_claim'], $match['p1_opp_score_claim']);

            processMatchResult($pdo, $winId, $losId, $winScore, $losScore, $match['mode']);
            $pdo->prepare("UPDATE active_matches SET status = 'finished' WHERE id = ?")->execute([$matchId]);
            
            echo json_encode(['state' => 'finished_agreement']);
            exit;
        } else {
            // Desaccord, passage en litige
            $pdo->prepare("UPDATE active_matches SET status = 'disputed' WHERE id = ?")->execute([$matchId]);
            $pdo->prepare("INSERT INTO litiges (match_id, p1_id, p2_id, p1_score_claim, p2_score_claim) VALUES (?, ?, ?, ?, ?)")
                ->execute([$matchId, $match['player1_id'], $match['player2_id'], $match['p1_score_claim']."-".$match['p1_opp_score_claim'], $match['p2_score_claim']."-".$match['p2_opp_score_claim']]);
            
            echo json_encode(['state' => 'disputed']);
            exit;
        }
    }
    echo json_encode(['state' => 'waiting']);
    exit;
}

// --- SOUMETTRE PREUVE ---
if ($action === 'submit_evidence' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $matchId = $_POST['match_id'];
    $message = $_POST['message'] ?? '';
    
    $uploadDir = 'uploads/litiges/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $filePath = null;
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
        $fileName = "litige_" . $matchId . "_" . $userId . "_" . time() . "." . $ext;
        $filePath = $uploadDir . $fileName;
        move_uploaded_file($_FILES['evidence']['tmp_name'], $filePath);
    }

    $stmt = $pdo->prepare("SELECT * FROM active_matches WHERE id = ?");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch();

    if ($match) {
        $isP1 = ($match['player1_id'] == $userId);
        if ($isP1) {
            $pdo->prepare("UPDATE litiges SET p1_evidence_path = ?, p1_message = ? WHERE match_id = ?")->execute([$filePath, $message, $matchId]);
        } else {
            $pdo->prepare("UPDATE litiges SET p2_evidence_path = ?, p2_message = ? WHERE match_id = ?")->execute([$filePath, $message, $matchId]);
        }
        
        // Si les deux preuves sont soumises
        $stmtLitige = $pdo->prepare("SELECT p1_evidence_path, p2_evidence_path FROM litiges WHERE match_id = ?");
        $stmtLitige->execute([$matchId]);
        $litige = $stmtLitige->fetch();
        if ($litige['p1_evidence_path'] && $litige['p2_evidence_path']) {
            $pdo->prepare("DELETE FROM active_matches WHERE id = ?")->execute([$matchId]);
        }

        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

// --- FONCTION CALCUL ELO ---
function processMatchResult($pdo, $winnerId, $loserId, $scoreWin, $scoreLose, $mode) {
    if (!$winnerId) return;

    $stmt = $pdo->prepare("SELECT elo FROM users WHERE id = ?");
    $stmt->execute([$winnerId]); $winElo = $stmt->fetchColumn();
    $stmt->execute([$loserId]); $losElo = $stmt->fetchColumn();

    $K = 40;
    $expectedWin = 1 / (1 + pow(10, ($losElo - $winElo) / 400));
    $eloChange = round($K * (1 - $expectedWin));
    if ($eloChange < 1) $eloChange = 1; 

    $pdo->prepare("INSERT INTO matches (winner_id, loser_id, winner_elo_change, loser_elo_change, mode, score_winner, score_loser) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([$winnerId, $loserId, $eloChange, -$eloChange, $mode, $scoreWin, $scoreLose]);

    if ($mode === 'ranked') {
        $pdo->prepare("UPDATE users SET elo = elo + ?, wins = wins + 1 WHERE id = ?")->execute([$eloChange, $winnerId]);
        $pdo->prepare("UPDATE users SET elo = elo - ?, losses = losses + 1 WHERE id = ?")->execute([$eloChange, $loserId]);
    }
}

?>