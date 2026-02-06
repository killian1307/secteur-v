<?php
session_start();
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

# Titre
$header = new Header("SECTEUR V - Match");

// Affiche le header
$header->render();
?>

<main class="dashboard-container">
        <h1>règlement</h1>
        <p class="subtitle">Le règlement est en cours de construction...</p>
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