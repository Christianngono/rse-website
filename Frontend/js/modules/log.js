import fs from 'fs';
const logFile = 'Frontend/assets/build.log';
const args = process.argv.slice(2);
const flags = Object.fromEntries(args.map(arg => [arg.replace('--', ''), true]));

function timestamp() {
  return new Date().toISOString().split('T')[1].split('.')[0];
}

function writeLog(level, message) {
  const line = `[${timestamp()}] [${level}] ${message}\n`;
  fs.appendFileSync(logFile, line);
  if (flags.quiet && level !== 'ERROR') return;
  if (!flags.verbose && level === 'DEBUG') return;
  const colors = {
    INFO: '\x1b[36m',
    SUCCESS: '\x1b[32m',
    WARNING: '\x1b[33m',
    ERROR: '\x1b[31m',
    DEBUG: '\x1b[90m'
  };
  console.log(`${colors[level] || ''}[${level}] ${message}\x1b[0m`);
}

export const logStep = msg => writeLog('INFO', msg);
export const logSuccess = msg => writeLog('SUCCESS', msg);
export const logWarning = msg => writeLog('WARNING', msg);
export const logError = msg => writeLog('ERROR', msg);
export const logDebug = msg => writeLog('DEBUG', msg);