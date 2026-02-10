// Fonction pour afficher/cacher le menu
function toggleMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('active');
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

// TEAM BUILDING
window.currentSlot = null;

document.addEventListener('DOMContentLoaded', () => {

    // Vérification cruciale : Est-ce que PHP a bien écrit la config ?
    if (typeof CONFIG_TEAM === 'undefined') {
        console.error("CONFIG_TEAM n'existe pas. Vérifie profil.php !");
        return;
    }

    if (typeof CONFIG_TEAM !== 'undefined' && CONFIG_TEAM.currentFormation) {
        updateLabels(CONFIG_TEAM.currentFormation);
    } else {
        // Fallback si pas de config (ex: 4-4-2 Diamant)
        updateLabels('4-4-2 Diamant');
    }

    // On charge l'équipe de l'ID indiqué dans la config
    loadTeam();

    // On active la recherche seulement si on est le propriétaire
    if (CONFIG_TEAM.isOwner) {
        setupSearch();
    }
});

// Fonction de chargement
async function loadTeam() {
    const targetId = CONFIG_TEAM.targetUserId;

    try {
        // Appel API
        const url = `api.php?action=get_team&user_id=${targetId}`;

        const response = await fetch(url);
        
        // Vérification si la réponse est du JSON valide
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();

        if(data.success && data.team) {
            renderTeam(data.team);
        } else {
            console.warn("Pas d'équipe trouvée ou erreur API :", data);
        }

    } catch(e) { 
        console.error("Erreur API (loadTeam) :", e); 
    }
}

// Rendu de l'équipe
function renderTeam(team) {
    console.log("🎨 Affichage de l'équipe et des noms...");

    ['coach', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'].forEach(id => {
        // Définition des clés pour récupérer les infos dans le JSON de l'API
        const isCoach = (id === 'coach');
        const imgKey = isCoach ? 'coach_img' : 'slot_' + id + '_img';
        const nameKey = isCoach ? 'coach_name' : 'slot_' + id + '_name'; // <--- NOUVEAU

        const img = team[imgKey];
        const name = team[nameKey]; // On récupère le nom du joueur

        // On cherche la div HTML
        const slotDiv = document.getElementById(`slot-display-${id}`);
        
        if(slotDiv) {
            // A. GESTION DE L'IMAGE (Code existant)
            if (img) {
                const labelEl = slotDiv.querySelector('.position-label');
                const labelHtml = labelEl ? labelEl.outerHTML : ''; 

                slotDiv.innerHTML = `<img src="${img}" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">${labelHtml}`;
                slotDiv.classList.add('filled');
                
                // B. GESTION DU TOOLTIP (NOUVEAU)
                // On remplace "Emplacement vide" par le nom du joueur
                slotDiv.setAttribute('data-tooltip', name); 
                
            } else {
                // Si pas de joueur, on remet le tooltip par défaut
                slotDiv.setAttribute('data-tooltip', isCoach ? "Sans coach" : "Emplacement vide");
            }
        }
    });
}

// Ouvrir la modale
window.openSelector = function(slot) {
    if (!CONFIG_TEAM.isOwner) {
        return;
    }

    window.currentSlot = (slot === 'coach') ? 'coach_id' : 'slot_' + slot;
    
    const modal = document.getElementById('playerSelectorModal');
    if (modal) {
        modal.style.display = 'flex';
        const input = document.getElementById('playerSearchInput');
        if(input) {
            input.value = ''; 
            input.focus();
        } 
        const title = document.getElementById('modalTitle');
        if(title) title.innerText = "Modifier Slot : " + slot;
    }
};

window.closeSelector = function() {
    const modal = document.getElementById('playerSelectorModal');
    if (modal) modal.style.display = 'none';
};

// Recherche
function setupSearch() {
    const input = document.getElementById('playerSearchInput');
    if(!input) return;

    let timer;
    input.addEventListener('input', (e) => {
        clearTimeout(timer);
        timer = setTimeout(() => doSearch(e.target.value), 300);
    });
}

async function doSearch(term) {
    if(term.length < 2) return;
    try {
        const res = await fetch(`api.php?action=search_players&term=${encodeURIComponent(term)}`);
        const players = await res.json();
        
        const grid = document.getElementById('searchResults');
        grid.innerHTML = '';
        
        if(!players || players.length === 0) {
            grid.innerHTML = '<p>Aucun résultat</p>'; 
            return;
        }

        players.forEach(p => {
            const div = document.createElement('div');
            div.className = 'search-card';
            div.innerHTML = `<img src="${p.image_webp}" style="width:50px;height:50px;border-radius:50%"><span>${p.name_en}</span>`;
            div.onclick = () => savePlayer(p);
            grid.appendChild(div);
        });
    } catch (e) { console.error("Erreur recherche :", e); }
}

