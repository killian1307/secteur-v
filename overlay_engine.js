// overlay_engine.js

let currentState = null; // Keeps track of where we are so we don't redraw unnecessarily
let currentMatchId = null;

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
        document.getElementById('panel-match').style.display = 'flex'; // Use flex for the dual-box layout
        
        if (data.match) {
            currentMatchId = data.match.match_id;
            document.getElementById('ui-opponent-name').innerText = data.match.opponent_name;
            document.getElementById('ui-opponent-elo').innerText = data.match.opponent_elo;

            // --- 1. RENDER CHAT ---
            const chatBox = document.getElementById('ui-chat-box');
            // Check if user is currently scrolled to the bottom
            const isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 10;
            
            chatBox.innerHTML = ''; // Clear old chat
            if (data.match.chat) {
                data.match.chat.forEach(msg => {
                    chatBox.innerHTML += `<div style="margin-bottom: 4px;"><strong style="color: #FFD700;">${msg.username}:</strong> <span style="color: #eee;">${msg.message}</span></div>`;
                });
            }
            
            // Auto-scroll down if they were already at the bottom
            if (isScrolledToBottom) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            // --- 2. RENDER SCORE STATE ---
            const scoreBtn = document.getElementById('score-submit-btn');
            const scoreStatus = document.getElementById('ui-score-status');
            const inputYou = document.getElementById('score-you');
            const inputOpp = document.getElementById('score-opp');

            if (data.match.my_score_claim !== null) {
                // User already submitted their score! Lock the inputs.
                inputYou.disabled = true;
                inputOpp.disabled = true;
                scoreBtn.style.display = 'none';
                scoreStatus.style.display = 'block';
            } else {
                // Waiting for user to submit
                inputYou.disabled = false;
                inputOpp.disabled = false;
                scoreBtn.style.display = 'block';
                scoreStatus.style.display = 'none';
            }
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

// --- CHAT & SCORE API FUNCTIONS ---

async function submitChat(e) {
    e.preventDefault(); // Prevent page reload
    const input = document.getElementById('chat-input');
    const msg = input.value;
    
    if (!msg || !currentMatchId) return;
    
    input.value = ''; // Instantly clear the input box
    
    // Send to server (Remembering our credentials: 'include' fix!)
    await fetch('api_overlay_action.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'send_chat', match_id: currentMatchId, message: msg })
    });
    
    fetchState(); // Force an immediate refresh to show the message
}

async function submitMatchScore() {
    const myScore = document.getElementById('score-you').value;
    const oppScore = document.getElementById('score-opp').value;
    
    if (myScore === '' || oppScore === '' || !currentMatchId) return;
    
    await fetch('api_overlay_action.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'submit_score', match_id: currentMatchId, my_score: myScore, opp_score: oppScore })
    });
    
    fetchState(); // Refresh to lock the inputs
}