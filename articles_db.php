<?php
require_once 'assets/init_session.php';
require 'db.php';

// Gestion du tri
$sort = $_GET['sort'] ?? 'recent';

switch ($sort) {
    case 'popular':
        $orderBy = "a.views DESC, a.created_at DESC";
        break;
    case 'author':
        $orderBy = "u.username ASC, a.created_at DESC";
        break;
    case 'recent':
    default:
        $orderBy = "a.created_at DESC";
        break;
}

$stmt = $pdo->query("
    SELECT a.*, u.username, u.grade 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY $orderBy
");
$articles = $stmt->fetchAll();

require 'assets/header.php';
$header = new Header(__('arts_page_title'));
$header->render();
?>

<main class="dashboard-container">
        <h1><?php echo __('arts_h1_1'); ?> <span style="color:var(--primary-purple)"><?php echo __('arts_h1_2'); ?></span></h1>
        <div style="display: flex; gap: 10px; background: rgba(0,0,0,0.3); padding: 5px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 2rem;">
            <a href="?sort=recent" style="padding: 8px 15px; color: <?php echo $sort === 'recent' ? 'var(--primary-purple)' : 'white'; ?>; text-decoration: none; font-weight: bold;"><?php echo __('arts_sort_recent'); ?></a>
            <a href="?sort=popular" style="padding: 8px 15px; color: <?php echo $sort === 'popular' ? 'var(--primary-purple)' : 'white'; ?>; text-decoration: none; font-weight: bold;"><?php echo __('arts_sort_popular'); ?></a>
            <a href="?sort=author" style="padding: 8px 15px; color: <?php echo $sort === 'author' ? 'var(--primary-purple)' : 'white'; ?>; text-decoration: none; font-weight: bold;"><?php echo __('arts_sort_author'); ?></a>
        </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php foreach ($articles as $art): ?>
            <?php 
                $excerpt = strip_tags($art['content']);
                if (strlen($excerpt) > 120) $excerpt = substr($excerpt, 0, 120) . '...';
            ?>
            
            <a href="article.php?slug=<?php echo htmlspecialchars($art['slug']); ?>" style="text-decoration: none; color: inherit;">
                <div class="profile-card" style="height: 100%; transition: transform 0.2s; text-align: left;">
                    <h2 style="font-size: 1.2rem; margin-bottom: 10px; color: white;"><?php echo htmlspecialchars($art['title']); ?></h2>
                    
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 15px; line-height: 1.5;">
                        <?php echo $excerpt; ?>
                    </p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                        <span><?php echo display_username($art['username'], $art['grade'], false); ?></span>
                        <span style="color: var(--text-secondary);"><i class="fas fa-eye"></i> <?php echo $art['views']; ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>

        <?php if (empty($articles)): ?>
            <p style="text-align: center; grid-column: 1 / -1; color: var(--text-secondary);"><?php echo __('arts_no_articles'); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php
require 'assets/footer.php';
$footer = new Footer();
$footer->render();
?>
</body>
</html>