import fs from 'fs';
import path from 'path';
import { logStep, logDebug } from './log.js';

export async function cleanAssets({ dryRun = false } = {}) {
  const buildDir = 'Frontend/assets/build';
  const trashDir = 'Frontend/assets/trash';
  const keepFiles = ['README.md', '.gitignore'];
  let removed = 0;

  if (!fs.existsSync(buildDir)) return removed;
  if (!fs.existsSync(trashDir)) fs.mkdirSync(trashDir);

  const files = fs.readdirSync(buildDir);
  for (const file of files) {
    if (keepFiles.includes(file)) continue;

    const src = path.join(buildDir, file);
    const dest = path.join(trashDir, file);

    if (dryRun) {
      logStep(`🔍 Simulation : ${file} serait archivé`);
    } else {
      fs.renameSync(src, dest);
      logStep(`🗑️ Archivé : ${file}`);
      removed++;
    }
    logDebug(`Fichier traité : ${file}`);
  }

  return removed;
}
