import { exec } from 'child_process';
import path from 'path';

export function playSound(type) {
  const sounds = {
    clean: 'assets/sounds/clean.wav',
    encode: 'assets/sounds/encode.wav',
    success: 'assets/sounds/success.wav'
  };

  const soundPath = path.resolve(sounds[type] || sounds.success);
  const command = process.platform === 'win32'
    ? `powershell -c (New-Object Media.SoundPlayer '${soundPath}').PlaySync();`
    : `aplay '${soundPath}'`;

  exec(command, err => {
    if (err) console.warn('🔇 Erreur de lecture sonore :', err.message);
  });
}