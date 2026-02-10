<?php
session_start();
require 'db.php';

// Si l'utilisateur n'est pas connecté, redirige vers la page d'accueil
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

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
        // Longueur max (12)
        } elseif (mb_strlen($newUsername) > 12) {
            $message = "Le pseudo ne doit pas dépasser 12 caractères.";
            $msgType = "error alert-danger";

        // Vérification Longueur Min
        } elseif (strlen($newUsername) < 3) {
            $message = "Le pseudo doit contenir au moins 3 caractères.";
            $msgType = "error";
        // Vérification du pseudo (lettres, chiffres, _ et .)
        } elseif (!preg_match('/^[a-zA-Z0-9_.]+$/', $newUsername)) {
            $message = "Le pseudo ne peut contenir que des lettres, points (.) et underscores (_).";
            $msgType = "error alert-danger";
        // ------------------------------------------

        } else {
                // TOUT EST BON : On met à jour
                try {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    if ($stmt->execute([$newUsername, $newEmail, $_SESSION['user_id']])) {
                        // Met à jour la session immédiatement pour que le header change
                        $_SESSION['username'] = $newUsername;
                        
                        $message = "Accréditations mises à jour avec succès.";
                        $msgType = "success alert-success";
                    } else {
                        $message = "Erreur technique lors de la mise à jour.";
                        $msgType = "error alert-danger";
                    }
                } catch (PDOException $e) {
                    $message = "Erreur : Ce nom d'utilisateur est déjà utilisé.";
                    $msgType = "error alert-danger";
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

require 'assets/header.php';
require 'assets/footer.php';

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
            
            <form method="POST" action="" class="profile-form">
                <div class="form-group">
                    <label for="username">Nom d'Utilisateur (Maj, min, chiffres, "_" et ".")</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required minlength="3" maxlength="12" pattern="[a-zA-Z0-9_.]+">
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
                    <i class="fas fa-save"></i> Enregistrer
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