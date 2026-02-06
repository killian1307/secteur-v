<?php
// 404.php

session_start();
require 'db.php';
require 'assets/Header.php';
require 'assets/Footer.php';

# Titre
$header = new Header("Secteur V - Erreur 404");

# Affiche le header
$header->render();
?>

<main class="error-page-container">
    <div class="error-grid">
        <div class="error-icon-col">
            <div class="robot-wrapper">
                <i class="fas fa-robot"></i>
                <i class="fas fa-square-full card-overlay"></i>
            </div>
        </div>

        <div class="error-text-col">
            <h1>404 HORS-JEU</h1>
            <p>
                Halte là ! Le <strong>Secteur V</strong> ne reconnait pas cette page. 
            </p>
            
            <div class="error-actions">
                <button class="cta-button" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Retour au vestiaire
                </button>
            </div>
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