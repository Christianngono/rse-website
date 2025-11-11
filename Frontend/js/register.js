document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registerForm");
  const loader = document.getElementById("loader");
  const messageBox = document.getElementById("message");
  const overlay = document.getElementById("confirmationOverlay");

  const usernameInput = document.getElementById("username");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const phoneInput = document.getElementById("phone");
  const roleSelect = document.getElementById("role");
  const profilSelect = document.getElementById("profil");
  const confirmCheckbox = document.getElementById("confirmCheckbox");
  const submitBtn = document.getElementById("submitBtn");

  const usernameStatus = document.getElementById("usernameStatus");
  const emailStatus = document.getElementById("emailStatus");
  const suggestionBox = document.getElementById("usernameSuggestions");

  const strengthDisplay = document.getElementById("passwordStrength");
  const barFill = document.querySelector(".bar-fill");

  const summaryBox = document.getElementById("accountSummary");
  const summaryUsername = document.getElementById("summaryUsername");
  const summaryEmail = document.getElementById("summaryEmail");
  const summaryRole = document.getElementById("summaryRole");
  const summaryProfil = document.getElementById("summaryProfil");
  const summaryPhone = document.getElementById("summaryPhone");

  const rolePreview = document.getElementById("rolePreview");
  const profilPreview = document.getElementById("profilPreview");

  const bannedPasswords = [
    "123456", "password", "123456789", "qwerty", "12345678", "111111", "123123",
    "abc123", "password1", "iloveyou", "admin"
  ];

  const roleDescriptions = {
    user: "👤 Utilisateur : accès standard au quiz et aux statistiques personnelles.",
    admin: "🛠️ Administrateur : accès complet à la gestion des utilisateurs et des rapports."
  };

  const profilDescriptions = {
    novice: "🌱 Novice : vous débutez dans le domaine RSE.",
    confirmé: "📘 Confirmé : vous avez déjà une bonne connaissance du sujet.",
    expert: "🚀 Expert : vous maîtrisez les enjeux RSE et souhaitez aller plus loin."
  };

  function updatePreview() {
    rolePreview.textContent = roleDescriptions[roleSelect.value] || "";
    profilPreview.textContent = profilDescriptions[profilSelect.value] || "";
  }

  function updateSummary() {
    summaryUsername.textContent = usernameInput.value;
    summaryEmail.textContent = emailInput.value;
    summaryRole.textContent = roleSelect.options[roleSelect.selectedIndex].text;
    summaryProfil.textContent = profilSelect.options[profilSelect.selectedIndex].text;
    summaryPhone.textContent = phoneInput.value || "Non renseigné";
    summaryBox.style.display = "block";
    confirmCheckbox.checked = false;
    submitBtn.disabled = true;
  }

  roleSelect.addEventListener("change", updatePreview);
  profilSelect.addEventListener("change", updatePreview);

  confirmCheckbox.addEventListener("change", () => {
    submitBtn.disabled = !confirmCheckbox.checked;
  });

  usernameInput.addEventListener("blur", async () => {
    const username = usernameInput.value.trim();
    suggestionBox.innerHTML = "";
    if (username.length >= 3) {
      try {
        const res = await fetch("/check_username.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ username })
        });
        const data = await res.json();
        if (data.username_available === false) {
          usernameStatus.textContent = "❌";
          usernameInput.classList.add("invalid");
          usernameInput.classList.remove("valid");

          const match = data.username_message.match(/Suggestions\s*:\s*(.+)/);
          if (match) {
            const suggestions = match[1].split(',').map(s => s.trim());
            suggestions.forEach(suggestion => {
              const btn = document.createElement("button");
              btn.textContent = suggestion;
              btn.className = "suggestion-btn";
              btn.addEventListener("click", () => {
                usernameInput.value = suggestion;
                usernameStatus.textContent = "✅";
                usernameInput.classList.add("valid");
                usernameInput.classList.remove("invalid");
                suggestionBox.innerHTML = "";
              });
              suggestionBox.appendChild(btn);
            });
          }
        } else {
          usernameStatus.textContent = "✅";
          usernameInput.classList.add("valid");
          usernameInput.classList.remove("invalid");
        }
      } catch {
        usernameStatus.textContent = "⚠️";
      }
    }
  });

  emailInput.addEventListener("blur", async () => {
    const email = emailInput.value.trim();
    if (email.length >= 5) {
      try {
        const res = await fetch("/check_username.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ email })
        });
        const data = await res.json();
        if (data.email_available === false) {
          emailStatus.textContent = "❌";
          emailInput.classList.add("invalid");
          emailInput.classList.remove("valid");
        } else {
          emailStatus.textContent = "✅";
          emailInput.classList.add("valid");
          emailInput.classList.remove("invalid");
        }
      } catch {
        emailStatus.textContent = "⚠️";
      }
    }
  });

  passwordInput.addEventListener("input", () => {
    const password = passwordInput.value;
    let strength = 0;

    if (bannedPasswords.includes(password.toLowerCase())) {
      strengthDisplay.textContent = "Mot de passe trop commun ❌";
      barFill.style.width = "0%";
      barFill.style.backgroundColor = "#dc3545";
      passwordInput.classList.add("invalid");
      passwordInput.classList.remove("valid");
      return;
    }

    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;

    const levels = ["Très faible", "Faible", "Moyen", "Fort", "Très fort"];
    const colors = ["#dc3545", "#fd7e14", "#ffc107", "#28a745", "#006400"];
    const widths = ["20%", "40%", "60%", "80%", "100%"];

    strengthDisplay.textContent = `Force : ${levels[strength]}`;
    barFill.style.width = widths[strength];
    barFill.style.backgroundColor = colors[strength];

    if (strength >= 3) {
      passwordInput.classList.add("valid");
      passwordInput.classList.remove("invalid");
    } else {
      passwordInput.classList.remove("valid");
    }
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    loader.style.display = "block";
    messageBox.textContent = "";
    updateSummary();

    try {
      const token = await grecaptcha.execute("6LcW17orAAAAAAfKbbStyEY1ItmPMLAKXP3Ye-Yi", {
        action: "register"
      });
      document.getElementById("recaptcha_token").value = token;

      const formData = new FormData(form);
      const response = await fetch("/register.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      loader.style.display = "none";

      if (result.success) {
        overlay.style.display = "flex";
        overlay.querySelector(".popup").classList.add("animated");
        setTimeout(() => {
          window.location.href = "/welcome.html";
        }, 4500);
      } else {
        messageBox.textContent = result.message;
        messageBox.style.color = "red";
        messageBox.classList.add("shake");
        setTimeout(() => messageBox.classList.remove("shake"), 500);
      }
    } catch {
      loader.style.display = "none";
      messageBox.textContent = "Erreur réseau ou serveur.";
      messageBox.style.color = "red";
    }
  });
});