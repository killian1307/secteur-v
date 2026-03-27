<?php
class Header {
    private $pageTitle;
    private $customCss;
    private $isHome;

    public function __construct($pageTitle, $customCss = null) {
        $this->pageTitle = $pageTitle;
        $this->customCss = $customCss;
        
        // Détecte si c'est l'accueil en regardant le titre de la page dans les 7 langues
        $this->isHome = (
            strpos($pageTitle, 'Accueil') !== false ||    // Français
            strpos($pageTitle, 'Home') !== false ||       // Anglais & Italien
            strpos($pageTitle, 'Inicio') !== false ||     // Espagnol
            strpos($pageTitle, 'Startseite') !== false || // Allemand
            strpos($pageTitle, 'ホーム') !== false ||       // Japonais
            strpos($pageTitle, 'الرئيسية') !== false        // Arabe
        );
    }

    public function render() {
        $isLoggedIn = isset($_SESSION['user_id']);
        
        // Vérifie si l'utilisateur est le créateur
        $isAdmin = false;
        
        if (isset($_SESSION['user_id'])) {
            global $pdo;
            
            $stmtGrade = $pdo->prepare("SELECT grade FROM users WHERE id = ?");
            $stmtGrade->execute([$_SESSION['user_id']]);
            $userGrade = $stmtGrade->fetchColumn();
            
            if ($userGrade === 'Créateur' || $userGrade === 'Administrateur') {
                $isAdmin = true;
            }
        }

        $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
        $initial = $isLoggedIn ? strtoupper(substr($username, 0, 1)) : '';
        $avatarUrl = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : null;
        // Logique logo (si accueil ET pas connecté, on cache le logo)
        $logoClass = ($this->isHome && !$isLoggedIn) ? 'header-logo logo-hidden' : 'header-logo';

        // Détermination du drapeau actuel
        $currentLang = $_SESSION['lang'] ?? 'fr';
        $flags = [
            'fr' => '🇫🇷',
            'en' => '🇬🇧',
            'es' => '🇪🇸',
            'it' => '🇮🇹',
            'de' => '🇩🇪',
            'ja' => '🇯🇵',
            'ar' => '🇸🇦'
        ];
        $flag = $flags[$currentLang] ?? '🇫🇷';

        // --- Construction dynamique des liens de langue ---
        $currentParams = $_GET; // On copie tous les paramètres actuels
        
        $availableLangs = [
            'fr' => '🇫🇷 Français',
            'en' => '🇬🇧 English',
            'es' => '🇪🇸 Español',
            'it' => '🇮🇹 Italiano',
            'de' => '🇩🇪 Deutsch',
            'ja' => '🇯🇵 日本語',
            'ar' => '🇸🇦 العربية'
        ];

        $langLinksHtml = '';
        foreach ($availableLangs as $code => $label) {
            $currentParams['lang'] = $code;
            $newUrl = '?' . http_build_query($currentParams); 
            $langLinksHtml .= '<a href="' . htmlspecialchars($newUrl) . '" class="dropdown-item">' . $label . '</a>';
        }

        echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($currentLang) . '">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($this->pageTitle) . '</title>

    <meta name="description" content="' . htmlspecialchars(__('footer_desc')) . '">
    <meta name="keywords" content="Inazuma Eleven, Victory Road, Secteur V, Classement, ELO, Matchmaking, PvP">
    <meta name="author" content="K">
    
    <meta name="theme-color" content="#FFD700"> <meta property="og:type" content="website">
    <meta property="og:url" content="https://secteur-v.letterk.me/">
    <meta property="og:title" content="' . htmlspecialchars($this->pageTitle) . '">
    <meta property="og:description" content="' . htmlspecialchars(__('footer_desc')) . '">
    <meta property="og:image" content="https://secteur-v.letterk.me/assets/img/v.webp">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://secteur-v.letterk.me/">
    <meta name="twitter:title" content="' . htmlspecialchars($this->pageTitle) . '">
    <meta name="twitter:description" content="' . htmlspecialchars(__('footer_desc')) . '">
    <meta name="twitter:image" content="https://secteur-v.letterk.me/assets/img/v.webp">

    <link rel="alternate" hreflang="fr" href="https://secteur-v.letterk.me/?lang=fr" />
    <link rel="alternate" hreflang="en" href="https://secteur-v.letterk.me/?lang=en" />
    <link rel="alternate" hreflang="es" href="https://secteur-v.letterk.me/?lang=es" />
    <link rel="alternate" hreflang="x-default" href="https://secteur-v.letterk.me/" />

    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=' . filemtime(__DIR__ . '/../style.css') . '">
    <link rel="stylesheet" href="style-dashboard.css?v=' . filemtime(__DIR__ . '/../style-dashboard.css') . '">';
    
        if ($this->customCss) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($this->customCss) . '">';
        }

