<?php
require_once 'assets/init_session.php';
require 'db.php';
require 'assets/header.php';
require 'assets/footer.php';
require 'assets/dashboard.php';
require 'assets/privacy_popup.php';

// Titre
$header = new Header(__('idx_title'));

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
        <h1><?php echo __('idx_h1'); ?> <img src="assets/img/v.webp" alt="V" class="v-icon"></h1>
        <p class="subtitle"><?php echo __('idx_subtitle'); ?></p>
        
            <button class="cta-button" onclick="openPrivacyModal()">
                <i class="fab fa-discord"></i> <?php echo __('idx_cta'); ?>
            </button>

        <div class="cards-container">
            <div class="card">
                <div class="card-icon" style="color: #e74c3c;"><i class="fas fa-medal"></i></div>
                <h3><?php echo __('idx_card1_title'); ?></h3>
                <p><?php echo __('idx_card1_desc'); ?></p>
            </div>
            <div class="card">
                <div class="card-icon" style="color: #9ae73c;"><i class="fas fa-users-cog"></i></div>
                <h3><?php echo __('idx_card2_title'); ?></h3>
                <p><?php echo __('idx_card2_desc'); ?></p>
            </div>
            <div class="card">
                <div class="card-icon" style="color: #3c78e7;"><i class="fas fa-gamepad"></i></div>
                <h3><?php echo __('idx_card3_title'); ?></h3>
                <p><?php echo __('idx_card3_desc'); ?></p>
            </div>
        </div>
    </main> 
        
    <div class="scroll-indicator-container">
            <a href="#presentation" class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
            </a>
    </div>

<section id="presentation" class="info-section">
    <h2><?php echo __('idx_pres_title1'); ?><span style="color: var(--primary-purple)"><?php echo __('idx_pres_title2'); ?></span></h2>
        <div class="info-section-content">
            <div class="info-block">
                <div class="info-text">
                    <h3><?php echo __('idx_pres_sub1'); ?></h3>
                    <p>
                        <?php echo __('idx_pres_p1'); ?>
                    </p>
                    <p>
                        <?php echo __('idx_pres_p2'); ?>
                    </p>
                    <ul class="info-list">
                        <li><i class="fas fa-check-circle"></i><?php echo __('idx_pres_li1'); ?></li>
                        <li><i class="fas fa-check-circle"></i><?php echo __('idx_pres_li2'); ?></li>
                        <li><i class="fas fa-check-circle"></i><?php echo __('idx_pres_li3'); ?></li>
                    </ul>
                </div>
                <div class="info-visual">
                    <img src="assets/img/img_1.webp" alt="Illustration du stade de la route du sacre" class="feature-img">
                </div>
            </div>

            <div class="info-block reverse">
                <div class="info-text">
                    <h3><?php echo __('idx_pres_sub2'); ?></h3>
                    <p>
                        <?php echo __('idx_pres_p3'); ?>
                    </p>
                    <a href="edp.php" class="link-arrow"><?php echo __('idx_pres_link'); ?> <i class="fas fa-arrow-right"></i></a>
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
    
</body>
</html>