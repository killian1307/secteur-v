<?php
session_start();
require 'db.php';

// Si l'utilisateur n'est pas connecté, redirige vers l'accueil
if (!isset($_SESSION['user_id'])) {
    header("Location: discord_login.php");
    exit;
}

// Récupération du profil à afficher
$profileUser = null;
$errorMsg = null;

// Détermine quel profil afficher
if (isset($_GET['username']) && !empty($_GET['username'])) {
    // Si un pseudo est demandé dans l'URL
    $targetUsername = $_GET['username'];
} else {
    // Si rien n'est précisé, affiche le profil de l'utilisateur connecté
    header("Location: profile.php?username=" . $_SESSION['username']);
    exit;
}

// Cherche l'utilisateur dans la base de données
$stmt = $pdo->prepare("SELECT id, username, avatar, created_at, elo, grade, bio FROM users WHERE username = ?");
$stmt->execute([$targetUsername]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header("Location: 404.php");
    exit;
}

$stmtTeam = $pdo->prepare("SELECT formation, team_name FROM teams WHERE user_id = ?");
$stmtTeam->execute([$profileUser['id']]);
$teamData = $stmtTeam->fetch();

// Si l'utilisateur a une formation sauvegardée, on la prend. Sinon par défaut '4-4-2 Diamant'.
$savedFormation = $teamData['formation'] ?? '4-4-2 Diamant';
$teamName = $teamData['team_name'] ?? 'Victory Team';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On nettoie l'entrée (supprime les espaces inutiles au début/fin)
    $newBio = trim($_POST['bio']);

    // Vérification de la longueur (Sécurité côté serveur)
    if (strlen($newBio) > 150) {
        // Si c'est trop long, on coupe brutalement ou on renvoie une erreur
        // Ici on coupe pour simplifier
        $newBio = substr($newBio, 0, 150);
    }

    $newTeamName = trim($_POST['team_name']);
    if (strlen($newTeamName) > 12) $newTeamName = substr($newTeamName, 0, 12); // Limite à 12 carac
    if (empty($newTeamName)) $newTeamName = "Victory Team"; // Fallback

    // Mise à jour en BDD
    try {
        $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->execute([$newBio, $_SESSION['user_id']]);

        // Mise à jour du nom d'équipe
        // Vérifie si l'équipe existe
        $checkTeam = $pdo->prepare("SELECT id FROM teams WHERE user_id = ?");
        $checkTeam->execute([$_SESSION['user_id']]);
        
        if ($checkTeam->fetch()) {
            $stmtTeam = $pdo->prepare("UPDATE teams SET team_name = ? WHERE user_id = ?");
            $stmtTeam->execute([$newTeamName, $_SESSION['user_id']]);
        } else {
            // Création si inexistante (cas rare)
            $stmtTeam = $pdo->prepare("INSERT INTO teams (user_id, team_name, formation) VALUES (?, ?, '4-4-2 Diamant')");
            $stmtTeam->execute([$_SESSION['user_id'], $newTeamName]);
        }
        
        // Succès : on retourne au profil
        header("Location: profile.php");
        exit;

    } catch (PDOException $e) {
        // En cas d'erreur technique
        die("Erreur lors de la mise à jour : " . $e->getMessage());
    }
}

// Vérifie si propriétaire
$isOwner = isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $profileUser['id']);
$targetUserId = $profileUser['id'];

// Classe CSS pour désactiver le curseur si on n'est pas proprio
$fieldClass = $isOwner ? 'editable-mode' : 'readonly-mode';

require 'assets/header.php';
require 'assets/footer.php';

# Titre de la page
$pageTitle = $profileUser ? "Profil de " . htmlspecialchars($profileUser['username']) : "Joueur Introuvable";
$header = new Header("SECTEUR V - " . $pageTitle);
$header->render();
?>

