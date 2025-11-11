import { navigateTo } from './router.js';
import { login } from './auth.js';
import { fetchDashboardReport } from './dashboard.js';
import { fetchAudit } from './audit.js';
import { downloadPDF, downloadExcel } from './export.js';

// Fonctions globales accessibles depuis les vues
window.handleLogin = async function () {
  const username = document.getElementById('username').value;
  const password = document.getElementById('password').value;
  const data = await login(username, password);
  document.getElementById('token').textContent = data.token || data.error;

  // Stocker le profil si disponible dans le payload
  if (data.token) {
    const payload = JSON.parse(atob(data.token.split('.')[1]));
    if (payload.profil) {
      localStorage.setItem('profil', payload.profil);
      applyProfilTheme(payload.profil);
    }
  }
};

window.loadDashboard = async function () {
  const html = await fetchDashboardReport();
  document.getElementById('rapport').innerHTML = html;
};

window.loadAudit = async function () {
  const txt = await fetchAudit();
  document.getElementById('audit').textContent = txt;
};

window.downloadPDF = downloadPDF;
window.downloadExcel = downloadExcel;

// Initialisation de la vue
navigateTo(location.hash.replace('#', '') || 'login');

// 🎨 Appliquer le thème selon le profil
function applyProfilTheme(profil) {
  document.body.classList.remove('profil-novice', 'profil-confirme', 'profil-expert');
  if (profil === 'novice') {
    document.body.classList.add('profil-novice');
  } else if (profil === 'confirmé') {
    document.body.classList.add('profil-confirme');
  } else if (profil === 'expert') {
    document.body.classList.add('profil-expert');
  }
}

// Appliquer le thème au démarrage si le profil est déjà stocké
const profil = localStorage.getItem('profil');
if (profil) {
  applyProfilTheme(profil);
}