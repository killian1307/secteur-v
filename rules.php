<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header(__('rul_page_title'));
$header->render();
?>

<style>
    .rules-content { max-width: 800px; margin: 0 auto; line-height: 1.6; color: var(--text-secondary); text-align: left; }
    .rules-content h2 { color: var(--text-primary); margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
    .rules-content h3 { color: var(--primary-purple); margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 1.1rem; }
    .rules-content ul, .rules-content ol { margin-left: 20px; margin-bottom: 1rem; list-style-type: disc; }
    .rules-content ol { list-style-type: decimal; }
    .rules-content li { margin-bottom: 0.5rem; }
    .rules-content .highlight { color: var(--primary-purple); font-weight: bold; }
    .rules-content .warning-box { 
        background: rgba(231, 76, 60, 0.1); 
        border-left: 4px solid #e74c3c; 
        padding: 15px; 
        margin: 20px 0; 
        border-radius: 0 5px 5px 0; 
        color: #e74c3c;
    }
</style>

<main class="dashboard-container">
    <h1><?php echo __('rul_h1_1'); ?> <span style="color:var(--primary-purple)"><?php echo __('rul_h1_2'); ?></span></h1>
    <p class="subtitle"><?php echo __('rul_subtitle'); ?></p>

    <div class="mm-view active rules-content" style="display: block;">
        
        <h2><?php echo __('rul_h2_1'); ?></h2>
        <p><?php echo __('rul_p_1'); ?></p>
        <ul>
            <li><?php echo __('rul_li_1_1'); ?></li>
            <li><?php echo __('rul_li_1_2'); ?></li>
            <li><?php echo __('rul_li_1_3'); ?></li>
        </ul>

        <h2><?php echo __('rul_h2_2'); ?></h2>
        <p><?php echo __('rul_p_2'); ?></p>
        
        <h3><?php echo __('rul_h3_1'); ?></h3>
        <ol>
            <li><?php echo __('rul_li_2_1'); ?></li>
            <li><?php echo __('rul_li_2_2'); ?></li>
            <li><?php echo __('rul_li_2_3'); ?></li>
            <li><?php echo __('rul_li_2_4'); ?></li>
        </ol>

        <h3><?php echo __('rul_h3_2'); ?></h3>
        <ol>
            <li><?php echo __('rul_li_3_1'); ?></li>
            <li><?php echo __('rul_li_3_2'); ?></li>
            <li><?php echo __('rul_li_3_3'); ?></li>
            <li><?php echo __('rul_li_3_4'); ?></li>
        </ol>

        <h2><?php echo __('rul_h2_3'); ?></h2>
        <p><?php echo __('rul_p_3'); ?></p>
        <ul>
            <li><?php echo __('rul_li_4_1'); ?></li>
            <li><?php echo __('rul_li_4_2'); ?></li>
            <li><?php echo __('rul_li_4_3'); ?></li>
            <li><?php echo __('rul_li_4_4'); ?></li>
        </ul>

        <div class="warning-box">
            <?php echo __('rul_warning'); ?>
        </div>

    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>