<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header(__('nav_app_settings'));
$header->render();
?>

<style>
    .settings-container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        background: var(--background-card);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    .setting-info h3 {
        margin: 0 0 5px 0;
        color: var(--text-primary);
        font-size: 1.1rem;
    }
    .setting-info p {
        margin: 0;
        color: var(--text-secondary);
        font-size: 0.9rem;
    }
    
    /* Toggle Switch Styles */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,0.5);
        transition: .4s;
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: var(--primary-purple);
        border-color: var(--primary-purple);
    }
    input:checked + .slider:before {
        transform: translateX(26px);
        background-color: #000; /* Dark circle when active looks good with yellow/gold */
    }
</style>

<main class="dashboard-container">
    <h1><?php echo __('nav_app_settings'); ?></h1>
    
    <div class="settings-container" id="appSettingsContent" style="display: none;">
        
        <div class="setting-item">
            <div class="setting-info">
                <h3><?php echo __('set_autostart_title'); ?></h3>
                <p><?php echo __('set_autostart_desc'); ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" id="autoStartToggle" onchange="toggleAutoStart(this)">
                <span class="slider"></span>
            </label>
        </div>

    </div>
    
    <div id="notClientMessage" style="text-align: center; color: var(--text-secondary); margin-top: 2rem;">
        <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
        <p><?php echo __('set_not_client'); ?></p>
    </div>

</main>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // Check if we are in the Proton app
        if (window.secteurV) {
            document.getElementById('appSettingsContent').style.display = 'block';
            document.getElementById('notClientMessage').style.display = 'none';

            // We need to fetch the initial state of the autostart setting from the client
            // Assuming your Proton bridge has a method to get this value (e.g., getAutoStartStatus)
            // If it doesn't, you might need to add one to your Proton Rust code.
            // For now, I'll wrap it in a try-catch in case it's not implemented yet.
            try {
                if(typeof window.secteurV.getAutoStartStatus === 'function'){
                    const isAutoStartEnabled = await window.secteurV.getAutoStartStatus();
                    document.getElementById('autoStartToggle').checked = isAutoStartEnabled;
                }
            } catch (e) {
                console.warn("Could not fetch initial autostart status from client.", e);
            }

        } else {
            // Not in client, keep the settings hidden and show the message
            document.getElementById('appSettingsContent').style.display = 'none';
            document.getElementById('notClientMessage').style.display = 'block';
        }
    });

    function toggleAutoStart(checkbox) {
        if (window.secteurV && typeof window.secteurV.toggleAutoStart === 'function') {
            const isEnabled = checkbox.checked;
            window.secteurV.toggleAutoStart(isEnabled);
            console.log("Auto-start toggled to: " + isEnabled);
        } else {
            console.error("Proton bridge or toggleAutoStart function not found.");
            // Revert the visual toggle if the function failed
            checkbox.checked = !checkbox.checked;
        }
    }
</script>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>