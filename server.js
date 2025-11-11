import express from 'express';
import { exec, fork } from 'child_process';
import jwt from 'jsonwebtoken';
import dotenv from 'dotenv';
import fs from 'fs';
dotenv.config();

const app = express();
const PASSWORD = 'rse2025'; // à stocker dans .env en prod

// Middleware simple par mot de passe
function authMiddleware(req, res, next) {
  const auth = req.headers.authorization;
  if (!auth || auth !== `Bearer ${PASSWORD}`) {
    return res.status(401).send('🔒 Accès refusé : token manquant ou invalide.');
  }
  next();
}

// Middleware JWT
function verifyToken(req, res, next) {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).send('🔒 Token manquant');

  try {
    jwt.verify(token, process.env.JWT_SECRET);
    next();
  } catch {
    res.status(403).send('⛔ Token invalide');
  }
}

// Route de login — génère un token JWT
app.post('/login', (req, res) => {
  const { username, password } = req.body;
  if (username === 'admin' && password === 'rse2025') {
    const token = jwt.sign({ user: username }, process.env.JWT_SECRET, { expiresIn: '1h' });
    return res.json({ token });
  }
  res.status(401).json({ error: 'Identifiants invalides' });
});

// Route sécurisée par mot de passe
app.get('/rapport', authMiddleware, (req, res) => {
  res.sendFile('Frontend/assets/build-report.html', { root: process.cwd() });
});

// Route rapport HTML sécurisée par JWT
app.get('/rapport-jwt', verifyToken, (req, res) => {
  res.sendFile('Frontend/assets/build-report.html', { root: process.cwd() });
});

// Route PDF
app.get('/rapport-pdf', (req, res) => {
  res.download('Frontend/assets/build-report.pdf');
});

// Route export Excel via sandbox
app.get('/export-excel', verifyToken, (req, res) => {
  const reportPath = 'Frontend/assets/image-report.json';
  if (!fs.existsSync(reportPath)) return res.status(404).send('Rapport introuvable');

  const data = JSON.parse(fs.readFileSync(reportPath, 'utf-8'));
  const child = fork('./Frontend/js/modules/exportExcelWorker.js');
  child.send(data);
  child.on('message', msg => {
    res.download('Frontend/assets/export.xlsx');
  });
});

// Route exécution de scripts npm
app.get('/run', (req, res) => {
  const script = req.query.script;
  exec(`npm run ${script}`, (err, stdout, stderr) => {
    if (err) return res.send(`Erreur : ${stderr}`);
    res.send(stdout);
  });
});

app.get('/audit', verifyToken, (req, res) => {
  exec('node audit.js', (err, stdout, stderr) => {
    if (err) return res.status(500).send(`Erreur audit : ${stderr}`);
    const report = fs.readFileSync('Frontend/assets/audit-report.txt', 'utf-8');
    res.type('text/plain').send(report);
  });
});


// Lancement du Server
app.listen(3000, () => console.log('🌐 Interface web dispo sur http://localhost:3000'));