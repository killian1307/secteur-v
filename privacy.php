<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header(__('priv_page_title'));
$header->render();
?>

<style>
    .legal-content { max-width: 800px; margin: 0 auto; line-height: 1.6; color: var(--text-secondary); text-align: left; }
    .legal-content h2 { color: var(--text-primary); margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
    .legal-content ul { margin-left: 20px; margin-bottom: 1rem; list-style-type: disc; }
    .legal-content li { margin-bottom: 0.5rem; }
</style>

<main class="dashboard-container">
    <h1><?php echo __('priv_h1_1'); ?> <span style="color:var(--primary-purple)"><?php echo __('priv_h1_2'); ?></span></h1>
    <p class="subtitle"><?php echo __('priv_subtitle'); ?></p>

    <div class="mm-view active legal-content" style="display: block;">
        <h2><?php echo __('priv_h2_1'); ?></h2>
        <p><?php echo __('priv_p_1'); ?></p>
        <ul>
            <li><?php echo __('priv_li_1_1'); ?></li>
            <li><?php echo __('priv_li_1_2'); ?></li>
            <li><?php echo __('priv_li_1_3'); ?></li>
            <li><?php echo __('priv_li_1_4'); ?></li>
            <li><?php echo __('priv_li_1_5'); ?></li>
            <li><?php echo __('priv_li_1_6'); ?></li>
        </ul>

        <h2><?php echo __('priv_h2_2'); ?></h2>
        <p><?php echo __('priv_p_2'); ?></p>
        <ul>
            <li><?php echo __('priv_li_2_1'); ?></li>
            <li><?php echo __('priv_li_2_2'); ?></li>
            <li><?php echo __('priv_li_2_3'); ?></li>
        </ul>
        <p><?php echo __('priv_p_3'); ?></p>

        <h2><?php echo __('priv_h2_3'); ?></h2>
        <p><?php echo __('priv_p_4'); ?></p>
        <p><?php echo __('priv_p_5'); ?></p>

        <h2><?php echo __('priv_h2_4'); ?></h2>
        <p><?php echo __('priv_p_6'); ?></p>
    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>