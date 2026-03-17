<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header("SECTEUR V - Confidentialité");
$header->render();
?>

<style>
    .legal-content { max-width: 800px; margin: 0 auto; line-height: 1.6; color: var(--text-secondary); text-align: left; }
    .legal-content h2 { color: var(--text-primary); margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
    .legal-content ul { margin-left: 20px; margin-bottom: 1rem; list-style-type: disc; }
    .legal-content li { margin-bottom: 0.5rem; }
</style>

<main class="dashboard-container">
    <h1>Politique de <span style="color:var(--primary-purple)">Confidentialité</span></h1>
    <p class="subtitle">Comment nous protégeons et utilisons vos données.</p>

    <div class="mm-view active legal-content" style="display: block;">
        <h2>1. Collecte des données via Discord</h2>
        <p>Pour assurer le bon fonctionnement du Secteur V (système de compte, matchmaking, et création de litiges), nous utilisons l'authentification tierce via <strong>Discord</strong>. Lors de votre inscription et de vos connexions, nous collectons automatiquement les données suivantes :</p>
        <ul>
            <li><strong>L'ID Discord unique :</strong> Pour lier votre compte Secteur V à votre compte Discord.</li>
            <li><strong>Le pseudo Discord :</strong> Utilisé comme nom d'affichage public sur notre classement et lors des matchs.</li>
            <li><strong>La photo de profil (Avatar) :</strong> Affichée sur votre profil et dans l'arène de matchmaking.</li>
            <li><strong>L'adresse Email :</strong> Collectée à des fins de sécurité et de communication (vous pouvez choisir de la masquer ou de la supprimer dans les paramètres de votre profil).</li>
        </ul>

        <h2>2. Utilisation des données</h2>
        <p>Ces données sont strictement utilisées dans le cadre du fonctionnement du site :</p>
        <ul>
            <li>Gestion de votre classement (points ELO, victoires/défaites).</li>
            <li>Résolution des litiges via notre bot Discord officiel (invitation dans des salons de modération).</li>
            <li>Affichage de l'historique des matchs.</li>
        </ul>
        <p><strong>Aucune de vos données personnelles n'est vendue, cédée ou échangée à des tiers à des fins commerciales.</strong></p>

        <h2>3. Droit de rétractation et suppression des données (RGPD)</h2>
        <p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression totale de vos données.</p>
        <p>Vous pouvez vous rétracter et exercer votre droit à l'oubli à tout moment. <strong>Pour supprimer l'intégralité de vos données de nos serveurs, il vous suffit de vous rendre sur la page <a href="profile_settings.php" style="color:var(--primary-purple);">Paramètres du Profil (profile_settings)</a> et de cliquer sur le bouton de suppression de compte.</strong> Cette action est irréversible et détruira instantanément vos statistiques, votre ELO, et votre lien avec Discord.</p>

        <h2>4. Cookies</h2>
        <p>Nous utilisons uniquement des cookies de session strictement nécessaires pour vous maintenir connecté à votre compte. Ces cookies ont une durée de vie d'un an et ne sont pas utilisés pour du pistage publicitaire.</p>
    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
<script src="script.js"></script>
</body>
</html>