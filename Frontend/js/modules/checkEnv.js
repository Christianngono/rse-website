import { execSync } from 'child_process';
import { accessSync, constants } from 'fs';
import path from 'path';
import os from 'os';

function log(title, value) {
  console.log(`\x1b[36m[✔] ${title}:\x1b[0m ${value}`);
}

function warn(title, value) {
  console.warn(`\x1b[33m[⚠] ${title}:\x1b[0m ${value}`);
}

function fail(title, value) {
  console.error(`\x1b[31m[✖] ${title}:\x1b[0m ${value}`);
  process.exit(1);
}

// ✅ Node.js version
try {
  const nodeVersion = process.version;
  log('Node.js version', nodeVersion);
} catch {
  fail('Node.js', 'Non détecté');
}

// ✅ PHP
try {
  const phpVersion = execSync('php -v').toString().split('\n')[0];
  log('PHP version', phpVersion);
} catch {
  fail('PHP', 'Non installé ou non accessible');
}

// ✅ Composer
try {
  const composerVersion = execSync('composer --version').toString().trim();
  log('Composer version', composerVersion);
} catch {
  fail('Composer', 'Non installé ou non accessible');
}

// ✅ Extensions PHP nécessaires
const requiredExtensions = ['pdo_mysql', 'mbstring', 'openssl'];
try {
  const loaded = execSync('php -m').toString().split('\n').map(e => e.trim());
  requiredExtensions.forEach(ext => {
    if (loaded.includes(ext)) {
      log(`Extension PHP`, `${ext} ✅`);
    } else {
      fail(`Extension PHP`, `${ext} ❌ manquante`);
    }
  });
} catch {
  fail('Extensions PHP', 'Impossible de les vérifier');
}

// ✅ Prefix npm
try {
  const prefix = execSync('npm config get prefix').toString().trim();
  log('npm prefix', prefix);

  // Vérifie permissions d’écriture
  accessSync(prefix, constants.W_OK);
  log('Permissions', `Écriture autorisée dans ${prefix}`);
} catch {
  fail('npm prefix', 'Non accessible ou permissions insuffisantes');
}

console.log('\x1b[32m✅ Environnement prêt pour le build.\x1b[0m');