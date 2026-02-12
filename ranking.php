<?php
session_start();
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Joueur';

// Sécurise l'affichage pour éviter les failles XSS
$safeName = htmlspecialchars($username);

# Titre
$header = new Header("SECTEUR V - Classement");

// Affiche le header
$header->render();
?>
<main class="dashboard-container">
        <h1>Bienvenue, <span style="color:var(--primary-purple)"><?php echo $safeName ?></span>.</h1>
        <p class="subtitle">Le classement est en cours de construction...</p>
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