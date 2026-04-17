<?php
require_once 'assets/init_session.php';
require 'db.php';
require 'assets/header.php';
require 'assets/footer.php';
require 'assets/dashboard.php';
require 'assets/privacy_popup.php';

$isMobile = preg_match("/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i", $_SERVER['HTTP_USER_AGENT']);

// Titre
$header = new Header(__('idx_title'));

// Affiche le header
$header->render();
?>

<?php if (isset($_SESSION['user_id'])): ?>

<?php
$dashboard = new Dashboard();
$dashboard->render();
?>

<?php else: ?>
    <main>
        <h1><?php echo __('idx_h1'); ?> <img src="assets/img/v.webp" alt="V" class="v-icon"></h1>
        <p class="subtitle"><?php echo __('idx_subtitle'); ?></p>
        
        <div class="hero-actions" id="web-btns">
            <?php if ($isMobile): ?>
                <button class="cta-button" onclick="handlePwaInstall()">
                    <i class="fas fa-mobile-alt"></i> <?php echo __('nav_install_app'); ?>
                </button>
            <?php else: ?>
                <a href="download_client.php" class="cta-button" style="text-decoration: none;">
                    <i class="fas fa-desktop"></i> <?php echo __('nav_download_client'); ?>
                </a>
            <?php endif; ?>

            <button class="secondary-outline-btn" onclick="openPrivacyModal()">
                <i class="fab fa-discord"></i> <?php echo __('idx_cta'); ?>
            </button>
        </div>

        <button class="cta-button" onclick="openPrivacyModal()" id="app-btns" style="display: none;">
            <i class="fab fa-discord"></i> <?php echo __('idx_cta'); ?>
        </button>

        <div class="cards-container">
            <div class="card">
                <div class="card-icon" style="color: #7289DA;"><i class="fab fa-discord"></i></div>
                <h3><?php echo __('idx_feat1_title'); ?></h3>
                <p><?php echo __('idx_feat1_desc'); ?></p>
            </div>
            <div class="card">
                <div class="card-icon" style="color: #e74c3c;"><i class="fas fa-users"></i></div>
                <h3><?php echo __('idx_card2_title'); ?></h3>
                <p><?php echo __('idx_card2_desc'); ?></p>
            </div>
            <div class="card">
                <div class="card-icon" style="color: #9ae73c;"><i class="fas fa-clipboard-list"></i></div>
                <h3><?php echo __('idx_feat3_title'); ?></h3>
                <p><?php echo __('idx_feat3_desc'); ?></p>
            </div>
        </div>
    </main> 
        
    <div class="scroll-indicator-container">
        <a href="#presentation" class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>

    <section id="presentation" class="info-section">
        <h2><?php echo __('idx_pres_title1'); ?> <span style="color: var(--primary-purple)"><?php echo __('idx_pres_title2'); ?></span></h2>
        
        <div class="info-section-content">
            <div class="info-block">
                <div class="info-text">
                    <h3><?php echo __('idx_mm_title'); ?></h3>
                    <p><?php echo __('idx_mm_desc1'); ?></p>
                    <p><?php echo __('idx_mm_desc2'); ?></p>
                    <ul class="info-list">
                        <li><i class="fas fa-medal"></i> <?php echo __('idx_mm_li1'); ?></li>
                        <li><i class="fas fa-shield-alt"></i> <?php echo __('idx_mm_li2'); ?></li>
                        <li><i class="fas fa-gavel"></i> <?php echo __('idx_mm_li3'); ?></li>
                    </ul>
                    <a href="edp.php" class="link-arrow"><?php echo __('idx_pres_link'); ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="info-visual">
                    <img src="assets/img/img_1.webp" alt="Matchmaking Secteur V" class="feature-img">
                </div>
            </div>

            <div class="info-block reverse client-highlight-block">
                <div class="info-text">
                    <?php if ($isMobile): ?>
                        <h3><i class="fas fa-mobile-alt" style="color: var(--primary-purple);"></i> <?php echo __('idx_client_title'); ?></h3>
                    <?php else: ?>
                        <h3><i class="fas fa-desktop" style="color: var(--primary-purple);"></i> <?php echo __('idx_client_title'); ?></h3>
                    <?php endif; ?>
                    <p><?php echo __('idx_client_desc1'); ?></p>
                    <p><?php echo __('idx_client_desc2'); ?></p>
                    <?php if ($isMobile): ?>
                        <button class="cta-button cta-section-button" onclick="handlePwaInstall()" style="width: fit-content; margin-top: 1.5rem; text-decoration: none; font-size: 1rem; padding: 1rem 2rem;">
                            <?php echo __('nav_install_app'); ?>
                        </button>
                    <?php else: ?>
                        <a href="download_client.php" class="cta-button cta-section-button" style="width: fit-content; margin-top: 1.5rem; text-decoration: none; font-size: 1rem; padding: 1rem 2rem;">
                            <?php echo __('nav_download_client'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="info-visual">
                    <img src="assets/img/img_2.webp" alt="Windows Client Overlay" class="feature-img client-glowing-img">
                </div>
            </div>
        </div>
    </section>

    <section class="roadmap-section">
        <h2><?php echo __('idx_roadmap_title1'); ?> <span style="color: var(--primary-purple)"><?php echo __('idx_roadmap_title2'); ?></span></h2>
        <p class="subtitle" style="margin-bottom: 4rem;"><?php echo __('idx_roadmap_subtitle'); ?></p>
        
        <div class="roadmap-grid">
            <div class="roadmap-card">
                <i class="fas fa-trophy roadmap-icon"></i>
                <h4><?php echo __('idx_rdm_tournaments'); ?></h4>
                <p><?php echo __('idx_rdm_tournaments_desc'); ?></p>
            </div>
            <div class="roadmap-card">
                <i class="fas fa-users-cog roadmap-icon"></i>
                <h4><?php echo __('idx_rdm_clans'); ?></h4>
                <p><?php echo __('idx_rdm_clans_desc'); ?></p>
            </div>
            <div class="roadmap-card">
                <i class="fas fa-star roadmap-icon"></i>
                <h4><?php echo __('idx_rdm_achievements'); ?></h4>
                <p><?php echo __('idx_rdm_achievements_desc'); ?></p>
            </div>
            <div class="roadmap-card">
                <i class="fas fa-plus roadmap-icon"></i>
                <h4><?php echo __('idx_rdm_more'); ?></h4>
                <p><?php echo __('idx_rdm_more_desc'); ?></p>
            </div>
        </div>
    </section>

<?php endif;?>

<?php
$popup = new PrivacyPopup();
$footer = new Footer();

$popup->render();
$footer->render();
?>

<script>
    if (window.secteurV) {
        const webBtns = document.getElementById('web-btns');
        const appBtns = document.getElementById('app-btns');
        if (webBtns) webBtns.style.display = 'none';
        if (appBtns) appBtns.style.display = 'inline-block';
    }
</script>

</body>
</html>