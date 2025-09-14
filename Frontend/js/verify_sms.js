document.getElementById("verifyForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch("Backend/public/verify_sms.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msg = document.getElementById("message");
    msg.textContent = data.message;
    msg.style.color = data.success ? "green" : "red";

    if (data.success) {
      setTimeout(() => {
        window.location.href = "../Frontend/quiz.html";
      }, 3000);
    }
  })
  .catch(() => {
    const msg = document.getElementById("message");
    msg.textContent = "Erreur réseau.";
    msg.style.color = "red";
  });
});