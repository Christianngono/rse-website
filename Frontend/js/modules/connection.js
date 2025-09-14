import { showNotification } from './notifications.js';

export function updateConnectionStatus() {
  const status = document.getElementById('connectionStatus');
  const offlineSound = new Audio('../Frontend/assets/sounds/correct.mp3');

  if (navigator.onLine) {
    status.textContent = 'Connecté';
    status.classList.remove('offline');
    showNotification("Connexion rétablie", "success");
  } else {
    status.textContent = 'Hors ligne';
    status.classList.add('offline');
    showNotification("Connexion perdue", "error");
    offlineSound.play();
    autoReconnect();
  }
}

export function autoReconnect(interval = 5000) {
  if (!navigator.onLine) {
    showNotification("Tentative de reconnexion...", "info");
    const check = setInterval(() => {
      if (navigator.onLine) {
        clearInterval(check);
        showNotification("Connexion rétablie", "success");
        loadDashboard();
      }
    }, interval);
  }
}