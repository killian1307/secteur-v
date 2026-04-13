<?php
require_once 'assets/init_session.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (strpos($userAgent, 'SecteurV-Desktop-App') === false) {
    // They are on a regular browser! Kick them to the download page.
    header("Location: download_client.php");
    exit;
}

require 'db.php';

require 'assets/header.php';
require 'assets/footer.php';

$header = new Header('SECTEUR V - Tournaments');
$header->render();
?>

<main class="dashboard-container" style="text-align: center; padding: 100px 20px; min-height: 60vh;">
    <h1 style="font-size: 3rem; color: #FFD700;"><i class="fas fa-tools"></i></h1>
    <h2 style="margin-top: 20px; text-transform: uppercase;">Tournaments Coming Soon</h2>
    <p style="color: var(--text-secondary); margin-top: 10px;">The ultimate bracket system is currently under construction.</p>
    
    <a href="/" style="display: inline-block; margin-top: 30px; color: var(--primary-purple); text-decoration: none; font-weight: bold;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</main>

<script>
    // Update Discord RPC to show that the user is on the Tournaments page
    document.addEventListener('DOMContentLoaded', () => {
        if (window.secteurV) {
            window.secteurV.sendRPCData({
                details: "Exploring Tournaments",
                state: "Waiting for the next cup"
            });
        }
    });
</script>

<?php
$footer = new Footer();
$footer->render();
?>