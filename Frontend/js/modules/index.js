import { getGreeting, showWelcomeMessage } from './welcome.js';
import { updateClock } from './clock.js';
import { updateConnectionStatus, autoReconnect } from './connection.js';
import { toggleTheme, applySavedTheme } from './theme.js';
import { showNotification, clearNotificationLog, exportNotificationLog } from './notifications.js';
import {loadUserStats} from './dashboardModules.js';
import {filterUsers, clearFilters, populateCategoryFilter} from './filters.js';


window.addEventListener('DOMContentLoaded', () => {
  // Filtres
  filterUsers();
  clearFilters();
  populateCategoryFilter(window.initialData || {});
  // Thème sombre
  applySavedTheme();
  toggleTheme();

  loadUserStats

  // Message de bienvenue
  getGreeting();
  showWelcomeMessage();

  // Horloge
  updateClock();

  // Connexion
  updateConnectionStatus();
  autoReconnect();
  
  // Notifications
  showNotification();
  clearNotificationLog();
  exportNotificationLog();


 
  // Animations et badges selon session PHP
  const session = window.userSession;

  if (session?.isLoggedIn) {
    // Animation d’accueil
    const intro = document.getElementById("introOverlay");
    if (intro) {
      intro.classList.add("animate-welcome");
    }

    // Son de bienvenue selon le profil
    const profileSoundMap = {
      expert: "welcome-expert.mp3",
      débutant: "welcome-beginner.mp3"
    };
    const soundFile = profileSoundMap[session.profil];
    if (soundFile) {
      const audio = new Audio(`assets/sounds/${soundFile}`);
      audio.play();
    }

    // Badge selon score
    const badgeContainer = document.createElement("div");
    badgeContainer.id = "badgeContainer";
    badgeContainer.style.position = "fixed";
    badgeContainer.style.bottom = "20px";
    badgeContainer.style.right = "20px";
    badgeContainer.style.padding = "10px";
    badgeContainer.style.background = "#ffd700";
    badgeContainer.style.borderRadius = "8px";
    badgeContainer.style.boxShadow = "0 0 10px rgba(0,0,0,0.3)";
    badgeContainer.style.zIndex = "1000";

    let badgeText = "Participant";
    if (session.quizScore >= 80) badgeText = "🏆 Champion RSE";
    else if (session.quizScore >= 50) badgeText = "🎯 Intermédiaire";
    else badgeText = "🚀 En progression";

    badgeContainer.textContent = badgeText;
    document.body.appendChild(badgeContainer);
  }

  // Masquer l’intro après délai
  setTimeout(() => {
    const intro = document.getElementById('introOverlay');
    if (intro) intro.style.display = 'none';
  }, 4000);
});