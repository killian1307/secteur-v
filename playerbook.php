<?php
session_start();
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

// Logique tri + recherche

// 1. Paramètres par défaut
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$allowed_sorts = ['id', 'name_en', 'name_jp', 'position', 'element', 'gender', 'total_stats'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'DESC' ? 'DESC' : 'ASC';

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construction de la requête
$sql = "SELECT * FROM players";
$params = [];

if ($search) {
    $sql .= " WHERE name_en LIKE ? OR name_jp LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY $sort $order LIMIT $limit OFFSET $offset";

// Exécution requête principale
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$players = $stmt->fetchAll();

// Compter le total (pour la pagination)
$sqlCount = "SELECT COUNT(*) FROM players";
if ($search) {
    $sqlCount .= " WHERE name_en LIKE ? OR name_jp LIKE ?";
}
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($search ? [$params[0], $params[1]] : []);
$totalPlayers = $stmtCount->fetchColumn();
$totalPages = ceil($totalPlayers / $limit);

// Fonction pour les liens
function getLink($newPage, $newSort = null, $currentSearch = '') {
    global $page, $sort, $order;
    
    $p = $newPage ?? $page;
    $s = $newSort ?? $sort;
    $o = $order;
    
    // Si clic sur colonne déjà active, inverse l'ordre
    if ($newSort && $newSort === $sort) {
        $o = ($order === 'ASC') ? 'DESC' : 'ASC';
    } elseif ($newSort) {
        // Nouvelle colonne = retour à ASC par défaut
        $o = 'ASC';
    }

    return "?page=$p&sort=$s&order=$o&search=" . urlencode($currentSearch);
}

// Fonction pour afficher la flèche de tri
function sortArrow($colName) {
    global $sort, $order;
    if ($sort === $colName) {
        return $order === 'ASC' ? '▲' : '▼';
    }
    return '<span style="opacity:0.3">⇅</span>';
}

$header = new Header("SECTEUR V - Liste des joueurs");
$header->render();
?>

<link rel="stylesheet" href="style-playerbook.css">

<main class="playerbook-container">
    
    <div class="pb-header">
        <h1 class="player-h1">Liste des <span style="color:var(--primary-purple)">Joueurs</span></h1>
        <p class="subtitle">Base de données officielle du Secteur V.</p>
    </div>

    <div class="controls-wrapper">
        <form method="GET" class="search-bar-container">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
            
            <input type="text" name="search" placeholder="Rechercher un joueur (Nom anglais ou japonais)..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
            <?php if($search): ?>
                <a href="playerbook.php" class="reset-btn" title="Réinitialiser">✖</a>
            <?php endif; ?>
        </form>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?php echo getLink($page - 1, null, $search); ?>" class="page-btn">← Précédent</a>
            <?php endif; ?>
            
            <span class="page-info">Page <?php echo $page; ?> / <?php echo $totalPages; ?> (Total: <?php echo $totalPlayers; ?>)</span>
            
            <?php if ($page < $totalPages): ?>
                <a href="<?php echo getLink($page + 1, null, $search); ?>" class="page-btn">Suivant →</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="player-table">
            <thead>
                <tr>
                    <th class="col-img">Aperçu</th>
                    <th><a href="<?php echo getLink(1, 'id', $search); ?>">ID <?php echo sortArrow('id'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'name_en', $search); ?>">Nom (EN) <?php echo sortArrow('name_en'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'name_jp', $search); ?>">Nom (JP) <?php echo sortArrow('name_jp'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'position', $search); ?>">Poste <?php echo sortArrow('position'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'element', $search); ?>">Élément <?php echo sortArrow('element'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'gender', $search); ?>">Genre <?php echo sortArrow('gender'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'total_stats', $search); ?>">Stats <?php echo sortArrow('total_stats'); ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($players) > 0): ?>
                    <?php foreach ($players as $p): ?>
                        <tr>
                            <td class="col-img">
                                <img src="<?php echo htmlspecialchars($p['image_webp']); ?>" alt="img" loading="lazy">
                            </td>
                            <td>#<?php echo $p['id']; ?></td>
                            <td class="font-bold"><?php echo htmlspecialchars($p['name_en']); ?></td>
                            <td style="color:var(--text-secondary)"><?php echo htmlspecialchars($p['name_jp']); ?></td>
                            
                            <td><span class="badge pos-<?php echo strtolower($p['position']); ?>"><?php echo $p['position']; ?></span></td>
                            <td><span class="badge elem-<?php echo strtolower($p['element']); ?>"><?php echo $p['element']; ?></span></td>
                            
                            <td><?php echo $p['gender']; ?></td>
                            <td class="stat-cell"><?php echo $p['total_stats']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 2rem;">Aucun joueur trouvé pour cette recherche.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<?php
$footer = new Footer();
$footer->render();
?>
<script src="script.js"></script>
</body>
</html>