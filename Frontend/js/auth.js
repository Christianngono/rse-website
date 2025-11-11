export async function login(username, password) {
  const res = await fetch('/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  });

  const data = await res.json();
  if (data.token) {
    const payload = JSON.parse(atob(data.token.split('.')[1]));
    if (!payload.is_active) {
      return { error: '⛔ Compte inactif. Veuillez valider votre email.' };
    }
    localStorage.setItem('jwt', data.token);
    localStorage.setItem('userRole', payload.role);
    localStorage.setItem('profil', payload.profil);
  }
  return data;
}