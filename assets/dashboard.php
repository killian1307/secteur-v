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

        $username = htmlspecialchars($this->user['username']);
        $wins = $this->user['wins'];
        $losses = $this->user['losses'];
        $elo = $this->user['elo'];
        $grade = htmlspecialchars($this->user['grade']);
        
        // Calcul du Winrate
        $totalMatches = $wins + $losses;
        $winrate = $totalMatches > 0 ? round(($wins / $totalMatches) * 100) : 0;

        // Récupère ID, Username, ELO et Avatar triés par ELO
        $stmtRank = $this->pdo->query("SELECT id, username, elo, avatar FROM users ORDER BY elo DESC");
        $allPlayers = $stmtRank->fetchAll(PDO::FETCH_ASSOC);

        ?>
        <main class="dashboard-main">
            <div class="dashboard-header">
                <h1 class="dashboard-h1">Bon retour, <span class="highlight-name"><?php echo $username; ?></span>.</h1>
                <p class="subtitle dashboard-sub">Prêt à prouver qui est le meilleur ?</p>
                
                <div class="mode-actions">
                    <a href="classement.php?mode=normal" class="btn-mode normal">
                        <i class="fas fa-running"></i> Match Amical
                    </a>
                    
                    <a href="classement.php?mode=ranked" class="btn-mode ranked">
                        <i class="fas fa-trophy"></i> Jouer en Classé
                    </a>
                </div>
            </div>

            <div class="dashboard-grid">
                
                <div class="dash-card history-card">
                    <h3><i class="fas fa-history"></i> Historique</h3>
                    <div class="card-header-tabs">
                        <button class="tab-btn active" onclick="switchTab('ranked')">Classé</button>
                        <button class="tab-btn" onclick="switchTab('normal')">Normal</button>
                    </div>
                    <!-- PLACEHOLDER GAMES -->
                    <div class="history-content" id="history-list">
                        <div class="match-item win" data-type="ranked">
                            <span class="match-result">V</span>
                            <div class="match-info">
                                <span class="vs">vs DarkEmperor</span>
                                <span class="score">3 - 2</span>
                            </div>
                            <span class="elo-change">+15</span>
                        </div>
                        <div class="match-item loss" data-type="ranked">
                            <span class="match-result">D</span>
                            <div class="match-info">
                                <span class="vs">vs AxelBlaze99</span>
                                <span class="score">1 - 4</span>
                            </div>
                            <span class="elo-change text-red">-12</span>
                        </div>
                        <div class="match-item win" data-type="normal" style="display:none;">
                            <span class="match-result">V</span>
                            <div class="match-info">
                                <span class="vs">vs Bot_Training</span>
                                <span class="score">5 - 0</span>
                            </div>
                            <span class="elo-change">---</span>
                        </div>
                    </div>
                    <a href="#" class="history-more">Voir tout l'historique</a>
                </div>

                <div class="dash-card stats-card">
                    <h3><i class="fas fa-chart-pie"></i> Performance</h3>
                    
                    <div class="winrate-container">
                        <div class="progress-circle" style="--p:<?php echo $winrate; ?>; --b:10px; --c:var(--primary-purple);">
                            <div class="stat-value"><?php echo $winrate; ?>%</div>
                        </div>
                        <p class="stat-label">Taux de Victoire</p>
                    </div>

                    <div class="mini-stats-grid">
                        <div class="mini-stat">
                            <span class="val"><?php echo $wins; ?></span>
                            <span class="lbl">Victoires</span>
                        </div>
                        <div class="mini-stat">
                            <span class="val"><?php echo $losses; ?></span>
                            <span class="lbl">Défaites</span>
                        </div>
                    </div>
                </div>

                <div class="dash-card rank-card">
                    <h3><i class="fas fa-crown"></i> Classement</h3>
                    
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
                            $avatar = $player['avatar'] ? htmlspecialchars($player['avatar']) : 'assets/img/default_avatar.png';
                        ?>
                            <div class="<?php echo $rowClass; ?>" <?php if($isMe) echo 'id="my-rank-row"'; ?>>
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
            // Gestion des boutons
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');

            // Gestion de l'affichage (Fake logic pour l'exemple statique)
            const items = document.querySelectorAll('.match-item');
            items.forEach(item => {
                if(item.dataset.type === type) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // AUTO SCROLL (Version Fixe : Ne bouge QUE la boîte, pas la page)
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