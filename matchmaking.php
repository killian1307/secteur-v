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

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Joueur';

// Sécurise l'affichage pour éviter les failles XSS
$safeName = htmlspecialchars($username);

# Titre
$header = new Header("SECTEUR V - Match");

// Affiche le header
$header->render();
?>
<main class="dashboard-container">
        <h1>Bienvenue, <span style="color:var(--primary-purple)"><?php echo $safeName ?></span>.</h1>
        <p class="subtitle">Les matchs sont en cours de construction...</p>
</main>

<section>
</section>

<?php
$footer = new Footer();

// Affiche le footer
$footer->render();
?>
    <script src="script.js"></script>
</body>
</html>