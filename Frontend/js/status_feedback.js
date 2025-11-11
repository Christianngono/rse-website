export function showStatus(message, {
  success = true,
  targetId = "status",
  duration = 3000,
  icon = true
} = {}) {
  const statusDiv = document.getElementById(targetId);
  if (!statusDiv) return;

  const symbol = icon ? (success ? "✅ " : "❌ ") : "";
  statusDiv.textContent = symbol + message;
  statusDiv.style.color = success ? "green" : "red";
  statusDiv.classList.remove("visible");
  void statusDiv.offsetWidth;
  statusDiv.classList.add("visible");

  if (duration > 0) {
    setTimeout(() => {
      statusDiv.classList.remove("visible");
    }, duration);
  }
}