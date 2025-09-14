document.getElementById("cleanBtn").addEventListener("click", () => {
  fetch("Backend/public/clean_expired_tokens.php")
    .then(res => res.json())
    .then(data => {
      const result = document.getElementById("result");
      result.textContent = `✅ ${data.message} (${data.deleted} supprimés)`;
    })
    .catch(() => {
      document.getElementById("result").textContent = "❌ Erreur lors du nettoyage.";
    });
});