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
                <p>' . __('footer_desc') . '</p>
            </div>
            <div class="footer-col">
                <h4>' . __('footer_links_title') . '</h4>
                <ul>
                    <li><a href="edp.php">' . __('footer_how_it_works') . '</a></li>
                    <li><a href="supports.php">' . __('nav_partners') . '</a></li>
                    <li><a href="rules.php">' . __('footer_rules') . '</a></li>
                    <li><a href="mailto:secteur-v@letterk.me">' . __('footer_contact') . '</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>' . __('footer_legal_title') . '</h4>
                <ul>
                    <li><a href="legal.php">' . __('footer_legal') . '</a></li>
                    <li><a href="privacy.php">' . __('footer_privacy') . '</a></li>
                    <li><a href="terms.php">' . __('footer_cgu') . '</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>' . __('footer_follow_us') . '</h4>
                <div class="social-links">
                    <a href="https://x.com/secteurvictory" target="_blank"><i class="fab fa-x-twitter"></i></a>
                    <a href="https://discord.gg/85AT6gGNGD" target="_blank"><i class="fab fa-discord"></i></a>
                    <a href="https://github.com/killian1307/secteur-v" target="_blank"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>' . __('footer_copyright') . '</p>
        </div>
    </footer>
    <script src="script.js?v=' . filemtime(__DIR__ . '/../script.js') . '"></script>';
    }
}
?>