<?php
class Dashboard {

    private $pdo;
    private $user;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;

        if (isset($_SESSION['user_id'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $this->user = $stmt->fetch();
        }
    }

    public function render() {
        if (!$this->user) return; // Sécurité

        $userId = $this->user['id'];
        $username = htmlspecialchars($this->user['username']);
        $wins = $this->user['wins'];
        $losses = $this->user['losses'];
        $elo = $this->user['elo'];
        $grade = htmlspecialchars($this->user['grade']);

        // Membres en ligne
        $stmtOnline = $this->pdo->query("SELECT COUNT(*) FROM users WHERE last_activity >= NOW() - INTERVAL 5 MINUTE");
        $onlineCount = $stmtOnline->fetchColumn();
        $pluriel = $onlineCount > 1 ? "s" : "";
        
        // Calcul du Winrate
        $totalMatches = $wins + $losses;
        $winrate = $totalMatches > 0 ? round(($wins / $totalMatches) * 100) : 0;

        // Récupère ID, Username, ELO et Avatar triés par ELO
        $stmtRank = $this->pdo->query("SELECT id, username, elo, avatar FROM users ORDER BY elo DESC");
        $allPlayers = $stmtRank->fetchAll(PDO::FETCH_ASSOC);

        // Récupération de l'historique
        $sqlHistory = "
            (SELECT m.*, 
                    COALESCE(w.username, '" . __('dash_deleted_user') . "') AS winner_name, 
                    COALESCE(l.username, '" . __('dash_deleted_user') . "') AS loser_name 
             FROM matches m 
             LEFT JOIN users w ON m.winner_id = w.id 
             LEFT JOIN users l ON m.loser_id = l.id 
             WHERE (m.winner_id = ? OR m.loser_id = ?) AND m.mode = 'ranked' 
             ORDER BY m.match_date DESC 
             LIMIT 10)
            
            UNION ALL
            
            (SELECT m.*, 
                    COALESCE(w.username, '" . __('dash_deleted_user') . "') AS winner_name, 
                    COALESCE(l.username, '" . __('dash_deleted_user') . "') AS loser_name 
             FROM matches m 
             LEFT JOIN users w ON m.winner_id = w.id 
             LEFT JOIN users l ON m.loser_id = l.id 
             WHERE (m.winner_id = ? OR m.loser_id = ?) AND m.mode = 'normal' 
             ORDER BY m.match_date DESC 
             LIMIT 10)
             
            ORDER BY match_date DESC
        ";
        
        $stmtHist = $this->pdo->prepare($sqlHistory);
        $stmtHist->execute([$userId, $userId, $userId, $userId]);
        $history = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <main class="dashboard-main">
            
            <style>
                .beta-banner {
                    width: 100%;
                    background: linear-gradient(90deg, rgba(255, 215, 0, 0.05), rgba(255, 215, 0, 0.15), rgba(255, 215, 0, 0.05));
                    border-bottom: 1px solid rgba(255, 215, 0, 0.2);
                    padding: 10px 0;
                    overflow: hidden;
                    white-space: nowrap;
                    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.05);
                    margin-bottom: 2rem;
                }

                .beta-banner-content {
                    display: inline-block;
                    padding-left: 100%;
                    animation: scrollText 25s linear infinite;
                    color: var(--text-primary);
                    font-size: 0.95rem;
                }

                .beta-banner-content strong {
                    color: #FFD700;
                    margin-right: 5px;
                }

                .beta-banner-content i {
                    color: #e74c3c;
                    margin: 0 5px;
                }

                @keyframes scrollText {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-100%); }
                }

                /* Met l'animation en pause quand on passe la souris dessus pour pouvoir lire tranquillement */
                .beta-banner:hover .beta-banner-content {
                    animation-play-state: paused;
                }
            </style>

            <div class="beta-banner">
                <div class="beta-banner-content">
                    <strong>🚧 <?php echo __('dash_beta_title'); ?></strong> <?php echo __('dash_beta_text_1'); ?> <i>⚠️ <?php echo __('dash_beta_warning'); ?></i> <?php echo __('dash_beta_text_2'); ?>
                </div>
            </div>
            <div class="dashboard-header">
                <h1 class="dashboard-h1"><?php echo __('dash_welcome'); ?> <span class="highlight-name"><?php echo $username; ?></span></h1>
                <p class="subtitle dashboard-sub" style="gap:10px;"><?php echo __('dash_subtitle'); ?></p>
                <div class="queue-status" style="margin-top: 10px; margin-bottom: 2rem;">
                    <span class="live-dot"></span>
                    <span class="online-count"><?php echo __('dash_online_count', $onlineCount, $pluriel); ?></span>
                </div>
                
                <div class="mode-actions">
                    <a href="matchmaking.php?mode=ranked" class="btn-mode ranked">
                        <i class="fas fa-trophy"></i> <?php echo __('dash_btn_ranked'); ?>
                    </a>

                    <a href="matchmaking.php?mode=normal" class="btn-mode normal">
                        <i class="fas fa-running"></i> <?php echo __('dash_btn_normal'); ?>
                    </a>
                </div>
            </div>

            <div class="dashboard-grid">
                
                <div class="dash-card history-card">
                <h3><i class="fas fa-history"></i> <?php echo __('dash_history_title'); ?></h3>
                    <div class="card-header-tabs">
                        <button class="tab-btn active" onclick="switchTab('ranked')"><?php echo __('dash_tab_ranked'); ?></button>
                        <button class="tab-btn" onclick="switchTab('normal')"><?php echo __('dash_tab_normal'); ?></button>
                    </div>
                    
                    <div class="history-content">
                        <?php if (empty($history)): ?>
                            <p style="text-align:center; color:var(--text-secondary); padding:20px;"><?php echo __('dash_no_match'); ?></p>
                        <?php else: ?>
                            <?php foreach($history as $match): 
                                // Détermine si victoire ou défaite
                                $isWin = ($match['winner_id'] == $userId);
                                
                                // Variables d'affichage
                                $resultLetter = $isWin ? 'V' : 'D';
                                $resultClass = $isWin ? 'win' : 'loss';
                                $eloChange = $isWin ? '+' . $match['winner_elo_change'] : $match['loser_elo_change'];
                                
                                // Nom de l'adversaire
                                $opponentName = $isWin ? $match['loser_name'] : $match['winner_name'];
                                
                                // Score
                                $myScore = $isWin ? $match['score_winner'] : $match['score_loser'];
                                $opScore = $isWin ? $match['score_loser'] : $match['score_winner'];
                                
                                // Mode Normal (Pas d'ELO)
                                $matchMode = $match['mode'];
                                if ($matchMode === 'normal') {
                                    $eloDisplay = '---';
                                    $eloClass = ''; 
                                } else {
                                    $eloDisplay = $eloChange;
                                    $eloClass = $isWin ? '' : 'text-red';
                                }

                                $displayStyle = ($matchMode === 'ranked') ? 'flex' : 'none';
                            ?>
                            
                            <div class="match-item <?php echo $resultClass; ?>" data-type="<?php echo htmlspecialchars($matchMode); ?>" style="display: <?php echo $displayStyle; ?>;">
                                <span class="match-result"><?php echo $resultLetter; ?></span>
                                <div class="match-info">
                                    <span class="vs">vs <?php echo htmlspecialchars($opponentName); ?></span>
                                    <span class="score"><?php echo $myScore . ' - ' . $opScore; ?></span>
                                </div>
                                <span class="elo-change <?php echo $eloClass; ?>"><?php echo $eloDisplay; ?></span>
                            </div>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    </div>

                <div class="dash-card stats-card">
                    <h3><i class="fas fa-chart-pie"></i> <?php echo __('dash_stats_title'); ?></h3>
                    
                    <div class="winrate-container">
                        <div class="progress-circle" style="--p:<?php echo $winrate; ?>; --b:10px; --c:var(--primary-purple);">
                            <div class="stat-value"><?php echo $winrate; ?>%</div>
                        </div>
                        <p class="stat-label"><?php echo __('dash_winrate'); ?></p>
                    </div>

                    <div class="mini-stats-grid">
                        <div class="mini-stat">
                            <span class="val"><?php echo $wins; ?></span>
                            <span class="lbl"><?php echo __('dash_wins'); ?></span>
                        </div>
                        <div class="mini-stat">
                            <span class="val"><?php echo $losses; ?></span>
                            <span class="lbl"><?php echo __('dash_losses'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="dash-card rank-card">
                    <h3><i class="fas fa-crown"></i> <?php echo __('dash_ladder_title'); ?></h3>
                    
                    <div class="leaderboard-container" id="leaderboardScroll">
                        <?php 
                        $rankCounter = 1;
                        foreach($allPlayers as $player): 
                            $isMe = ($player['id'] == $_SESSION['user_id']);
                            $isFirst = ($rankCounter === 1);
                            
                            // Classes CSS dynamiques
                            $rowClass = "rank-row";
                            if ($isFirst) $rowClass .= " rank-1";
                            if ($isMe) $rowClass .= " is-me";
                            
                            // Avatar par défaut si null
                            $avatar = $player['avatar'] ? htmlspecialchars($player['avatar']) : 'assets/img/default_user.webp';
                            $profileUrl = "profile.php?username=" . urlencode($player['username']);
                        ?>
                            <div class="<?php echo $rowClass; ?>" <?php if($isMe) echo 'id="my-rank-row"'; ?> onclick="window.location.href='<?php echo $profileUrl; ?>'">
                                <div class="rank-pos">
                                    <?php if($isFirst): ?>
                                        <i class="fas fa-crown"></i>
                                    <?php else: ?>
                                        #<?php echo $rankCounter; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="rank-user">
                                    <img src="<?php echo $avatar; ?>" alt="avatar" class="mini-avatar">
                                    <span class="r-name"><?php echo htmlspecialchars($player['username']); ?></span>
                                </div>
                                
                                <div class="rank-elo">
                                    <?php echo $player['elo']; ?> <small>edp</small>
                                </div>
                            </div>
                        <?php 
                            $rankCounter++; 
                        endforeach; 
                        ?>
                    </div>
                </div>

            </div>
        </main>

        <script>

        function switchTab(type) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
            
            // On masque tout d'abord
            const items = document.querySelectorAll('.match-item');
            let hasVisibleItem = false;

            items.forEach(item => {
                if(item.dataset.type === type) {
                    item.style.display = 'flex';
                    hasVisibleItem = true;
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // AUTO SCROLL
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                const container = document.getElementById('leaderboardScroll');
                const myRow = document.getElementById('my-rank-row');

                if (container && myRow) {
                    // Calcul mathématique précis :
                    // Position de ma ligne dans la boite (offsetTop)
                    // - Moitié de la hauteur de la boite (pour centrer)
                    // + Moitié de la hauteur de ma ligne (pour ajuster)
                    const targetScroll = myRow.offsetTop - (container.clientHeight / 2) + (myRow.clientHeight / 2);

                    // On applique le scroll UNIQUEMENT sur le conteneur
                    container.scrollTo({
                        top: targetScroll,
                        behavior: 'smooth'
                    });
                }
            }, 300); // Petit délai pour être sûr que le CSS est chargé
        });
        </script>
        <?php
    }
}
?>