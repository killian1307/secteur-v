<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header("SECTEUR V - Mentions Légales");
$header->render();
?>

<style>
    .legal-content { max-width: 800px; margin: 0 auto; line-height: 1.6; color: var(--text-secondary); text-align: left; }
    .legal-content h2 { color: var(--text-primary); margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
    .legal-content p { margin-bottom: 1rem; }
    .legal-content a { color: var(--primary-purple); text-decoration: none; }
    .legal-content a:hover { text-decoration: underline; }
</style>

<main class="dashboard-container">
    <h1>Mentions <span style="color:var(--primary-purple)">Légales</span></h1>
    <p class="subtitle">Informations juridiques concernant le site Secteur V.</p>

    <div class="mm-view active legal-content" style="display: block;">
        <h2>1. Éditeur du site</h2>
        <p>Le site <strong>Secteur V</strong> (secteur-v.letterk.me) est édité par :<br>
        <strong>Killian (ou "K") aussi connu sous le pseudo "Acciaw"</strong><br>
        Contact : secteur-v@letterk.me</p>

        <h2>2. Hébergement</h2>
        <p>Ce site est hébergé par :<br>
        <strong>IONOS SARL</strong><br>
        7 place de la Gare, BP 70109, 57200 Sarreguemines Cedex, France.<br>
        Site web : <a href="https://www.ionos.fr" target="_blank">www.ionos.fr</a></p>

        <p>La sécurité, la gestion DNS et la distribution mondiale de ce site sont assurées par :<br>
        <strong>Cloudflare, Inc.</strong><br>
        101 Townsend St, San Francisco, CA 94107, États-Unis.<br>
        Site web : <a href="https://www.cloudflare.com/fr-fr/" target="_blank">www.cloudflare.com</a></p>

        <h2>3. Propriété intellectuelle</h2>
        <p>Le code source, le design et l'architecture du site sont la propriété exclusive de l'éditeur du Secteur V. Toute reproduction non autorisée est interdite. Les images, noms de personnages et éléments visuels issus du jeu vidéo Inazuma Eleven sont la propriété intellectuelle de Level-5 Inc.</p>

        <h2>4. Responsabilité</h2>
        <p>L'éditeur décline toute responsabilité quant aux éventuels dysfonctionnements pouvant survenir sur le site et entraîner une perte de données ou une indisponibilité de l'accès aux informations produites sur celui-ci. Les liens hypertextes présents sur le site orientant les utilisateurs vers d'autres sites Internet n'engagent pas la responsabilité de l'éditeur.</p>
    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>