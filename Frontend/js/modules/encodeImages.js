import fs from 'fs';
import path from 'path';
import { logStep, logDebug } from './log.js';
import sizeOf from 'image-size'; // npm install image-size

const MIN_SIZE_BYTES = 1024;

function animateProgress(current, total) {
  const percent = Math.round((current / total) * 100);
  const bar = '█'.repeat(percent / 5) + '-'.repeat(20 - percent / 5);
  process.stdout.write(`\r\x1b[36m[${bar}] ${percent}% (${current}/${total})\x1b[0m`);
}

export async function encodeImages({ dryRun = false } = {}) {
  const inputDir = 'Frontend/assets/images';
  const outputFile = 'Frontend/assets/encodedImages.json';
  const reportFile = 'Frontend/assets/image-report.json';
  const result = {};
  const reportData = [];

  const files = fs.readdirSync(inputDir).filter(f => /\.(png|jpg|jpeg)$/i.test(f));
  const alreadyEncoded = fs.existsSync(outputFile)
    ? Object.keys(JSON.parse(fs.readFileSync(outputFile, 'utf-8')))
    : [];

  const total = files.length;
  let count = 0;

  for (const file of files) {
    const filePath = path.join(inputDir, file);
    const stats = fs.statSync(filePath);
    const dimensions = sizeOf(filePath);

    if (stats.size < MIN_SIZE_BYTES || alreadyEncoded.includes(file)) {
      logStep(`⏭️ Ignoré : ${file} (trop petit ou déjà encodé)`);
      continue;
    }

    if (!dryRun) {
      const base64 = fs.readFileSync(filePath).toString('base64');
      result[file] = `data:image/${path.extname(file).slice(1)};base64,${base64}`;
    }

    reportData.push({
      name: file,
      size: `${(stats.size / 1024).toFixed(1)} Ko`,
      width: dimensions.width,
      height: dimensions.height
    });

    count++;
    animateProgress(count, total);
    logDebug(`Encodé : ${file}`);
  }

  process.stdout.write('\n');

  if (!dryRun) {
    fs.writeFileSync(outputFile, JSON.stringify(result, null, 2));
  }
  fs.writeFileSync(reportFile, JSON.stringify(reportData, null, 2));

  return count;
}