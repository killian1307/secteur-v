<?php
session_start();
require 'db.php';

// Si l'utilisateur n'est pas connecté, redirige vers l'accueil
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
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
$stmt = $pdo->prepare("SELECT username, avatar, created_at, elo, grade, bio FROM users WHERE username = ?");
$stmt->execute([$targetUsername]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header("Location: 404.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On nettoie l'entrée (supprime les espaces inutiles au début/fin)
    $newBio = trim($_POST['bio']);

    // Vérification de la longueur (Sécurité côté serveur)
    if (strlen($newBio) > 150) {
        // Si c'est trop long, on coupe brutalement ou on renvoie une erreur
        // Ici on coupe pour simplifier
        $newBio = substr($newBio, 0, 150);
    }

    // 3. Mise à jour en BDD
    try {
        $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->execute([$newBio, $_SESSION['user_id']]);
        
        // Succès : on retourne au profil
        header("Location: profile.php");
        exit;

    } catch (PDOException $e) {
        // En cas d'erreur technique
        die("Erreur lors de la mise à jour : " . $e->getMessage());
    }
}

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
            // Sécurisation des données
            $displayUsername = htmlspecialchars($profileUser['username']);
            // Avatar par défaut si null
            $displayAvatar = $profileUser['avatar'] ? htmlspecialchars($profileUser['avatar']) : 'assets/img/default_user.webp';
            // Date (optionnel, pour le style)
            $joinDate = date('d/m/Y', strtotime($profileUser['created_at'] ?? 'now'));

            $elo = $profileUser['elo'];

            $grade = $profileUser['grade'];

            $bio = $profileUser['bio'];

            // Bio
            if (empty($bio)) {
                $displayBio = "*Bruits de criquets*";
                $bioClass = "empty-bio";
            } else {
                $displayBio = htmlspecialchars($bio);
                $bioClass = "";
            }
            
            // Grades spéciaux
            $gradeClass = '';

            if ($grade === 'Créateur') {
                $gradeClass = 'is-creator';
            } elseif ($grade === 'VIP') {
                $gradeClass = 'is-vip';
            } elseif ($grade === 'Modérateur') {
                $gradeClass = 'is-moderator';
            }
        ?>

        <div class="player-card-container">
            <div class="player-card">
                
                <div class="player-avatar-wrapper">
                    <img src="<?php echo $displayAvatar; ?>" alt="<?php echo $displayUsername; ?>" class="player-avatar">
                    <div class="rank-badge tooltip" data-tooltip="Utilisateur Vérifié">
                        <i class="fas fa-star" ></i> </div>
                </div>

                <div class="player-info">
                    <h1 class="player-name"><?php echo $displayUsername; ?></h1>
                    <p class="player-title <?php echo $gradeClass; ?>">
                        <?php echo htmlspecialchars($grade); ?> du Secteur V
                    </p>
                    
                    <div class="player-stats-row">
                        <div class="stat-pill">
                            <i class="fas fa-calendar-alt"></i> Depuis le <?php echo $joinDate; ?>
                        </div>
                        <div class="stat-pill highlight">
                            <i class="fas fa-trophy"></i> <?php echo $elo; ?>
                        </div>
                    </div>
                </div>

                                    <div class="player-bio-container">
    <p class="player-bio <?php echo $bioClass; ?>">
        <?php echo $displayBio; ?>
    </p>
</div> 

                <?php if ($profileUser['username'] === $_SESSION['username']): ?>
                    <div class="card-actions">
                        <button class="other-button" onclick="openEditModal()">
                            <i class="fas fa-cog"></i> Modifier mon dossier
                        </button>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    <?php endif; ?>

</main>

<div id="editModal" class="modal-overlay" onclick="closeEditModal(event)">
    <div class="modal-box">
        <div class="modal-header">
            <i class="fas fa-edit"></i> Mise à jour du Dossier
        </div>
        
        <form action="" method="POST" class="modal-body">
            
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
<script src="script.js"></script>
</body>
</html>