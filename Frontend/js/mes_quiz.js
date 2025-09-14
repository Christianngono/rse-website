let allQuiz = [];

function renderQuizList(data) {
  const container = document.getElementById("quizList");
  container.innerHTML = "";

  const category = document.getElementById("categoryFilter").value;
  const difficulty = document.getElementById("difficultyFilter").value;

  const grouped = {};
  data.forEach(q => {
    if (
      (category && q.category !== category) ||
      (difficulty && q.difficulty !== difficulty)
    ) return;

    if (!grouped[q.question_id]) {
      grouped[q.question_id] = {
        question_text: q.question_text,
        category: q.category,
        difficulty: q.difficulty,
        correct: 0,
        total: 0,
        answers: [],
        user_name: q.user_name
      };
    }
    grouped[q.question_id].total++;
    if (q.is_correct) grouped[q.question_id].correct++;
    grouped[q.question_id].answers.push(q.user_answer);
  });

  Object.entries(grouped).forEach(([id, q]) => {
    const div = document.createElement("div");
    div.className = "quiz-item";
    const percent = Math.round((q.correct / q.total) * 100);

    div.innerHTML = `
      <strong>${q.question_text}</strong><br>
      Catégorie : ${q.category} — Difficulté : ${q.difficulty}<br>
      <button class="resumeBtn" data-id="${id}">🔄 Reprendre ce quiz</button>
      <div class="progress-badge">${percent}% réussi</div>
    `;
    container.appendChild(div);
  });

  document.querySelectorAll(".resumeBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      const questionId = btn.dataset.id;
      localStorage.setItem("resumeQuestionId", questionId);
      window.location.href = "quiz.html";
    });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  fetch("Backend/public/mes_quiz.php")
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById("quizList");
      if (data.error) {
        container.innerHTML = `
          <div class="error-message">
            ❌ ${data.error}<br>
            <a href="login.html" class="btn">🔐 Se reconnecter</a>
          </div>
        `;
        return;
      }
      allQuiz = data;
      renderQuizList(allQuiz);
    });

  document.getElementById("categoryFilter").addEventListener("change", () => renderQuizList(allQuiz));
  document.getElementById("difficultyFilter").addEventListener("change", () => renderQuizList(allQuiz));
});

document.getElementById("exportProgressBtn").addEventListener("click", () => {
  fetch("Backend/public/mes_quiz.php")
    .then(res => res.json())
    .then(data => {
      if (data.error) return alert("Impossible d’exporter : " + data.error);

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      const user = data[0]?.user_name || localStorage.getItem("userName") || "Utilisateur";
      const dateStr = new Date().toLocaleDateString("fr-FR");

      doc.setFontSize(16);
      doc.text("Mes quiz en cours", 20, 20);
      doc.setFontSize(12);
      doc.text(`Nom : ${user}`, 20, 30);
      doc.text(`Date : ${dateStr}`, 20, 40);

      let y = 55;
      const grouped = {};

      data.forEach(q => {
        if (!grouped[q.question_id]) {
          grouped[q.question_id] = {
            question_text: q.question_text,
            category: q.category,
            difficulty: q.difficulty,
            correct: 0,
            total: 0,
            answers: []
          };
        }
        grouped[q.question_id].total++;
        if (q.is_correct) grouped[q.question_id].correct++;
        grouped[q.question_id].answers.push(q.user_answer);
      });

      Object.entries(grouped).forEach(([id, q], i) => {
        const percent = Math.round((q.correct / q.total) * 100);
        doc.text(`${i + 1}. ${q.question_text}`, 20, y);
        y += 6;
        doc.text(`→ Catégorie : ${q.category} | Difficulté : ${q.difficulty}`, 25, y);
        y += 6;
        doc.text(`→ Réponses données : ${q.answers.join(", ")}`, 25, y);
        y += 6;
        doc.text(`→ Taux de réussite : ${percent}%`, 25, y);
        y += 10;
        if (y > 270) {
          doc.addPage();
          y = 20;
        }
      });

      // Générer le QR code sécurisé
      fetch("Backend/public/generate_token.php")
        .then(res => res.json())
        .then(tokenData => {
          if (!tokenData.token) {
            alert("Erreur lors de la génération du QR sécurisé.");
            return;
          }

          const qrUrl = `https://rse-website.com/mes_quiz.html?token=${tokenData.token}`;
          const qr = new Image();
          qr.src = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(qrUrl)}`;
          qr.onload = () => {
            doc.addImage(qr, "PNG", 20, 250, 40, 40);
            doc.text("🔗 Revenir à mes quiz", 20, 265);
            doc.save("mes_quiz_en_cours.pdf");
          };
        });
    });
});