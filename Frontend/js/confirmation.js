import { showToast } from './toast.js';

// Récupérer les paramètres de l'URL
const urlParams = new URLSearchParams(window.location.search);
const file = urlParams.get('file');

// Sélectionner les éléments HTML
const downloadBtn = document.getElementById('downloadBtn');
const copyBtn = document.getElementById('copyLinkBtn');
const confirmMsg = document.getElementById('copyConfirm');
const errorMsg = document.getElementById('errorMsg');
const toast = document.getElementById('toast');
const toastLink = document.getElementById('toastLink');

// lancer la lecture du son de réussite
const audio = new Audio('../../assets/sounds/success.mp3');
audio.play();

// Rediriger automatique après 6 secondes vers la page de remerciement
setTimeout(() => {
  if (file) {
    window.location.href = `remerciement.html?file=${encodeURIComponent(file)}`;
  } else {
    window.location.href = 'remerciement.html';
  }
}, 6000);

// Vérifier si le fichier PDF existe 
if (file) {
  const filePath = `../../certificats/${file}`;
  fetch(filePath, { method: 'HEAD' })
    .then(response => {
      if (response.ok) {
        // Afficher les boutons
        downloadBtn.href = filePath;
        downloadBtn.style.display = 'inline-block';
        copyBtn.style.display = 'inline-block';

        // Activer la copie du lien
        copyBtn.addEventListener('click', () => {
          const link = downloadBtn.href;
          if (!link || link === '#') {
            showToast('error', '❌', "Lien de certificat non disponible.");
            return;
          }
          navigator.clipboard.writeText(link).then(() => {
            showToast('success', '✅', 'Lien copié : <a id="toastLink" href="#" target="_blank">Voir le certificat</a>', link);
          });
        });
      } else {
        errorMsg.style.display = 'block';
      }
    })
    .catch(() => {
      errorMsg.style.display = 'block';
    });
} else {
  errorMsg.textContent = "❌ Aucun fichier spécifié.";
  errorMsg.style.display = 'block';
}

// Partager sur les réseaux sociaux
const shareText = encodeURIComponent("🎓 J'ai obtenu mon certificat RSE ! Découvrez le quiz sur la responsabilité sociétale des entreprises.");
const shareUrl = encodeURIComponent("https://rse-website.com");

document.getElementById('shareFacebook').href = `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`;
document.getElementById('shareTwitter').href = `https://twitter.com/intent/tweet?text=${shareText}&url=${shareUrl}`;
document.getElementById('shareLinkedIn').href = `https://www.linkedin.com/sharing/share-offsite/?url=${shareUrl}`;