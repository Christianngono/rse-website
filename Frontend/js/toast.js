export function showToast(type, icon, message, link = null) {
  const toast = document.getElementById('toast');
  const toastIcon = document.getElementById('toastIcon');
  const toastMessage = document.getElementById('toastMessage');
  const toastLink = document.getElementById('toastLink');

  toast.className = `toast ${type}`;
  toastIcon.textContent = icon;
  toastMessage.innerHTML = message;

  if (link) {
    toastLink.href = link;
    toastLink.style.display = 'inline';
  } else {
    toastLink.style.display = 'none';
  }

  toast.style.display = 'block';
  setTimeout(() => {
    toast.style.display = 'none';
  }, 4000);
}