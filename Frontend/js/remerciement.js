import { showBanner, hideBanner } from './banner.js';
import { showToast } from './toast.js';

// Afficher la bannière de confirmation
showBanner('success', '✅', 'Certificat envoyé avec succès', true);

function launchCelebration(type = 'star') {
  const layer = document.getElementById('celebrationLayer');
  const symbols = {
    star: '✨',
    heart: '❤️',
    firework: '🎆'
  };
  const icon = symbols[type] || '✨';

  for (let i = 0; i < 40; i++) {
    const el = document.createElement('div');
    el.className = type;
    el.textContent = icon;
    el.style.left = Math.random() * 100 + 'vw';
    el.style.top = Math.random() * 100 + 'vh';
    el.style.fontSize = Math.random() * 20 + 20 + 'px';
    layer.appendChild(el);

    setTimeout(() => {
      const btn = document.getElementById('downloadCertBtn');
      if (fileName) {
        btn.href = `../../certificats/${fileName}`;
        btn.style.display = 'inline-block';
      } else {
    showToast('error', '❌', 'Nom du certificat introuvable.');
  }
    }, 3000);
  }
}
launchCelebration('star');
launchCelebration('heart');
launchCelebration('firework');

// Bouton “Recommencer”
document.getElementById('resetBtn').addEventListener('click', () => {
  hideBanner();
  window.location.href = 'certificat.html';
});

// Copier le lien du certificat
document.getElementById('copyLinkBtn').addEventListener('click', () => {
  const link = document.getElementById('toastLink').href;
  if (!link || link === '#') {
    showToast('error', '❌', "Lien de certificat non disponible.");
    return;
  }
  navigator.clipboard.writeText(link).then(() => {
    showToast('success', '✅', 'Lien copié : <a id="toastLink" href="#" target="_blank">Voir le certificat</a>', link);
  });
});

// Partager sur les réseaux sociaux
const shareText = encodeURIComponent("🎓 J'ai obtenu mon certificat RSE ! Découvrez le quiz sur la responsabilité sociétale des entreprises.");
const shareUrl = encodeURIComponent("https://rse-website.com");

document.getElementById('shareFacebook').href = `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`;
document.getElementById('shareTwitter').href = `https://twitter.com/intent/tweet?text=${shareText}&url=${shareUrl}`;
document.getElementById('shareLinkedIn').href = `https://www.linkedin.com/sharing/share-offsite/?url=${shareUrl}`;