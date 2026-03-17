<?php
require_once 'assets/init_session.php';
require 'db.php';

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Compter le nombre total de joueurs pour la pagination
$stmtCount = $pdo->query("SELECT COUNT(*) FROM users");
$totalPlayers = $stmtCount->fetchColumn();

$totalPages = ceil($totalPlayers / $limit);
if ($totalPages < 1) $totalPages = 1;

// Récupérer les joueurs de la page actuelle, triés par ELO
$sqlRank = "SELECT id, username, elo, avatar FROM users ORDER BY elo DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
$stmtRank = $pdo->query($sqlRank);
$players = $stmtRank->fetchAll(PDO::FETCH_ASSOC);

// Header
require 'assets/header.php';
require 'assets/footer.php';
$header = new Header("SECTEUR V - Classement Global");
$header->render();
?>

<link rel="stylesheet" href="style-playerbook.css">

<style>
    /* Styles spécifiques au classement */
    .rank-avatar {
        width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
        vertical-align: middle; margin-right: 15px; border: 2px solid transparent;
    }
    .rank-name {
        font-weight: bold; color: var(--text-primary); text-decoration: none; font-size: 1.1rem;
    }
    .rank-name:hover {
        color: var(--primary-purple); text-decoration: underline;
    }
    .is-me {
        background-color: rgba(155, 89, 182, 0.1) !important;
        border-left: 4px solid var(--primary-purple);
    }
    .is-me .rank-avatar {
        border-color: var(--primary-purple);
    }
    .crown-gold { color: #FFD700; font-size: 1.2rem; }
    .crown-silver { color: #C0C0C0; font-size: 1.1rem; }
    .crown-bronze { color: #CD7F32; font-size: 1rem; }
    
    /* Ajustement de la largeur des colonnes */
    .col-rank { width: 100px; text-align: center !important; font-weight: bold; font-size: 1.2rem; }
    .col-elo { width: 150px; text-align: right !important; font-weight: bold; color: var(--primary-purple); font-size: 1.1rem; }
</style>

<main class="playerbook-container">
    
    <div class="pb-header">
        <h1 class="dashboard-h1">Classement <span style="color:var(--primary-purple)">Global</span></h1>
        <p class="subtitle">Les meilleurs joueurs du Secteur V.</p>
    </div>

    <div class="controls-wrapper" style="justify-content: flex-end;">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="page-btn">←</a>
            <?php endif; ?>
            
            <form method="GET" class="page-jump-form">
                <span class="page-info">Page</span>
                <input type="number" name="page" value="<?php echo $page; ?>" min="1" max="<?php echo $totalPages; ?>" class="page-input">
                <span class="page-info">/ <?php echo $totalPages; ?></span>
            </form>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="page-btn">→</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="player-table">
            <thead>
                <tr>
                    <th class="col-rank">Rang</th>
                    <th>Joueur</th>
                    <th class="col-elo">Points EDP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($players) > 0): ?>
                    <?php 
                    // Le vrai rang dépend de la page où on est
                    $rankCounter = $offset + 1;
                    
                    foreach ($players as $p): 
                        // Vérifie si c'est la ligne de l'utilisateur connecté
                        $isMe = (isset($_SESSION['user_id']) && $p['id'] == $_SESSION['user_id']);
                        
                        // Image par défaut et URL du profil
                        $avatar = $p['avatar'] ? htmlspecialchars($p['avatar']) : 'assets/img/default_user.webp';
                        $profileUrl = "profile.php?username=" . urlencode($p['username']);
                    ?>
                        <tr class="<?php echo $isMe ? 'is-me' : ''; ?>">
                            <td data-label="Rang" class="col-rank">
                                <?php if ($rankCounter === 1): ?>
                                    <i class="fas fa-crown crown-gold" title="1er"></i>
                                <?php elseif ($rankCounter === 2): ?>
                                    <i class="fas fa-crown crown-silver" title="2ème"></i>
                                <?php elseif ($rankCounter === 3): ?>
                                    <i class="fas fa-crown crown-bronze" title="3ème"></i>
                                <?php else: ?>
                                    #<?php echo $rankCounter; ?>
                                <?php endif; ?>
                            </td>
                            
                            <td data-label="Joueur">
                                <img src="<?php echo $avatar; ?>" alt="Avatar de <?php echo htmlspecialchars($p['username']); ?>" class="rank-avatar" loading="lazy">
                                <a href="<?php echo $profileUrl; ?>" class="rank-name"><?php echo htmlspecialchars($p['username']); ?></a>
                            </td>
                            
                            <td data-label="Points EDP" class="col-elo">
                                <?php echo $p['elo']; ?> <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: normal;">edp</span>
                            </td>
                        </tr>
                    <?php 
                    $rankCounter++; 
                    endforeach; 
                    ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding: 2rem;">Aucun joueur trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<?php
$footer = new Footer();
$footer->render();
?>
<script src="script.js"></script>
</body>
</html>