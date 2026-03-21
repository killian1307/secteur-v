<?php
require_once 'assets/init_session.php';
require 'db.php';

// Verif connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Verif créateur
$stmt = $pdo->prepare("SELECT grade FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['grade'] !== 'Créateur' && $user['grade'] !== 'Administrateur') {
    require 'assets/header.php';
    $header = new Header("Access Denied");
    $header->render();
    echo "<main class='dashboard-container' style='text-align:center; padding: 5rem 0;'>
            <i class='fas fa-lock' style='font-size: 4rem; color: #e74c3c; margin-bottom: 1rem;'></i>
            <h1>Access <span style='color:#e74c3c;'>Denied</span></h1>
            <p>You do not have the necessary permissions to access this page.</p>
            <a href='index.php' class='other-button' style='margin-top: 2rem;'>Return to Home</a>
          </main>";
    require 'assets/footer.php';
    $footer = new Footer();
    $footer->render();
    exit;
}

$partner_message = "";
$grade_message = "";

// Formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Ajouter un partenaire
    if ($action === 'add_partner') {
        $invite_link = trim($_POST['invite_link']);
        $description = trim($_POST['description']);
        $pioneer = isset($_POST['pioneer']) ? 1 : 0;
        
        // Champs optionnels
        $name = trim($_POST['name'] ?? '');
        $picture_url = trim($_POST['picture_url'] ?? '');
        $member_count = (int)($_POST['member_count'] ?? 0);

        // API Discord
        if (!empty($invite_link)) {
            $invite_code = basename(parse_url($invite_link, PHP_URL_PATH));
            $api_url = "https://discord.com/api/v10/invites/" . $invite_code . "?with_counts=true";
            
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !IS_LOCAL);
            $response = curl_exec($ch);
            curl_close($ch);

            $discord_data = json_decode($response, true);

            if (isset($discord_data['guild'])) {
                $name = $discord_data['guild']['name']; // On récupère le vrai nom
                if (isset($discord_data['approximate_member_count'])) {
                    $member_count = $discord_data['approximate_member_count']; // Le vrai nombre de membres
                }
                if (isset($discord_data['guild']['icon'])) {
                    $guild_id = $discord_data['guild']['id'];
                    $icon_hash = $discord_data['guild']['icon'];
                    $picture_url = "https://cdn.discordapp.com/icons/{$guild_id}/{$icon_hash}.png?size=256"; // La vraie image
                }
            }
        }

        // Sécurité
        if (empty($picture_url)) {
            $picture_url = 'assets/img/default_user.webp';
        }

        // On vérifie que le nom n'est pas vide
        if (empty($name) || empty($description) || empty($invite_link)) {
            $partner_message = "<div class='alert alert-danger' style='background: rgba(231,76,60,0.1); color: #e74c3c; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #e74c3c;'>Veuillez remplir le Lien et la Description (Si le lien est invalide, remplissez aussi le Nom manuellement).</div>";
        } else {
            $insert = $pdo->prepare("INSERT INTO supports (name, description, picture_url, invite_link, member_count, pioneer) VALUES (?, ?, ?, ?, ?, ?)");
            if ($insert->execute([$name, $description, $picture_url, $invite_link, $member_count, $pioneer])) {
                $partner_message = "<div class='alert alert-success' style='background: rgba(46, 204, 113, 0.1); color: #2ecc71; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #2ecc71;'>Le partenaire <strong>" . htmlspecialchars($name) . "</strong> a été ajouté !</div>";
            } else {
                $partner_message = "<div class='alert alert-danger' style='background: rgba(231,76,60,0.1); color: #e74c3c; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #e74c3c;'>Une erreur est survenue lors de l'ajout en base de données.</div>";
            }
        }
    }

    // Grade
    elseif ($action === 'update_grade') {
        $target_username = trim($_POST['target_username']);
        $new_grade = $_POST['new_grade'];
        
        // Liste des grades autorisés
        $allowed_grades = ['Membre', 'VIP', 'Partenaire', 'Modérateur', 'Administrateur'];

        if (empty($target_username) || !in_array($new_grade, $allowed_grades)) {
            $grade_message = "<div class='alert alert-danger' style='background: rgba(231,76,60,0.1); color: #e74c3c; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #e74c3c;'>Données invalides ou grade non autorisé.</div>";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET grade = ? WHERE username = ?");
            $stmt->execute([$new_grade, $target_username]);
            
            if ($stmt->rowCount() > 0) {
                $grade_message = "<div class='alert alert-success' style='background: rgba(46, 204, 113, 0.1); color: #2ecc71; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #2ecc71;'>Le grade de <strong>" . htmlspecialchars($target_username) . "</strong> est maintenant : $new_grade !</div>";
            } else {
                $grade_message = "<div class='alert alert-danger' style='background: rgba(231,76,60,0.1); color: #e74c3c; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #e74c3c;'>Utilisateur introuvable ou il possède déjà ce grade.</div>";
            }
        }
    }
}