<main class="profile-viewer-main">
    
    <?php if ($errorMsg): ?>
        <div class="dashboard-container">
            <h1 style="color: #e74c3c;">Erreur 404</h1>
            <p class="subtitle"><?php echo $errorMsg; ?></p>
            <button class="cta-button" onclick="window.history.back()">Retour</button>
        </div>

    <?php else: ?>
        <?php 
            // Data Prep
            $displayUsername = htmlspecialchars($profileUser['username']);

            $displayAvatar = $profileUser['avatar'] ? htmlspecialchars($profileUser['avatar']) : 'assets/img/default_user.webp';
            $joinDate = date('d/m/Y', strtotime($profileUser['created_at'] ?? 'now'));
            $elo = $profileUser['elo'];
            $grade = $profileUser['grade'];
            $bio = $profileUser['bio'];
            
            // Bio check
            $displayBio = empty($bio) ? "Aucune bio définie." : htmlspecialchars($bio);
            $bioClass = empty($bio) ? "empty-bio" : "";

            // Grade coloring
            $gradeClass = '';
            if ($grade === 'Créateur') $gradeClass = 'is-creator';
            elseif ($grade === 'VIP') $gradeClass = 'is-vip';
            elseif ($grade === 'Modérateur') $gradeClass = 'is-moderator';
        ?>

        <div class="osu-layout">

            <div class="osu-profile-header">
                
                <div class="header-avatar-section">
                    <img src="<?php echo $displayAvatar; ?>" alt="<?php echo $displayUsername; ?>" class="osu-avatar">
                </div>

                <div class="header-info-section">
                    <div class="name-row">
                        <h1 class="osu-username" title="<?php echo htmlspecialchars($profileUser['username']); ?>">
                            <?php echo $displayUsername; ?>
                        </h1>
                        <span class="osu-grade <?php echo $gradeClass; ?>"><?php echo htmlspecialchars($grade); ?></span>
                    </div>

                    <div class="badges-row">
                        <div class="badge-pill tooltip" data-tooltip="Utilisateur Vérifié">
                            <i class="fas fa-check-circle"></i> Vérifié
                        </div>
                        <div class="badge-pill">
                            <i class="fas fa-calendar-alt"></i> <?php echo $joinDate; ?>
                        </div>
                        </div>

                    <div class="bio-row">
                        <p class="osu-bio <?php echo $bioClass; ?>"><?php echo $displayBio; ?></p>
                        <?php if ($isOwner): ?>
                            <button class="mini-edit-btn" onclick="openEditModal()" title="Modifier la bio">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="header-stats-section">
                    <div class="stat-box">
                        <span class="stat-value"><?php echo $elo; ?></span>
                        <span class="stat-label">Points EDP</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value">0</span> <span class="stat-label">Matchs</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value">0%</span> <span class="stat-label">Win Rate</span>
                    </div>
                </div>

            </div>

            <div class="osu-body-section">
                    <?php
                        $formationsMap = [
                            '4-4-2 Diamant'       => '4-4-2-diamant',
                            '4-4-2 Boîte'         => '4-4-2-boite',
                            '3-5-2 Liberté'       => '3-5-2-liberte',
                            '4-3-3 Triangle'      => '4-3-3-triangle',
                            '4-3-3 Delta'         => '4-3-3-delta',
                            '4-5-1 Équilibré'     => '4-5-1-equilibre',
                            '3-6-1 Hexa'          => '3-6-1-hexa',
                            '5-4-1 Double Volante'=> '5-4-1-double-volante'
                        ];
                        $currentClass = $formationsMap[$savedFormation] ?? '4-4-2-diamant';
                    ?>
            <p class="section-title">Équipe</p>
                <div class="team-header">
                <div class="team-info-left">
                    <h2 class="team-name-title">
                    <?php echo htmlspecialchars($teamName); ?>
                        <?php if ($isOwner): ?>
                            <button class="mini-edit-btn" onclick="openEditModal()" title="Changer le nom d'équipe">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        <?php endif; ?>
                    </h2>
                    <div class="team-meta">
                        <div class="formation-selector-wrapper">
                        <span class="formation-tag" id="formationDisplay">
                            <i class="fas fa-chess-board"></i> 
                            <span id="formationLabelText"><?php echo htmlspecialchars($savedFormation); ?></span>

                            <?php if ($isOwner): ?>
                                <i class="fas fa-pencil-alt" style="margin-left: 8px; font-size: 0.8em; opacity: 0.7;"></i>
                            <?php endif; ?>
                        </span>

                        <?php if ($isOwner): ?>
                            <select id="formationSelect" onchange="changeFormation(this)" class="ghost-select" title="Changer la formation">
                                <?php 
                                foreach($formationsMap as $name => $cssClass) {
                                    $isSelected = ($name === $savedFormation) ? 'selected' : '';
                                    echo "<option value='$name' data-class='$cssClass' $isSelected>$name</option>";
                                }
                                ?>
                            </select>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="team-coach-right">
                            <div class="player-slot coach-slot tooltip <?php echo $isOwner ? '' : 'is-readonly'; ?>" 
                                data-tooltip="Sans coach" 
                                <?php echo $isOwner ? "onclick=\"openSelector('coach')\"" : ""; ?> 
                                id="slot-display-coach">
                                <div class="empty-state"><i class="fas fa-user-tie"></i></div>
                            </div>
                    </div>
                </div>
                </div>

                <div class="builder-container">

                    <div class="soccer-field <?php echo $fieldClass; ?> formation-<?php echo $currentClass; ?>" id="field-container">
                        <?php
                        function renderSlot($id, $label, $isOwner) {
                            $onclick = $isOwner ? "onclick=\"openSelector('$id')\"" : "";
                            $icon = $isOwner ? '<i class="fas fa-plus"></i>' : '';
                            echo "<div class=\"player-slot tooltip\" data-tooltip=\"Emplacement vide\" $onclick id=\"slot-display-$id\" data-slot=\"$id\">
                                    <div class=\"empty-state\">$icon</div>
                                    <span class=\"position-label\">$label</span>
                                  </div>";
                        }
                        renderSlot('1', 'GK', $isOwner);
                        renderSlot('2', 'DF', $isOwner);
                        renderSlot('3', 'DF', $isOwner);
                        renderSlot('4', 'DF', $isOwner);
                        renderSlot('5', 'DF', $isOwner);
                        renderSlot('6', 'MF', $isOwner);
                        renderSlot('7', 'MF', $isOwner);
                        renderSlot('8', 'MF', $isOwner);
                        renderSlot('9', 'MF', $isOwner);
                        renderSlot('10', 'FW', $isOwner);
                        renderSlot('11', 'FW', $isOwner);
                        ?>
                    </div>
                </div>
            </div>

        </div> <?php endif; ?>

    <div id="playerSelectorModal" onclick="if(event.target === this) closeSelector()">
    <div class="modal-box-team">
        <button class="close-btn" onclick="closeSelector()">×</button>
        <h3 id="modalTitle">Choisir un joueur</h3>
        
        <input type="text" id="playerSearchInput" placeholder="Chercher...">
        
        <div id="searchResults" class="results-grid">
        </div>
    </div>
