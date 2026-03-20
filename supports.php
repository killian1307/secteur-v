<?php
require_once 'assets/init_session.php';
require 'db.php';

// Récupération des 3 catégories (Limité à 5 par colonne pour ne pas surcharger)
$firstSupports = $pdo->query("SELECT * FROM supports WHERE pioneer = 1 ORDER BY joined_at ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$newestSupports = $pdo->query("SELECT * FROM supports ORDER BY joined_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$popularSupports = $pdo->query("SELECT * FROM supports ORDER BY member_count DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header(__('sup_page_title'));
$header->render();
?>

<style>
    .supports-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        margin-top: 2rem;
    }
    
    /* Responsive : 1 colonne sur mobile, 3 sur PC */
    @media (max-width: 992px) {
        .supports-grid {
            grid-template-columns: 1fr;
        }
    }

    .support-column h2 {
        color: var(--primary-purple);
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        text-align: center;
        border-bottom: 2px solid rgba(155, 89, 182, 0.3);
        padding-bottom: 0.5rem;
    }

    /* Le design de la carte Serveur */
    .server-card {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        /* ATTENTION : On enlève la bordure ici ! */
        padding: 20px;
        margin-bottom: 1.5rem;
        text-align: center;
        transition: transform 0.3s ease;
        text-decoration: none;
        display: block;
        color: var(--text-primary);
        
        /* Active l'accélération matérielle pour la fluidité */
        transform: translateZ(0); 
    }

    /* LA SOLUTION MAGIQUE : La bordure est dessinée STRICTEMENT au-dessus de tout */
    .server-card::after {
        content: '';
        position: absolute;
        inset: 0; /* S'étire sur toute la carte (top/left/right/bottom: 0) */
        border-radius: inherit; /* Reprend l'arrondi de 12px automatiquement */
        border: 2px solid rgba(255, 255, 255, 0.1); /* Ta bordure de base */
        z-index: 10; /* Force la bordure à être au premier plan */
        pointer-events: none; /* Empêche la bordure de bloquer le clic sur la carte */
        transition: border-color 0.3s ease;
    }

    /* L'animation au survol de la carte */
    .server-card:hover {
        transform: translateY(-5px) translateZ(0);
    }

    /* L'animation de la bordure au survol */
    .server-card:hover::after {
        /* Mets ta couleur jaune ici si tu as changé ! */
        border-color: var(--primary-purple); 
    }

    /* L'effet de fond flouté et assombri */
    .server-card .card-bg {
        position: absolute;
        inset: 0;
        background-image: var(--bg-img);
        background-size: cover;
        background-position: center;
        filter: blur(15px) brightness(0.35);
        z-index: 1;
        /* On zoome l'image de 20% pour cacher les bords délavés du flou */
        transform: scale(1.2); 
    }

    /* Le contenu (le logo, le texte, le bouton) */
    .server-card .card-content {
        position: relative;
        z-index: 2; /* Reste coincé en sandwich entre le fond flouté et la bordure par-dessus */
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .server-card .server-icon {
        width: 80px; 
        height: 80px; 
        border-radius: 50%; 
        border: 3px solid rgba(255, 255, 255, 0.2);
        object-fit: cover; 
        margin-bottom: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    }

    .server-card h3 {
        margin: 0 0 10px 0;
        font-size: 1.2rem;
    }

    .server-card p {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin: 0 0 15px 0;
        line-height: 1.4;
    }

    .server-card .join-badge {
        background: rgba(17, 19, 45, 0.8);
        color: #5865F2; /* Couleur Discord */
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: bold;
        border: 1px solid rgba(88, 101, 242, 0.5);
    }
</style>

<main class="dashboard-container">
    <h1><?php echo __('sup_h1_1'); ?> <span style="color:var(--primary-purple)"><?php echo __('sup_h1_2'); ?></span></h1>
    <p class="subtitle"><?php echo __('sup_subtitle'); ?></p>

    <div class="supports-grid">
        
        <div class="support-column">
            <h2><i class="fas fa-hourglass-start"></i> <?php echo __('sup_col_first'); ?></h2>
            <?php foreach ($firstSupports as $server): ?>
                <?php $pic = htmlspecialchars($server['picture_url'] ?: 'assets/img/default_user.webp'); ?>
                <a href="<?php echo htmlspecialchars($server['invite_link']); ?>" target="_blank" class="server-card" style="--bg-img: url('<?php echo $pic; ?>');">
                    <div class="card-bg"></div>
                    <div class="card-content">
                        <img src="<?php echo $pic; ?>" alt="Icon" class="server-icon" loading="lazy">
                        <h3><?php echo htmlspecialchars($server['name']); ?></h3>
                        <p><?php echo htmlspecialchars($server['description']); ?></p>
                        <span class="join-badge"><i class="fab fa-discord"></i> <?php echo __('sup_join'); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="support-column">
            <h2><i class="fas fa-fire"></i> <?php echo __('sup_col_popular'); ?></h2>
            <?php foreach ($popularSupports as $server): ?>
                <?php $pic = htmlspecialchars($server['picture_url'] ?: 'assets/img/default_user.webp'); ?>
                <a href="<?php echo htmlspecialchars($server['invite_link']); ?>" target="_blank" class="server-card" style="--bg-img: url('<?php echo $pic; ?>');">
                    <div class="card-bg"></div>
                    <div class="card-content">
                        <img src="<?php echo $pic; ?>" alt="Icon" class="server-icon" loading="lazy">
                        <h3><?php echo htmlspecialchars($server['name']); ?></h3>
                        <p><?php echo htmlspecialchars($server['description']); ?></p>
                        <span class="join-badge"><i class="fab fa-discord"></i> <?php echo __('sup_join'); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="support-column">
            <h2><i class="fas fa-seedling"></i> <?php echo __('sup_col_newest'); ?></h2>
            <?php foreach ($newestSupports as $server): ?>
                <?php $pic = htmlspecialchars($server['picture_url'] ?: 'assets/img/default_user.webp'); ?>
                <a href="<?php echo htmlspecialchars($server['invite_link']); ?>" target="_blank" class="server-card" style="--bg-img: url('<?php echo $pic; ?>');">
                    <div class="card-bg"></div>
                    <div class="card-content">
                        <img src="<?php echo $pic; ?>" alt="Icon" class="server-icon" loading="lazy">
                        <h3><?php echo htmlspecialchars($server['name']); ?></h3>
                        <p><?php echo htmlspecialchars($server['description']); ?></p>
                        <span class="join-badge"><i class="fab fa-discord"></i> <?php echo __('sup_join'); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>