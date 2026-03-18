<?php
class PrivacyPopup {

public function render() {
        echo '<div id="privacyModal" class="modal-overlay" onclick="closePrivacyModal(event)">
    <div class="modal-box">
        <div class="modal-header">
            <i class="fas fa-user-shield"></i> ' . __('priv_title') . '
        </div>
        
        <div class="modal-body">
            <p>' . __('priv_intro') . '</p>
            
            <ul class="data-list">
                <li><i class="fab fa-discord"></i> <strong>' . __('priv_discord_id') . '</strong> ' . __('priv_discord_desc') . '</li>
                <li><i class="fas fa-image"></i> <strong>' . __('priv_avatar') . '</strong> ' . __('priv_avatar_desc') . '</li>
                <li><i class="fas fa-envelope"></i> <strong>' . __('priv_email') . '</strong> ' . __('priv_email_desc') . '</li>
            </ul>

            <p class="small-text">
                ' . __('priv_open_source') . ' <strong>Open Source</strong>. ' . __('priv_open_source_desc') . '
            </p>

            <div class="links-row">
                <a href="https://github.com/killian1307/secteur-v" target="_blank" class="text-link"><i class="fab fa-github"></i> ' . __('priv_github') . '</a>
                <span>|</span>
                <a href="privacy.php" class="text-link">' . __('priv_policy') . '</a>
            </div>
        </div>
            <div class="modal-footer">
                <button id="btn-accept-privacy" class="other-button" onclick="acceptAndRedirect()">
                    ' . __('priv_accept') . ' <i class="fas fa-arrow-right"></i>
                </button>
            <button class="ghost-btn" onclick="closePrivacyModal(null)">' . __('priv_cancel') . '</button>      
        </div>
    </div>
</div>';
    }
}
?>