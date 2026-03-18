<?php
// 404.php

require_once 'assets/init_session.php';
require 'db.php';
require 'assets/header.php';
require 'assets/footer.php';

# Titre
$header = new Header(__('err_404_title'));

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
            <h1><?php echo __('err_404_h1'); ?></h1>
            <p>
                <?php echo __('err_404_p'); ?>
            </p>
            
            <div class="error-actions">
                <button class="other-button" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> <?php echo __('err_404_btn'); ?>
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
</body>
</html>