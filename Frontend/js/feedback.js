// feedback.js
export function showFeedback(score, total) {
  const percentage = total > 0 ? Math.round((score / total) * 100) : 0;

  const feedbackContainer = document.createElement("div");
  feedbackContainer.className = "feedback animated";

  // Déterminer le message et la classe CSS
  let message = "";
  let feedbackClass = "";
  if (percentage === 100) {
    message = "Parfait ! Vous êtes un(e) expert(e) RSE. Continuez à inspirer les autres.";
    feedbackClass = "perfect";
  } else if (percentage >= 75) {
    message = "Très bon score ! Vous maîtrisez bien les enjeux RSE.";
    feedbackClass = "great";
  } else if (percentage >= 50) {
    message = "Pas mal ! Quelques révisions et vous serez au top.";
    feedbackClass = "average";
  } else {
    message = "Ne vous découragez pas ! Chaque erreur est une opportunité d’apprendre.";
    feedbackClass = "low";
  }

  feedbackContainer.classList.add(feedbackClass);
  feedbackContainer.textContent = message;

  // Bouton de fermeture
  const closeBtn = document.createElement("button");
  closeBtn.textContent = "×";
  closeBtn.className = "close-btn";
  closeBtn.onclick = () => feedbackContainer.remove();
  feedbackContainer.appendChild(closeBtn);

  // Bouton de partage
  const shareBtn = document.createElement("button");
  shareBtn.textContent = "Partager maintenant";
  shareBtn.className = "share-btn";
  shareBtn.onclick = () => {
    const text = encodeURIComponent(`J'ai obtenu ${score}/${total} au quiz RSE ! (${percentage}%)`);
    const url = encodeURIComponent(window.location.href);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
  };
  feedbackContainer.appendChild(shareBtn);

  // Bouton de copie
  const copyBtn = document.createElement("button");
  copyBtn.textContent = "Copier le score";
  copyBtn.className = "copy-btn";
  copyBtn.onclick = () => {
    const scoreMessage = `J'ai obtenu ${score}/${total} au quiz RSE (${percentage}%) !`;
    navigator.clipboard.writeText(scoreMessage).then(() => {
      toastText.textContent = "Score copié dans le presse-papier !";
      toast.classList.add("show");

      const sound = document.getElementById("copySound");
      if (sound) sound.play();

      setTimeout(() => {
        toast.classList.remove("show");
      }, 2000);
    });
  };
  feedbackContainer.appendChild(copyBtn);

  document.body.appendChild(feedbackContainer);

  // Toast de confirmation
  const toast = document.createElement("div");
  toast.className = "toast";

  const icon = document.createElement("span");
  icon.className = "toast-icon";
  icon.textContent = "✔️";
  toast.appendChild(icon);

  const toastText = document.createElement("span");
  toast.appendChild(toastText);

  document.body.appendChild(toast);
}