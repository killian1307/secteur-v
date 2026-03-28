<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header(__('trm_page_title'));
$header->render();
?>

<style>
    .legal-content { max-width: 800px; margin: 0 auto; line-height: 1.6; color: var(--text-secondary); text-align: left; }
    .legal-content h2 { color: var(--text-primary); margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
    .legal-content p { margin-bottom: 1rem; }
    .legal-content ul { margin-left: 2rem; margin-bottom: 1rem; }
    .legal-content li { margin-bottom: 0.5rem; }
</style>

<main class="dashboard-container">
    <h1><?php echo __('trm_h1_1'); ?> <span style="color:var(--primary-purple)"><?php echo __('trm_h1_2'); ?></span></h1>
    <p class="subtitle"><?php echo __('trm_subtitle'); ?></p>

    <div class="mm-view active legal-content" style="display: block;">
        <h2><?php echo __('trm_h2_1'); ?></h2>
        <p><?php echo __('trm_p_1'); ?></p>

        <h2><?php echo __('trm_h2_2'); ?></h2>
        <p><?php echo __('trm_p_2'); ?></p>
        <p><?php echo __('trm_p_3'); ?></p>

        <h2><?php echo __('trm_h2_3'); ?></h2>
        <p><?php echo __('trm_p_4'); ?></p>
        <ul>
            <li><?php echo __('trm_li_1'); ?></li>
            <li><?php echo __('trm_li_2'); ?></li>
            <li><?php echo __('trm_li_3'); ?></li>
            <li><?php echo __('trm_li_4'); ?></li>
        </ul>

        <h2><?php echo __('trm_h2_4'); ?></h2>
        <p><?php echo __('trm_p_5'); ?></p>
        <p><?php echo __('trm_p_6'); ?></p>
        
        <h2><?php echo __('cgu_chat_title'); ?></h2>
        <p><?php echo __('cgu_chat_p1'); ?></p>
        <p><?php echo __('cgu_chat_p2'); ?></p>
        <ul>
            <li><?php echo __('cgu_chat_li1'); ?></li>
            <li><?php echo __('cgu_chat_li2'); ?></li>
            <li><?php echo __('cgu_chat_li3'); ?></li>
        </ul>
        <p style="color: var(--primary-purple); font-weight: bold; background: rgba(255, 215, 0, 0.05); padding: 15px; border-radius: 8px; border-left: 4px solid var(--primary-purple);">
            <i class="fas fa-shield-alt" style="margin-right: 8px;"></i> <?php echo __('cgu_chat_warn'); ?>
        </p>

        <h2><?php echo __('trm_h2_5'); ?></h2>
        <p><?php echo __('trm_p_7'); ?></p>
    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>