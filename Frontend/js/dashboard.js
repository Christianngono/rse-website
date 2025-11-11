export async function fetchDashboardReport() {
  const token = localStorage.getItem('jwt');
  if (!token) {
    console.warn('Aucun token JWT trouvé.');
    return '⛔ Token manquant. Veuillez vous connecter.';
  }

  try {
    const res = await fetch('/rapport-jwt', {
      headers: { Authorization: `Bearer ${token}` }
    });

    if (!res.ok) {
      return '⛔ Accès refusé. Token invalide ou expiré.';
    }

    const html = await res.text();
    return html;
  } catch (err) {
    console.error('Erreur lors de la récupération du rapport :', err);
    return '⛔ Erreur réseau ou serveur.';
  }
}