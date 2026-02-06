    <?php
class Footer {
    private $customClass;

    public function __construct($customClass = '') {
        $this->customClass = $customClass;
    }

    public function render() {
        echo '<footer class="site-footer '. $this->customClass .'">
        <div class="footer-container">
            <div class="footer-col">
                <h3>SECTEUR V</h3>
                <p>Le challenge ultime pour les fans de Inazuma Eleven.</p>
            </div>
            <div class="footer-col">
                <h4>Liens Utiles</h4>
                <ul>
                    <li><a href="classement.php">Comment ça marche</a></li>
                    <li><a href="copyright.php">Sources</a></li>
                    <li><a href="mailto:secteur-v@letterk.me">Nous Contacter</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Légal</h4>
                <ul>
                    <li><a href="#">Mentions Légales</a></li>
                    <li><a href="#">Confidentialité</a></li>
                    <li><a href="#">CGU</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Suivez-nous</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-x-twitter"></i></a>
                    <a href="https://discord.gg/A98PfnH8SC" target="_blank"><i class="fab fa-discord"></i></a>
                    <a href="https://github.com/killian1307/secteur-v" target="_blank"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 SECTEUR V - Fan Project Inazuma Eleven.</p>
        </div>
    </footer>';
    }
}
?>