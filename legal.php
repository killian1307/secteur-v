<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header(__('leg_title'));
$header->render();
?>

<style>
    .legal-content { max-width: 800px; margin: 0 auto; line-height: 1.6; color: var(--text-secondary); text-align: left; }
    .legal-content h2 { color: var(--text-primary); margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
    .legal-content p { margin-bottom: 1rem; }
    .legal-content a { color: var(--primary-purple); text-decoration: none; }
    .legal-content a:hover { text-decoration: underline; }
</style>

<main class="dashboard-container">
    <h1><?php echo __('leg_h1_1'); ?> <span style="color:var(--primary-purple)"><?php echo __('leg_h1_2'); ?></span></h1>
    <p class="subtitle"><?php echo __('leg_subtitle'); ?></p>

    <div class="mm-view active legal-content" style="display: block;">
        <h2><?php echo __('leg_h2_1'); ?></h2>
        <p><?php echo __('leg_p_1'); ?></p>

        <h2><?php echo __('leg_h2_2'); ?></h2>
        <p><?php echo __('leg_p_2'); ?></p>

        <p><?php echo __('leg_p_3'); ?></p>

        <h2><?php echo __('leg_h2_3'); ?></h2>
        <p><?php echo __('leg_p_4'); ?></p>

        <h2><?php echo __('leg_h2_4'); ?></h2>
        <p><?php echo __('leg_p_5'); ?></p>
    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>