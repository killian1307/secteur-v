<?php
require_once 'assets/init_session.php';
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT username, elo, grade FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Secteur V Overlay</title>
    <link rel="stylesheet" href="style-overlay.css">
</head>
<body>

<div id="overlay-wrapper">
    

        <div id="panel-error" class="panel" style="display: block;">
            <h3 style="margin: 0; color: #ff4444;">Login Required</h3>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Please log in to the main client.</p>
        </div>

    <div id="panel-idle" class="panel">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img id="ui-avatar" src="" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #FFD700; object-fit: cover;">
            <div>
                <h3 style="margin: 0; color: #FFD700;" id="ui-username">Loading...</h3>
                <p style="margin: 5px 0 0 0;">ELO: <span id="ui-elo">--</span></p>
            </div>
        </div>
        
        <div style="margin-top: 15px; display: flex; gap: 5px;">
            <select id="queue-mode-select" style="background: #222; color: #fff; border: 1px solid #555; padding: 5px; border-radius: 4px;">
                <option value="ranked">Ranked</option>
                <option value="normal">Normal</option>
            </select>
            <button onclick="sendJoinQueue()" style="background: #FFD700; color: #000; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px; font-weight: bold;">
                Enter Queue
            </button>
        </div>
    </div>

    <div id="panel-queue" class="panel">
        <h3 style="margin: 0; color: #00ffcc;">Searching for Opponent...</h3>
        <div style="margin: 5px 0 0 0; display: flex; align-items: center; gap: 8px;">
            <p style="margin: 0;">Players searching: <span id="ui-queue-count">1</span></p>
            <div class="loader"></div> </div>
        <button onclick="sendAction('leave_queue')" style="margin-top: 10px; background: #ff4444; color: #fff; border: none; padding: 5px 10px; cursor: pointer;">Cancel Search</button>
    </div>

    <div id="panel-match">
        
        <div class="match-scoreboard">
            <div class="scoreboard-teams">
                <div class="team-block">
                    <img id="match-my-avatar" class="team-avatar" src="">
                    <h4 id="match-my-name" class="team-name">Player 1</h4>
                    <p class="team-elo">ELO: <span id="match-my-elo">--</span></p>
                </div>
                <div class="vs-badge">VS</div>
                <div class="team-block">
                    <img id="ui-opponent-avatar" class="team-avatar" src="">
                    <h4 id="ui-opponent-name" class="team-name">Player 2</h4>
                    <p class="team-elo">ELO: <span id="ui-opponent-elo">--</span></p>
                </div>
            </div>

            <div class="score-inputs">
                <span style="color: #aaa; font-size: 0.8rem; font-weight: bold;">YOUR SCORE</span>
                <input type="number" id="score-you" class="score-input-box" min="0" max="99">
                <span style="color: #555; font-size: 1.5rem;">-</span>
                <input type="number" id="score-opp" class="score-input-box" min="0" max="99">
                <span style="color: #aaa; font-size: 0.8rem; font-weight: bold;">OPP SCORE</span>
                <button id="score-submit-btn" class="score-submit-btn" onclick="submitMatchScore()">Confirm</button>
            </div>
            <p id="ui-score-status" style="font-size: 0.85rem; color: #ffcc00; display: none; text-align: center; margin-top: 5px; margin-bottom: 0;">Score submitted. Waiting for opponent...</p>
        </div>

        <div class="match-chat">
            <div id="ui-chat-box" class="chat-messages">
                <div style="color: #888; font-style: italic; text-align: center; margin-top: 10px;">Live chat connected...</div>
            </div>
            <form class="chat-input-area" onsubmit="submitChat(event)">
                <input type="text" id="chat-input" class="chat-input-field" autocomplete="off" placeholder="Type message..." maxlength="64">
                <button type="submit" class="chat-submit-btn">Send</button>
            </form>
        </div>

    </div>

    <div class="hint">Press SHIFT + TAB to interact with Secteur V</div>
</div>

<script src="overlay_engine.js"></script>

</body>
</html>