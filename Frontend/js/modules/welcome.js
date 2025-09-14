export function getGreeting() {
  const hour = new Date().getHours();
  if (hour < 6) return "Bonne nuit";
  if (hour < 12) return "Bonjour";
  if (hour < 18) return "Bon après-midi";
  return "Bonsoir";
}

export function showWelcomeMessage() {
  const greeting = getGreeting();
  const overlay = document.getElementById('introOverlay');
  if (overlay) overlay.querySelector('h1').textContent = `${greeting} Christian`;
}