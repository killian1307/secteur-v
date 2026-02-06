<?php
class Dashboard {

    public function render() {
        // Classe username si la session est active
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Joueur';
        
        // Sécurise l'affichage pour éviter les failles XSS
        $safeName = htmlspecialchars($username);

        echo '<main>
            <div style="text-align: center; padding-top: 5rem;">
                <h1>Bienvenue, ' . $safeName . '.</h1>
                <p style="color: var(--text-secondary);">Le tableau de bord est en cours de construction...</p>
            </div>
        </main>';
    }
}
?>