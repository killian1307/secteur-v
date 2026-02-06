<?php
session_start();
require 'db.php';

// Si l'utilisateur n'est pas connecté, redirige vers la page d'accueil
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require 'assets/header.php';
require 'assets/footer.php';

$message = "";
$msgType = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Mise à jour du profil
    if (isset($_POST['update_profile'])) {
        $newUsername = trim($_POST['username']);
        $newEmail = trim($_POST['email']);
        
        // Si l'email est vide, met à NULL
        if (empty($newEmail)) {
            $newEmail = null;
        }

        if (empty($newUsername)) {
            $message = "Le pseudo ne peut pas être vide.";
            $msgType = "error";
        } elseif (strlen($newUsername) < 3) {
            $message = "Le pseudo doit contenir au moins 3 caractères.";
            $msgType = "error";
        } else {
            // Mise à jour BDD
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$newUsername, $newEmail, $_SESSION['user_id']])) {
                // Met à jour la session
                $_SESSION['username'] = $newUsername;
                
                $message = "Données mises à jour avec succès.";
                $msgType = "success";
            } else {
                $message = "Erreur lors de la mise à jour.";
                $msgType = "error";
            }
        }
    }

    // Suppression du compte
    if (isset($_POST['delete_account'])) {
        // Supprime l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Détruit la session et redirige
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

// Récupère les données de l'utilisateur pour pré-remplir le formulaire
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

# Titre
$header = new Header("SECTEUR V - Dossier Personnel");

// Affiche le header
$header->render();
?>

<main class="profile-main">
    
    <h1>Dossier <span style="color: var(--primary-purple)">Personnel</span></h1>
    <p class="subtitle">Gérez vos accréditations et vos informations de contact.</p>

    <?php if ($message): ?>
        <div class="alert <?php echo $msgType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="profile-settings-container">
        
        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-id-card-alt"></i> Informations
            </div>
            
            <form method="POST" action="profile_settings.php" class="profile-form">
                <div class="form-group">
                    <label for="username">Nom d'Utilisateur</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required minlength="3">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Non renseigné">
                    </div>
                    <small style="color: var(--text-secondary); font-size: 0.8rem;">Laissez vide pour supprimer l'email.</small>
                </div>

                <button type="submit" name="update_profile" class="other-button">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </form>
        </div>

        <div class="profile-card danger-zone">
            <div class="card-header" style="color: #e74c3c;">
                <i class="fas fa-exclamation-triangle"></i> Résiliation
            </div>
            <p>
                Cette action est irréversible. Toutes vos données, votre rang EDP et votre historique de matchs seront effacés des archives du Secteur V.
            </p>
            
            <form method="POST" action="profile_settings.php" onsubmit="return confirm('Êtes-vous certain de vouloir quitter le Secteur V ? Cette action est définitive.');">
                <button type="submit" name="delete_account" class="delete-btn">
                    <i class="fas fa-trash-alt"></i> Supprimer mon compte
                </button>
            </form>
        </div>

    </div>
</main>

<?php
$footer = new Footer();

// Affiche le footer
$footer->render();
?>
    <script src="script.js"></script>
</body>
</html>