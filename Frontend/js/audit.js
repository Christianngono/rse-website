// ~/rse-website/rse-website/Frontend/js/audit.js
export async function fetchAudit() {
  const token = localStorage.getItem('jwt');
  const res = await fetch('/audit', {
    headers: { Authorization: `Bearer ${token}` }
  });
  return await res.text();
}