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
// PARTIE MATCHMAKING & TEMPS RÉEL
// ============================================================

// --- 1. REJOINDRE LA FILE ---
if ($action === 'join_queue') {
    $mode = $_GET['mode'] ?? 'ranked';
    // Nettoyer les anciennes présences
    $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("INSERT INTO matchmaking_queue (user_id, mode) VALUES (?, ?)")->execute([$userId, $mode]);
    echo json_encode(['success' => true]);
    exit;
}

// --- 2. QUITTER LA FILE OU FORFAIT ---
if ($action === 'leave_match') {
    // S'il était dans la file, on le retire
    $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id = ?")->execute([$userId]);
    
    // S'il était en match, c'est un FORFAIT (Il perd)
    $stmt = $pdo->prepare("SELECT * FROM active_matches WHERE (player1_id = ? OR player2_id = ?) AND status != 'finished'");
    $stmt->execute([$userId, $userId]);
    $match = $stmt->fetch();
    
    if ($match) {
        $oppId = ($match['player1_id'] == $userId) ? $match['player2_id'] : $match['player1_id'];
        processMatchResult($pdo, $oppId, $userId, 99, 0, $match['mode']); // Adversaire gagne 3-0 par forfait
        $pdo->prepare("DELETE FROM active_matches WHERE id = ?")->execute([$match['id']]);
    }
    echo json_encode(['success' => true]);
    exit;
}

// --- 3. LE COEUR : POLLING (Appelé toutes les 2 secondes) ---
if ($action === 'poll_match') {
    $mode = $_GET['mode'] ?? 'ranked';
    
    // A. Vérifier si je suis dans un match actif
    $stmt = $pdo->prepare("SELECT * FROM active_matches WHERE player1_id = ? OR player2_id = ?");
    $stmt->execute([$userId, $userId]);
    $match = $stmt->fetch();

    if ($match) {
        $isP1 = ($match['player1_id'] == $userId);
        $oppId = $isP1 ? $match['player2_id'] : $match['player1_id'];
        $myPingCol = $isP1 ? 'p1_last_ping' : 'p2_last_ping';
        $oppPingCol = $isP1 ? 'p2_last_ping' : 'p1_last_ping';

        // 1. Mettre à jour mon Ping
        $pdo->prepare("UPDATE active_matches SET $myPingCol = NOW() WHERE id = ?")->execute([$match['id']]);

        // 2. Vérifier si l'adversaire a Ragequit/Crash (Ping > 15s)
        $stmtPing = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, $oppPingCol, NOW()) as diff FROM active_matches WHERE id = ?");
        $stmtPing->execute([$match['id']]);
        $pingDiff = $stmtPing->fetchColumn();

        // Si l'adversaire est parti et qu'on n'est pas déjà en litige
        if ($pingDiff > 15 && $match['status'] !== 'disputed') {
            processMatchResult($pdo, $userId, $oppId, 99, 0, $match['mode']); // Je gagne par forfait
            $pdo->prepare("DELETE FROM active_matches WHERE id = ?")->execute([$match['id']]);
            echo json_encode(['state' => 'opponent_left']);
            exit;
        }

        // 3. Récupérer infos adversaire
        $stmtOpp = $pdo->prepare("SELECT username, elo, avatar FROM users WHERE id = ?");
        $stmtOpp->execute([$oppId]);
        $oppInfo = $stmtOpp->fetch();

        // 4. Récupérer le tchat
        $stmtChat = $pdo->prepare("SELECT c.message, c.sender_id, DATE_FORMAT(c.sent_at, '%H:%i') as time 
                                   FROM match_chat c WHERE match_id = ? ORDER BY sent_at ASC");
        $stmtChat->execute([$match['id']]);
        $chat = $stmtChat->fetchAll();

        echo json_encode([
            'state' => 'in_match',
            'status' => $match['status'], // ongoing, resolving, disputed
            'match_id' => $match['id'],
            'opponent' => ['username' => $oppInfo['username'], 'elo' => $oppInfo['elo'], 'avatar' => $oppInfo['avatar']],
            'chat' => $chat,
            'my_id' => $userId
        ]);
        exit;
    }

    // B. Si pas de match, chercher dans la file d'attente
    $stmtQueue = $pdo->prepare("SELECT * FROM matchmaking_queue WHERE user_id = ?");
    $stmtQueue->execute([$userId]);
    
    if ($stmtQueue->fetch()) {
        // Je suis dans la file, je cherche un adversaire (avec Verrou pour éviter les doublons)
        $pdo->beginTransaction();
        $stmtFind = $pdo->prepare("SELECT user_id FROM matchmaking_queue WHERE mode = ? AND user_id != ? ORDER BY joined_at ASC LIMIT 1 FOR UPDATE");
        $stmtFind->execute([$mode, $userId]);
        $opponent = $stmtFind->fetch();

        if ($opponent) {
            $oppId = $opponent['user_id'];
            // Créer le match
            $pdo->prepare("INSERT INTO active_matches (player1_id, player2_id, mode) VALUES (?, ?, ?)")->execute([$userId, $oppId, $mode]);
            // Retirer de la file
            $pdo->prepare("DELETE FROM matchmaking_queue WHERE user_id IN (?, ?)")->execute([$userId, $oppId]);
            $pdo->commit();
            echo json_encode(['state' => 'match_found']);
        } else {
            $pdo->commit();
            echo json_encode(['state' => 'searching']);
        }
        exit;
    }

    echo json_encode(['state' => 'lobby']);
    exit;
}

