<?php
session_start();
require 'db.php';
require 'assets/header.php';
require 'assets/footer.php';
require 'assets/dashboard.php';
require 'assets/privacy_popup.php';

// Titre
$header = new Header("SECTEUR V - Accueil");

// Affiche le header
$header->render();
?>

<!-- Si l'utilisateur est connecté, affiche le dashboard, sinon la page d'accueil classique -->
<?php if (isset($_SESSION['user_id'])): ?>

<?php
$dashboard = new Dashboard();

// Affiche le dashboard
$dashboard->render();
?>

<?php else: ?>
    <!-- Page d'accueil classique pour les visiteurs non connectés -->
    <main>
        <h1>SECTEUR <img src="assets/img/v.webp" alt="V" class="v-icon-dark"> <img src="assets/img/v_light.webp" alt="V" class="v-icon-light"></h1>
        <p class="subtitle">Le challenge <b>ultime</b> pour les fans de <b>Inazuma Eleven</b>.</p>
        
            <button class="cta-button" onclick="openPrivacyModal()">
                <i class="fab fa-discord"></i> Rejoindre la compétition
            </button>

        <div class="cards-container">
            <div class="card">
                <div class="card-icon" style="color: #e74c3c;"><i class="fas fa-medal"></i></div>
                <h3>Classement</h3>
                <p>Un système (EDP) pour vous mesurer aux meilleurs joueurs du monde.</p>
            </div>
            <div class="card">
                <div class="card-icon" style="color: #9ae73c;"><i class="fas fa-users-cog"></i></div>
                <h3>Gestion d'Équipe</h3>
                <p>Enregistrez votre équipe et affichez-la sur votre profil joueur.</p>
            </div>
            <div class="card">
                <div class="card-icon" style="color: #3c78e7;"><i class="fas fa-gamepad"></i></div>
                <h3>Matchmaking</h3>
                <p>Trouvez rapidement un adversaire à votre hauteur pour un duel acharné.</p>
            </div>
        </div>
    </main> 
        
    <div class="scroll-indicator-container">
            <a href="#presentation" class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
            </a>
    </div>

<section id="presentation" class="info-section">
        <div class="info-section-content">
            <div class="info-block">
                <div class="info-text">
                    <h2>Dominez la Victory Road</h2>
                    <p>
                        La route vers le sommet est longue et semée d'embûches, mais pour espérer y parvenir, il vous faut un objectif clair.
                    </p>
                    <p>
                        Le <strong>Secteur Victory</strong> centralise la scène compétitive pour offrir aux joueurs de Victory Road un espace de progression simple et accessible.
                    </p>
                    <ul class="info-list">
                        <li><i class="fas fa-check-circle"></i>Gratuit et Open Source</li>
                        <li><i class="fas fa-check-circle"></i>Classement mis à jour en temps réel</li>
                        <li><i class="fas fa-check-circle"></i>Communauté de stratèges passionnés</li>
                    </ul>
                </div>
                <div class="info-visual">
                    <img src="assets/img/img_1.webp" alt="Illustration du stade de la route du sacre" class="feature-img">
                </div>
            </div>

            <div class="info-block reverse">
                <div class="info-text">
                    <h2>Un Football <del>régulé</del> encadré</h2>
                    <p>
                        Loin des scores décidés à l'avance, notre vision du <strong>Secteur Victory</strong> est celle de l'équité absolue. Nous fournissons la structure, les règles et l'arène, mais seule la victoire sur le terrain compte. 
                    </p>
                    <a href="classement.php" class="link-arrow">En savoir plus sur le classement <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="info-visual">
                    <img src="assets/img/img_2.webp" alt="Illustration de Quentin Cinquedea" class="feature-img">
                </div>
            </div>
        </div>
    </section>

<?php endif;?>

<?php
$popup = new PrivacyPopup();
$footer = new Footer();

// Ajoute le popup de confidentialité
$popup->render();

// Affiche le footer
$footer->render();
?>
    <script src="script.js"></script>
</body>
</html>