document.addEventListener('DOMContentLoaded', () => {
  // Animation du titre principal
  const title = document.querySelector('main h1');
  if (title) {
    title.classList.add('animate-welcome');
  }

  // Animation du message (si présent)
  const message = document.querySelector('.message');
  if (message) {
    message.classList.add('fade-in');
    setTimeout(() => message.classList.remove('fade-in'), 3000);
  }

  // Lecture du son selon le profil utilisateur
  const session = window.userSession;
  if (session?.isLoggedIn) {
    const profileSoundMap = {
      expert: "welcome-expert.mp3",
      débutant: "welcome-beginner.mp3",
      confirmé: "welcome-confirmed.mp3"
    };

    const soundFile = profileSoundMap[session.profil];
    if (soundFile) {
      const audio = new Audio(`assets/sounds/${soundFile}`);
      audio.volume = 0.7;
      audio.play().catch(err => {
        console.warn("Lecture audio bloquée :", err);
      });
    }

    // Affichage du loader
    const loader = document.getElementById('loader');
    if (loader) {
      loader.classList.remove('hidden');
      loader.classList.add('visible');
    }

    loader.classList.add('fade-out');

    // Redirection automatique après 5 secondes
    setTimeout(() => {
      window.location.href = "index.php";
    }, 6000);
  }
});