require 'assets/header.php';
$header = new Header("Admin Panel");
$header->render();
?>

<main class="dashboard-container">
    <h1>Admin <span style="color:var(--primary-purple)">Panel</span></h1>
    <p class="subtitle">Secteur V's Administration</p>

    <div class="profile-card" style="max-width: 600px; margin: 2rem auto; text-align: left;">
        <h2 style="font-size: 1.2rem; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <i class="fas fa-plus-circle" style="color: var(--primary-purple);"></i> Add a partner
        </h2>

        <?php echo $partner_message; ?>

        <form method="POST" action="panel.php" class="profile-form">
            <input type="hidden" name="action" value="add_partner">
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Discord Invite Link *</label>
                <div class="input-wrapper">
                    <i class="fas fa-link"></i>
                    <input type="url" name="invite_link" required placeholder="https://discord.gg/...">
                </div>
                <small style="color: var(--text-secondary); display: block; margin-top: 5px;">The name, logo and members will be retrieved automatically.</small>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Short Description (150 characters max) *</label>
                <div class="input-wrapper">
                    <i class="fas fa-align-left"></i>
                    <input type="text" name="description" maxlength="150" required placeholder="The largest community...">
                </div>
            </div>

            <div style="border-top: 1px dashed rgba(255,255,255,0.1); margin: 1.5rem 0; padding-top: 1rem;">
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;"><i class="fas fa-exclamation-triangle"></i> Fill only if the Discord link fails:</p>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>Manual Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-server"></i>
                        <input type="text" name="name" placeholder="Ex: Inazuma Eleven FR">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>Manual Image URL</label>
                    <div class="input-wrapper">
                        <i class="fas fa-image"></i>
                        <input type="text" name="picture_url" placeholder="https://...">
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                <input type="checkbox" id="pioneer" name="pioneer" style="width: 20px; height: 20px; cursor: pointer;">
                <label for="pioneer" style="margin: 0; cursor: pointer; color: #f1c40f;">
                    <i class="fas fa-crown"></i> Mark this server as "Pioneer"
                </label>
            </div>

            <button type="submit" class="other-button" style="width: 100%; justify-content: center;">
                <i class="fas fa-save"></i> Save Partner
            </button>
        </form>
    </div>

    <div class="profile-card" style="max-width: 600px; margin: 2rem auto; text-align: left;">
        <h2 style="font-size: 1.2rem; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <i class="fas fa-user-shield" style="color: var(--primary-purple);"></i> Manage Grades
        </h2>

        <?php echo $grade_message; ?>

        <form method="POST" action="panel.php" class="profile-form">
            <input type="hidden" name="action" value="update_grade">
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Exact Player Username *</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="target_username" required placeholder="Ex: Gouenji99">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>New Role *</label>
                <div class="input-wrapper" style="padding-right: 15px;">
                    <select name="new_grade" required style="background: transparent; border: none; color: white; padding: 10px 0; flex: 1; outline: none; cursor: pointer;">
                        <option value="Membre" style="color: #2c3e50;">Membre (Default)</option>
                        <option value="VIP" style="color: #2c3e50;">VIP</option>
                        <option value="Partenaire" style="color: #2c3e50;">Partner</option>
                        <option value="Modérateur" style="color: #2c3e50;">Moderator</option>
                        <option value="Administrateur" style="color: #2c3e50;">Administrator</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="other-button" style="width: 100%; justify-content: center;">
                <i class="fas fa-sync-alt"></i> Update Grade
            </button>
        </form>
    </div>

</main>

<?php
require 'assets/footer.php';
$footer = new Footer();
$footer->render();
?>
</body>
</html>