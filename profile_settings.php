<?php
require_once 'assets/init_session.php';
require 'db.php';

// Si l'utilisateur n'est pas connecté, redirige vers la page d'accueil
if (!isset($_SESSION['user_id'])) {
    header("Location: /");
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
            $message = __('ps_err_empty_user');
            $msgType = "error";
        // Longueur max (12)
        } elseif (mb_strlen($newUsername) > 12) {
            $message = __('ps_err_user_length_max');
            $msgType = "error alert-danger";

        // Vérification Longueur Min
        } elseif (strlen($newUsername) < 3) {
            $message = __('ps_err_user_length_min');
            $msgType = "error";
        // Vérification du pseudo (lettres, chiffres, _ et .)
        } elseif (!preg_match('/^[a-zA-Z0-9_.]+$/', $newUsername)) {
            $message = __('ps_err_user_format');
            $msgType = "error alert-danger";
        // ------------------------------------------

        } else {
                // Met à jour
                try {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    if ($stmt->execute([$newUsername, $newEmail, $_SESSION['user_id']])) {
                        // Met à jour la session immédiatement pour que le header change
                        $_SESSION['username'] = $newUsername;
                        
                        $message = __('ps_success_update');
                        $msgType = "success alert-success";
                    } else {
                        $message = __('ps_err_tech');
                        $msgType = "error alert-danger";
                    }
                } catch (PDOException $e) {
                    $message = __('ps_err_user_taken');
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
$header = new Header(__('ps_page_title'));

// Affiche le header
$header->render();
?>

<main class="profile-main">
    
    <h1><?php echo __('ps_h1_1'); ?> <span style="color: var(--primary-purple)"><?php echo __('ps_h1_2'); ?></span></h1>
    <p class="subtitle"><?php echo __('ps_subtitle'); ?></p>

    <?php if ($message): ?>
        <div class="alert <?php echo $msgType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="profile-settings-container">
        
        <div class="profile-card">
            <div class="card-header">
                <i class="fas fa-id-card-alt"></i> <?php echo __('ps_card_info_title'); ?>
            </div>
            
            <form method="POST" action="" class="profile-form">
                <div class="form-group">
                    <label for="username"><?php echo __('ps_label_username'); ?></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required minlength="3" maxlength="12" pattern="[a-zA-Z0-9_.]+">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email"><?php echo __('ps_label_email'); ?></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="<?php echo htmlspecialchars(__('ps_placeholder_email')); ?>">
                    </div>
                    <small style="color: var(--text-secondary); font-size: 0.8rem;"><?php echo __('ps_email_help'); ?></small>
                </div>

                <button type="submit" name="update_profile" class="other-button">
                    <i class="fas fa-save"></i> <?php echo __('ps_btn_save'); ?>
                </button>
            </form>
        </div>

        <div class="profile-card danger-zone">
            <div class="card-header" style="color: #e74c3c;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo __('ps_card_danger_title'); ?>
            </div>
            <p>
                <?php echo __('ps_danger_desc'); ?>
            </p>
            
            <form method="POST" action="profile_settings.php" onsubmit="return confirm('<?php echo addslashes(__('ps_delete_confirm')); ?>');">
                <button type="submit" name="delete_account" class="delete-btn">
                    <i class="fas fa-trash-alt"></i> <?php echo __('ps_btn_delete'); ?>
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
</body>
</html>