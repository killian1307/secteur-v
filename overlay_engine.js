// overlay_engine.js

let currentState = null; // Keeps track of where we are so we don't redraw unnecessarily

// 1. Setup the Electron Hotkey Bridge
if (window.secteurV) {
    window.secteurV.onOverlayToggle((isInteractive) => {
        const wrapper = document.getElementById('overlay-wrapper');
        isInteractive ? wrapper.classList.add('interactive') : wrapper.classList.remove('interactive');
    });

    // If the main app says "I just logged in!", trigger a fast refresh
    window.secteurV.onUpdateOverlay(() => {
        fetchState(); 
    });
}

// 2. The Master Fetch Loop
async function fetchState() {
    try {
        const response = await fetch('api_overlay.php');
        const data = await response.json();

        updateUI(data);
    } catch (error) {
        console.error("API Error:", error);
    }
}

// 3. The UI Director
function updateUI(data) {
    // Hide all panels first
    document.getElementById('panel-error').style.display = 'none';
    document.getElementById('panel-idle').style.display = 'none';
    document.getElementById('panel-queue').style.display = 'none';
    document.getElementById('panel-match').style.display = 'none';

    if (data.state === "not_logged_in") {
        document.getElementById('panel-error').style.display = 'block';
        return; // Stop here
    }

    // Update global user stats
    if (data.user) {
        document.getElementById('ui-username').innerText = data.user.username;
        document.getElementById('ui-elo').innerText = data.user.elo;
    }

    // Show the correct panel based on state
    if (data.state === "idle") {
        document.getElementById('panel-idle').style.display = 'block';
    
    } else if (data.state === "in_queue") {
        document.getElementById('panel-queue').style.display = 'block';
        if (data.queue) {
            document.getElementById('ui-queue-count').innerText = data.queue.players_searching;
        }

    } else if (data.state === "in_match") {
        document.getElementById('panel-match').style.display = 'block';
        if (data.match) {
            document.getElementById('ui-opponent-name').innerText = data.match.opponent_name;
            document.getElementById('ui-opponent-elo').innerText = data.match.opponent_elo;
        }
    }
}

// 4. Send Actions back to the Server (Join/Leave Queue)
async function sendAction(actionType) {
    console.log("Sending action to server:", actionType);
    
    try {
        const response = await fetch('api_overlay_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': actionType
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // The database was successfully updated!
            // Instantly fetch the new state to update the UI
            fetchState();
        } else {
            console.error("Server refused action:", result.error);
        }
    } catch (error) {
        console.error("Failed to reach action API:", error);
    }
}

// 5. Start the Engine!
// Run immediately once, then poll every 3 seconds
fetchState();
setInterval(fetchState, 3000);