<?php
session_start();
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

// Logique de tri, recherche et filtres

// Paramètres par défaut
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50; 
$offset = ($page - 1) * $limit;

// Tris autorisés
$allowed_sorts = ['id', 'name_en', 'name_jp', 'gender', 'total_stats'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'DESC' ? 'DESC' : 'ASC';

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupération des Filtres
$filter_pos = isset($_GET['pos']) ? $_GET['pos'] : '';
$filter_elem = isset($_GET['elem']) ? $_GET['elem'] : '';

// Construction de la requête SQL
$sql = "SELECT * FROM players WHERE 1=1";
$params = [];

// Ajout Recherche
if ($search) {
    $sql .= " AND (name_en LIKE ? OR name_jp LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Filtre Position
if ($filter_pos) {
    $sql .= " AND position = ?";
    $params[] = $filter_pos;
}

// Filtre Élément
if ($filter_elem) {
    $sql .= " AND element = ?";
    $params[] = $filter_elem;
}

// Tri et Pagination
$sql .= " ORDER BY $sort $order LIMIT $limit OFFSET $offset";

// Exécution requête principale
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$players = $stmt->fetchAll();

// Compte le total
$sqlCount = "SELECT COUNT(*) FROM players WHERE 1=1";
$paramsCount = [];

if ($search) {
    $sqlCount .= " AND (name_en LIKE ? OR name_jp LIKE ?)";
    $paramsCount[] = "%$search%";
    $paramsCount[] = "%$search%";
}
if ($filter_pos) {
    $sqlCount .= " AND position = ?";
    $paramsCount[] = $filter_pos;
}
if ($filter_elem) {
    $sqlCount .= " AND element = ?";
    $paramsCount[] = $filter_elem;
}

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($paramsCount);
$totalPlayers = $stmtCount->fetchColumn();
$totalPages = ceil($totalPlayers / $limit);
if ($totalPages < 1) $totalPages = 1;

// Fonction helper pour les liens
function getLink($newPage, $newSort = null, $currentSearch = '', $pPos = null, $pElem = null) {
    global $page, $sort, $order, $filter_pos, $filter_elem;
    
    $p = $newPage ?? $page;
    $s = $newSort ?? $sort;
    $currPos = $pPos !== null ? $pPos : $filter_pos;
    $currElem = $pElem !== null ? $pElem : $filter_elem;
    
    $o = $order;
    // Logique d'inversion
    if ($newSort && $newSort === $sort) {
        $o = ($order === 'ASC') ? 'DESC' : 'ASC';
    } elseif ($newSort) {
        $o = 'DESC';
    }

    // Construction URL
    $url = "?page=$p&sort=$s&order=$o";
    if ($currentSearch) $url .= "&search=" . urlencode($currentSearch);
    if ($currPos) $url .= "&pos=" . urlencode($currPos);
    if ($currElem) $url .= "&elem=" . urlencode($currElem);

    return $url;
}

function sortArrow($colName) {
    global $sort, $order;
    if ($sort === $colName) {
        return $order === 'ASC' ? '▲' : '▼';
    }
    return '<span style="opacity:0.3">⇅</span>';
}

$header = new Header("SECTEUR V - Player Book");
$header->render();
?>

<link rel="stylesheet" href="style-playerbook.css">

<main class="playerbook-container">
    
    <div class="pb-header">
        <h1>Player <span style="color:var(--primary-purple)">Book</span></h1>
        <p class="subtitle">Base de données officielle du Secteur V.</p>
    </div>

    <div class="controls-wrapper">
        <form method="GET" class="search-bar-container">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
            <?php if($filter_pos): ?><input type="hidden" name="pos" value="<?php echo htmlspecialchars($filter_pos); ?>"><?php endif; ?>
            <?php if($filter_elem): ?><input type="hidden" name="elem" value="<?php echo htmlspecialchars($filter_elem); ?>"><?php endif; ?>
            
            <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
            <?php if($search || $filter_pos || $filter_elem): ?>
                <a href="playerbook.php" class="reset-btn" title="Tout réinitialiser">✖</a>
            <?php endif; ?>
        </form>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?php echo getLink($page - 1); ?>" class="page-btn">←</a>
            <?php endif; ?>
            <!-- Pagination -->
            <form method="GET" class="page-jump-form">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="pos" value="<?php echo htmlspecialchars($filter_pos); ?>">
                <input type="hidden" name="elem" value="<?php echo htmlspecialchars($filter_elem); ?>">

                <span class="page-info">Page</span>
                <input type="number" name="page" value="<?php echo $page; ?>" min="1" max="<?php echo $totalPages; ?>" class="page-input">
                <span class="page-info">/ <?php echo $totalPages; ?></span>
            </form>
            <?php if ($page < $totalPages): ?>
                <a href="<?php echo getLink($page + 1); ?>" class="page-btn">→</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="player-table">
            <thead>
                <tr>
                    <th class="col-img">Aperçu</th>
                    <th><a href="<?php echo getLink(1, 'id'); ?>">ID <?php echo sortArrow('id'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'name_en'); ?>">Nom (EN) <?php echo sortArrow('name_en'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'name_jp'); ?>">Nom (JP) <?php echo sortArrow('name_jp'); ?></a></th>
                    
                    <th class="th-dropdown">
                        <span>Poste <i class="fas fa-filter" style="font-size:0.7rem; opacity:0.7;"></i></span>
                        <div class="dropdown-content">
                            <a href="<?php echo getLink(1, null, $search, ''); ?>" class="<?php echo !$filter_pos ? 'active' : ''; ?>">Tout</a>
                            <a href="<?php echo getLink(1, null, $search, 'GK'); ?>" class="<?php echo $filter_pos === 'GK' ? 'active' : ''; ?>">GK</a>
                            <a href="<?php echo getLink(1, null, $search, 'DF'); ?>" class="<?php echo $filter_pos === 'DF' ? 'active' : ''; ?>">DF</a>
                            <a href="<?php echo getLink(1, null, $search, 'MF'); ?>" class="<?php echo $filter_pos === 'MF' ? 'active' : ''; ?>">MF</a>
                            <a href="<?php echo getLink(1, null, $search, 'FW'); ?>" class="<?php echo $filter_pos === 'FW' ? 'active' : ''; ?>">FW</a>
                        </div>
                    </th>

                    <th class="th-dropdown">
                        <span>Élément <i class="fas fa-filter" style="font-size:0.7rem; opacity:0.7;"></i></span>
                        <div class="dropdown-content">
                            <a href="<?php echo getLink(1, null, $search, null, ''); ?>" class="<?php echo !$filter_elem ? 'active' : ''; ?>">Tout</a>
                            <a href="<?php echo getLink(1, null, $search, null, 'Wind'); ?>" class="<?php echo $filter_elem === 'Wind' ? 'active' : ''; ?>">Air</a>
                            <a href="<?php echo getLink(1, null, $search, null, 'Mountain'); ?>" class="<?php echo $filter_elem === 'Mountain' ? 'active' : ''; ?>">Terre</a>
                            <a href="<?php echo getLink(1, null, $search, null, 'Fire'); ?>" class="<?php echo $filter_elem === 'Fire' ? 'active' : ''; ?>">Feu</a>
                            <a href="<?php echo getLink(1, null, $search, null, 'Forest'); ?>" class="<?php echo $filter_elem === 'Wood' ? 'active' : ''; ?>">Bois</a>
                        </div>
                    </th>
                    
                    <th><a href="<?php echo getLink(1, 'gender'); ?>">Genre <?php echo sortArrow('gender'); ?></a></th>
                    <th><a href="<?php echo getLink(1, 'total_stats'); ?>">Stats <?php echo sortArrow('total_stats'); ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($players) > 0): ?>
                    <?php foreach ($players as $p): ?>
                        <tr>
                            <td class="col-img">
                                <img src="<?php echo htmlspecialchars($p['image_webp']); ?>" alt="img" loading="lazy">
                            </td>
                            <td data-label="ID">#<?php echo $p['id']; ?></td>
                            <td data-label="Nom (EN)" class="font-bold"><?php echo htmlspecialchars($p['name_en']); ?></td>
                            <td data-label="Nom (JP)" style="color:var(--text-secondary)"><?php echo htmlspecialchars($p['name_jp']); ?></td>
                            
                            <td data-label="Poste">
                                <span class="badge pos-<?php echo strtolower($p['position']); ?>"><?php echo $p['position']; ?></span>
                            </td>
                            <td data-label="Élément">
                                <span class="badge elem-<?php echo strtolower($p['element']); ?>"><?php echo $p['element']; ?></span>
                            </td>
                            
                            <td data-label="Genre"><?php echo $p['gender']; ?></td>
                            <td data-label="Total Stats" class="stat-cell"><?php echo $p['total_stats']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 2rem;">Aucun joueur trouvé avec ces filtres.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination bottom">
        <?php if ($page > 1): ?>
            <a href="<?php echo getLink($page - 1); ?>" class="page-btn">←</a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
            <a href="<?php echo getLink($page + 1); ?>" class="page-btn">→</a>
        <?php endif; ?>
    </div>

</main>

<?php
$footer = new Footer();
$footer->render();
?>
<script src="script.js"></script>
</body>
</html>