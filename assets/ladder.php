<?php
class Ladder {

    public function render() {
        // Classe username si la session est active
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Joueur';
        
        // Sécurise l'affichage pour éviter les failles XSS
        $safeName = htmlspecialchars($username);

        echo '<main>
            <div class="dashboard-container">
                <h1>Bienvenue, <span style="color: var(--primary-purple)">' . $safeName . '</span>.</h1>
                <p class="subtitle">Le classement est en cours de construction...</p>
            </div>
        </main>';
    }
}
?>