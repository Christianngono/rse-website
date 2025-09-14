// quiz.core.js
export let questions = [];
export let currentIndex = 0;

export function loadQuestions(category = "Environnement") {
  fetch("Backend/public/quiz.php", {
    method: "POST",
    body: new URLSearchParams({ category })
  })
    .then(res => res.json())
    .then(data => {
      questions = data;
      showQuestion(currentIndex);
    });
}

export function showQuestion(index) {
  const container = document.getElementById("questionContainer");
  container.classList.add("fade-out");

  setTimeout(() => {
    container.innerHTML = "";
    const q = questions[index];

    const fieldset = document.createElement("fieldset");
    const legend = document.createElement("legend");
    legend.textContent = `Question ${index + 1} sur ${questions.length} : ${q.question_text}`;
    fieldset.appendChild(legend);

    q.answers.forEach(answer => {
      const label = document.createElement("label");
      label.innerHTML = `<input type="radio" name="answer_${q.id}" value="${answer}" required> ${answer}`;
      fieldset.appendChild(label);
      fieldset.appendChild(document.createElement("br"));
    });

    const hidden = document.createElement("input");
    hidden.type = "hidden";
    hidden.name = `correct_${q.id}`;
    hidden.value = q.correct_answer;
    fieldset.appendChild(hidden);

    container.appendChild(fieldset);
    container.classList.remove("fade-out");
    container.classList.add("fade-in");

    document.getElementById("prevBtn").disabled = index === 0;
    document.getElementById("nextBtn").disabled = index === questions.length - 1;
    document.getElementById("submit-button").classList.toggle("hidden", index !== questions.length - 1);
  }, 200);
}

export function syncProgressToServer(formData) {
  const answers = [];

  formData.forEach((value, key) => {
    if (key.startsWith("answer_")) {
      answers.push({ id: key.replace("answer_", ""), answer: value });
    }
  });

  fetch("Backend/public/save_progress.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ answers })
  });
}