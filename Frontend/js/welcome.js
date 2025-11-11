document.addEventListener("DOMContentLoaded", () => {
  const sound = document.getElementById("welcomeSound");
  if (sound) {
    sound.volume = 0.5;
    sound.play().catch(() => {
      console.warn("Lecture automatique bloquée par le navigateur.");
    });
  }

  const btn = document.querySelector(".btn");
  btn.addEventListener("click", () => {
    btn.classList.remove("pulse");
    btn.textContent = "Chargement...";
    setTimeout(() => {
      window.location.href = btn.getAttribute("href");
    }, 800);
  });
});