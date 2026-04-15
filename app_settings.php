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
        padding: 1.5rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .setting-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .setting-item:first-child {
        padding-top: 0;
    }
    .setting-info {
        flex: 1;
        padding-right: 20px;
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
        flex-shrink: 0;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: rgba(0,0,0,0.5); transition: .4s; border-radius: 24px;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .slider:before {
        position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px;
        background-color: white; transition: .4s; border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--primary-purple); border-color: var(--primary-purple); }
    input:checked + .slider:before { transform: translateX(26px); background-color: #000; }
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

        <div class="setting-item">
            <div class="setting-info">
                <h3><?php echo __('set_start_minimized_title'); ?></h3>
                <p><?php echo __('set_start_minimized_desc'); ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" id="startMinimizedToggle" onchange="toggleStartMinimized(this)">
                <span class="slider"></span>
            </label>
        </div>

        <div class="setting-item">
            <div class="setting-info">
                <h3><?php echo __('set_overlay_title'); ?></h3>
                <p><?php echo __('set_overlay_desc'); ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" id="overlayEnabledToggle" onchange="toggleOverlaySetting(this)">
                <span class="slider"></span>
            </label>
        </div>

        <div class="setting-item">
            <div class="setting-info">
                <h3><?php echo __('set_mute_title'); ?></h3>
                <p><?php echo __('set_mute_desc'); ?></p>
            </div>
            <label class="switch">
                <input type="checkbox" id="overlayMuteToggle" onchange="toggleOverlayMuteSetting(this)">
                <span class="slider"></span>
            </label>
        </div>

        <div class="setting-item">
            <div class="setting-info">
                <h3><?php echo __('set_volume_title'); ?></h3>
                <p><?php echo __('set_volume_desc'); ?></p>
            </div>
            <input type="range" id="overlayVolumeSlider" min="0" max="1" step="0.05" oninput="setOverlayVolumeSetting(this)" style="width: 150px; cursor: pointer;">
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

            try {
                // Fetch Auto-Start Status
                if(typeof window.secteurV.getAutoStartStatus === 'function'){
                    const isAutoStartEnabled = await window.secteurV.getAutoStartStatus();
                    document.getElementById('autoStartToggle').checked = isAutoStartEnabled;
                }
                
                // Fetch Start Minimized Status
                if(typeof window.secteurV.getStartMinimizedStatus === 'function'){
                    const isStartMinimizedEnabled = await window.secteurV.getStartMinimizedStatus();
                    document.getElementById('startMinimizedToggle').checked = isStartMinimizedEnabled;
                }

                // Fetch Overlay Settings
                if(typeof window.secteurV.getOverlaySettings === 'function'){
                    const ovSettings = await window.secteurV.getOverlaySettings();
                    document.getElementById('overlayEnabledToggle').checked = ovSettings.overlayEnabled;
                    document.getElementById('overlayMuteToggle').checked = ovSettings.overlayMuted;
                    document.getElementById('overlayVolumeSlider').value = ovSettings.overlayVolume;
                }
            } catch (e) {
                console.warn("Could not fetch initial settings from client.", e);
            }
        } else {
            // Not in client, keep the settings hidden and show the message
            document.getElementById('appSettingsContent').style.display = 'none';
            document.getElementById('notClientMessage').style.display = 'block';
        }
    });

    function toggleAutoStart(checkbox) {
        if (window.secteurV && typeof window.secteurV.toggleAutoStart === 'function') {
            window.secteurV.toggleAutoStart(checkbox.checked);
        } else {
            checkbox.checked = !checkbox.checked; // Revert if failed
        }
    }

    function toggleStartMinimized(checkbox) {
        if (window.secteurV && typeof window.secteurV.toggleStartMinimized === 'function') {
            window.secteurV.toggleStartMinimized(checkbox.checked);
        } else {
            checkbox.checked = !checkbox.checked; // Revert if failed
        }
    }

    // Overlay Setting Functions
    function toggleOverlaySetting(checkbox) {
        if (window.secteurV && window.secteurV.toggleOverlay) {
            window.secteurV.toggleOverlay(checkbox.checked);
        }
    }
    function toggleOverlayMuteSetting(checkbox) {
        if (window.secteurV && window.secteurV.toggleOverlayMute) {
            window.secteurV.toggleOverlayMute(checkbox.checked);
        }
    }
    function setOverlayVolumeSetting(slider) {
        if (window.secteurV && window.secteurV.setOverlayVolume) {
            window.secteurV.setOverlayVolume(slider.value);
        }
    }
</script>

<?php
$footer = new Footer();
$footer->render();
?>