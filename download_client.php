<?php
require_once 'assets/init_session.php';
require 'db.php';
require 'assets/header.php';
require 'assets/footer.php';

// Initialize the Header
$header = new Header(__('dl_title'));
$header->render();
?>

<main>
    <h1 class="dashboard-h1"><?php echo __('dl_h1_1'); ?> <span style="color: var(--primary-purple);"><?php echo __('dl_h1_2'); ?></span></h1>
    <p class="subtitle"><?php echo __('dl_subtitle'); ?></p>
    
    <button id="download-btn" onclick="downloadLatestRelease()" class="cta-button">
        <i class="fab fa-windows"></i> <span id="btn-text"><?php echo __('dl_cta'); ?></span>
    </button>

    <div class="cards-container" style="margin-top: 50px;">
        <div class="card">
            <div class="card-icon" style="color: #f1c40f;"><i class="fas fa-sitemap"></i></div>
            <h3><?php echo __('dl_card1_title'); ?></h3>
            <p><?php echo __('dl_card1_desc'); ?></p>
        </div>
        <div class="card">
            <div class="card-icon" style="color: #e74c3c;"><i class="fas fa-bell"></i></div>
            <h3><?php echo __('dl_card2_title'); ?></h3>
            <p><?php echo __('dl_card2_desc'); ?></p>
        </div>
        <div class="card">
            <div class="card-icon" style="color: #3498db;"><i class="fas fa-satellite-dish"></i></div>
            <h3><?php echo __('dl_card3_title'); ?></h3>
            <p><?php echo __('dl_card3_desc'); ?></p>
        </div>
    </div>

    <div style="margin-top: 50px;">
        <a href="/" class="retour-accueil">
            <i class="fas fa-arrow-left"></i> <?php echo __('mm_back_home'); ?>
        </a>
    </div>
    <script>
    async function downloadLatestRelease() {
        const btnText = document.getElementById('btn-text');
        const btn = document.getElementById('download-btn');
        const originalText = btnText.innerText;
        
        // On change le texte du bouton pour montrer que ça charge
        btnText.innerText = "Recherche de la mise à jour...";
        btn.style.opacity = "0.7";
        btn.disabled = true;

        try {
            // On interroge l'API GitHub pour votre repository exact
            const response = await fetch('https://api.github.com/repos/killian1307/secteur-v-client/releases/latest');
            const data = await response.json();
            
            // On cherche dans les fichiers de la release celui qui finit par ".exe"
            const exeAsset = data.assets.find(asset => asset.name.endsWith('.exe'));
            
            if (exeAsset) {
                // Si on le trouve, on force le navigateur à le télécharger !
                btnText.innerText = "Téléchargement en cours !";
                window.location.href = exeAsset.browser_download_url;
            } else {
                // S'il n'y a pas de .exe (erreur de compilation par ex), on redirige vers la page GitHub classique
                window.location.href = 'https://github.com/killian1307/secteur-v-client/releases/latest';
            }
        } catch (error) {
            console.error("Erreur avec l'API GitHub:", error);
            // En cas de bug réseau, on redirige vers la page GitHub classique
            window.location.href = 'https://github.com/killian1307/secteur-v-client/releases/latest';
        }

        // On remet le bouton à la normale après 3 secondes
        setTimeout(() => {
            btnText.innerText = originalText;
            btn.style.opacity = "1";
            btn.disabled = false;
        }, 3000);
    }
    </script>
</main> 

<?php
// Initialize and render the Footer
$footer = new Footer();
$footer->render();
?>