<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Récupération et contrôle du mode depuis l'URL
$validModes = ['ranked', 'normal'];
$mode = isset($_GET['mode']) && in_array($_GET['mode'], $validModes) ? $_GET['mode'] : 'ranked';
$otherMode = ($mode === 'ranked') ? 'normal' : 'ranked';

require 'assets/header.php';
require 'assets/footer.php';
$username = $_SESSION['username'];
$header = new Header("SECTEUR V - Matchmaking");
$header->render();
?>

<style>
    /* CSS Spécifique au Matchmaking */
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
        display: none; /* Caché par défaut */
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

    /* Versus Screen */
    .vs-screen { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: rgba(0,0,0,0.3); padding: 1.5rem; border-radius: 10px; }
    .player-box { width: 40%; }
    .player-box img { width: 80px; height: 80px; border-radius: 50%; border: 3px solid #fff; }
    .vs-text { font-size: 2.5rem; font-weight: 900; color: #e74c3c; font-style: italic; }

    /* Match Layout (Chat + Score) */
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

    /* Spinner */
    .spinner { width: 60px; height: 60px; border: 5px solid rgba(255,255,255,0.1); border-top: 5px solid var(--primary-purple); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
    @keyframes spin { 100% { transform: rotate(360deg); } }

    @media (max-width: 768px) {
        .match-layout { grid-template-columns: 1fr; }
        .vs-screen { flex-direction: column; gap: 15px; }
    }
</style>

<main class="dashboard-container">
    <h1>Arène <span style="color:var(--primary-purple)">Secteur V</span></h1>
    <p class="subtitle">Mode sélectionné : <strong style="text-transform:uppercase;"><?php echo $mode; ?></strong></p>

    <div class="mm-container">
        
        <div id="view-lobby" class="mm-view active">
            <h2>Prêt pour le match ?</h2>
            <p style="color:var(--text-secondary); margin-bottom: 2rem;">Trouvez un adversaire et prouvez votre valeur.</p>
            
            <button onclick="joinQueue()" class="btn-large <?php echo $mode === 'ranked' ? 'btn-ranked' : 'btn-normal'; ?>">
                <i class="fas fa-play"></i> Lancer la recherche
            </button>
            
            <a href="matchmaking.php?mode=<?php echo $otherMode; ?>" style="text-decoration:none;">
                <button class="btn-large btn-switch"><i class="fas fa-exchange-alt"></i> Jouer en mode <?php echo $otherMode; ?></button>
            </a>
            
            <a href="index.php" style="display:block; margin-top:2rem; color:var(--text-secondary);"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
        </div>

        <div id="view-queue" class="mm-view">
            <div class="spinner"></div>
            <h2>Recherche d'adversaire...</h2>
            <p>Temps écoulé : <span id="queue-time">00:00</span></p>
            <button onclick="leaveMatch()" class="btn-large btn-danger" style="max-width:200px;">Annuler</button>
        </div>

        <div id="view-match" class="mm-view">
            <div class="vs-screen">
                <div class="player-box">
                    <img src="<?php echo $_SESSION['avatar'] ?? 'assets/img/default_avatar.png'; ?>" alt="Moi">
                    <h3 style="margin-top:10px;"><?php echo htmlspecialchars($username); ?></h3>
                </div>
                <div class="vs-text">VS</div>
                <div class="player-box">
                    <img id="opp-avatar" src="" alt="Adversaire">
                    <h3 id="opp-name" style="margin-top:10px;">???</h3>
                    <p id="opp-elo" style="color:var(--text-secondary); font-size:0.9rem;"></p>
                </div>
            </div>

            <div class="match-layout">
                <div class="chat-box">
                    <div id="chat-messages" class="chat-messages">
                        <div style="color:var(--text-secondary); font-style:italic; text-align:center;">Le match commence !</div>
                    </div>
                    <form class="chat-input" onsubmit="sendChat(event)">
                        <input type="text" id="chat-input" placeholder="Message..." autocomplete="off">
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>

                <div class="score-box" id="score-section">
                    <h3 style="margin-bottom:15px;">Fin du match</h3>
                    
                    <div class="score-label">Mon Score</div>
                    <input type="number" id="my-score" class="score-input" min="0" required>
                    
                    <div class="score-label">Score Adversaire</div>
                    <input type="number" id="opp-score" class="score-input" min="0" required>
                    
                    <button onclick="submitScore()" class="btn-large btn-ranked" style="margin-top:0; border-radius:5px; font-size:1rem;">Valider</button>
                    
                    <button onclick="leaveMatch(true)" class="btn-large btn-danger" style="margin-top:10px; border-radius:5px; font-size:0.8rem; background:transparent; border:1px solid #e74c3c; color:#e74c3c;">Déclarer Forfait</button>
                </div>
            </div>
        </div>

        <div id="view-dispute" class="mm-view">
            <h2 style="color:#e74c3c;"><i class="fas fa-exclamation-triangle"></i> Litige détecté</h2>
            <p style="margin-bottom:20px;">Les scores rentrés ne correspondent pas. Veuillez fournir une preuve (Capture d'écran du résultat).</p>
            
            <form id="evidence-form" onsubmit="submitEvidence(event)" style="text-align:left; background:rgba(0,0,0,0.2); padding:20px; border-radius:10px;">
                <div class="score-label">Preuve (Image) *</div>
                <input type="file" id="evidence-file" accept="image/png, image/jpeg, image/webp" required style="margin-bottom:15px; width:100%;">
                
                <div class="score-label">Message d'explication (Optionnel)</div>
                <textarea id="evidence-message" rows="3" style="width:100%; background:transparent; border:1px solid rgba(255,255,255,0.2); color:#fff; padding:10px; border-radius:5px; margin-bottom:15px;"></textarea>
                
                <button type="submit" class="btn-large btn-ranked" style="width:100%; border-radius:5px;">Envoyer la preuve</button>
            </form>
            <p style="font-size:0.8rem; color:var(--text-secondary); margin-top:10px;">Ne quittez pas la page avant d'avoir envoyé votre preuve, sinon vous serez déclaré perdant par forfait.</p>
        </div>

    </div>
</main>

<script>
    const MODE = "<?php echo $mode; ?>";
    const MY_ID = <?php echo $_SESSION['user_id']; ?>;
    
    let currentState = 'lobby'; // lobby, searching, in_match, resolving, disputed
    let currentMatchId = null;
    let pollInterval = null;
    let secondsInQueue = 0;
    let hasSubmittedEvidence = false;

    // --- SÉCURITÉ : Anti-Ragequit / Changement de page ---
    window.addEventListener('beforeunload', (e) => {
        if (['searching', 'in_match', 'resolving', 'disputed'].includes(currentState) && !hasSubmittedEvidence) {
            // Signalement asynchrone instantané lors de la fermeture
            navigator.sendBeacon('api.php?action=leave_match'); 
            e.preventDefault();
            e.returnValue = ''; // Déclenche la popup native du navigateur
        }
    });

    // --- DÉMARRAGE : Vérifie si un match était déjà en cours (refresh) ---
    document.addEventListener('DOMContentLoaded', () => {
        pollServer(); // Un premier poll pour voir où on en est
    });

    // --- NAVIGATION DES VUES ---
    function setView(viewId) {
        document.querySelectorAll('.mm-view').forEach(v => v.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
    }

    // --- ACTIONS UTILISATEUR ---
    async function joinQueue() {
        setView('view-queue');
        currentState = 'searching';
        secondsInQueue = 0;
        
        await fetch(`api.php?action=join_queue&mode=${MODE}`);
        
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(pollServer, 2000); // Poll toutes les 2s
    }

    async function leaveMatch(isForfeit = false) {
        if (isForfeit && !confirm("Voulez-vous vraiment déclarer forfait ? Vous perdrez ce match (0-3).")) return;
        
        clearInterval(pollInterval);
        await fetch(`api.php?action=leave_match`);
        window.location.href = 'index.php'; // Retour à l'accueil
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
        pollServer(); // Force un rafraichissement immédiat du chat
    }

    async function submitScore() {
        const myScore = document.getElementById('my-score').value;
        const oppScore = document.getElementById('opp-score').value;
        
        if (myScore === '' || oppScore === '') { alert("Veuillez remplir les deux scores."); return; }
        
        document.getElementById('score-section').innerHTML = '<h3 style="color:#f1c40f;">En attente de l\'adversaire...</h3><p>Ne quittez pas la page.</p>';
        currentState = 'resolving';

        await fetch('api.php?action=submit_score', {
            method: 'POST',
            body: JSON.stringify({ match_id: currentMatchId, my_score: myScore, opp_score: oppScore })
        });
        pollServer();
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
            body: formData // Pas de Content-Type manuel pour FormData (le navigateur le gère)
        });
        
        const data = await res.json();
        if (data.success) {
            hasSubmittedEvidence = true; // Débloque le beforeunload
            clearInterval(pollInterval);
            alert("Preuve envoyée. Le litige sera examiné par l'équipe. Vous pouvez quitter cette page.");
            window.location.href = 'index.php';
        }
    }

    // --- LE COEUR : BOUCLE DE POLLING ---
    async function pollServer() {
        if (currentState === 'lobby' && document.getElementById('view-lobby').classList.contains('active')) return;

        try {
            const res = await fetch(`api.php?action=poll_match&mode=${MODE}`);
            const data = await res.json();

            // 1. Recherche en cours
            if (data.state === 'searching') {
                secondsInQueue += 2;
                const m = Math.floor(secondsInQueue / 60).toString().padStart(2, '0');
                const s = (secondsInQueue % 60).toString().padStart(2, '0');
                document.getElementById('queue-time').innerText = `${m}:${s}`;
            }
            
            // 2. Adversaire Déconnecté (Victoire auto)
            else if (data.state === 'opponent_left') {
                clearInterval(pollInterval);
                currentState = 'lobby'; // Débloque le leave
                alert("L'adversaire a quitté la partie (Forfait). Vous gagnez !");
                window.location.href = 'index.php';
            }

            // 3. Match en cours (Initialisation et MAJ Tchat)
            else if (data.state === 'in_match' || data.state === 'match_found') {
                currentMatchId = data.match_id;
                
                // Si on vient d'entrer dans le match
                if (currentState !== 'in_match' && currentState !== 'resolving') {
                    currentState = 'in_match';
                    setView('view-match');
                    document.getElementById('opp-name').innerText = data.opponent.username;
                    document.getElementById('opp-elo').innerText = "Elo: " + data.opponent.elo;
                    document.getElementById('opp-avatar').src = data.opponent.avatar || 'assets/img/default_avatar.png';
                }

                // Maj du statut interne si passage en resolving/disputed via l'adversaire
                if (data.status === 'disputed' && currentState !== 'disputed') {
                    currentState = 'disputed';
                    setView('view-dispute');
                }

                // Maj du tchat
                if (data.chat && currentState !== 'disputed') {
                    const chatBox = document.getElementById('chat-messages');
                    chatBox.innerHTML = '';
                    data.chat.forEach(c => {
                        const isMe = (c.sender_id == data.my_id);
                        const color = isMe ? 'var(--primary-purple)' : '#2ecc71';
                        const name = isMe ? 'Moi' : data.opponent.username;
                        chatBox.innerHTML += `<div class="chat-msg"><strong style="color:${color}">${name}</strong> <span style="font-size:0.7rem; color:#888;">${c.time}</span><br>${c.message}</div>`;
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            }
            
            // 4. Match Terminé avec Accord
            else if (data.state === 'finished_agreement') {
                clearInterval(pollInterval);
                currentState = 'lobby';
                alert("Match validé ! Scores correspondants. Retour à l'accueil.");
                window.location.href = 'index.php';
            }
            
            // 5. Litige
            else if (data.state === 'disputed') {
                currentState = 'disputed';
                setView('view-dispute');
            }

        } catch(e) {
            console.error("Erreur Polling", e);
        }
    }
</script>

<?php
$footer = new Footer();
$footer->render();
?>