// --- 4. TCHAT ---
if ($action === 'send_chat' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $pdo->prepare("INSERT INTO match_chat (match_id, sender_id, message) VALUES (?, ?, ?)")
        ->execute([$input['match_id'], $userId, htmlspecialchars($input['message'])]);
    echo json_encode(['success' => true]);
    exit;
}

// --- 5. SOUMETTRE LE SCORE ---
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
    
    // Enregistrer ma déclaration
    if ($isP1) {
        $pdo->prepare("UPDATE active_matches SET p1_score_claim = ?, p1_opp_score_claim = ?, status = 'resolving' WHERE id = ?")
            ->execute([$myScore, $oppScore, $matchId]);
    } else {
        $pdo->prepare("UPDATE active_matches SET p2_score_claim = ?, p2_opp_score_claim = ?, status = 'resolving' WHERE id = ?")
            ->execute([$myScore, $oppScore, $matchId]);
    }

    // Re-vérifier l'état du match pour voir si l'autre a aussi soumis
    $stmt->execute([$matchId]);
    $match = $stmt->fetch();

    if ($match['p1_score_claim'] !== null && $match['p2_score_claim'] !== null) {
        // Les deux ont soumis. Est-ce qu'ils sont d'accord ?
        // P1 dit (X - Y). P2 dit (Y - X) (du point de vue de P2)
        if ($match['p1_score_claim'] === $match['p2_opp_score_claim'] && $match['p1_opp_score_claim'] === $match['p2_score_claim']) {
            // ACCORD ! On valide le match
            $winId = ($match['p1_score_claim'] > $match['p1_opp_score_claim']) ? $match['player1_id'] : (($match['p1_score_claim'] < $match['p1_opp_score_claim']) ? $match['player2_id'] : null);
            $losId = ($winId == $match['player1_id']) ? $match['player2_id'] : $match['player1_id'];
            $winScore = max($match['p1_score_claim'], $match['p1_opp_score_claim']);
            $losScore = min($match['p1_score_claim'], $match['p1_opp_score_claim']);

            processMatchResult($pdo, $winId, $losId, $winScore, $losScore, $match['mode']);
            $pdo->prepare("DELETE FROM active_matches WHERE id = ?")->execute([$matchId]);
            
            echo json_encode(['state' => 'finished_agreement']);
            exit;
        } else {
            // DESACCORD ! Litige
            $pdo->prepare("UPDATE active_matches SET status = 'disputed' WHERE id = ?")->execute([$matchId]);
            // Créer l'entrée dans la table litiges
            $pdo->prepare("INSERT INTO litiges (match_id, p1_id, p2_id, p1_claimed_score, p2_claimed_score) VALUES (?, ?, ?, ?, ?)")
                ->execute([$matchId, $match['player1_id'], $match['player2_id'], $match['p1_score_claim']."-".$match['p1_opp_score_claim'], $match['p2_score_claim']."-".$match['p2_opp_score_claim']]);
            
            echo json_encode(['state' => 'disputed']);
            exit;
        }
    }
    echo json_encode(['state' => 'waiting']);
    exit;
}

// --- 6. SOUMETTRE PREUVE (LITIGE) ---
if ($action === 'submit_evidence' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $matchId = $_POST['match_id'];
    $message = $_POST['message'] ?? '';
    
    // Dossier d'upload
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
            $pdo->prepare("UPDATE litiges SET p1_evidence_path = ?, p1_message = ? WHERE match_id = ?")
                ->execute([$filePath, $message, $matchId]);
        } else {
            $pdo->prepare("UPDATE litiges SET p2_evidence_path = ?, p2_message = ? WHERE match_id = ?")
                ->execute([$filePath, $message, $matchId]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

// --- FONCTION CALCUL ELO & INSERTION MATCH ---
function processMatchResult($pdo, $winnerId, $loserId, $scoreWin, $scoreLose, $mode) {
    if (!$winnerId) return; // Si match nul parfait (rare), on ignore ou on gère différemment

    // Récupérer ELO
    $stmt = $pdo->prepare("SELECT elo FROM users WHERE id = ?");
    $stmt->execute([$winnerId]); $winElo = $stmt->fetchColumn();
    $stmt->execute([$loserId]); $losElo = $stmt->fetchColumn();

    // Formule ELO (K=40 pour avoir ~20pts à niveau égal)
    $K = 40;
    $expectedWin = 1 / (1 + pow(10, ($losElo - $winElo) / 400));
    $eloChange = round($K * (1 - $expectedWin)); // Toujours positif pour le gagnant
    if ($eloChange < 1) $eloChange = 1; // Minimum 1 pt

    // Enregistrer le match
    $pdo->prepare("INSERT INTO matches (winner_id, loser_id, winner_elo_change, loser_elo_change, mode, score_winner, score_loser) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([$winnerId, $loserId, $eloChange, -$eloChange, $mode, $scoreWin, $scoreLose]);

    // Mettre à jour les utilisateurs (uniquement en ranked)
    if ($mode === 'ranked') {
        $pdo->prepare("UPDATE users SET elo = elo + ?, wins = wins + 1 WHERE id = ?")->execute([$eloChange, $winnerId]);
        $pdo->prepare("UPDATE users SET elo = elo - ?, losses = losses + 1 WHERE id = ?")->execute([$eloChange, $loserId]);
    }
}
?>