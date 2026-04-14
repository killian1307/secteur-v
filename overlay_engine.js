// overlay_engine.js

let currentState = null; // Keeps track of where we are so we don't redraw unnecessarily
let currentMatchId = null;

// Setup the Electron Hotkey Bridge
if (window.secteurV) {
    window.secteurV.onOverlayToggle((isInteractive) => {
        const wrapper = document.getElementById('overlay-wrapper');
        if (isInteractive) {
            wrapper.classList.add('interactive');
            // If we are currently in a match, auto-focus the chat box!
            if (document.getElementById('panel-match').style.display === 'flex') {
                setTimeout(() => {
                    document.getElementById('chat-input')?.focus();
                }, 50); // Tiny delay to ensure the window is active
            }
        } else {
            wrapper.classList.remove('interactive');
            document.getElementById('chat-input')?.blur(); // Unfocus when hiding
        }
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

            // --- PULL SOCIAL DATA ---
            if (data.state === "idle" || data.state === "in_queue") {
                pollSocialSystem();
            }

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
    document.getElementById('panel-social').style.display = 'none';

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
            document.getElementById('match-my-name').innerText = data.user.username;
            document.getElementById('match-my-elo').innerText = data.user.elo;
            document.getElementById('match-my-avatar').src = data.user.avatar || 'assets/img/default_user.webp';

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
                    // Decide the color
                    const isMe = (msg.username === data.user.username);
                    const nameColor = isMe ? '#FFD700' : '#2b9927';
                    
                    chatBox.innerHTML += `<div class="chat-bubble"><strong style="color: ${nameColor};">${msg.username}:</strong> <span style="color: #eee;">${msg.message}</span></div>`;
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

    // Toggle Social Panel visibility based on whether we're in the lobby or not
    const panelSocial = document.getElementById('panel-social');
    if (panelSocial) {
        if (data.state === "idle" || data.state === "in_queue") {
            panelSocial.style.display = 'flex';
            setTimeout(() => panelSocial.classList.add('show'), 10);
        } else {
            panelSocial.classList.remove('show');
            setTimeout(() => panelSocial.style.display = 'none', 400);
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

// --- Auto Fade the SHIFT+TAB Hint ---
setTimeout(() => {
    const hintElement = document.querySelector('.hint');
    if (hintElement) {
        hintElement.style.opacity = '0';
    }
}, 5000);

// --- AUTO-LEAVE QUEUE ON APP QUIT ---
window.addEventListener('beforeunload', () => {
    // If the queue panel is visible when the window is being killed
    if (document.getElementById('panel-queue').style.display === 'block') {

        const payload = new URLSearchParams({ action: 'leave_queue' });
        navigator.sendBeacon('api_overlay_action.php', payload);
    }
});

// ==========================================
// OVERLAY SOCIAL ENGINE
// ==========================================
let currentSocialTab = 'dms';
let activeDMUserId = null;

function setSocialTab(tabName) {
    currentSocialTab = tabName;
    activeDMUserId = null; 
    
    // Update the tab colors
    ['dms', 'friends', 'notifs'].forEach(t => {
        const tab = document.getElementById(`tab-${t}`);
        if (tab) tab.classList.remove('active');
    });
    const activeTab = document.getElementById(`tab-${tabName}`);
    if (activeTab) activeTab.classList.add('active');
    
    // Hide chat UI
    const header = document.getElementById('social-chat-header');
    if (header) header.style.display = 'none';
    const form = document.getElementById('social-chat-form');
    if (form) form.style.display = 'none';
    
    // Force the loader onto the screen instantly
    const content = document.getElementById('social-content');
    if (content) {
        content.innerHTML = '<div style="display:flex; justify-content:center; margin-top:20px;"><div class="loader"></div></div>';
    }
    
    // Fetch the new data in the background
    pollSocialSystem(); 
}

function openDM(userId, username) {
    activeDMUserId = userId;
    // Inject the username into the header!
    if (username) document.getElementById('social-chat-name').innerText = username;
    
    document.getElementById('social-chat-header').style.display = 'flex';
    document.getElementById('social-chat-form').style.display = 'flex';
    document.getElementById('social-chat-input').focus();
    pollSocialSystem(); 
}

function closeActiveDM() {
    activeDMUserId = null;
    document.getElementById('social-chat-header').style.display = 'none';
    document.getElementById('social-chat-form').style.display = 'none';
    pollSocialSystem();
}

async function sendDMChat(e) {
    e.preventDefault();
    const input = document.getElementById('social-chat-input');
    const msg = input.value;
    if (!msg || !activeDMUserId) return;
    
    input.value = '';
    
    // Added "action: 'send_message'" so the PHP server actually knows what to do!
    const payload = new URLSearchParams({ 
        action: 'send_message', 
        target_id: activeDMUserId, 
        message: msg 
    });

    await fetch('api_overlay_action.php', {
        method: 'POST', 
        credentials: 'include',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload
    });
    
    pollSocialSystem(); // Instantly refresh chat to show the new message
}

async function respondRequest(senderId, responseStatus) {
    // Send the Accept or Deny request to the PHP server
    await fetch('api_overlay_action.php', {
        method: 'POST', credentials: 'include',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'respond_friend_request', sender_id: senderId, response: responseStatus })
    });
    // Instantly refresh the UI
    pollSocialSystem(); 
}

async function pollSocialSystem() {
    const contentBox = document.getElementById('social-content');
    if (!contentBox) return;

    // Helper function to force strict POST requests
    async function fetchSocial(actionName, extraParams = {}) {
        const payload = new URLSearchParams({ action: actionName, ...extraParams });
        const res = await fetch('api_overlay_action.php', {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload
        });
        return await res.json();
    }

    try {
        // --- ALWAYS UPDATE THE RED DOTS FIRST ---
        const countData = await fetchSocial('get_social_counts');
        if (countData) {
            document.getElementById('tab-dms').innerHTML = countData.unread_dms > 0 ? '💬<span class="tab-badge"></span>' : '💬';
            document.getElementById('tab-notifs').innerHTML = countData.pending_requests > 0 ? '🔔<span class="tab-badge"></span>' : '🔔';
        }

        // --- LOAD TAB DATA ---
        if (activeDMUserId) {
            const data = await fetchSocial('get_chat', { target_id: activeDMUserId });
            const isScrolledToBottom = contentBox.scrollHeight - contentBox.clientHeight <= contentBox.scrollTop + 10;
            contentBox.innerHTML = '';
            
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    const isMe = (msg.sender_id != activeDMUserId);
                    const bubbleClass = isMe ? 'me' : 'them';
                    contentBox.innerHTML += `<div class="dm-bubble ${bubbleClass}">${msg.message}</div>`;
                });
            } else {
                contentBox.innerHTML = '<div style="text-align:center; color:#888; margin-top:20px;">Start a conversation!</div>';
            }
            if (isScrolledToBottom) contentBox.scrollTop = contentBox.scrollHeight;
            return;
        }

        if (currentSocialTab === 'dms') {
            const data = await fetchSocial('poll_messages');
            contentBox.innerHTML = '';
            if (data.conversations && data.conversations.length > 0) {
                data.conversations.forEach(conv => {
                    const unreadDot = conv.unread > 0 ? '<span class="unread-dot"></span>' : '';
                    contentBox.innerHTML += `
                        <div class="social-item" onclick="openDM(${conv.user_id}, '${conv.username.replace(/'/g, "\\'")}')">
                            <img src="${conv.avatar || 'assets/img/default_user.webp'}" class="social-avatar">
                            <div style="flex: 1;">
                                <p class="social-name">${conv.username}</p>
                                <p class="social-sub">${conv.last_message}</p>
                            </div>
                            ${unreadDot}
                        </div>`;
                });
            } else {
                contentBox.innerHTML = '<div style="text-align:center; color:#888; margin-top:20px;">No messages yet.</div>';
            }
        }
        else if (currentSocialTab === 'friends') {
            const data = await fetchSocial('poll_social');
            contentBox.innerHTML = '';
            if (data.friends && data.friends.length > 0) {
                data.friends.forEach(friend => {
                    contentBox.innerHTML += `
                        <div class="social-item" onclick="setSocialTab('dms'); openDM(${friend.id}, '${friend.username.replace(/'/g, "\\'")}');">
                            <img src="${friend.avatar || 'assets/img/default_user.webp'}" class="social-avatar">
                            <div>
                                <p class="social-name">${friend.username}</p>
                                <p class="social-sub">ELO: ${friend.elo}</p>
                            </div>
                        </div>`;
                });
            } else {
                contentBox.innerHTML = '<div style="text-align:center; color:#888; margin-top:20px;">No friends online.</div>';
            }
        }
        else if (currentSocialTab === 'notifs') {
            const data = await fetchSocial('poll_notifications');
            contentBox.innerHTML = '';
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(req => {
                    contentBox.innerHTML += `
                        <div class="social-item" style="cursor: default;">
                            <img src="${req.avatar || 'assets/img/default_user.webp'}" class="social-avatar">
                            <div style="flex: 1;">
                                <p class="social-name" style="font-size: 0.9rem;">${req.username}</p>
                                <p class="social-sub" style="color: #FFD700;">Sent a friend request</p>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <button onclick="respondRequest(${req.sender_id}, 'accepted')" style="background: rgba(0, 255, 204, 0.8); color: #000; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; padding: 4px 8px; transition: 0.2s;">✓</button>
                                <button onclick="respondRequest(${req.sender_id}, 'declined')" style="background: rgba(255, 68, 68, 0.8); color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; padding: 4px 8px; transition: 0.2s;">✕</button>
                            </div>
                        </div>`;
                });
            } else {
                contentBox.innerHTML = '<div style="text-align:center; color:#888; margin-top:20px;">No pending requests.</div>';
            }
        }
    } catch (e) {
        console.error("Social Polling Error:", e);
    }
}