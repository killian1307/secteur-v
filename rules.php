<?php
require_once 'assets/init_session.php';
require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header("SECTEUR V - Règlement");
$header->render();
?>

<style>
    .rules-content { max-width: 800px; margin: 0 auto; line-height: 1.6; color: var(--text-secondary); text-align: left; }
    .rules-content h2 { color: var(--text-primary); margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
    .rules-content h3 { color: var(--primary-purple); margin-top: 1.5rem; margin-bottom: 0.5rem; font-size: 1.1rem; }
    .rules-content ul, .rules-content ol { margin-left: 20px; margin-bottom: 1rem; list-style-type: disc; }
    .rules-content ol { list-style-type: decimal; }
    .rules-content li { margin-bottom: 0.5rem; }
    .rules-content .highlight { color: var(--primary-purple); font-weight: bold; }
    .rules-content .warning-box { 
        background: rgba(231, 76, 60, 0.1); 
        border-left: 4px solid #e74c3c; 
        padding: 15px; 
        margin: 20px 0; 
        border-radius: 0 5px 5px 0; 
        color: #e74c3c;
    }
</style>

<main class="dashboard-container">
    <h1>Règlement <span style="color:var(--primary-purple)">Officiel</span></h1>
    <p class="subtitle">Tout ce qu'il faut savoir avant de lancer un match sur le Secteur V.</p>

    <div class="mm-view active rules-content" style="display: block;">
        
        <h2>1. Déroulement d'un Match</h2>
        <p>Par défaut, tous les matchs classés (Ranked) et normaux se jouent en <strong>une seule manche gagnante (BO1)</strong>.</p>
        <ul>
            <li><strong>Résultat :</strong> À la fin du match, le vainqueur et le perdant doivent déclarer le score exact sur le site.</li>
            <li><strong>Égalité :</strong> C'est la seule exception valable pour rejouer un match. Si la partie se solde par une égalité, vous devez relancer un match pour vous départager.</li>
            <li><strong>Matchs en BO3 (Best of 3) :</strong> Si les deux joueurs sont d'accord, vous pouvez convenir dans le tchat de jouer en 2 manches gagnantes.<br>
            <span style="font-size: 0.9rem; opacity: 0.8;"><em>Attention : Le Secteur V décline toute responsabilité en cas de déconnexion ou de litige lors d'un BO3. Si votre adversaire quitte après la première partie, seul ce premier résultat fera foi pour le classement.</em></span></li>
        </ul>

        <h2>2. Comment s'affronter sur Victory Road ?</h2>
        <p>Pour vous retrouver en jeu, vous devez utiliser le <strong>tchat intégré à votre page de match</strong> sur Secteur V pour vous échanger le mot de passe du salon.</p>
        
        <h3>Pour le Créateur du salon (Hôte) :</h3>
        <ol>
            <li>Dans le jeu, allez dans <strong>Jeu en ligne</strong> > <strong>Match de salon</strong>.</li>
            <li>Choisissez <strong>Créer un salon</strong>.</li>
            <li>Définissez un <strong>mot de passe</strong> (un code simple à chiffres suffit).</li>
            <li>Envoyez ce mot de passe à votre adversaire via le tchat Secteur V et attendez qu'il rejoigne.</li>
        </ol>

        <h3>Pour l'Adversaire :</h3>
        <ol>
            <li>Dans le jeu, allez dans <strong>Jeu en ligne</strong> > <strong>Match de salon</strong>.</li>
            <li>Choisissez <strong>Chercher un salon</strong>.</li>
            <li>Entrez le <strong>mot de passe</strong> communiqué par l'hôte dans le tchat.</li>
            <li>Rejoignez la salle et préparez-vous au coup d'envoi !</li>
        </ol>

        <h2>3. Fair-play et Comportement (Rappel des CGU)</h2>
        <p>L'arène du Secteur V est un lieu de compétition saine. Tout manquement aux règles suivantes entraînera des sanctions sévères (perte d'ELO, bannissement temporaire ou définitif).</p>
        <ul>
            <li><strong>Engagement :</strong> Vous devez jouer vos matchs jusqu'au bout. L'abandon volontaire (rage quit) sans raison valable est interdit.</li>
            <li><strong>Honnêteté :</strong> Déclarez toujours le vrai score. En cas de litige, vous devrez fournir des preuves (capture d'écran de l'écran de fin) aux modérateurs sur Discord.</li>
            <li><strong>Anti-Triche (Win-Trading) :</strong> Il est strictement interdit d'utiliser plusieurs comptes (smurfs) ou de s'arranger avec d'autres joueurs pour truquer les résultats d'un match afin de manipuler le classement.</li>
            <li><strong>Respect :</strong> Restez toujours courtois dans le tchat de match. Les provocations, insultes ou comportements toxiques n'ont pas leur place ici.</li>
        </ul>

        <div class="warning-box">
            <strong>Rappel :</strong> En cas de litige, l'équipe de modération du Secteur V a le dernier mot. Jouez fair-play, prenez vos captures d'écran en fin de match par sécurité, et tout se passera bien !
        </div>

    </div>
</main>

<?php
$footer = new Footer();
$footer->render();
?>
<script src="script.js"></script>
</body>
</html>