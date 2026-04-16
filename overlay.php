<?php
require_once 'assets/init_session.php';
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

// Mobile detection
$isMobile = preg_match("/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i", $_SERVER['HTTP_USER_AGENT']);
$bodyClass = $isMobile ? 'mobile-mode' : '';

if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT username, elo, grade FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} else {
    // If they are on a phone and NOT logged in, kick them to Discord instantly
    if ($isMobile) {
        // Change 'login.php' to whatever file handles your Discord OAuth
        // You may need to update your auth script to check for a ?redirect=overlay param so it sends them back here!
        header('Location: login.php?redirect=overlay');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo __('ov_title'); ?></title>
    <link rel="stylesheet" href="style-overlay.css?v=<?php echo filemtime(__DIR__ . '/style-overlay.css'); ?>">

    <?php if ($isMobile): ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
        
        <link rel="manifest" href="manifest.json">
        
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <?php endif; ?>
</head>
<body class="<?php echo $bodyClass; ?>">

<div id="overlay-wrapper" class="<?php echo $isMobile ? 'interactive' : ''; ?>">
    
    <div class="overlay-logo">
        <span>Secteur</span>
        <img src="assets/img/v.webp" alt="V">
    </div>

    <div id="panel-spotify" class="spotify-widget">
        <img id="spot-cover" src="assets/img/default_album.webp" alt="Cover">
        
        <div class="spot-info">
            <div class="spot-marquee-container">
                <div id="spot-title" class="spot-marquee"><?php echo __('ov_spotify_not_playing'); ?></div>
            </div>
            <div id="spot-artist" class="spot-artist"><?php echo __('ov_spotify_artist'); ?></div>
        </div>
        
        <div class="spot-controls">
            <button onclick="sendSpotifyAction('previous')">⏮</button>
            <button id="spot-playpause" onclick="sendSpotifyAction('playpause')">▶</button>
            <button onclick="sendSpotifyAction('next')">⏭</button>
        </div>
    </div>

    <div id="panel-error" class="panel" style="display: block;">
        <h3 style="margin: 0; color: #ff4444;"><?php echo __('ov_login_required_title'); ?></h3>
        <p style="margin: 5px 0 0 0; font-size: 14px;"><?php echo __('ov_login_required_desc'); ?></p>
    </div>

    <div id="panel-idle" class="panel">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img id="ui-avatar" src="" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #FFD700; object-fit: cover;">
                <div>
                    <h3 style="margin: 0; color: #FFD700;" id="ui-username"><?php echo __('ov_loading'); ?></h3>
                    <p style="margin: 5px 0 0 0;"><?php echo __('ov_elo'); ?> <span id="ui-elo">--</span></p>
                </div>
            </div>
            <div class="expand-arrow">▼</div>
        </div>
        
        <div class="idle-expandable">
            <div style="margin-top: 15px; display: flex; gap: 5px;">
                <select id="queue-mode-select" style="background: #222; color: #fff; border: 1px solid #555; padding: 5px; border-radius: 4px;">
                    <option value="ranked"><?php echo __('ov_ranked'); ?></option>
                    <option value="normal"><?php echo __('ov_normal'); ?></option>
                </select>
                <button onclick="sendJoinQueue()" style="background: #FFD700; color: #000; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px; font-weight: bold;">
                    <?php echo __('ov_enter_queue'); ?>
                </button>
            </div>
        </div>
    </div>

    <div id="panel-queue" class="panel">
        <h3 style="margin: 0; color: #00ffcc;"><?php echo __('ov_searching'); ?></h3>
        <div style="margin: 5px 0 0 0; display: flex; align-items: center; gap: 8px;">
            <p style="margin: 0;"><?php echo __('ov_players_searching'); ?> <span id="ui-queue-count">1</span></p>
            <div class="loader"></div> </div>
        <button onclick="sendAction('leave_queue')" style="margin-top: 10px; background: #ff4444; color: #fff; border: none; padding: 5px 10px; cursor: pointer;"><?php echo __('ov_cancel_search'); ?></button>
    </div>

    <div id="panel-match">
        
        <div class="match-scoreboard">
            <div class="scoreboard-teams">
                <div class="team-block">
                    <img id="match-my-avatar" class="team-avatar" src="">
                    <h4 id="match-my-name" class="team-name"><?php echo __('ov_player_1'); ?></h4>
                    <p class="team-elo"><?php echo __('ov_elo'); ?> <span id="match-my-elo">--</span></p>
                </div>
                <div class="vs-badge">VS</div>
                <div class="team-block">
                    <img id="ui-opponent-avatar" class="team-avatar" src="">
                    <h4 id="ui-opponent-name" class="team-name"><?php echo __('ov_player_2'); ?></h4>
                    <p class="team-elo"><?php echo __('ov_elo'); ?> <span id="ui-opponent-elo">--</span></p>
                </div>
            </div>


            <div class="score-expandable">
                <div class="score-inputs">
                    <span style="color: #aaa; font-size: 0.8rem; font-weight: bold;"><?php echo __('ov_your_score'); ?></span>
                    <input type="number" id="score-you" class="score-input-box" min="0" max="99">
                    <span style="color: #555; font-size: 1.5rem;">-</span>
                    <input type="number" id="score-opp" class="score-input-box" min="0" max="99">
                    <span style="color: #aaa; font-size: 0.8rem; font-weight: bold;"><?php echo __('ov_opp_score'); ?></span>
                    <button id="score-submit-btn" class="score-submit-btn" onclick="submitMatchScore()"><?php echo __('ov_confirm_score'); ?></button>
                </div>
                <p id="ui-score-status" style="font-size: 0.85rem; color: #ffcc00; display: none; text-align: center; margin-top: 5px; margin-bottom: 0;"><?php echo __('ov_score_submitted'); ?></p>
            </div>
        </div>

        <div class="match-chat">
            <div id="ui-chat-box" class="chat-messages">
                <div style="color: #888; font-style: italic; text-align: center; margin-top: 10px;"><?php echo __('ov_chat_connected'); ?></div>
            </div>
            <form class="chat-input-area" onsubmit="submitChat(event)">
                <input type="text" id="chat-input" class="chat-input-field" autocomplete="off" placeholder="<?php echo __('ov_chat_placeholder'); ?>" maxlength="64">
                <button type="submit" class="chat-submit-btn"><?php echo __('ov_chat_send'); ?></button>
            </form>
        </div>

    </div>

    <div id="panel-social" class="panel">
        <div class="social-nav">
            <button id="tab-dms" onclick="setSocialTab('dms')" class="social-tab-btn active">💬</button>
            <button id="tab-friends" onclick="setSocialTab('friends')" class="social-tab-btn">👥</button>
            <button id="tab-notifs" onclick="setSocialTab('notifs')" class="social-tab-btn">🔔</button>
        </div>
        
        <div id="social-chat-header" style="display:none; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,215,0,0.3); padding-bottom: 8px; margin-bottom: 8px;">
            <span id="social-chat-name" style="font-weight: bold; color: #fff; font-size: 0.95rem;"><?php echo __('ov_username'); ?></span>
            <button class="social-back-btn" style="margin-bottom: 0;" onclick="closeActiveDM()"><?php echo __('ov_back'); ?></button>
        </div>
        
        <div id="social-content" class="social-list">
            <div style="text-align: center; color: #888; font-size: 0.9rem; margin-top: 20px;"><?php echo __('ov_loading'); ?></div>
        </div>
        
        <form id="social-chat-form" class="chat-input-area" style="display:none; margin-top: auto; padding: 5px;" onsubmit="sendDMChat(event)">
            <input type="text" id="social-chat-input" class="chat-input-field" autocomplete="off" placeholder="<?php echo __('ov_dm_placeholder'); ?>" maxlength="255">
            <button type="submit" class="chat-submit-btn" style="padding: 0 8px;">></button>
        </form>
    </div>

    <div class="overlay-copyright"><?php echo __('ov_copyright'); ?></div>

    <div class="hint"><?php echo __('ov_hint'); ?></div>
</div>

<!-- Optional invisible video element to keep mobile devices awake while the overlay is open -->
<?php if ($isMobile): ?>
    <video id="nosleep-video" playsinline loop muted style="position:absolute; width:1px; height:1px;">
        <source src="assets/vid/blank.mp4" type="video/mp4">
    </video>
<?php endif; ?>

<script>
    window.langTexts = {
        readyToPlay: "<?php echo __('ov_ready_to_play'); ?>",
        mediaPlayer: "<?php echo __('ov_media_player'); ?>"
    };

    // Bridge PHP RPC Translations to JavaScript
    window.rpcTexts = {
        dashDetails: "<?php echo __('rpc_dash_details'); ?>",
        dashState: "<?php echo __('rpc_dash_state'); ?>",
        
        queueDetails: "<?php echo __('rpc_queue_details'); ?>",
        queueState: "<?php echo __('rpc_queue_state'); ?>",
        
        matchDetails: "<?php echo __('rpc_match_details'); ?>",
        matchState1: "<?php echo __('rpc_match_state1'); ?>",
        matchState2: "<?php echo __('rpc_match_state2'); ?>",
        
        profileDetails: "<?php echo __('rpc_profile_details'); ?>",
        profileState: "<?php echo __('rpc_profile_state'); ?>"
    };
</script>
<script src="overlay_engine.js?v=<?php echo filemtime(__DIR__ . '/overlay_engine.js'); ?>"></script>

</body>
</html>