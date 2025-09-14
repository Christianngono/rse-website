import { showToast } from './toast.js';
import { showBanner, hideBanner } from './banner.js';

// Pré-remplir le formulaire si des données sont dans l’URL
const params = new URLSearchParams(window.location.search);
const username = params.get('username');
const email = params.get('email');
const score = params.get('score');

let restored = false;

if (username) {
  document.querySelector('[name="username"]').value = username;
  restored = true;
}
if (email) {
  document.querySelector('[name="email"]').value = email;
  restored = true;
}
if (score) {
  document.querySelector('[name="score"]').value = score;
  restored = true;
}

// Afficher la bannière et le bouton avec effet de fondu
const banner = document.getElementById('restoreBanner');
const button = document.getElementById('resetBtn');

// Afficher la bannière et le bouton si restauration
if (restored) {
  showBanner('info', '✨', 'Vos données ont été restaurées automatiquement.', true);
}

// Réinitialiser les champs et nettoyer l’URL
document.getElementById('resetBtn').addEventListener('click', () => {
  document.querySelector('[name="username"]').value = '';
  document.querySelector('[name="email"]').value = '';
  document.querySelector('[name="score"]').value = '';
  hideBanner();
  
  // Supprimer les paramètres de l’URL
  const cleanUrl = window.location.origin + window.location.pathname;
  window.history.replaceState({}, document.title, cleanUrl);
});

// Envoi du formulaire
document.getElementById('certificatForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const data = {
    username: formData.get('username'),
    email: formData.get('email'),
    score: formData.get('score')
  };

  // Sauvegarde dans le localStorage
  localStorage.setItem('certificatData', JSON.stringify(data));

  fetch('../../Backend/public/demander_signature.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(response => {
    if (response.success) {
      showToast('success', '✅', response.message);
    } else {
      showToast('error', '❌', response.message || "Erreur lors de l'envoi.");
    }
  })
  .catch(() => {
    showToast('error', '❌', "Erreur de communication avec le serveur.");
  });
});
