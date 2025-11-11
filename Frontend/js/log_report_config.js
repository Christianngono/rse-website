import { showStatus } from "./status_feedback.js";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("configForm");

  // === Pré-remplissage du formulaire ===
  fetch("Backend/public/get_report_config.php")
    .then(res => res.json())
    .then(data => {
      if (data.email) form.email.value = data.email;
      if (data.frequency) form.frequency.value = data.frequency;
      if (data.format) form.format.value = data.format;
    });

  // === Soumission avec validation ===
  form.addEventListener("submit", e => {
    e.preventDefault();

    const email = form.email.value.trim();
    const frequency = form.frequency.value;
    const format = form.format.value;

    // === Validation côté client ===
    if (!email || !email.includes("@") || !email.includes(".")) {
      showStatus("Adresse email invalide.", {
        success: false,
        targetId: "status",
        duration: 4000,
        icon: true
      });
      return;
    }

    if (!["daily", "weekly"].includes(frequency)) {
      showStatus("Fréquence invalide.", {
        success: false,
        targetId: "status",
        duration: 4000,
        icon: true
      });
      return;
    }

    if (!["pdf", "csv", "both"].includes(format)) {
      showStatus("Format invalide.", {
        success: false,
        targetId: "status",
        duration: 4000,
        icon: true
      });
      return;
    }

    const formData = new FormData(form);
    fetch("Backend/public/save_report_config.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      showStatus(data.message, {
        success: data.success,
        targetId: "status",
        duration: 5000,
        icon: true
      });
    })
    .catch(() => {
      showStatus("Erreur lors de l’enregistrement.", {
        success: false,
        targetId: "status",
        duration: 5000,
        icon: true
      });
    });
  });

  // === Exemple d'utilisation autonome ===
  showStatus("Enregistrement réussi", {
    success: true,
    targetId: "status",
    duration: 5000,
    icon: true
  });
});