// 6. Sauvegarde
async function savePlayer(player) {
    if (!CONFIG_TEAM.isOwner || !window.currentSlot) return;

    try {
        const res = await fetch('api.php?action=save_slot', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ slot: window.currentSlot, player_id: player.id })
        });
        
        const data = await res.json();
        if(data.success) {
            console.log("Sauvegarde réussie !");
            closeSelector();
            loadTeam(); // Rechargement simple (utilise la variable globale)
        } else {
            console.error("Erreur sauvegarde :", data);
        }
    } catch (e) { console.error("Erreur Fetch Save :", e); }
}

// --- GESTION DES FORMATIONS (VICTORY ROAD) ---
/* --- CONFIGURATION DES ROLES --- */
const FORMATION_ROLES = {
    '4-4-2 Diamant':        { 1:'GK', 2:'DF', 3:'DF', 4:'DF', 5:'DF', 6:'MF', 7:'MF', 8:'MF', 9:'MF', 10:'FW', 11:'FW' },
    '4-4-2 Boîte':          { 1:'GK', 2:'DF', 3:'DF', 4:'DF', 5:'DF', 6:'MF', 7:'MF', 8:'MF', 9:'MF', 10:'FW', 11:'FW' },
    
    '3-5-2 Liberté':        { 1:'GK', 2:'DF', 3:'DF', 4:'DF', 5:'MF', 6:'MF', 7:'MF', 8:'MF', 9:'MF', 10:'FW', 11:'FW' },
    
    '4-3-3 Triangle':       { 1:'GK', 2:'DF', 3:'DF', 4:'DF', 5:'DF', 6:'MF', 7:'MF', 8:'FW', 9:'MF', 10:'FW', 11:'FW' },
    '4-3-3 Delta':          { 1:'GK', 2:'DF', 3:'DF', 4:'DF', 5:'DF', 6:'MF', 7:'MF', 8:'FW', 9:'MF', 10:'FW', 11:'FW' },
    
    '4-5-1 Équilibré':      { 1:'GK', 2:'DF', 3:'DF', 4:'DF', 5:'DF', 6:'MF', 7:'MF', 8:'MF', 9:'MF', 10:'MF', 11:'FW' },
    
    '3-6-1 Hexa':           { 1:'GK', 2:'DF', 3:'DF', 4:'DF', 5:'MF', 6:'MF', 7:'MF', 8:'MF', 9:'MF', 10:'MF', 11:'FW' },
    
    '5-4-1 Double Volante': { 1:'GK', 2:'DF', 3:'DF', 4:'DF', 5:'DF', 6:'MF', 7:'DF', 8:'MF', 9:'MF', 10:'FW', 11:'MF' }
};

function updateLabels(formationName) {
    console.log("Mise à jour des labels pour :", formationName);
    
    // On récupère les rôles pour cette formation (ou par défaut 4-4-2 Diamant si introuvable)
    const roles = FORMATION_ROLES[formationName] || FORMATION_ROLES['4-4-2 Diamant'];

    // On boucle sur les 11 joueurs
    for (let id = 1; id <= 11; id++) {
        // On cherche la div du slot
        const slotDiv = document.getElementById(`slot-display-${id}`);
        
        if (slotDiv) {
            // On cherche le span qui contient le texte (GK, DF...)
            const labelSpan = slotDiv.querySelector('.position-label');
            
            // S'il existe, on change le texte
            if (labelSpan) {
                labelSpan.innerText = roles[id];
            }
        }
    }
}

async function changeFormation(selectElement) {
    // 1. On récupère la vraie valeur (pour la BDD) et la classe CSS (pour l'affichage)
    const newFormationName = selectElement.value; // ex: "4-4-2 Diamant"
    const cssClass = selectElement.options[selectElement.selectedIndex].getAttribute('data-class'); // ex: "4-4-2-diamant"

    console.log("Changement vers :", newFormationName, "| Classe CSS :", cssClass);

    const labelText = document.getElementById('formationLabelText');
    if (labelText) {
        labelText.innerText = newFormationName;
    }

    const field = document.getElementById('field-container');
    
    // 2. Changement Visuel : On utilise la classe CSS propre
    // On retire toutes les anciennes classes formation-...
    field.className = field.className.replace(/formation-[a-z0-9-]+/g, '').trim();
    // On ajoute la nouvelle
    field.classList.add('formation-' + cssClass);

    updateLabels(newFormationName);

    // 3. Sauvegarde API : On envoie le vrai nom avec espaces
    if (typeof CONFIG_TEAM !== 'undefined' && CONFIG_TEAM.isOwner) {
        try {
            const res = await fetch('api.php?action=save_formation', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ formation: newFormationName })
            });
            const data = await res.json();
            if (data.success) {
                console.log("Formation sauvegardée !");
            } else {
                console.error("Erreur save formation:", data.message);
            }
        } catch (e) {
            console.error("Erreur réseau formation:", e);
        }
    }
}