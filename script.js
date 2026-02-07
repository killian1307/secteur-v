// Fonction pour afficher/cacher le menu
function toggleMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('active');
}

// Fonction pour basculer le thème
function toggleTheme() {
    const body = document.body;
    const themeIcon = document.getElementById('theme-icon');
    const themeText = document.getElementById('theme-text');

    // Bascule la classe
    body.classList.toggle('light-mode');

    // Vérifie l'état actuel pour mettre à jour l'interface et le cookie
    let isLight = body.classList.contains('light-mode');

    if (isLight) {
        // Mise à jour visuelle
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
        if(themeText) themeText.innerText = "Mode Sombre";
        
        // Sauvegardee dans un cookie pendant 1 an
        setCookie("theme", "light", 365);
    } else {
        // Mise à jour visuelle
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        if(themeText) themeText.innerText = "Mode Clair";
        
        // Sauvegarde dans un cookie
        setCookie("theme", "dark", 365);
    }
}

// Ferme le menu si on clique ailleurs sur la page
window.onclick = function(event) {
    if (!event.target.closest('.profile-container')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
        }
    }
}

// Gestion du scroll pour la flèche
window.addEventListener('scroll', function() {
    const indicator = document.querySelector('.scroll-indicator-container');

    const presentationSection = document.getElementById('presentation');
    const rulesSection = document.getElementById('rules');

    const targetSection = presentationSection || rulesSection;
    
    if (targetSection && indicator) {
        // Récupère la position du haut de la section présentation par rapport à la fenêtre
        const sectionTop = targetSection.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;

        // Si le haut de la section présentation est visible dans le bas de l'écranv (marge de 100px)
        if (sectionTop < windowHeight - 100) {
            indicator.classList.add('hidden');
        } else {
            indicator.classList.remove('hidden');
        }
    }
});

// Fonction pour créer un cookie
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    // path=/ pour que le cookie soit accessible sur tout le site
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

// Fonction pour lire un cookie
function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(';');
    for(let i=0;i < ca.length;i++) {
        let c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

// Vérifie si un cookie existe déjà pour appliquer le thème au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = getCookie("theme");
    const themeIcon = document.getElementById('theme-icon');
    const themeText = document.getElementById('theme-text');

    if (savedTheme === 'light') {
        document.body.classList.add('light-mode');
        
        // Met à jour l'icône dans le menu si elle existe
        if (themeIcon) {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
        if (themeText) {
            themeText.innerText = "Mode Sombre";
        }
    }
});

// Gestion du scroll pour afficher le logo dans la barre de navigation
window.addEventListener('scroll', function() {
    const navLogo = document.getElementById('navLogo');
    
    // Que si le logo a la classe 'logo-hidden' au départ
    if (navLogo && navLogo.classList.contains('logo-hidden')) {
        
        if (window.scrollY > 300) {
            navLogo.classList.add('logo-visible');
        } else {
            navLogo.classList.remove('logo-visible');
        }
    }
});

// Gestion du Menu (anciennement mobile)
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('active');
    
    const btnIcon = document.querySelector('.hamburger-btn i');
    if (menu.classList.contains('active')) {
        btnIcon.classList.remove('fa-bars');
        btnIcon.classList.add('fa-times');
    } else {
        btnIcon.classList.remove('fa-times');
        btnIcon.classList.add('fa-bars');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    
    // Sélectionne tous les liens à l'intérieur du menu
    const mobileLinks = document.querySelectorAll('.mobile-link');

    // Surveillance du clic
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Si le menu est ouvert, ferme après le clic
            const menu = document.getElementById('mobileMenu');
            if (menu.classList.contains('active')) {
                toggleMobileMenu();
            }
        });
    });
});

// --- POPUP CONFIDENTIALITÉ ---

function openPrivacyModal() {
    // Est-ce que l'utilisateur a déjà accepté ?
    if (localStorage.getItem('secteur_v_privacy') === 'true') {
        // Si oui, redirige directement sans afficher la modale
        window.location.href = 'discord_login.php';
        return; 
    }

    // Sinon, ouvre le popup
    document.getElementById('privacyModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closePrivacyModal(event) {
    if (!event || event.target.id === 'privacyModal') {
        document.getElementById('privacyModal').classList.remove('active');
        document.body.style.overflow = '';
    }
}

function acceptAndRedirect() {
    // Enregistre la preuve d'acceptation dans le navigateur
    localStorage.setItem('secteur_v_privacy', 'true');
    
    // Redirige vers Discord
    window.location.href = 'discord_login.php';
}

// --- POPUP ÉDITION PROFIL ---

function openEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Met à jour le compteur dès l'ouverture
    const textarea = document.getElementById('bio-input');
    updateCharCount(textarea);
}

function closeEditModal(event) {
    if (!event || event.target.id === 'editModal') {
        document.getElementById('editModal').classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Fonction pour mettre à jour le compteur
function updateCharCount(textarea) {
    const countSpan = document.getElementById('char-count');
    const currentLength = textarea.value.length;
    
    countSpan.textContent = currentLength;
    
    // Change la couleur du compteur si on dépasse les 140 caractères
    if (currentLength >= 140) {
        countSpan.style.color = '#e74c3c';
    } else {
        countSpan.style.color = '#888';
    }
}