import { showToast } from './toast.js';

const params = new URLSearchParams(window.location.search);
const token = params.get('token');

const title = document.getElementById('statusTitle');
const text = document.getElementById('statusText');
const spinner = document.getElementById('spinner');
const countdown = document.getElementById('countdown');
const retryBtn = document.getElementById('retryBtn');

text.classList.add('loading');

if (token) {
  fetch('./Backend/public/valider_signature.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ token })
  })
  .then(res => res.json())
  .then(data => {
    spinner.style.display = 'none';
    text.classList.remove('loading');

    if (data.success) {
      title.textContent = '✅ Signature validée';
      text.textContent = 'Merci d’avoir confirmé votre certificat RSE.';
      showToast('success', '✅', data.message);

      // ⏳ Compte à rebours visuel (ex. 10 min)
      let seconds = 600;
      const interval = setInterval(() => {
        const min = Math.floor(seconds / 60);
        const sec = seconds % 60;
        countdown.textContent = `Lien actif encore ${min}m ${sec}s`;
        seconds--;
        if (seconds < 0) {
          clearInterval(interval);
          countdown.textContent = '⏱️ Le lien a expiré.';
          showToast('error', '⏳', 'Le lien de signature a expiré.');
          retryBtn.style.display = 'inline-block';
        }
      }, 1000);
    } else {
      title.textContent = '❌ Échec de validation';
      text.textContent = data.message;
      showToast('error', '❌', data.message);
      spinner.style.display = 'none';
      retryBtn.style.display = 'inline-block';
    }
  })
  .catch(() => {
    spinner.style.display = 'none';
    text.classList.remove('loading');
    showToast('error', '❌', 'Erreur de communication avec le serveur.');
    retryBtn.style.display = 'inline-block';
  });
} else {
  spinner.style.display = 'none';
  text.classList.remove('loading');
  showToast('error', '❌', 'Aucun token fourni dans l’URL.');
  retryBtn.style.display = 'inline-block';
}

// Action du bouton “Redemander un lien”
retryBtn.addEventListener('click', () => {
    const saved = localStorage.getItem('certificatData');
    if (saved) {
      const data = JSON.parse(saved);
      const query = new URLSearchParams(data).toString();
      window.location.href = `certificat.html?${query}`;
    } else {
      window.location.href = 'certificat.html';
    }  
});