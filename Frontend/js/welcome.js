document.addEventListener("DOMContentLoaded", () => {
  const welcomeSound = document.getElementById("welcomeSound");
  const clickSound = document.getElementById("clickSound");
  const btn = document.querySelector(".btn");

  if (welcomeSound) {
    welcomeSound.volume = 0.5;
    welcomeSound.play().catch(() => {});
  }

  btn.addEventListener("click", (e) => {
    e.preventDefault();
    btn.classList.remove("pulse");
    btn.classList.add("clicked");
    btn.textContent = "Chargement...";

    if (clickSound) {
      clickSound.volume = 0.6;
      clickSound.play().catch(() => {});
    }

    setTimeout(() => {
      window.location.href = btn.getAttribute("href");
    }, 1200);
  });
});