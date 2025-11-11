import './checkEnv.js';
import { showSplash } from './splash.js';
import { logStep, logSuccess } from './log.js';
import { playSound } from './sound.js';
import { encodeImages } from './encodeImages.js';
import { cleanAssets } from './cleanAssets.js';
import fs from 'fs';

showSplash();

const args = process.argv.slice(2);
const flags = Object.fromEntries(args.map(arg => [arg.replace('--', ''), true]));
const reportPath = 'Frontend/assets/build-report.txt';
const htmlPath = 'Frontend/assets/build-report.html';
const isDryRun = flags['dry-run'];

if (flags['report-only']) {
  if (fs.existsSync(reportPath)) {
    const content = fs.readFileSync(reportPath, 'utf-8');
    console.log('\x1b[35m📄 Rapport de build :\x1b[0m\n' + content);
  } else {
    console.warn('\x1b[33m⚠ Aucun rapport trouvé.\x1b[0m');
  }
  process.exit(0);
}

(async () => {
  if (flags['clean-only']) {
    logStep('Nettoyage des assets obsolètes...');
    const removedCount = await cleanAssets({ dryRun: isDryRun });
    playSound('clean');
    logSuccess(`🧹 Nettoyage terminé (${removedCount} fichiers${isDryRun ? ' simulés' : ''}).`);
    return;
  }

  logStep(`🔧 Build lancé${isDryRun ? ' en mode simulation' : ''}...`);

  const removedCount = await cleanAssets({ dryRun: isDryRun });
  playSound('clean');

  logStep('Encodage des images...');
  const encodedCount = await encodeImages({ dryRun: isDryRun });
  playSound('encode');

  const report = `Build terminé à ${new Date().toLocaleString()}
🧹 Fichiers supprimés : ${removedCount}
🖼️ Images encodées : ${encodedCount}
Mode : ${isDryRun ? 'Simulation (dry-run)' : 'Production'}
`;
  fs.writeFileSync(reportPath, report);

  const html = `
<html>
<head><title>Rapport de Build</title></head>
<body style="font-family:sans-serif">
<h1>🧾 Rapport de Build</h1>
<p><strong>Date :</strong> ${new Date().toLocaleString()}</p>
<p><strong>Fichiers supprimés :</strong> ${removedCount}</p>
<p><strong>Images encodées :</strong> ${encodedCount}</p>
<p><strong>Mode :</strong> ${isDryRun ? 'Simulation (dry-run)' : 'Production'}</p>
</body>
</html>
`;
  fs.writeFileSync(htmlPath, html);

  logSuccess('✅ Build terminé avec succès.');
  playSound('success');

  console.log(`
\x1b[32m
╔══════════════════════════════════════╗
║   🎯 MISSION ACCOMPLIE — BUILD OK    ║
╚══════════════════════════════════════╝
\x1b[0m`);
})();