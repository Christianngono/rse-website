import { checkUsernameAvailability, checkEmailAvailability } from './check_username.js';

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registerForm");
  const msg = document.getElementById("message");
  const loader = document.getElementById("loader");
  const submitBtn = form.querySelector("input[type='submit']");

  const usernameInput = form.username;
  const emailInput = form.email;
  const passwordInput = form.password;
  const phoneInput = form.phone;

  const usernameStatus = document.getElementById("usernameStatus");
  const emailStatus = document.getElementById("emailStatus");
  const passwordStrength = document.getElementById("passwordStrength");
  const phoneStatus = document.getElementById("phoneStatus");
  const validationMessage = document.getElementById("validationMessage");
  const confirmationOverlay = document.getElementById("confirmationOverlay");

  const audio = new Audio("Frontend/assets/sounds/success.mp3");
  audio.volume = 0.2;
  audio.load();

  if (!form || !msg || !loader || !submitBtn || !usernameInput || !emailInput || !passwordInput || !phoneInput || !usernameStatus || !emailStatus || !passwordStrength || !phoneStatus || !validationMessage || !confirmationOverlay) {
    console.error("Éléments du DOM manquants.");
    return;
  }

  let usernameValid = false;
  let emailValid = false;
  let passwordValid = false;
  let phoneValid = false;

  function updateSubmitState() {
    const allValid = usernameValid && emailValid && passwordValid && phoneValid;
    submitBtn.disabled = !allValid;
    submitBtn.classList.toggle("active", allValid);
    submitBtn.classList.toggle("bounce", allValid);

    if (allValid) {
      validationMessage.textContent = "Tous les champs sont valides ✅";
      validationMessage.classList.add("visible");
    } else {
      validationMessage.classList.remove("visible");
    }
  }

  // Vérification du nom d'utilisateur
  let lastCheck = 0;
  usernameInput.addEventListener("input", () => {
    const now = Date.now();
    if (now - lastCheck < 1000) return;
    lastCheck = now;

    const username = usernameInput.value.trim();
    checkUsernameAvailability(username, loader, usernameStatus, isValid => {
      usernameValid = isValid;
      updateSubmitState();
    });
  });

  // Vérification de l'email
  emailInput.addEventListener("input", () => {
    const email = emailInput.value.trim();
    checkEmailAvailability(email, loader, emailStatus, isValid => {
      emailValid = isValid;
      updateSubmitState();
    });
  });

  // Sécurité du mot de passe
  passwordInput.addEventListener("input", () => {
    const password = passwordInput.value;
    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    const levels = ["Très faible", "Faible", "Moyenne", "Bonne", "Excellente"];
    const colors = ["red", "orange", "gold", "green", "darkgreen"];

    passwordStrength.textContent = "Sécurité : " + levels[score];
    passwordStrength.style.color = colors[score];

    passwordValid = score >= 3;
    updateSubmitState();
  });

  // Vérification du téléphone
  phoneInput.addEventListener("input", () => {
    const phoneRegex = /^[0-9]{10}$/;
    phoneValid = phoneRegex.test(phoneInput.value.trim());
    phoneStatus.textContent = phoneValid ? "✅" : "❌";
    phoneStatus.className = phoneValid ? "valid" : "invalid";
    updateSubmitState();
  });

  // Soumission du formulaire
  form.addEventListener("submit", e => {
    e.preventDefault();
    const formData = new FormData(form);

    submitBtn.classList.add("loading");

    grecaptcha.ready(() => {
      grecaptcha.execute('6LcW17orAAAAAAfKbbStyEY1ItmPMLAKXP3Ye-Yi', { action: 'register' })
        .then(token => {
          formData.append('recaptcha_token', token);
          loader.classList.add("visible");

          fetch("Backend/public/register.php", {
            method: "POST",
            body: formData
          })
            .then(res => res.json())
            .then(data => {
              loader.classList.remove("visible");
              submitBtn.classList.remove("loading");

              msg.innerHTML = data.success
                ? `<span class="icon">🎉</span> Inscription réussie ! Redirection en cours...`
                : `<span class="icon">⚠️</span> ${data.message}`;
              msg.style.color = data.success ? "green" : "red";
              msg.classList.add("visible");
              msg.style.opacity = 0;
              setTimeout(() => { msg.style.opacity = 1; }, 100);

              if (data.success) {
                audio.play();
                confirmationOverlay.style.display = "flex";
                localStorage.setItem("welcome", "true");

                form.reset();
                usernameStatus.textContent = "";
                emailStatus.textContent = "";
                passwordStrength.textContent = "";
                phoneStatus.textContent = "";
                submitBtn.disabled = true;
                submitBtn.classList.remove("active");

                setTimeout(() => {
                  confirmationOverlay.classList.add("fade-out");
                  setTimeout(() => {
                    confirmationOverlay.style.display = "none";
                    window.location.href = "Frontend/quiz.html";
                  }, 500);
                }, 2500);
              }
            })
            .catch(() => {
              submitBtn.classList.remove("loading");
              msg.innerHTML = `<span class="icon">⚠️</span> Erreur réseau.`;
              msg.style.color = "red";
              msg.classList.add("visible");
              loader.classList.remove("visible");
            });
        });
    });
  });
});
