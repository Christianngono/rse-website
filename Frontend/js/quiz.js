import { loadQuestions, showQuestion, syncProgressToServer, questions, currentIndex } from './quiz.core.js';
import { showCorrectFeedback, showErrorFeedback } from './quiz.feedback.js';
import { generatePDF, exportAnswers } from './quiz.export.js';

document.addEventListener("DOMContentLoaded", () => {
  loadQuestions();

  // Naviguer entre les questions
  document.getElementById("nextBtn").addEventListener("click", () => {
    if (currentIndex < questions.length - 1) {
      currentIndex++;
      showQuestion(currentIndex);
    }
  });

  document.getElementById("prevBtn").addEventListener("click", () => {
    if (currentIndex > 0) {
      currentIndex--;
      showQuestion(currentIndex);
    }
  });

  // checker le Feedback visuel et sonore
  document.getElementById("quizForm").addEventListener("change", (e) => {
    const input = e.target;
    if (input.type === "radio") {
      const isCorrect = input.dataset.correct === "true";
      isCorrect ? showCorrectFeedback() : showErrorFeedback();
    }
  });

  // Soummettre le quiz
  document.getElementById("submit-button").addEventListener("click", () => {
    const form = document.getElementById("quizForm");
    const formData = new FormData(form);

    let score = 0;
    let total = 0;

    questions.forEach(q => {
      const selected = form.querySelector(`input[name="answer_${q.id}"]:checked`);
      if (selected) {
        total++;
        if (selected.value === q.correct_answer) {
          score++;
        }
      }
    });

    let message = '';
    if (score === total) {
      message = '🎯 Parfait ! Tu as tout bon !';
    } else if (score >= total * 0.7) {
      message = '👍 Très bien joué ! Tu maîtrises le sujet.';
    } else if (score >= total * 0.4) {
      message = '👌 Pas mal, mais tu peux faire mieux.';
    } else {
      message = '📚 Oups… Il est temps de réviser un peu.';
    }

    document.getElementById("final-score").textContent = `Score : ${score} / ${total} — ${message}`;
    document.getElementById("result").classList.remove("hidden");

    form.querySelectorAll("input").forEach(input => input.disabled = true);
    document.getElementById("submit-button").disabled = true;

    syncProgressToServer(formData);

    document.getElementById("downloadCertificateBtn").addEventListener("click", () => {
      if (typeof generatePDF === "function") {
        generatePDF(score, total, message); // Tu peux stocker score/message en global si besoin
      }
    });
  });
  // Exporter des réponses
  document.getElementById("exportAnswersBtn").addEventListener("click", exportAnswers);
});