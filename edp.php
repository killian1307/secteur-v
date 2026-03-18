<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';
require 'assets/privacy_popup.php';

# Titre
$header = new Header(__('edp_title'));

// Affiche le header
$header->render();
?>

    <main class="ladder-main">
        <h1><?php echo __('edp_h1_1'); ?> <span style="color: var(--primary-purple)"><?php echo __('edp_h1_2'); ?></span></h1>
        
        <p class="subtitle">
            <?php echo __('edp_subtitle'); ?>
        </p>

        <?php if (!isset($_SESSION['user_id'])) { ?>
        <button class="cta-button" onclick="openPrivacyModal()">
            <i class="fab fa-discord"></i> <?php echo __('edp_join_btn'); ?>
        </button>
        <?php } ?>

        <div class="process-container">
            <div class="process-step">
                <div class="step-icon"><i class="fas fa-gamepad"></i></div>
                <h3><?php echo __('edp_step1_title'); ?></h3>
                <p><?php echo __('edp_step1_desc'); ?></p>
            </div>
            
            <div class="process-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>

            <div class="process-step">
                <div class="step-icon"><i class="fas fa-trophy"></i></div>
                <h3><?php echo __('edp_step2_title'); ?></h3>
                <p><?php echo __('edp_step2_desc'); ?></p>
            </div>

            <div class="process-arrow">
                <i class="fas fa-chevron-right"></i>
            </div>

            <div class="process-step">
                <div class="step-icon"><i class="fas fa-chart-line"></i></div>
                <h3><?php echo __('edp_step3_title'); ?></h3>
                <p><?php echo __('edp_step3_desc'); ?></p>
            </div>
        </div>
    </main> 

    <div class="scroll-indicator-container">
        <a href="#rules" class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>

    <section id="rules" class="info-section">
        <h2><span style="color: var(--primary-purple)"><?php echo __('edp_rules_h2_1'); ?></span><?php echo __('edp_rules_h2_2'); ?></h2>
        <div class="info-section-content">
            <div class="info-block">
                <div class="info-text">
                    <h3><?php echo __('edp_math_title'); ?></h3>
                    <p>
                        <?php echo __('edp_math_p1'); ?>
                    </p>
                    <ul class="info-list-ladder">
                        <li>
                            <i class="fas fa-balance-scale"></i> 
                            <span class="ladder-li"><?php echo __('edp_math_li1'); ?></span>
                        </li>
                        <li>
                            <i class="fas fa-shield-alt"></i> 
                            <span class="ladder-li"><?php echo __('edp_math_li2'); ?></span>
                        </li>
                    </ul>
                </div>
                <div class="info-visual">
                    <img src="assets/img/img_3.webp" alt="Illustration des joueurs de Raimon devant leur entraîneur" class="feature-img">
                </div>
            </div>

            <div class="info-block reverse">
                <div class="info-text">
                    <h3><?php echo __('edp_proto_title'); ?></h3>
                    <p>
                        <?php echo __('edp_proto_p1'); ?>
                    </p>
                    <p>
                        <?php echo __('edp_proto_p2'); ?>
                    </p>
                    <a href="rules.php" class="link-arrow"><?php echo __('edp_proto_link'); ?> <i class="fas fa-arrow-right"></i></a>
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
</body>
</html>