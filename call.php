<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Videochiamata semplice</title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; }
        #jitsi-container { width: 100%; height: 100vh; }
        #startBtn { position: absolute; z-index: 10; margin: 10px; padding: 10px 20px; font-size: 16px; }
    </style>
</head>
<body>
    <button id="startBtn">Avvia Videochiamata</button>
    <div id="jitsi-container"></div>

    <!-- Libreria Jitsi Meet API -->
    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        document.getElementById('startBtn').onclick = function() {
            this.style.display = 'none'; // nasconde il pulsante

            // Nome stanza unico per questa chiamata
            const roomName = "VideoCall_<?php echo rand(1000,9999); ?>";

            const options = {
                roomName: roomName,
                parentNode: document.getElementById('jitsi-container'),
                width: '100%',
                height: '100%',
                configOverwrite: {
                    startWithAudioMuted: false,
                    startWithVideoMuted: false
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    SHOW_POWERED_BY: false
                }
            };

            const api = new JitsiMeetExternalAPI('meet.jit.si', options);

            // Eventi opzionali
            api.addEventListener('videoConferenceJoined', event => {
                console.log('Entrato nella stanza:', event.roomName);
            });
        };
    </script>
</body>
</html>
