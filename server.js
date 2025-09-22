const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 8080 });

let peers = [];

wss.on('connection', ws => {
    peers.push(ws);

    ws.on('message', message => {
        // inoltra il messaggio a tutti gli altri peer
        peers.forEach(peer => {
            if (peer !== ws && peer.readyState === WebSocket.OPEN) {
                peer.send(message);
            }
        });
    });

    ws.on('close', () => {
        peers = peers.filter(p => p !== ws);
    });
});

console.log('Signaling server attivo su ws://localhost:8080');
