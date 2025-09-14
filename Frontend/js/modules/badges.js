export function getBadgeLevel(score) {
  if (score >= 80) return 'gold';
  if (score >= 50) return 'silver';
  if (score > 0) return 'bronze';
  return 'none';
}

export function triggerBadge(score) {
  const level = getBadgeLevel(score);
  const badgeBox = document.createElement('div');
  badgeBox.className = `badge-anim badge-${level}`;
  badgeBox.innerHTML = `<p>🎉 Badge ${level.toUpperCase()} débloqué !</p>`;
  document.body.appendChild(badgeBox);

  const sound = new Audio(`../../Frontend/assets/sounds/badge_${level}.mp3`);
  sound.play();

  setTimeout(() => badgeBox.remove(), 4000);
}

export function showBadgeAnimation(imagePath, message = "🎉 Nouveau badge débloqué !") {
  const badgeBox = document.getElementById('badgeAnimation');
  badgeBox.querySelector('img').src = imagePath;
  badgeBox.querySelector('p').textContent = message;

  const badgeSound = new Audio('../../Frontend/assets/sounds/badge.mp3');
  badgeSound.play();

  badgeBox.classList.remove('hidden');
  badgeBox.classList.add('show');

  setTimeout(() => {
    badgeBox.classList.remove('show');
    badgeBox.classList.add('hidden');
  }, 4000);
}