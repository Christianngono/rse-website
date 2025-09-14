let quizData = null;

document.addEventListener("DOMContentLoaded", () => {
  // Sons de feedback
  const sounds = {
    success: "Frontend/assets/sounds/correct.mp3",
    fail: "Frontend/assets/sounds/wrong.mp3",
    neutral: "Frontend/assets/sounds/correct.mp3",
    chart: "Frontend/assets/sounds/chart.mp3"
  };

  const playSound = type => new Audio(sounds[type]).play();

  const animateScore = (element, percentage) => {
    let current = 0;
    const interval = setInterval(() => {
      element.textContent = `${current}%`;
      current++;
      if (current > percentage) clearInterval(interval);
    }, 20);
  };

  // Récupération des résultats via AJAX
  fetch("Backend/public/results.php", {
    method: "POST",
    body: new URLSearchParams(localStorage.getItem("quizData"))
  })
    .then(res => res.json())
    .then(data => {
      quizData = data;

      // Affichage des données
      const userNameEl = document.getElementById("userName");
      const scoreEl = document.getElementById("score");
      const feedbackEl = document.getElementById("feedback");

      userNameEl.textContent = `Utilisateur : ${data.user_name}`;
      scoreEl.textContent = `Score : ${data.score} / ${data.total}`;
      feedbackEl.textContent = data.feedback;

      const percentage = data.total > 0 ? Math.round((data.score / data.total) * 100) : 0;
      animateScore(scoreEl, percentage);

      // Couleur + son du feedback
      if (percentage === 100) {
        feedbackEl.style.color = "green";
        playSound("success");
      } else if (percentage >= 75) {
        feedbackEl.style.color = "limegreen";
        playSound("success");
      } else if (percentage >= 50) {
        feedbackEl.style.color = "orange";
        playSound("neutral");
      } else {
        feedbackEl.style.color = "red";
        playSound("fail");
      }

      // Graphique donut
      const ctx = document.getElementById("donutChart").getContext("2d");
      new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: ["Réussites", "Erreurs"],
          datasets: [{
            data: [data.score, data.total - data.score],
            backgroundColor: ["#4CAF50", "#F44336"]
          }]
        },
        options: {
          responsive: false,
          animation: {
            animateScale: true,
            animateRotate: true,
            onComplete: () => playSound("chart")
          },
          plugins: {
            legend: { position: "bottom" },
            tooltip: {
              callbacks: {
                label: ctx => `${ctx.label}: ${ctx.raw} réponses`
              }
            }
          }
        }
      });

      // Liste des réponses
      const list = document.getElementById("answerList");
      data.results.forEach((r, i) => {
        const li = document.createElement("li");
        li.innerHTML = `
          <strong>Question #${r.question_id}</strong><br>
          <span style="color:${r.is_correct ? 'green' : 'red'};">
            ${r.is_correct ? 'Bonne réponse' : 'Mauvaise réponse'}
          </span><br>
          Votre réponse : <em>${r.user_answer}</em><br>
          Réponse correcte : <strong>${r.correct_answer}</strong>
          <hr>`;
        list.appendChild(li);
      });

      // Nettoyage
      localStorage.removeItem("quizData");
    });

  // Génération du PDF
  document.getElementById("downloadPDFBtn").addEventListener("click", () => {
    if (!quizData) return;

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(16);
    doc.text("Résultats du Quiz RSE", 20, 20);

    // Logo
    const logo = new Image();
    logo.src = "Frontend/assets/images/logo.png";
    logo.onload = () => {
      doc.addImage(logo, "PNG", 150, 10, 40, 20);

      doc.setFontSize(12);
      doc.text(`Nom : ${quizData.user_name}`, 20, 30);
      doc.text(`Score : ${quizData.score} / ${quizData.total}`, 20, 40);
      doc.text(`Commentaire : ${quizData.feedback}`, 20, 50);

      let y = 65;
      quizData.results.forEach((r, i) => {
        doc.text(`${i + 1}. Q#${r.question_id}`, 20, y);
        y += 6;
        doc.text(`Réponse : ${r.user_answer}`, 25, y);
        y += 6;
        doc.text(`Correcte : ${r.correct_answer}`, 25, y);
        y += 10;
      });

      let qrTarget = "https://rse-website.com/leaderboard.html"; // par défaut

      const scoreRatio = quizData.total > 0 ? quizData.score / quizData.total : 0;

      if (scoreRatio < 0.5) {
        qrTarget = "https://rse-website.com/ressources.html";
      } else if (scoreRatio < 0.75) {
        qrTarget = "https://rse-website.com/revision.html";
      }

      // QR code vers leaderboard
      const qr = new Image();
      qr.src = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(qrTarget)}`;
      qr.onload = () => {
        doc.addImage(qr, "PNG", 20, 250, 30, 30);
        doc.text("Voir le classement", 55, 265);

        // Signature
        const signature = new Image();
        signature.src = "Frontend/assets/images/signature.png";
        signature.onload = () => {
          doc.addImage(signature, "PNG", 130, 250, 50, 20);
          doc.text("Signature", 130, 245);
          doc.save("resultat_quiz.pdf");
        };
      };
    };
  });
});