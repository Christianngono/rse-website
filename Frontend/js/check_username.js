// check_username.js
export function checkUsernameAvailability(username, loader, usernameStatus, callback) {
  if (username.length < 3) {
    usernameStatus.textContent = "❌ Nom trop court";
    usernameStatus.className = "invalid";
    callback(false);
    return;
  }

  loader.classList.add("visible");
  fetch(`check_username.php?username=${encodeURIComponent(username)}`)
    .then(res => res.json())
    .then(data => {
      usernameStatus.textContent = data.username_available
        ? "✅ Disponible"
        : data.message;
      usernameStatus.className = data.username_available ? "valid" : "invalid";
      callback(data.username_available);
      loader.classList.remove("visible");
    })
    .catch(() => {
      usernameStatus.textContent = "❌ Erreur serveur";
      usernameStatus.className = "invalid";
      callback(false);
      loader.classList.remove("visible");
    });
}

export function checkEmailAvailability(email, loader, emailStatus, callback) {
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    emailStatus.textContent = "❌ Format invalide";
    emailStatus.className = "invalid";
    callback(false);
    return;
  }

  loader.classList.add("visible");
  fetch(`check_username.php?email=${encodeURIComponent(email)}`)
    .then(res => res.json())
    .then(data => {
      emailStatus.textContent = data.email_available
        ? "✅ Disponible"
        : "❌ Déjà utilisé";
      emailStatus.className = data.email_available ? "valid" : "invalid";
      callback(data.email_available);
      loader.classList.remove("visible");
    })
    .catch(() => {
      emailStatus.textContent = "❌ Erreur serveur";
      emailStatus.className = "invalid";
      callback(false);
      loader.classList.remove("visible");
    });
} 

    