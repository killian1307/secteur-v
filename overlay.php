<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Secteur V Overlay</title>
    <style>
        body, html { margin: 0; padding: 0; width: 100vw; height: 100vh; background: transparent !important; overflow: hidden; font-family: 'Segoe UI', sans-serif; }
        #overlay-wrapper { width: 100%; height: 100%; transition: background-color 0.2s ease; }
        #overlay-wrapper.interactive { background-color: rgba(0, 0, 0, 0.6); }

        /* The Base Panel Style */
        .panel {
            position: absolute; top: 20px; left: 20px;
            background: rgba(10, 25, 49, 0.9); border: 2px solid #FFD700;
            border-radius: 8px; padding: 15px; color: #EDEEF2;
            pointer-events: auto; display: none;
        }

        /* Specific Panel Colors */
        #panel-error { border-color: #ff4444; }
        #panel-queue { border-color: #00ffcc; animation: pulse 2s infinite; }
        #panel-match { border-color: #ff0055; width: 300px; }

        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(0, 255, 204, 0.4); } 70% { box-shadow: 0 0 10px 10px rgba(0, 255, 204, 0); } 100% { box-shadow: 0 0 0 0 rgba(0, 255, 204, 0); } }

        .hint { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: #FFD700; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); }
    </style>
</head>
<body>

<div id="overlay-wrapper">
    
    <div id="panel-error" class="panel">
        <h3 style="margin: 0; color: #ff4444;">Login Required</h3>
        <p style="margin: 5px 0 0 0; font-size: 14px;">Please log in to the main client.</p>
    </div>

    <div id="panel-idle" class="panel">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img id="ui-avatar" src="" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #FFD700; object-fit: cover;">
            <div>
                <h3 style="margin: 0; color: #FFD700;" id="ui-username">Loading...</h3>
                <p style="margin: 5px 0 0 0;">ELO: <span id="ui-elo">--</span></p>
            </div>
        </div>
        
        <div style="margin-top: 10px; display: flex; gap: 5px;">
            <select id="queue-mode-select" style="background: #222; color: #fff; border: 1px solid #555; padding: 5px; border-radius: 4px;">
                <option value="ranked">Ranked</option>
                <option value="normal">Normal</option>
            </select>
            <button onclick="sendJoinQueue()" style="background: #FFD700; color: #000; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px; font-weight: bold;">
                Enter Queue
            </button>
        </div>
    </div>

    <div id="panel-queue" class="panel">
        <h3 style="margin: 0; color: #00ffcc;">Searching for Opponent...</h3>
        <p style="margin: 5px 0 0 0;">Players searching: <span id="ui-queue-count">1</span></p>
        <button onclick="sendAction('leave_queue')" style="margin-top: 10px; background: #ff4444; color: #fff; border: none; padding: 5px 10px; cursor: pointer;">Cancel Search</button>
    </div>

    <div id="panel-match" class="panel" style="width: 500px; display: flex; gap: 20px; border-color: #ff0055;">
        
        <div style="flex: 1; display: flex; flex-direction: column;">
            <h3 style="margin: 0 0 10px 0; color: #ff0055;">MATCH FOUND!</h3>
            
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <img id="ui-opponent-avatar" src="" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #ff0055; object-fit: cover;">
                <div>
                    <p style="margin: 0;">VS <strong id="ui-opponent-name">Unknown</strong></p>
                    <p style="margin: 0; font-size: 13px; color: #ccc;">ELO: <span id="ui-opponent-elo">--</span></p>
                </div>
            </div>
            
            <div id="ui-chat-box" style="flex: 1; min-height: 120px; background: rgba(0,0,0,0.5); padding: 8px; overflow-y: auto; border: 1px solid #444; font-size: 13px; margin-bottom: 5px; border-radius: 4px;">
                </div>
            
            <form onsubmit="submitChat(event)" style="display: flex; gap: 5px;">
                <input type="text" id="chat-input" autocomplete="off" placeholder="Type message..." style="flex: 1; padding: 6px; background: #222; color: #fff; border: 1px solid #555; border-radius: 3px;">
                <button type="submit" style="background: #FFD700; color: #000; border: none; padding: 0 15px; cursor: pointer; border-radius: 3px; font-weight: bold;">Send</button>
            </form>
        </div>

        <div id="ui-score-section" style="width: 140px; border-left: 1px solid #444; padding-left: 20px; display: flex; flex-direction: column; justify-content: center;">
            <h4 style="margin: 0 0 10px 0; color: #FFD700; text-align: center;">Report Score</h4>
            
            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px; color: #aaa;">Your Score:</label>
                <input type="number" id="score-you" min="0" max="99" style="width: 100%; box-sizing: border-box; padding: 6px; background: #222; color: #fff; border: 1px solid #555; text-align: center; border-radius: 3px; font-size: 16px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12px; color: #aaa;">Opponent Score:</label>
                <input type="number" id="score-opp" min="0" max="99" style="width: 100%; box-sizing: border-box; padding: 6px; background: #222; color: #fff; border: 1px solid #555; text-align: center; border-radius: 3px; font-size: 16px;">
            </div>
            
            <button id="score-submit-btn" onclick="submitMatchScore()" style="width: 100%; background: #00ffcc; color: #000; border: none; padding: 8px; font-weight: bold; cursor: pointer; border-radius: 3px;">Confirm</button>
            <p id="ui-score-status" style="font-size: 11px; color: #ffcc00; display: none; text-align: center; margin-top: 10px; line-height: 1.3;">Score submitted.<br>Waiting for opponent...</p>
        </div>
    </div>

    <div class="hint">Press SHIFT + TAB to interact</div>
</div>

<script src="overlay_engine.js"></script>

</body>
</html>