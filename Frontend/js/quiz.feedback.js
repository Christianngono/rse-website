// quiz.feedback.js
export const correctSound = new Audio('assets/sounds/correct.mp3');
export const errorSound = new Audio('assets/sounds/wrong.mp3');

correctSound.volume = 0.3;
errorSound.volume = 0.3;

export function showCorrectFeedback() {
  const visual = document.getElementById("feedbackVisual");
  correctSound.play();
  visual.classList.add("show");
  setTimeout(() => visual.classList.remove("show"), 1000);
}

export function showErrorFeedback() {
  const visual = document.getElementById("feedbackError");
  errorSound.play();
  visual.classList.add("show");
  setTimeout(() => visual.classList.remove("show"), 1000);
}