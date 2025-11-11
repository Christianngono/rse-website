import fs from 'fs';
import readline from 'readline';
import { logStep } from './log.js';

function showBuildReport() {
  const path = 'Frontend/assets/build-report.txt';
  if (!fs.existsSync(path)) return console.log('\x1b[33m⚠ Aucun rapport de build trouvé.\x1b[0m');
  const content = fs.readFileSync(path, 'utf-8');
  console.log('\x1b[35m📄 Rapport de Build :\x1b[0m\n' + content);
}

function showImageReport() {
  const path = 'Frontend/assets/image-report.json';
  if (!fs.existsSync(path)) return console.log('\x1b[33m⚠ Aucun rapport d’image trouvé.\x1b[0m');
  const data = JSON.parse(fs.readFileSync(path, 'utf-8'));
  console.log('\x1b[36m🖼️ Images encodées :\x1b[0m');
  data.forEach((img, i) => {
    console.log(` ${i + 1}. ${img.name} — ${img.size}, ${img.width}x${img.height}`);
  });
}

function showMenu() {
  console.clear();
  console.log(`
\x1b[32m╔══════════════════════════════════════╗
║     🧾 DASHBOARD DE RAPPORTS CLI      ║
╚══════════════════════════════════════╝\x1b[0m
1. Voir rapport de build
2. Voir rapport d’images
q. Quitter
`);
}

const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
showMenu();

rl.on('line', input => {
  switch (input.trim()) {
    case '1':
      showBuildReport();
      break;
    case '2':
      showImageReport();
      break;
    case 'q':
      rl.close();
      return;
    default:
      console.log('\x1b[33mCommande inconnue.\x1b[0m');
  }
  console.log('\nAppuie sur une touche pour continuer...');
});