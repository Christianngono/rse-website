export function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  if (sidebar) {
    sidebar.classList.toggle('hidden');
  }
}

export function showSection(id) {
  const sections = document.querySelectorAll('section');
  sections.forEach(section => section.classList.add('hidden'));

  const target = document.getElementById(id);
  if (target) {
    target.classList.remove('hidden');
  }
}

toggleSidebar(); // Devrait rétracter le panneau
showSection('rapport');