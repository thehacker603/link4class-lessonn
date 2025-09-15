const { PeerServer } = require('peer');

const PORT = 9000;       // porta server
const PATH = '/';   // path PeerJS

const peerServer = PeerServer({ port: PORT, path: PATH });

peerServer.on('connection', (client) => {
  console.log(`Peer connesso: ${client.id}`);
});

peerServer.on('disconnect', (client) => {
  console.log(`Peer disconnesso: ${client.id}`);
});

console.log(`PeerServer avviato su http://localhost:${PORT}${PATH}`);