        echo '</head>
<body>

    <header>
        <div class="nav-left">
            <button class="hamburger-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <a href="/" class="' . $logoClass . '" id="navLogo"><span class="header-text">SECTEUR</span> <img src="assets/img/v.webp" alt="V" class="v-icon"></a>
        
        <div class="header-right" style="justify-self: end; display: flex; align-items: center; gap: 20px;">
            
            <div class="lang-container" style="position: relative;">
                <div class="lang-icon" onclick="document.getElementById(\'langDropdown\').classList.toggle(\'active\'); document.getElementById(\'userDropdown\').classList.remove(\'active\');" style="cursor: pointer; font-size: 1.5rem; filter: drop-shadow(0 0 5px rgba(255,255,255,0.1)); transition: transform 0.2s;">
                    ' . $flag . '
                </div>
                <div class="dropdown-menu" id="langDropdown" style="width: max-content; right: -10px; top: 150%;">
                    ' . $langLinksHtml . '
                </div>
            </div>';

        // --- MENU AMIS ---
        if ($isLoggedIn) {
            ?>
            <div class="lang-container friends-container" style="position: relative;">
                <div class="lang-icon" onclick="toggleFriendsMenu()" style="position: relative;">
                    <i class="fas fa-user-friends"></i>
                    <span id="friend-notif-badge" style="display:none; position:absolute; top:-5px; right:-10px; background:#e74c3c; color:white; font-size:0.7rem; font-weight:bold; padding:2px 6px; border-radius:50%;">0</span>
                </div>
                
                <div class="dropdown-menu" id="friendsDropdown" style="width: 300px; right: -10px; top: 150%; padding: 0; overflow: hidden;">
                    <div style="display:flex; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <button onclick="switchFriendTab('friends')" id="tab-friends" style="flex:1; padding:10px; background:transparent; color:var(--primary-purple); border:none; cursor:pointer; font-weight:bold;">Amis</button>
                        <button onclick="switchFriendTab('requests')" id="tab-requests" style="flex:1; padding:10px; background:transparent; color:white; border:none; cursor:pointer; font-weight:bold;">Demandes</button>
                    </div>
                    
                    <div id="friends-list-content" class="scrollable-menu" style="max-height: 300px; overflow-y: auto; padding: 10px;">
                    </div>
                </div>
            </div>

            <script>
                function toggleFriendsMenu() {
                    const dropdown = document.getElementById('friendsDropdown');
                    dropdown.classList.toggle('active');
                    document.getElementById('userDropdown').classList.remove('active');
                    document.getElementById('langDropdown').classList.remove('active');
                    if(dropdown.classList.contains('active')) loadFriendsData();
                }

                let currentFriendTab = 'friends';

                function switchFriendTab(tab) {
                    currentFriendTab = tab;
                    document.getElementById('tab-friends').style.color = tab === 'friends' ? 'var(--primary-purple)' : 'white';
                    document.getElementById('tab-requests').style.color = tab === 'requests' ? 'var(--primary-purple)' : 'white';
                    renderFriendsList();
                }

                let friendsData = { pending: [], friends: [] };

                async function loadFriendsData() {
                    const res = await fetch('api.php?action=get_friends_data');
                    const data = await res.json();
                    if(data.success) {
                        friendsData = data;
                        renderFriendsList();
                        const badge = document.getElementById('friend-notif-badge');
                        if(data.pending_count > 0) {
                            badge.innerText = data.pending_count;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }

                function renderFriendsList() {
                    const container = document.getElementById('friends-list-content');
                    container.innerHTML = '';
                    
                    document.getElementById('tab-friends').innerText = `Amis (${friendsData.friends.length}/50)`;
                    
                    const list = currentFriendTab === 'friends' ? friendsData.friends : friendsData.pending;
                    
                    if(list.length === 0) {
                        container.innerHTML = `<p style="text-align:center; color:var(--text-secondary); font-size:0.9rem; padding:10px;">Aucun résultat.</p>`;
                        return;
                    }

                    list.forEach(user => {
                        const avatar = user.avatar || 'assets/img/default_user.webp';
                        const actions = currentFriendTab === 'requests' 
                            ? `<button onclick="handleFriendAction(${user.id}, 'accept')" style="background:#2ecc71; color:white; border:none; border-radius:3px; padding:3px 8px; cursor:pointer; margin-right:5px;"><i class="fas fa-check"></i></button>
                               <button onclick="handleFriendAction(${user.id}, 'reject')" style="background:#e74c3c; color:white; border:none; border-radius:3px; padding:3px 8px; cursor:pointer;"><i class="fas fa-times"></i></button>`
                            : `<a href="profile.php?username=${user.username}" style="color:var(--text-secondary);"><i class="fas fa-external-link-alt"></i></a>`;

                        container.innerHTML += `
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.05);">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="${avatar}" style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                                    <span style="font-size:0.9rem; color: white;">${user.display_name}</span>
                                </div>
                                <div>${actions}</div>
                            </div>
                        `;
                    });
                }

                async function handleFriendAction(targetId, type) {
                    const res = await fetch('api.php?action=friend_action', {
                        method: 'POST',
                        body: JSON.stringify({ target_id: targetId, type: type })
                    });
                    const data = await res.json();
                    
                    if (!data.success && data.message) {
                        alert(data.message);
                    }
                    loadFriendsData();
                }

                document.addEventListener('DOMContentLoaded', loadFriendsData);
            </script>
            <?php
        }
        // --- FIN DU MENU AMIS ---

        echo '
            <div class="profile-container">
                <div class="profile-icon" onclick="document.getElementById(\'userDropdown\').classList.toggle(\'active\'); document.getElementById(\'langDropdown\').classList.remove(\'active\');">';

        if ($isLoggedIn) {
            if ($avatarUrl) {
                // Si avatar Discord, l'affiche
                echo '<img src="' . htmlspecialchars($avatarUrl) . '" alt="Profile" class="user-avatar-img">';
            } else {
                // Fallback
                $initial = strtoupper(substr($_SESSION['username'], 0, 1));
                echo '<div class="user-avatar">' . $initial . '</div>';
            }
        } else {
            echo '<i class="far fa-user-circle"></i>';
        }

        echo '</div>
                
                <div class="dropdown-menu" id="userDropdown">';
                
        if ($isLoggedIn) {
             echo '<a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> ' . __('nav_profile') . '</a>';
             if ($isAdmin) {
                echo '<a href="panel.php" class="dropdown-item"><i class="fas fa-tools"></i> Admin Panel</a>';
                echo '<a href="write_article.php" class="dropdown-item"><i class="fas fa-pen"></i> Write Article</a>';
             }
             echo '
                   <a href="profile_settings.php" class="dropdown-item"><i class="fas fa-cog"></i> ' . __('nav_settings') . '</a>
                   <div class="dropdown-divider"></div>
                   <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> ' . __('nav_logout') . '</a>';
        } else {
             echo '<a href="discord_login.php" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> ' . __('nav_login') . '</a>';
        }

        echo '
                </div>
            </div>
        </div>
    </header>

    <div class="mobile-menu-overlay" id="mobileMenu" onclick="toggleMobileMenu()">
        <div class="mobile-menu-content" onclick="event.stopPropagation()">
            <a href="/" class="mobile-link"><i class="fas fa-home"></i> ' . __('nav_home') . '</a>';
if ($isLoggedIn) {
            // Si connecté, menu match
            echo '<a href="matchmaking.php" class="mobile-link"><i class="fas fa-gamepad"></i> ' . __('nav_match') . '</a>';
        } else {
            // Sinon, présentation
            echo '<a href="index.php#presentation" class="mobile-link"><i class="fas fa-info-circle"></i> ' . __('nav_presentation') . '</a>';
            echo '<a href="edp.php" class="mobile-link"><i class="fas fa-dumbbell"></i> ' . __('nav_edp') . '</a>';
        }
        echo '<a href="articles_db.php" class="mobile-link"><i class="fas fa-newspaper"></i> ' . __('nav_articles') . '</a>';
        echo '<a href="ranking.php" class="mobile-link"><i class="fas fa-list-ol"></i> ' . __('nav_ranking') . '</a>';
        echo '<a href="playerbook.php" class="mobile-link"><i class="fas fa-address-card"></i> ' . __('nav_playerbook') . '</a>';
        echo '<a href="rules.php" class="mobile-link"><i class="fas fa-book"></i> ' . __('nav_rules') . '</a>';
        echo '<a href="supports.php" class="mobile-link"><i class="fas fa-hands-helping"></i> ' . __('nav_partners') . '</a>';
        echo '<a href="https://discord.gg/85AT6gGNGD" class="mobile-link" target="_blank"><i class="fab fa-discord"></i> Discord</a>
        </div>
    </div>';
    }
}
?>