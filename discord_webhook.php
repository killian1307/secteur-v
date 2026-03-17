<?php
require 'db.php';

// Charge le .env
try {
    loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    die("Erreur de configuration : " . $e->getMessage());
}

$logFile = 'discord_log.txt';
file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "--- NOUVELLE REQUÊTE ---\n", FILE_APPEND);

try {
    // Vérification de la clé
    $publicKey = $_ENV['DISCORD_PUBLIC_KEY'] ?? '';
    $publicKey = trim($publicKey);
    file_put_contents($logFile, "Clé publique récupérée. Longueur : " . strlen($publicKey) . " (Devrait être 64)\n", FILE_APPEND);

    if (empty($publicKey)) {
        file_put_contents($logFile, "ERREUR : Clé publique vide.\n", FILE_APPEND);
        http_response_code(401); exit;
    }

    // Vérification des Headers
    $signature = $_SERVER['HTTP_X_SIGNATURE_ED25519'] ?? '';
    $timestamp = $_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'] ?? '';
    file_put_contents($logFile, "Headers -> Signature: " . (empty($signature) ? "VIDE" : "OK") . " | Timestamp: " . (empty($timestamp) ? "VIDE" : "OK") . "\n", FILE_APPEND);

    if (empty($signature) || empty($timestamp)) {
        file_put_contents($logFile, "ERREUR : Headers manquants.\n", FILE_APPEND);
        http_response_code(401); exit;
    }

    // Récupération du body
    $body = file_get_contents('php://input');
    file_put_contents($logFile, "Body récupéré. Longueur : " . strlen($body) . "\n", FILE_APPEND);

    // Conversion Hexadécimale
    $binSignature = hex2bin($signature);
    $binKey = hex2bin($publicKey);

    if ($binSignature === false || $binKey === false) {
        file_put_contents($logFile, "ERREUR : Impossible de convertir la clé ou la signature en binaire (Caractères invalides ?).\n", FILE_APPEND);
        http_response_code(401); exit;
    }
    file_put_contents($logFile, "Conversion binaire OK.\n", FILE_APPEND);

    // Vérification Sodium
    $isValid = sodium_crypto_sign_verify_detached($binSignature, $timestamp . $body, $binKey);
    file_put_contents($logFile, "Résultat Sodium : " . ($isValid ? "VALIDE" : "INVALIDE") . "\n", FILE_APPEND);

    if (!$isValid) {
        http_response_code(401); exit;
    }

    // Traitement de la requête
    $request = json_decode($body, true);
    
    if ($request['type'] == 1) {
        file_put_contents($logFile, "PING DE DISCORD REÇU ET VALIDÉ !\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['type' => 1]);
        exit;
    }

    // Litige
    if ($request['type'] == 2 && $request['data']['name'] === 'resoudre-litige') {
        file_put_contents($logFile, "Commande /resoudre-litige reçue !\n", FILE_APPEND);
        
        $options = $request['data']['options'];
        $matchId = null; $winnerDiscordId = null; $loserDiscordId = null;
        
        foreach ($options as $opt) {
            if ($opt['name'] === 'match_id') $matchId = $opt['value'];
            if ($opt['name'] === 'vainqueur') $winnerDiscordId = $opt['value'];
            if ($opt['name'] === 'perdant') $loserDiscordId = $opt['value'];
            if ($opt['name'] === 'score_gagnant') $scoreGagnant = $opt['value'];
            if ($opt['name'] === 'score_perdant') $scorePerdant = $opt['value'];
        }

        $stmt = $pdo->prepare("SELECT * FROM litiges WHERE match_id = ?");
        $stmt->execute([$matchId]);
        $litige = $stmt->fetch();

        if (!$litige) {
            sendDiscordResponse("❌ Ce litige n'existe pas ou a déjà été résolu.");
            exit;
        }

        $stmtUsers = $pdo->prepare("SELECT id, discord_id FROM users WHERE discord_id IN (?, ?)");
        $stmtUsers->execute([$winnerDiscordId, $loserDiscordId]);
        $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        $winnerInternalId = null; $loserInternalId = null;
        foreach ($users as $u) {
            if ($u['discord_id'] === $winnerDiscordId) $winnerInternalId = $u['id'];
            if ($u['discord_id'] === $loserDiscordId) $loserInternalId = $u['id'];
        }

        if (!$winnerInternalId || !$loserInternalId) {
            sendDiscordResponse("❌ Impossible de trouver ces joueurs dans la DB.");
            exit;
        }

        $isWinnerP1 = ($litige['p1_id'] == $winnerInternalId);
        $eloGain = $isWinnerP1 ? $litige['p1_win_gain'] : $litige['p2_win_gain'];

        $winScore = $scoreGagnant;
        $loseScore = $scorePerdant;

        $matchMode = $litige['mode'] ?? 'ranked';

        $pdo->prepare("INSERT INTO matches (winner_id, loser_id, winner_elo_change, loser_elo_change, mode, score_winner, score_loser) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$winnerInternalId, $loserInternalId, $eloGain, -$eloGain, $matchMode, $winScore, $loseScore]);
        if ($matchMode === 'ranked') {
            $pdo->prepare("UPDATE users SET elo = elo + ?, wins = wins + 1 WHERE id = ?")->execute([$eloGain, $winnerInternalId]);
            $pdo->prepare("UPDATE users SET elo = elo - ?, losses = losses + 1 WHERE id = ?")->execute([$eloGain, $loserInternalId]);
            $messageDiscord = "✅ **Litige #$matchId résolu (Classé) !**\n<@$winnerDiscordId> remporte la victoire (`$winScore - $loseScore`) et gagne **+$eloGain ELO**.\nCe salon peut maintenant être supprimé.";
        } else {
            $messageDiscord = "✅ **Litige #$matchId résolu (Normal) !**\n<@$winnerDiscordId> remporte la victoire (`$winScore - $loseScore`).\nCe salon peut maintenant être supprimé.";
        }

        sendDiscordResponse($messageDiscord);
        $pdo->prepare("DELETE FROM litiges WHERE match_id = ?")->execute([$matchId]);
        exit;
    }

} catch (Throwable $e) { // Attrape les Exceptions et Erreurs
    file_put_contents($logFile, "CRASH FATAL LIGNE " . $e->getLine() . " : " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
}

function sendDiscordResponse($message) {
    header('Content-Type: application/json');
    echo json_encode(['type' => 4, 'data' => ['content' => $message]]);
}
?>