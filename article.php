<?php
require_once 'assets/init_session.php';
require 'db.php';

if (!isset($_GET['slug'])) {
    header("Location: articles.php");
    exit;
}

$slug = $_GET['slug'];

// On va chercher l'article ET les infos de son auteur
$stmt = $pdo->prepare("
    SELECT a.*, u.username, u.avatar, u.grade 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    WHERE a.slug = ?
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: 404.php");
    exit;
}

// Ajouter +1 vue à l'article
$pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

require 'assets/header.php';
$header = new Header(htmlspecialchars($article['title']));
$header->render();
?>

<style>
    /* Styles pour rendre l'article beau à lire */
    .article-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: var(--background-card);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .article-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .article-meta {
        color: var(--text-secondary);
        font-size: 0.9rem;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-top: 1rem;
    }
    .article-content {
        line-height: 1.8;
        font-size: 1.1rem;
        color: var(--text-primary);
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
    }
    .article-content h1, .article-content h2, .article-content h3 {
        color: var(--primary-purple);
        margin-top: 2rem;
    }

    .retour-accueil {
        display: inline-block;
        margin-top: 0.5rem;
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.3s;
    }

    .retour-accueil:hover {
        text-decoration: underline;
    }

    p {
        margin-bottom: 20px;
        text-align: justify;
    }

</style>

<main class="dashboard-container">
    <div class="article-container">
        
        <div class="article-header">
            <h1 style="color: white; font-size: 2.5rem;"><?php echo htmlspecialchars($article['title']); ?></h1>
            <div class="article-meta">
                <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
                <span><i class="fas fa-eye"></i> <?php echo $article['views'] + 1; ?> <?php echo __('art_views'); ?></span>
                <span>
                    <img src="<?php echo htmlspecialchars($article['avatar'] ?? 'assets/img/default_user.webp'); ?>" style="width:20px; border-radius:50%; vertical-align:middle;">
                    <?php echo display_username($article['username'], $article['grade'], true); ?>
                </span>
            </div>
        </div>

        <div class="article-content">
            <?php echo $article['content']; ?>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="articles.php" class="retour-accueil"><i class="fas fa-arrow-left"></i> <?php echo __('art_back_to_articles'); ?></a>
        </div>

    </div>
</main>

<?php
require 'assets/footer.php';
$footer = new Footer();
$footer->render();
?>
</body>
</html>