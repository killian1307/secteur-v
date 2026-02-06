<?php
class Header {
    private $pageTitle;
    private $customCss;
    private $isHome;

    public function __construct($pageTitle, $customCss = null) {
        $this->pageTitle = $pageTitle;
        $this->customCss = $customCss;
        
        // Détecte si c'est l'accueil en regardant le titre de la page
        $this->isHome = (strpos($pageTitle, 'Accueil') !== false);
    }

public function render() {
        $isLoggedIn = isset($_SESSION['user_id']);
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
        $initial = $isLoggedIn ? strtoupper(substr($username, 0, 1)) : '';
        $avatarUrl = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : null;
        
        // Logique logo (si accueil ET pas connecté, on cache le logo)
        $logoClass = ($this->isHome && !$isLoggedIn) ? 'header-logo logo-hidden' : 'header-logo';

        echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($this->pageTitle) . '</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">';
    
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

        <a href="index.php#" class="' . $logoClass . '" id="navLogo">SECTEUR <img src="assets/img/v.webp" alt="V" class="v-icon-dark"><img src="assets/img/v_light.webp" alt="V" class="v-icon-light"></a>
        
        <div class="profile-container">
            <div class="profile-icon" onclick="toggleMenu()">';

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
             echo '<a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> Afficher le profil</a>
                   <a href="profile_settings.php" class="dropdown-item"><i class="fas fa-cog"></i> Gérer le compte</a>
                   <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>';
        } else {
             echo '<a onclick="openPrivacyModal()" class="dropdown-item"><i class="fas fa-sign-in-alt"></i> Connexion</a>';
        }

        echo '<div class="dropdown-divider"></div>
                <div class="dropdown-item" onclick="toggleTheme()">
                    <i class="fas fa-sun" id="theme-icon"></i> 
                    <span id="theme-text">Mode Clair</span>
                </div>
            </div>
        </div>
    </header>

    <div class="mobile-menu-overlay" id="mobileMenu" onclick="toggleMobileMenu()">
        <div class="mobile-menu-content" onclick="event.stopPropagation()">
            <a href="index.php#" class="mobile-link"><i class="fas fa-home"></i> Accueil</a>';
if ($isLoggedIn) {
            // Si connecté, menu match et classement
            echo '<a href="matchmaking.php" class="mobile-link"><i class="fas fa-gamepad"></i> Match</a>';
        } else {
            // Sinon, présentation
            echo '<a href="index.php#presentation" class="mobile-link"><i class="fas fa-info-circle"></i> Présentation</a>';
        }
        echo '<a href="classement.php" class="mobile-link"><i class="fas fa-list-ol"></i> Classement</a>';
        echo '<a href="https://discord.gg/A98PfnH8SC" class="mobile-link" target="_blank"><i class="fab fa-discord"></i> Discord</a>
        </div>
    </div>';
    }
}
?>