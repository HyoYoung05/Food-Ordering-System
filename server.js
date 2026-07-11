const http = require('http');
const fs = require('fs');
const path = require('path');

const root = __dirname;
const types = { '.html': 'text/html', '.css': 'text/css', '.js': 'text/javascript', '.svg': 'image/svg+xml' };

http.createServer((req, res) => {
  const requested = req.url === '/' ? '/app/views/index.html' : req.url.split('?')[0];
  if (requested.split('/').some(part => part.startsWith('.')) || requested.startsWith('/config/') || requested.startsWith('/vendor/')) {
    res.writeHead(403);
    return res.end('Forbidden');
  }
  const file = path.join(root, requested);
  if (!file.startsWith(root)) { res.writeHead(403); return res.end('Forbidden'); }
  fs.readFile(file, (error, data) => {
    if (error) { res.writeHead(404); return res.end('Not found'); }
    res.writeHead(200, { 'Content-Type': types[path.extname(file)] || 'application/octet-stream' });
    res.end(data);
  });
}).listen(4173, '127.0.0.1', () => console.log('Savorly running at http://127.0.0.1:4173'));
