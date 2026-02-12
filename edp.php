<?php
session_start();
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';
require 'assets/ladder.php';
require 'assets/privacy_popup.php';

# Titre
$header = new Header("SECTEUR V - Classement");

// Affiche le header
$header->render();
?>

    <main class="ladder-main">
        <h1>L'Échelle du <span style="color: var(--primary-purple)">Pouvoir</span></h1>
        
        <p class="subtitle">
            Le système de classement <b>absolu</b> du Secteur&nbsp;V.
        </p>

        <button class="cta-button" onclick="openPrivacyModal()">
            <i class="fab fa-discord"></i> Rejoindre la compétition
        </button>

        <div class="process-container">
            <div class="process-step">
                <div class="step-icon"><i class="fas fa-gamepad"></i></div>
                <h3>1. Défiez</h3>
                <p>Trouvez un adversaire via notre matchmaking.</p>
            </div>
            
            <div class="process-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>

            <div class="process-step">
                <div class="step-icon"><i class="fas fa-trophy"></i></div>
                <h3>2. Triomphez</h3>
                <p>Jouez le match sur Victory Road et remportez la victoire.</p>
            </div>

            <div class="process-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>

            <div class="process-step">
                <div class="step-icon"><i class="fas fa-chart-line"></i></div>
                <h3>3. Progressez</h3>
                <p>Votre EDP est mis à jour instantanément après validation.</p>
            </div>
        </div>
    </main> 

    <div class="scroll-indicator-container">
        <a href="#rules" class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>

    <section id="rules" class="info-section">
        <h2><span style="color: var(--primary-purple)">Règle</span>ment</h2>
        <div class="info-section-content">
            <div class="info-block">
                <div class="info-text">
                    <h3>Les Mathématiques de la Victoire</h3>
                    <p>
                        Nous utilisons une variante du système ELO&nbsp;: le <strong>système EDP</strong>. Chaque joueur commence à 1000 points.
                    </p>
                    <ul class="info-list-ladder">
                        <li>
                            <i class="fas fa-balance-scale"></i> 
                            <span class="ladder-li"><strong>Équité&nbsp;:</strong> Battre un joueur mieux classé rapporte beaucoup de points. Battre un débutant en rapporte peu.</span>
                        </li>
                        <li>
                            <i class="fas fa-shield-alt"></i> 
                            <span class="ladder-li"><strong>Protection&nbsp;:</strong> Les défaites contre des adversaires trop forts sont moins pénalisantes.</span>
                        </li>
                    </ul>
                </div>
                <div class="info-visual">
                    <img src="assets/img/img_3.webp" alt="Illustration des joueurs de Raimon devant leur entraîneur" class="feature-img">
                </div>
            </div>

            <div class="info-block reverse">
                <div class="info-text">
                    <h3>Protocole de Match</h3>
                    <p>
                        Pour garantir l'intégrité du classement, chaque match doit suivre le <strong>Protocole Omega</strong>.
                    </p>
                    <p>
                        Une fois le match terminé, le vainqueur déclare le score. Le perdant doit confirmer. En cas de litige, les <strong>Arbitres du Secteur V</strong> interviennent sur preuves (screenshots).
                    </p>
                    <a href="rules.php" class="link-arrow">Lire le règlement complet <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="info-visual">
                    <img src="assets/img/img_4.webp" alt="Illustration d'Astero Black se faisant punir par les arbitres" class="feature-img">
                </div>
            </div>

        </div>
    </section>

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