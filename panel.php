<?php
require_once 'assets/init_session.php';
require 'db.php';

// Verif connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Verif grade "Créateur"
$stmt = $pdo->prepare("SELECT grade FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['grade'] !== 'Créateur') {
    require 'assets/header.php';
    $header = new Header("Accès Refusé");
    $header->render();
    echo "<main class='dashboard-container' style='text-align:center; padding: 5rem 0;'>
            <i class='fas fa-lock' style='font-size: 4rem; color: #e74c3c; margin-bottom: 1rem;'></i>
            <h1>Accès <span style='color:#e74c3c;'>Refusé</span></h1>
            <p>Vous n'avez pas les autorisations nécessaires pour accéder à cette page.</p>
            <a href='index.php' class='other-button' style='margin-top: 2rem;'>Retour à l'accueil</a>
          </main>";
    require 'assets/footer.php';
    $footer = new Footer();
    $footer->render();
    exit;
}

$message = "";

// Formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invite_link = trim($_POST['invite_link']);
    $description = trim($_POST['description']);
    $pioneer = isset($_POST['pioneer']) ? 1 : 0;
    
    // Valeurs par défaut issues du formulaire (au cas où l'API échoue)
    $name = trim($_POST['name']);

    // Discord API
    if (!empty($invite_link)) {
        $invite_code = basename(parse_url($invite_link, PHP_URL_PATH));

        // 2. Interroger l'API Discord (avec le paramètre pour avoir le nombre de membres)
        $api_url = "https://discord.com/api/v10/invites/" . $invite_code . "?with_counts=true";
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !IS_LOCAL);
        $response = curl_exec($ch);
        curl_close($ch);

        $discord_data = json_decode($response, true);

        // Si la réponse est valide et contient les données du serveur
        if (isset($discord_data['guild'])) {
            // Le nom officiel du serveur
            $name = $discord_data['guild']['name'];
            
            // Le nombre de membres
            if (isset($discord_data['approximate_member_count'])) {
                $member_count = $discord_data['approximate_member_count'];
            }

            // L'icône du serveur
            if (isset($discord_data['guild']['icon'])) {
                $guild_id = $discord_data['guild']['id'];
                $icon_hash = $discord_data['guild']['icon'];
                // On génère le lien de l'image en bonne qualité
                $picture_url = "https://cdn.discordapp.com/icons/{$guild_id}/{$icon_hash}.png?size=256";
            }
        }
    }

    if (empty($picture_url)) {
        $picture_url = 'assets/img/default_user.webp';
    }


    if (empty($name) || empty($description) || empty($invite_link)) {
        $message = "<div class='alert alert-danger' style='background: rgba(231,76,60,0.1); color: #e74c3c; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #e74c3c;'>Veuillez remplir les champs obligatoires (Nom, Description, Lien).</div>";
    } else {
        // Insertion dans la base de données
        $insert = $pdo->prepare("INSERT INTO supports (name, description, picture_url, invite_link, member_count, pioneer) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($insert->execute([$name, $description, $picture_url, $invite_link, $member_count, $pioneer])) {
            $message = "<div class='alert alert-success' style='background: rgba(46, 204, 113, 0.1); color: #2ecc71; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #2ecc71;'>Le partenaire <strong>" . htmlspecialchars($name) . "</strong> a été ajouté avec succès !</div>";
        } else {
            $message = "<div class='alert alert-danger' style='background: rgba(231,76,60,0.1); color: #e74c3c; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #e74c3c;'>Une erreur est survenue lors de l'ajout.</div>";
        }
    }
}

require 'assets/header.php';
$header = new Header("Panel Créateur");
$header->render();
?>

<main class="dashboard-container">
    <h1>Panel <span style="color:var(--primary-purple)">Créateur</span></h1>
    <p class="subtitle">Gestion des partenaires du Secteur V.</p>

    <div class="profile-card" style="max-width: 600px; margin: 2rem auto; text-align: left;">
        <h2 style="font-size: 1.2rem; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <i class="fas fa-plus-circle" style="color: var(--primary-purple);"></i> Ajouter un nouveau partenaire
        </h2>

        <?php echo $message; ?>

        <form method="POST" action="panel.php" class="profile-form">
            
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Nom du serveur *</label>
                <div class="input-wrapper">
                    <i class="fas fa-server"></i>
                    <input type="text" name="name" required placeholder="Ex: Inazuma Eleven FR">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Courte Description (150 caractères max) *</label>
                <div class="input-wrapper">
                    <i class="fas fa-align-left"></i>
                    <input type="text" name="description" maxlength="150" required placeholder="La plus grande communauté...">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Lien d'invitation Discord *</label>
                <div class="input-wrapper">
                    <i class="fas fa-link"></i>
                    <input type="url" name="invite_link" required placeholder="https://discord.gg/...">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                <input type="checkbox" id="pioneer" name="pioneer" style="width: 20px; height: 20px; cursor: pointer;">
                <label for="pioneer" style="margin: 0; cursor: pointer; color: #f1c40f;">
                    <i class="fas fa-crown"></i> Marquer ce serveur comme "Pionnier"
                </label>
            </div>

            <button type="submit" class="other-button" style="width: 100%; justify-content: center;">
                <i class="fas fa-save"></i> Enregistrer le partenaire
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