<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header("SECTEUR V - CGU");
$header->render();
?>

<style>
    .legal-content { max-width: 800px; margin: 0 auto; line-height: 1.6; color: var(--text-secondary); text-align: left; }
    .legal-content h2 { color: var(--text-primary); margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
    .legal-content p { margin-bottom: 1rem; }
</style>

<main class="dashboard-container">
    <h1>Conditions Générales <span style="color:var(--primary-purple)">d'Utilisation</span></h1>
    <p class="subtitle">Les règles de bonne conduite du Secteur V.</p>

    <div class="mm-view active legal-content" style="display: block;">
        <h2>1. Acceptation des conditions</h2>
        <p>En vous connectant au Secteur V via votre compte Discord, vous acceptez sans réserve les présentes Conditions Générales d'Utilisation (CGU). Si vous n'acceptez pas ces conditions, vous ne pouvez pas utiliser nos services.</p>

        <h2>2. Accès au service et Compte Utilisateur</h2>
        <p>L'accès au matchmaking nécessite un compte Discord valide. Vous êtes responsable de la sécurité de votre compte Discord. Le Secteur V se réserve le droit de bannir tout compte lié à des comportements suspects, au "smurfing" (création de comptes multiples pour manipuler le classement) ou à la triche.</p>
        <p>Vous êtes libre de cesser d'utiliser nos services à tout moment. Vous pouvez supprimer définitivement votre compte et toutes les statistiques associées depuis la page <strong>Paramètres du Profil</strong>.</p>

        <h2>3. Règles du Matchmaking et Fair-play</h2>
        <p>Le Secteur V est une plateforme compétitive basée sur le respect mutuel. Lors de vos matchs, vous vous engagez à :</p>
        <ul>
            <li>Jouer vos matchs jusqu'au bout. L'abandon répété peut entraîner des pénalités.</li>
            <li>Renseigner les scores exacts à l'issue de la partie.</li>
            <li><strong>Ne pas manipuler le classement (Elo-boosting / Win-trading) :</strong> Toute tentative d'arrangement avec des amis pour truquer le résultat d'un match, ou l'utilisation de plusieurs comptes (smurfs) pour faire monter artificiellement vos propres statistiques, entraînera un bannissement définitif et/ou la suppression de vos points.</li>
            <li>Rester courtois dans le tchat de match. Toute insulte, harcèlement ou comportement toxique entraînera une exclusion de la plateforme.</li>
        </ul>

        <h2>4. Gestion des Litiges</h2>
        <p>En cas de désaccord sur le score final, le match passe en mode "Litige". Un salon privé est automatiquement créé sur notre serveur Discord officiel. Vous devez fournir une preuve vidéo ou photo (capture d'écran de l'écran de fin de match) aux modérateurs. Les décisions prises par l'équipe de modération sont définitives.</p>
        <p>Toute tentative de fraude lors d'un litige (falsification de preuves, mensonge) entraînera un bannissement définitif et la perte de vos points EDP.</p>

        <h2>5. Modification des CGU</h2>
        <p>Secteur V se réserve le droit de modifier ces CGU à tout moment, notamment pour s'adapter aux évolutions du jeu ou de la législation. Les joueurs seront informés des changements majeurs via notre serveur Discord.</p>
    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
</body>
</html>