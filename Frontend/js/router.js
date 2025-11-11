export function navigateTo(view) {
  const token = localStorage.getItem('jwt');
  const protectedViews = ['dashboard', 'audit', 'export'];

  if (protectedViews.includes(view) && !token) {
    alert('⛔ Accès refusé. Veuillez vous connecter.');
    view = 'login';
  }

  fetch(`./views/${view}.html`)
    .then(res => res.text())
    .then(html => {
      const container = document.getElementById('app');
      container.innerHTML = html;
      window.history.pushState({}, '', `#${view}`);
      animateViewTransition();
    });
}

window.addEventListener('popstate', () => {
  const view = location.hash.replace('#', '') || 'login';
  navigateTo(view);
});