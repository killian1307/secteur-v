<?php
session_start();
require 'db.php';
require 'assets/header.php';
require 'assets/footer.php';

# Titre
$header = new Header("SECTEUR V - Profil");

// Affiche le header
$header->render();
?>

    <main>
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