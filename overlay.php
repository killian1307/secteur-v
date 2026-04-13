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
            pointer-events: auto; display: none; /* HIDDEN BY DEFAULT */
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
        <h3 style="margin: 0; color: #FFD700;" id="ui-username">Loading...</h3>
        <p style="margin: 5px 0 0 0;">ELO: <span id="ui-elo">--</span></p>
        <button onclick="sendAction('join_queue')" style="margin-top: 10px; background: #FFD700; color: #000; border: none; padding: 5px 10px; cursor: pointer;">Enter Queue</button>
    </div>

    <div id="panel-queue" class="panel">
        <h3 style="margin: 0; color: #00ffcc;">Searching for Opponent...</h3>
        <p style="margin: 5px 0 0 0;">Players searching: <span id="ui-queue-count">1</span></p>
        <button onclick="sendAction('leave_queue')" style="margin-top: 10px; background: #ff4444; color: #fff; border: none; padding: 5px 10px; cursor: pointer;">Cancel Search</button>
    </div>

    <div id="panel-match" class="panel">
        <h3 style="margin: 0; color: #ff0055;">MATCH FOUND!</h3>
        <p style="margin: 10px 0 5px 0;">VS <strong id="ui-opponent-name">Unknown</strong> (ELO: <span id="ui-opponent-elo">--</span>)</p>
        <div style="height: 100px; background: #000; margin-top: 10px; padding: 5px; overflow-y: auto;">
            <p style="color: gray; font-size: 12px; margin: 0;">Live chat connected...</p>
        </div>
        <input type="text" placeholder="Type message..." style="width: 100%; box-sizing: border-box; margin-top: 5px; padding: 5px;">
    </div>

    <div class="hint">Press SHIFT + TAB to interact</div>
</div>

<script src="overlay_engine.js"></script>

</body>
</html>