</div>
</main>

<div id="editModal" class="modal-overlay" onclick="closeEditModal(event)">
    <div class="modal-box">
        <div class="modal-header">
            <i class="fas fa-edit"></i> Mise à jour du Dossier
        </div>
        
        <form action="" method="POST">
        <div class="modal-body">
            <div class="form-group">
                <label for="bio-input" class="form-label">Votre Bio (Max 150 carac.)</label>
                <textarea 
                    name="bio" 
                    id="bio-input" 
                    class="edit-textarea" 
                    maxlength="150" 
                    oninput="updateCharCount(this)"
                    placeholder="Écrivez quelque chose..."><?php echo htmlspecialchars($profileUser['bio'] ?? ''); ?></textarea>
                
                <div class="char-count-wrapper">
                    <span id="char-count">0</span>/150
                </div>
            </div>
            <div class="form-group">
                <label for="team-name-input" class="form-label">Nom de l'équipe (Max 12)</label>
                <div class="input-wrapper">
                    <i class="fas fa-shield-alt"></i>
                    <input type="text" 
                           name="team_name" 
                           id="team-name-input" 
                           value="<?php echo htmlspecialchars($teamName); ?>" 
                           maxlength="12"
                           required>
                </div>
            </div>
        </div>
            <div class="modal-footer">
                <button type="submit" class="other-button">Enregistrer</button>
                <button type="button" class="ghost-btn" onclick="closeEditModal(null)">Annuler</button>
            </div>
            
        </form>
    </div>
</div>

<?php
$footer = new Footer('profile-footer');
$footer->render();
?>
<script>
    const CONFIG_TEAM = {
        targetUserId: <?php echo $targetUserId; ?>,
        isOwner: <?php echo $isOwner ? 'true' : 'false'; ?>,
        currentFormation: "<?php echo htmlspecialchars($savedFormation); ?>"
    };
</script>
<script src="script.js"></script>
</body>
</html>