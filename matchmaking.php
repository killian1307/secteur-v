<?php
require_once 'assets/init_session.php';
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

// Récupération et contrôle du mode depuis l'URL
$validModes = ['ranked', 'normal'];
$mode = isset($_GET['mode']) && in_array($_GET['mode'], $validModes) ? $_GET['mode'] : 'ranked';
$otherMode = ($mode === 'ranked') ? 'normal' : 'ranked';

require 'assets/header.php';
require 'assets/footer.php';
$username = $_SESSION['username'];

// Récupération de l'Elo pour l'affichage
$stmtElo = $pdo->prepare("SELECT elo FROM users WHERE id = ?");
$stmtElo->execute([$_SESSION['user_id']]);
$myElo = $stmtElo->fetchColumn();

$header = new Header(__('mm_title'));
$header->render();
?>

<style>
    .mm-container {
        max-width: 900px;
        margin: 2rem auto;
        min-height: 60vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .mm-view {
        width: 100%;
        background: var(--background-card);
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.05);
        display: none;
        text-align: center;
    }
    .mm-view.active { display: block; animation: fadeIn 0.3s; }
    
    @keyframes fadeIn { from{opacity:0; transform:translateY(-10px);} to{opacity:1; transform:translateY(0);} }

    .btn-large {
        padding: 1rem 2rem; font-size: 1.2rem; font-weight: bold; border: none; border-radius: 8px;
        cursor: pointer; text-transform: uppercase; width: 100%; max-width: 400px; margin: 10px auto;
        display: flex; justify-content: center; align-items: center; gap: 10px;
    }
    .btn-ranked { background: linear-gradient(135deg, #FFD700, #FFA500); color: #000; }
    .btn-normal { background: linear-gradient(135deg, #3498db, #2980b9); color: #fff; }
    .btn-danger { background: #e74c3c; color: #fff; margin-top: 20px; }
    .btn-switch { background: transparent; color: var(--text-secondary); border: 1px solid var(--text-secondary); margin-top:1rem;}

    /* Ecran versus */
    .vs-screen { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: rgba(0,0,0,0.3); padding: 1.5rem; border-radius: 10px; }
    .player-box { width: 40%; }
    .player-box img { width: 80px; height: 80px; border-radius: 50%; border: 3px solid #fff; }
    .vs-text { font-size: 2.5rem; font-weight: 900; color: #e74c3c; font-style: italic; }

    /* Layout en match */
    .match-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    
    .chat-box { background: rgba(0,0,0,0.2); height: 350px; border-radius: 10px; display: flex; flex-direction: column; }
    .chat-messages { flex-grow: 1; padding: 15px; overflow-y: auto; text-align: left; }
    .chat-msg { margin-bottom: 8px; line-height: 1.4; }
    .chat-input { display: flex; border-top: 1px solid rgba(255,255,255,0.1); }
    .chat-input input { flex-grow: 1; padding: 12px; background: transparent; border: none; color: #fff; }
    .chat-input button { padding: 0 20px; background: var(--primary-purple); border: none; cursor: pointer; color:#000; font-weight:bold;}

    .score-box { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 10px; display: flex; flex-direction: column; justify-content: center;}
    .score-input { width: 100%; padding: 10px; font-size: 1.5rem; text-align: center; margin-bottom: 15px; border-radius: 5px; border: none; }
    .score-label { text-align: left; font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 5px; text-transform:uppercase;}

    /* Recherche */
    .spinner { width: 60px; height: 60px; border: 5px solid rgba(255,255,255,0.1); border-top: 5px solid var(--primary-purple); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    @media (max-width: 768px) {
        .match-layout { grid-template-columns: 1fr; }
        .vs-screen { flex-direction: column; gap: 15px; }
    }

    .queue-status {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.05);
        padding: 8px 18px;
        border-radius: 20px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        font-weight: bold;
        color: var(--text-secondary);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .live-dot {
        width: 10px;
        height: 10px;
        background-color: #2ecc71;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 10px #2ecc71;
        animation: pulseLive 1.5s infinite;
    }
    
    @keyframes pulseLive {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(46, 204, 113, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }

    .discord_link {
        text-decoration: none !important;
    }

    .subtitle-match {
        margin-bottom: 0.5rem !important;
    }

    h4 {
        margin-top: 1.5rem;
        color: var(--primary-purple);
    }

    i.rulespopup {
        color: var(--primary-purple);
        padding-right: 10px;
    }

    .retour-accueil {
        display: inline-block;
        margin-top: 0.5rem;
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.3s;
    }

    .retour-accueil:hover {
        text-decoration: underline;
    }
</style>

<main class="dashboard-container">
    <h1><?php echo __('mm_h1_1'); ?> <span style="color:var(--primary-purple)"><?php echo __('mm_h1_2'); ?></span></h1>
    <p class="subtitle subtitle-match"><?php echo __('mm_mode_selected'); ?> <strong style="text-transform:uppercase;"><?php echo __('dash_tab_' . $mode); ?></strong></p>

    <div class="mm-container">
        
        <div id="view-lobby" class="mm-view active">
            <h2><?php echo __('mm_ready'); ?></h2>
            <p style="color:var(--text-secondary); margin-bottom: 2rem;"><?php echo __('mm_ready_desc'); ?></p>
            
            <div class="queue-status">
                <span class="live-dot"></span>
                <span id="queue-count-text"><?php echo __('mm_searching'); ?></span>
            </div>

            <button onclick="joinQueue()" class="btn-large <?php echo $mode === 'ranked' ? 'btn-ranked' : 'btn-normal'; ?>">
                <i class="fas fa-play"></i> <?php echo __('mm_start_btn'); ?>
            </button>
            
            <a href="matchmaking.php?mode=<?php echo $otherMode; ?>" style="text-decoration:none;">
                <button class="btn-large btn-switch"><i class="fas fa-exchange-alt"></i> <?php echo __('mm_play_mode'); ?> <?php echo __('dash_tab_' . $otherMode); ?></button>
            </a>

        <div style="margin-top: 2rem;">
            <a href="#" class="retour-accueil" onclick="document.getElementById('match-rules-modal').classList.add('active');"><i class="fas fa-book"></i> <?php echo __('mm_review_tutorial'); ?></a>
            |
            <a href="/" class="retour-accueil"><i class="fas fa-arrow-left"></i> <?php echo __('mm_back_home'); ?></a>
        </div>
        </div>

        <div id="view-queue" class="mm-view">
            <div class="spinner"></div>
            <h2><?php echo __('mm_searching_opp'); ?></h2>
            <p><?php echo __('mm_elapsed'); ?> <span id="queue-time">00:00</span></p>
            <button onclick="leaveMatch()" class="btn-large btn-danger" style="max-width:200px;"><?php echo __('mm_cancel'); ?></button>
        </div>

        <div id="view-match" class="mm-view">
            <div class="vs-screen">
                <div class="player-box">
                    <img src="<?php echo $_SESSION['avatar'] ?? 'assets/img/default_user.webp'; ?>" alt="<?php echo __('mm_me'); ?>">
                    <h3 style="margin-top:10px;"><?php echo htmlspecialchars($username); ?></h3>
                    <p style="color:var(--text-secondary); font-size:0.9rem;"><?php echo __('mm_elo'); ?> <?php echo $myElo; ?></p>
                </div>
                <div class="vs-text">VS</div>
                <div class="player-box">
                    <img id="opp-avatar" src="" alt="<?php echo __('mm_opp'); ?>">
                    <h3 id="opp-name" style="margin-top:10px;">???</h3>
                    <p id="opp-elo" style="color:var(--text-secondary); font-size:0.9rem;"></p>
                </div>
            </div>

            <div class="match-layout">
                <div class="chat-box">
                    <div id="chat-messages" class="chat-messages">
                        <div style="color:var(--text-secondary); font-style:italic; text-align:center;"><?php echo __('mm_match_starts'); ?></div>
                    </div>
                    <form class="chat-input" onsubmit="sendChat(event)">
                        <input type="text" id="chat-input" placeholder="<?php echo __('mm_chat_placeholder'); ?>" autocomplete="off" maxlength="64">
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>

                <div class="score-box" id="score-section">
                    <h3 style="margin-bottom:15px;"><?php echo __('mm_end_match'); ?></h3>
                    
                    <div class="score-label"><?php echo __('mm_my_score'); ?></div>
                    <input type="number" id="my-score" class="score-input" min="0" required>
                    
                    <div class="score-label"><?php echo __('mm_opp_score'); ?></div>
                    <input type="number" id="opp-score" class="score-input" min="0" required>
                    
                    <button onclick="submitScore()" class="btn-large btn-ranked" style="margin-top:0; border-radius:5px; font-size:1rem;"><?php echo __('mm_validate'); ?></button>
                    
                    <button onclick="leaveMatch(true)" class="btn-large btn-danger" style="margin-top:10px; border-radius:5px; font-size:0.8rem; background:transparent; border:1px solid #e74c3c; color:#e74c3c;"><?php echo __('mm_forfeit'); ?></button>
                </div>
            </div>
        </div>

        <div id="view-dispute" class="mm-view">
            <h2 style="color:#e74c3c;"><i class="fas fa-exclamation-triangle"></i> <?php echo __('mm_dispute_h2'); ?></h2>
            <p style="margin-bottom:20px;"><?php echo __('mm_dispute_p1'); ?></p>
            
            <div style="background:rgba(0,0,0,0.2); padding:20px; border-radius:10px; margin-bottom: 20px;">
                <p><?php echo __('mm_dispute_p2'); ?></p>
                <p><?php echo __('mm_dispute_p3'); ?></p>
            </div>
            
            <a class="discord_link" href="https://discord.gg/85AT6gGNGD" target="_blank">
                <button class="btn-large" style="background:#5865F2; color:#fff; width:100%;">
                    <i class="fab fa-discord"></i> <?php echo __('mm_open_ticket'); ?>
                </button>
            </a>
            
            <button onclick="window.location.href='/'" class="btn-large btn-switch" style="width:100%;"><?php echo __('mm_back_home'); ?></button>
        </div>

    </div>
</main>

<!-- Popup à l'ouverture de la page pour la première fois -->
 <div id="match-rules-modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header" style="color: var(--background-main);">
            <i class="fas fa-info-circle" style="color: var(--background-main);"></i> <?php echo __('mm_rules_title'); ?>
        </div>
        <div class="modal-body" style="text-align:justify;">
            <p><?php echo __('mm_rules_intro'); ?></p>
                
                    
                    <h4><i class="fas fa-gamepad rulespopup"></i><?php echo __('mm_rules_h4_1'); ?></h4>
                    <p><?php echo __('mm_rules_p_1'); ?></p>
                    <p style="padding-top: 10px;"><strong><i class="fas fa-arrow-right rulespopup"></i><?php echo __('mm_rules_p_2_strong'); ?></strong> <?php echo __('mm_rules_p_2'); ?></p>

                    <h4><i class="fas fa-trophy rulespopup"></i><?php echo __('mm_rules_h4_2'); ?></h4>
                    <p><?php echo __('mm_rules_p_3'); ?></p>

                    <h4><i class="fas fa-exclamation-triangle rulespopup"></i><?php echo __('mm_rules_h4_3'); ?></h4>
                    <p><?php echo __('mm_rules_p_4'); ?></p>
            
            <div class="links-row">
                <a href="rules.php" target="_blank" class="text-link"><?php echo __('mm_rules_link'); ?></a>
            </div>
        </div>
        <div class="modal-footer">
            <button class="other-button" onclick="closeRulesModal()" style="margin: 0 auto; width: 100%; justify-content: center; color: var(--background-main);"><?php echo __('mm_rules_btn'); ?></button>
        </div>
    </div>
</div>

<!-- Son de notification pour les nouveaux messages -->
<audio id="chat-sound" src="assets/sounds/notification.wav" preload="auto"></audio>

<script>
    const MODE = "<?php echo $mode; ?>";
    const MY_ID = "<?php echo $_SESSION['user_id']; ?>";
    
    let currentState = 'lobby'; // lobby, searching, in_match, resolving, disputed
    let currentMatchId = null;
    let pollInterval = null;
    let secondsInQueue = 0;
    let hasSubmittedEvidence = false;

    // Anti-Ragequit
    window.addEventListener('beforeunload', (e) => {
        if (['searching', 'in_match', 'resolving', 'disputed'].includes(currentState) && !hasSubmittedEvidence) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Si l'utilisateur qui la page
    window.addEventListener('pagehide', (e) => {
        if (['searching', 'in_match', 'resolving', 'disputed'].includes(currentState) && !hasSubmittedEvidence) {
            navigator.sendBeacon('api.php?action=leave_match'); 
        }
    });

    // Vérifie si un match était déjà en cours
    document.addEventListener('DOMContentLoaded', () => {
        pollServer();
    });

    // Navigation entre vues
    function setView(viewId) {
        document.querySelectorAll('.mm-view').forEach(v => v.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
    }

    // Actions utilisateur
    async function joinQueue() {
        setView('view-queue');
        currentState = 'searching';
        secondsInQueue = 0;
        
        await fetch(`api.php?action=join_queue&mode=${MODE}`);
        
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(pollServer, 1000); // Poll toutes les secondes
    }

    async function leaveMatch(isForfeit = false) {
        if (isForfeit && !confirm("<?php echo addslashes(__('mm_forfeit_confirm')); ?>")) return;
        
        clearInterval(pollInterval);
        await fetch(`api.php?action=leave_match`);
        window.location.href = '/';
    }

    async function sendChat(e) {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const msg = input.value.trim();
        if (!msg || !currentMatchId) return;

        input.value = '';
        await fetch('api.php?action=send_chat', {
            method: 'POST',
            body: JSON.stringify({ match_id: currentMatchId, message: msg })
        });
        pollServer();
    }

    async function submitScore() {
        const myScore = document.getElementById('my-score').value;
        const oppScore = document.getElementById('opp-score').value;
        
        if (myScore === '' || oppScore === '') { alert("<?php echo addslashes(__('mm_fill_scores')); ?>"); return; }
        
        document.getElementById('score-section').innerHTML = '<h3 style="color:#f1c40f;">' + "<?php echo addslashes(__('mm_waiting_opp_title')); ?>" + '</h3><p>' + "<?php echo addslashes(__('mm_waiting_opp_desc')); ?>" + '</p>';
        currentState = 'resolving';

        const res = await fetch('api.php?action=submit_score', {
            method: 'POST',
            body: JSON.stringify({ match_id: currentMatchId, my_score: myScore, opp_score: oppScore })
        });
        const data = await res.json();
        
        // On analyse la réponse de notre soumission
        if (data.state === 'finished_agreement') {
            clearInterval(pollInterval);
            currentState = 'lobby';
            alert("<?php echo addslashes(__('mm_match_validated')); ?>");
            window.location.href = '/';
        } else if (data.state === 'disputed') {
            currentState = 'disputed';
            setView('view-dispute');
        }
    }

    async function submitEvidence(e) {
        e.preventDefault();
        const fileInput = document.getElementById('evidence-file');
        const msgInput = document.getElementById('evidence-message');
        
        if (!fileInput.files[0]) return;

        const formData = new FormData();
        formData.append('match_id', currentMatchId);
        formData.append('evidence', fileInput.files[0]);
        formData.append('message', msgInput.value);

        const res = await fetch('api.php?action=submit_evidence', {
            method: 'POST',
            body: formData
        });
        
        const data = await res.json();
        if (data.success) {
            hasSubmittedEvidence = true;
            clearInterval(pollInterval);
            alert("<?php echo addslashes(__('mm_evidence_sent')); ?>");
            window.location.href = '/';
        }
    }

    let previousMessageCount = 0;

    // Boucle de polling
    async function pollServer() {
        if (currentState === 'lobby' && document.getElementById('view-lobby').classList.contains('active')) return;

        try {
            const res = await fetch(`api.php?action=poll_match&mode=${MODE}`);
            const data = await res.json();

            // Recherche en cours
            if (data.state === 'searching') {
                secondsInQueue += 1;
                const m = Math.floor(secondsInQueue / 60).toString().padStart(2, '0');
                const s = (secondsInQueue % 60).toString().padStart(2, '0');
                document.getElementById('queue-time').innerText = `${m}:${s}`;
                
                // --- LOG DE L'ALGORITHME DE RECHERCHE ---
                if (data.debug_radius !== undefined) {
                    console.log(`[Recherche] Mon ELO: ${data.debug_elo} | Écart toléré: +/- ${data.debug_radius} (Cibles potentielles: ${data.debug_elo - data.debug_radius} à ${data.debug_elo + data.debug_radius})`);
                }
            }
            
            // Adversaire Déconnecté
            else if (data.state === 'opponent_left') {
                clearInterval(pollInterval);
                currentState = 'lobby';
                alert("<?php echo addslashes(__('mm_opp_left')); ?>");
                window.location.href = '/';
            }

            // Match en cours
            else if (data.state === 'in_match') {
                currentMatchId = data.match_id;
                
                if (currentState !== 'in_match' && currentState !== 'resolving') {
                    currentState = 'in_match';
                    setView('view-match');
                    document.getElementById('opp-name').innerText = data.opponent.username;
                    document.getElementById('opp-elo').innerText = "<?php echo addslashes(__('mm_elo')); ?> " + data.opponent.elo;
                    document.getElementById('opp-avatar').src = data.opponent.avatar || 'assets/img/default_user.webp';
                }

                if (data.status === 'disputed' && currentState !== 'disputed') {
                    currentState = 'disputed';
                    setView('view-dispute');
                }

                if (data.chat && currentState !== 'disputed') {
                    const chatBox = document.getElementById('chat-messages');
                    chatBox.innerHTML = '';
                        data.chat.forEach(c => {
                            const isMe = (c.sender_id == data.my_id);
                            const color = isMe ? 'var(--primary-purple)' : '#2ecc71';
                            const name = isMe ? "<?php echo addslashes(__('mm_me')); ?>" : data.opponent.username;
                            chatBox.innerHTML += `<div class="chat-msg"><strong style="color:${color}">${name}</strong> <span style="font-size:0.7rem; color:#888;">${c.time}</span><br>${c.message}</div>`;
                        });

                        const newMessageCount = chatBox.childElementCount;

                        // Joue un son à l'arrivée d'un nouveau message et scroll vers le bas
                        if (newMessageCount > previousMessageCount) {
                            chatBox.scrollTop = chatBox.scrollHeight;
                            const lastMessage = chatBox.lastElementChild;
                            const isMyOwnMessage = lastMessage ? lastMessage.textContent.includes("<?php echo addslashes(__('mm_me')); ?>") : false;
                            if (!isMyOwnMessage) {
                                let sound = document.getElementById('chat-sound');
                                sound.play().catch(e => console.log("Son bloqué par le navigateur", e)); 
                            }
                            previousMessageCount = newMessageCount;
                        }
                }
            }
            
            // Match Terminé avec Accord Mutuel
            else if (data.state === 'finished_agreement') {
                clearInterval(pollInterval);
                currentState = 'lobby';
                alert("<?php echo addslashes(__('mm_match_validated_opp')); ?>");
                window.location.href = '/';
            }
            
            // Litige
            else if (data.state === 'disputed') {
                currentState = 'disputed';
                setView('view-dispute');
            }

            // Si le match a été clôturé de force
            else if (data.state === 'lobby') {
                if (['in_match', 'resolving'].includes(currentState)) {
                    clearInterval(pollInterval);
                    alert("<?php echo addslashes(__('mm_match_closed')); ?>");
                    window.location.href = '/';
                }
            }

        } catch(e) {
            console.error("Erreur Polling", e);
        }
    }

    // Compteur de file d'attente
    async function fetchQueueCount() {
        if (currentState !== 'lobby') return; 
        
        try {
            const res = await fetch(`api.php?action=get_queue_count&mode=${MODE}`);
            const data = await res.json();
            
            if (data.success) {
                const pluriel = data.count > 1 ? "s" : "";
                const textTpl = "<?php echo addslashes(__('mm_queue_count_js')); ?>";
                document.getElementById('queue-count-text').innerText = textTpl.replace('%d', data.count).replace('%s', pluriel);
            }
        } catch(e) {
            console.error("Erreur maj compteur", e);
        }
    }

    // Empêche les téléphones de se mettre en veille
    let wakeLock = null;

    async function requestWakeLock() {
        try {
            // Vérifie si le navigateur supporte cette fonctionnalité
            if ('wakeLock' in navigator) {
                wakeLock = await navigator.wakeLock.request('screen');
                console.log('Wake Lock activé : l\'écran restera allumé.');

                // Si le système annule le lock
                wakeLock.addEventListener('release', () => {
                    console.log('Wake Lock relâché par le système.');
                });
            }
        } catch (err) {
            console.error(`Impossible d'activer le Wake Lock : ${err.name}, ${err.message}`);
        }
    }

    // Si changement d'onglet ou de visibilité, tente de réactiver le Wake Lock
    document.addEventListener('visibilitychange', async () => {
        if (wakeLock !== null && document.visibilityState === 'visible') {
            await requestWakeLock();
        }
    });

    function closeRulesModal() {
        document.getElementById('match-rules-modal').classList.remove('active');
        localStorage.setItem('hasSeenMatchRules', 'true');
    }

    // Lance dès le chargement de la page
    document.addEventListener('DOMContentLoaded', () => {
        pollServer(); 
        fetchQueueCount();
        setInterval(fetchQueueCount, 5000); // Boucle toutes les 5s

        // Affiche le modal de règles si c'est la première visite
        if (!localStorage.getItem('hasSeenMatchRules')) {
            document.getElementById('match-rules-modal').classList.add('active');
        }
    });
</script>

<?php
$footer = new Footer();
$footer->render();
?>

</body>
</html>