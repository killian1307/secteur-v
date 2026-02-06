    <?php
class PrivacyPopup {

public function render() {
        echo '<div id="privacyModal" class="modal-overlay" onclick="closePrivacyModal(event)">
    <div class="modal-box">
        <div class="modal-header">
            <i class="fas fa-user-shield"></i> Protocole de Données
        </div>
        
        <div class="modal-body">
            <p>Avant d&#39entrer sur le terrain, nous tenons à jouer franc-jeu concernant vos données collectées :</p>
            
            <ul class="data-list">
                <li><i class="fab fa-discord"></i> <strong>ID Discord :</strong> Uniquement pour vous identifier et vous connecter.</li>
                <li><i class="fas fa-image"></i> <strong>Avatar :</strong> Pour afficher votre photo de profil sur le site.</li>
                <li><i class="fas fa-envelope"></i> <strong>Email :</strong> (Facultatif) Pour vous contacter. Vous pourrez le supprimer ou le modifier via votre profil à tout moment.</li>
            </ul>

            <p class="small-text">
                Ce projet est <strong>Open Source</strong>. Vous pouvez vérifier notre code pour voir comment nous traitons vos données.
            </p>

            <div class="links-row">
                <a href="https://github.com/killian1307/secteur-v" target="_blank" class="text-link"><i class="fab fa-github"></i> Voir le code source</a>
                <span>|</span>
                <a href="privacy.php" class="text-link">Politique de confidentialité</a>
            </div>

            <div class="modal-footer">
                <button class="other-button" onclick="acceptAndRedirect()">
                    Accepter & Continuer <i class="fas fa-arrow-right"></i>
                </button>
            <button class="ghost-btn" onclick="closePrivacyModal(null)">Annuler</button>
        </div>
        </div>
    </div>
</div>';
    }
}
?>