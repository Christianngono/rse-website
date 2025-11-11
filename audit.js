import { exec } from 'child_process';
import fs from 'fs';

exec('npm audit --json', (err, stdout) => {
  const report = JSON.parse(stdout);
  const issues = report.metadata.vulnerabilities;
  const summary = `
Audit du ${new Date().toLocaleString()}
🔐 Failles détectées :
- Critiques : ${issues.critical}
- Hautes : ${issues.high}
- Modérées : ${issues.moderate}
- Faibles : ${issues.low}
`;
  fs.writeFileSync('Frontend/assets/audit-report.txt', summary);
  console.log(summary);
});