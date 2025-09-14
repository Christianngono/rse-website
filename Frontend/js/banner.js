export function showBanner(type, icon, message, showReset = false) {
  const banner = document.getElementById('restoreBanner');
  const bannerIcon = document.getElementById('bannerIcon');
  const bannerText = document.getElementById('bannerText');
  const resetBtn = document.getElementById('resetBtn');

  banner.className = `banner ${type}`;
  bannerIcon.textContent = icon;
  bannerText.textContent = message;

  banner.style.display = 'flex';
  banner.classList.add('fade-in');

  if (showReset) {
    resetBtn.style.display = 'inline-block';
    resetBtn.classList.add('fade-in');
  } else {
    resetBtn.style.display = 'none';
  }
}

export function hideBanner() {
  const banner = document.getElementById('restoreBanner');
  const resetBtn = document.getElementById('resetBtn');

  banner.classList.add('fade-out');
  resetBtn.classList.add('fade-out');

  setTimeout(() => {
    banner.style.display = 'none';
    resetBtn.style.display = 'none';
    banner.classList.remove('fade-out', 'fade-in');
    resetBtn.classList.remove('fade-out', 'fade-in');
  }, 500);
}