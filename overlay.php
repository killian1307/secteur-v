<?php
require_once 'assets/init_session.php';
require 'db.php';

// Check if the user is logged in, but DO NOT exit if they aren't!
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    // Only fetch data if the session exists
    $stmt = $pdo->prepare("SELECT username, elo, grade FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Secteur V Overlay</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            background-color: transparent !important; 
            overflow: hidden; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #overlay-wrapper {
            width: 100%;
            height: 100%;
            transition: background-color 0.2s ease;
        }
        
        #overlay-wrapper.interactive {
            background-color: rgba(0, 0, 0, 0.4); 
        }

        /* The Standard Gold Widget */
        .widget-stats {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(10, 25, 49, 0.85); 
            border: 2px solid #FFD700;
            border-radius: 8px;
            padding: 15px;
            color: #EDEEF2;
            pointer-events: auto; 
        }

        /* The Error Red Widget */
        .widget-error {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(10, 25, 49, 0.85); 
            border: 2px solid #ff4444; /* Red border */
            border-radius: 8px;
            padding: 15px;
            color: #EDEEF2;
            pointer-events: auto; 
        }

        .hint {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }
    </style>
</head>
<body>

<div id="overlay-wrapper">
    
    <?php if ($isLoggedIn && $user): ?>
        <div class="widget-stats">
            <h3 style="margin: 0; color: #FFD700;"><?php echo htmlspecialchars($user['username']); ?></h3>
            <p style="margin: 5px 0 0 0;">ELO: <?php echo $user['elo']; ?></p>
            <button onclick="joinQueue()" style="margin-top: 10px; background: #FFD700; color: #000; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                Enter Queue
            </button>
        </div>
        <div class="hint">Press SHIFT + TAB to interact with Secteur V</div>

    <?php else: ?>
        <div class="widget-error">
            <h3 style="margin: 0; color: #ff4444;">Login Required</h3>
            <p style="margin: 5px 0 0 0; font-size: 14px; max-width: 250px; line-height: 1.4;">
                Please log in to the main Secteur V client first to enable the overlay.
            </p>
        </div>
    <?php endif; ?>

</div>

<script>
    if (window.secteurV) {
        // Handle the Shift+Tab interaction
        window.secteurV.onOverlayToggle((isInteractive) => {
            const wrapper = document.getElementById('overlay-wrapper');
            if (isInteractive) {
                wrapper.classList.add('interactive');
            } else {
                wrapper.classList.remove('interactive');
            }
        });

        // Listen for login notifications to refresh the overlay
        if (window.secteurV.onUpdateOverlay) {
            window.secteurV.onUpdateOverlay(() => {
                console.log("Login detected! Fetching live stats...");
                // Seamlessly reload the overlay to execute the PHP session check again
                window.location.reload();
            });
        }
    }

    function joinQueue() {
        console.log("Queue joined via overlay!");
    }
</script>

</body>
</html>