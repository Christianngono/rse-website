document.getElementById("generateBtn").addEventListener("click", () => {
  fetch("Backend/public/generate_token.php")
    .then(res => res.json())
    .then(data => {
      const result = document.getElementById("tokenResult");
      if (data.token) {
        result.innerHTML = `
          ✅ Token : <code>${data.token}</code><br>
          Expire le : ${data.expires_at}
        `;
      } else {
        result.textContent = `❌ Erreur : ${data.error || "Impossible de générer le token."}`;
      }
    });
});