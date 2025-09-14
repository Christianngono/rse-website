const sounds = {
  success: new Audio('../Frontend/assets/sounds/correct.mp3'),
  error: new Audio('../Frontend/assets/sounds/wrong.mp3'),
  info: new Audio('../Frontend/assets/sounds/correct.mp3')
};

export function showNotification(message, type = 'info') {
  const notif = document.getElementById('notification');
  const log = document.getElementById('notificationLog');

  notif.textContent = message;
  notif.className = `show ${type}`;
  sounds[type]?.play();

  const entry = document.createElement('li');
  entry.className = type;
  entry.textContent = `${new Date().toLocaleTimeString()} — ${message}`;
  log.prepend(entry);

  setTimeout(() => notif.className = '', 3000);
}

export function clearNotificationLog() {
  document.getElementById('notificationLog').innerHTML = '';
  showNotification("Historique effacé", "info");
}

export function exportNotificationLog() {
  const items = document.querySelectorAll('#notificationLog li');
  if (!items.length) return showNotification("Aucune notification à exporter", "error");

  const content = Array.from(items).map(item => item.textContent).join('\n');
  const blob = new Blob([`Historique des notifications\n\n${content}`], { type: 'text/plain' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `notification_log_${new Date().toISOString().slice(0, 10)}.txt`;
  a.click();
  URL.revokeObjectURL(url);

  showNotification("Historique exporté", "success");
}