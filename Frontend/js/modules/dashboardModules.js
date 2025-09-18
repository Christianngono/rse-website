import { fetchDashboardData } from '../rse-website/Frontend/js/admin.js';
import { renderCategoryChart } from '../rse-website/Frontend/js/modules/chartStats.js';
import { renderDonutChart } from './chartDonut.js';
import { showNotification } from './notifications.js';
import { populateCategoryFilter } from './filters.js';

// === Graphiques statiques initiaux ===
const chartData = {
  labels: ['Environnement', 'Éthique', 'Social', 'Gouvernance', 'Développement durable', 'Normes et labels', 'Parties prenantes'],
  values: [85, 60, 90, 45, 75, 50, 95]
};

const donutData = {
  labels: ['Bonnes réponses', 'Mauvaises réponses', 'Non répondu', 'Autres'],
  values: [120, 30, 10, 5]
};

renderCategoryChart({
  categoryStats: chartData.labels.map((label, i) => ({
    category: label,
    success_rate: chartData.values[i]
  }))
});

renderDonutChart(donutData);

// === Chargement complet du dashboard ===
export async function loadDashboard(user = null, page = 1) {
  try {
    const dashboardData = await fetchDashboardData(user, page);
    renderUserTable(dashboardData);
    renderUserSelect(dashboardData);
    renderQuestionTable(dashboardData);
    if (user) renderCategoryChart(dashboardData);
    renderUserPagination(dashboardData);
    populateCategoryFilter(dashboardData);
    showNotification("Données mises à jour", "success");
  } catch (error) {
    showNotification("Erreur de chargement : " + error.message, "error");
  } finally {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'none';
  }
}

// === Chargement des stats utilisateur sélectionné ===
export function loadUserStats() {
  const select = document.getElementById('userSelect');
  if (!select) return;
  const selectedUser = select.value;
  showNotification(`Utilisateur sélectionné : ${selectedUser}`, "info");
  loadDashboard(selectedUser);
}

// === Rendu du tableau des utilisateurs ===
export function renderUserTable(data) {
  const tbody = document.querySelector('#userTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  tbody.classList.add('fade-in');

  if (data.users.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4">Aucun utilisateur trouvé.</td></tr>`;
    return;
  }

  data.users.forEach(user => {
    const score = user.total > 0 ? Math.round((user.bonnes / user.total) * 100) : 0;
    const category = user.category || '';
    tbody.innerHTML += `
      <tr data-category="${category}">
        <td data-label="Nom">${user.user_name}</td>
        <td data-label="Bonnes réponses">${user.bonnes}</td>
        <td data-label="Total réponses">${user.total}</td>
        <td data-label="% Réussite">${score}%</td>
      </tr>`;
  });
}

// === Pagination utilisateur ===
export function renderUserPagination(data) {
  const container = document.getElementById('userPagination');
  if (!container) return;
  container.innerHTML = '';

  const prevBtn = document.createElement('button');
  prevBtn.textContent = '← Précédent';
  prevBtn.disabled = data.currentPage <= 1;
  prevBtn.addEventListener('click', () => loadDashboard(null, data.currentPage - 1));

  const nextBtn = document.createElement('button');
  nextBtn.textContent = 'Suivant →';
  nextBtn.disabled = data.currentPage >= data.totalPages;
  nextBtn.addEventListener('click', () => loadDashboard(null, data.currentPage + 1));

  const info = document.createElement('span');
  info.textContent = ` Page ${data.currentPage} / ${data.totalPages} `;

  container.appendChild(prevBtn);
  container.appendChild(info);
  container.appendChild(nextBtn);
}

// === Sélecteur utilisateur ===
export function renderUserSelect(data) {
  const select = document.getElementById('userSelect');
  if (!select) return;
  select.innerHTML = '';
  data.users.forEach(user => {
    const option = document.createElement('option');
    option.value = user.user_name;
    option.textContent = user.user_name;
    if (user.user_name === data.selectedUser) option.selected = true;
    select.appendChild(option);
  });
}

// === Tableau des questions ===
export function renderQuestionTable(data) {
  const tbody = document.querySelector('#questionTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  tbody.classList.add('fade-in');

  data.questionStats.forEach(question => {
    tbody.innerHTML += `
      <tr>
        <td data-label="ID">${question.id}</td>
        <td data-label="Question">${question.question_text}</td>
        <td data-label="Réponses">${question.total}</td>
        <td data-label="Bonnes">${question.correct}</td>
        <td data-label="% Réussite">${question.success_rate}%</td>
      </tr>`;
  });
}
