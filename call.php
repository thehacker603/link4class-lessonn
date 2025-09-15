<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Videochiamata Daily.co</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 0;
            height: 100vh;
            background-color: #f4f4f9;
        }
        #input-container {
            margin-top: 50px;
            text-align: center;
        }
        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            width: 250px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            margin-left: 10px;
            cursor: pointer;
        }
        #video-container {
            margin-top: 20px;
            width: 80%;
            height: 70%;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <div id="input-container">
        <h2>Entra nella Videochiamata</h2>
        <input type="text" id="roomCode" placeholder="Inserisci codice stanza Daily.co">
        <button onclick="joinRoom()">Entra</button>
        <button onclick="createRoom()">Crea stanza casuale</button>
    </div>

    <div id="video-container"></div>

    <script>
        function joinRoom() {
            const roomCode = document.getElementById('roomCode').value.trim();
            if (!roomCode) {
                alert("Per favore inserisci un codice stanza!");
                return;
            }
            startCall(roomCode);
        }

        function createRoom() {
            // Genera un nome stanza casuale, es. "stanza-1234abcd"
            const randomRoom = 'stanza-' + Math.random().toString(36).substring(2, 10);
            document.getElementById('roomCode').value = randomRoom;
            startCall(randomRoom);
        }

        function startCall(roomCode) {
            const container = document.getElementById('video-container');
            container.innerHTML = ''; // Pulisco il contenitore

            // Creo l'iframe Daily.co
            const iframe = document.createElement('iframe');
            iframe.src = `https://${encodeURIComponent(roomCode)}.daily.co`;
            iframe.allow = "camera; microphone; fullscreen";
            container.appendChild(iframe);
        }
    </script>
</body>
</html>
