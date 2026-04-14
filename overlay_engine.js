// overlay_engine.js

let currentState = null; // Keeps track of where we are so we don't redraw unnecessarily
let currentMatchId = null;

// Setup the Electron Hotkey Bridge
if (window.secteurV) {
    window.secteurV.onOverlayToggle((isInteractive) => {
        const wrapper = document.getElementById('overlay-wrapper');
        isInteractive ? wrapper.classList.add('interactive') : wrapper.classList.remove('interactive');
    });

    window.secteurV.onUpdateOverlay(() => {
        fetchState(); 
    });
}

let currentPollRate = 3000; // Default to 3 seconds

// The Master Fetch Loop
async function fetchState() {
    try {
        // --- FETCH OVERLAY DATA ---
        const response = await fetch('api_overlay.php', { credentials: 'include' });
        const data = await response.json();
        updateUI(data);

        // --- DYNAMIC CHAT SPEED ---
        // If in a match, shift to 1 second for smooth chat! Otherwise, relax at 3 seconds.
        currentPollRate = (data.state === "in_match") ? 1000 : 3000;

        // --- AFK FIX & STATUS MESSAGES ---
        // As long as we are logged in, ALWAYS check the official match status to catch any changes (like opponent leaving or match finishing), even if the user isn't actively looking at the overlay.
        if (data.state !== "not_logged_in") {
            
            const currentMode = document.getElementById('queue-mode-select') 
                ? document.getElementById('queue-mode-select').value 
                : 'ranked';

            const officialRes = await fetch(`api.php?action=poll_match&mode=${currentMode}`, { credentials: 'include' });
            const officialData = await officialRes.json();

            // Check for match-ending events
            if (officialData.state === 'opponent_left') {
                alert("Opponent left the match! Returning to Lobby.");
                window.location.reload(); 
            } 
            else if (officialData.state === 'finished_agreement') {
                alert("Scores validated! Match is complete. Returning to Lobby.");
                window.location.reload();
            }
            else if (officialData.state === 'lobby' && data.state === 'in_match') {
                alert("Match was closed or timed out.");
                window.location.reload();
            }
            else if (officialData.status === 'disputed') {
                const statusText = document.getElementById('ui-score-status');
                if (statusText) {
                    statusText.innerHTML = "<span style='color: #ff4444; font-weight: bold;'>DISPUTE! Scores do not match.<br>Please check the website.</span>";
                    statusText.style.display = 'block';
                }
            }
        }

    } catch (error) {
        console.error("API Error:", error);
    }

    // Call itself again after the timer finishes
    setTimeout(fetchState, currentPollRate);
}

// The UI Director
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
        document.getElementById('ui-avatar').src = data.user.avatar || 'assets/img/default_user.webp';
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
        document.getElementById('panel-match').style.display = 'flex';
        
        if (data.match) {
            currentMatchId = data.match.match_id;
            document.getElementById('ui-opponent-name').innerText = data.match.opponent_name;
            document.getElementById('ui-opponent-elo').innerText = data.match.opponent_elo;
            document.getElementById('ui-opponent-avatar').src = data.match.opponent_avatar || 'assets/img/default_user.webp';

            // --- RENDER CHAT ---
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

            // --- RENDER SCORE STATE ---
            const scoreBtn = document.getElementById('score-submit-btn');
            const scoreStatus = document.getElementById('ui-score-status');
            const inputYou = document.getElementById('score-you');
            const inputOpp = document.getElementById('score-opp');

            if (data.match.my_score_claim !== null) {
                // User already submitted their score, lock the inputs.
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

    // --- DISCORD RPC DYNAMIC UPDATES ---
    if (window.secteurV && window.secteurV.sendRPCData) {
        let rpcData = null;

        if (data.state === "idle" && data.user) {
            rpcData = {
                details: "Navigating Secteur V",
                state: "In Menus",
                hover: `${data.user.username} - ${data.user.elo} EDP`
            };
        } 
        else if (data.state === "in_queue" && data.user) {
            rpcData = {
                details: "Searching for a Match",
                state: "In Queue",
                hover: `${data.user.username} - ${data.user.elo} EDP`
            };
        } 
        else if (data.state === "in_match" && data.match && data.user) {
            rpcData = {
                details: "In a Match",
                state: `VS ${data.match.opponent_name}`,
                hover: `${data.user.username} - ${data.user.elo} EDP`
            };
        }

        // Send the payload to Electron
        if (rpcData) {
            window.secteurV.sendRPCData(rpcData);
        }
    }
}

// Send Actions back to the Server (Join/Leave Queue)
async function sendAction(actionType, extraData = {}) {
    console.log("Sending action to server:", actionType);
    
    // Combine our action type with any extra data (like 'mode')
    const payload = new URLSearchParams({ action: actionType, ...extraData });

    try {
        const response = await fetch('api_overlay_action.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload
        });
        
        const result = await response.json();
        
        if (result.success) fetchState();
        else console.error("Server refused action:", result.error);
        
    } catch (error) {
        console.error("Failed to reach action API:", error);
    }
}

function sendJoinQueue() {
    const mode = document.getElementById('queue-mode-select').value;
    sendAction('join_queue', { mode: mode });
}

// Start the Engine
fetchState();

// --- CHAT & SCORE API FUNCTIONS ---

async function submitChat(e) {
    e.preventDefault(); // Prevent page reload
    const input = document.getElementById('chat-input');
    const msg = input.value;
    
    if (!msg || !currentMatchId) return;
    
    input.value = ''; // Instantly clear the input box
    
    // Send to server
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