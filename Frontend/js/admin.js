import { showToast } from './toast.js';
import { logoBase64, signatureBase64, backgroundBase64 } from './imagesBase64.js';
import { jsPDF } from 'jspdf';
import 'jspdf-autotable';

const searchInput = document.getElementById('searchInput');
const paginationContainer = document.getElementById('paginationContainer');
const resultCount = document.getElementById('resultCount');
const tableBody = document.querySelector('#adminTable tbody');
const addUserForm = document.getElementById('addUserForm');
const filterForm = document.getElementById('filterForm');
const deleteSound = document.getElementById('deleteSound');
const successSound = document.getElementById('successSound');
const exportCSVBtn = document.getElementById('exportCSV');
const exportPDFBtn = document.getElementById('exportPDF');
const testNotifyBtn = document.getElementById('testNotifyBtn');

let allUsers = [];
let currentPage = 1;
const rowsPerPage = 10;

// 🔄 Chargement des utilisateurs
async function loadUsers(filters = {}) {
  const params = new URLSearchParams(filters);
  const response = await fetch(`Backend/public/admin.php?${params.toString()}`);
  allUsers = await response.json();
  currentPage = 1;
  renderTable();
  renderPagination();
}

// 📊 Affichage du tableau
function renderTable() {
  tableBody.innerHTML = '';
  const searchTerm = searchInput && 'value' in searchInput ? searchInput.value.toLowerCase() : '';

  const filteredUsers = allUsers.filter(user =>
    user.username.toLowerCase().includes(searchTerm) ||
    user.email.toLowerCase().includes(searchTerm)
  );

  if (resultCount && 'textContent' in resultCount) {
    resultCount.textContent = `${filteredUsers.length} utilisateur(s) trouvé(s)`;
  }

  const start = (currentPage - 1) * rowsPerPage;
  const usersToDisplay = filteredUsers.slice(start, start + rowsPerPage);

  if (usersToDisplay.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="7">Aucun utilisateur trouvé.</td></tr>';
    return;
  }

  usersToDisplay.forEach((user, index) => {
    const row = document.createElement('tr');
    row.style.animationDelay = `${index * 50}ms`;

    row.innerHTML = `
      <td>${user.username}</td>
      <td>
        <select class="role-select">
          <option value="admin">admin</option>
          <option value="user">user</option>
        </select>
        <svg class="role-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="#3498db" stroke-width="2" fill="transparent" />
        </svg>
      </td>
      <td>${user.profil}</td>
      <td>${user.email}</td>
      <td>${user.quiz_score}</td>
      <td>
        <button class="change">Changer</button>
        <button class="delete">Supprimer</button>
        <button class="block">${user.is_active ? 'Bloquer' : 'Débloquer'}</button>
      </td>
      <td>
        <button class="validate" ${user.is_active ? 'disabled' : ''}>Valider</button>
      </td>
    `;

    const select = row.querySelector('.role-select');
    if (select && 'value' in select) select.value = user.role;

    row.querySelector('.change')?.addEventListener('click', async () => {
      const newRole = select && 'value' in select ? select.value : 'user';
      const res = await fetch('Backend/public/admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'change_role',
          user_id: user.id,
          new_role: newRole
        })
      });
      const result = await res.json();
      showToast('success', '🔄', result.message);
      const icon = row.querySelector('.role-icon');
      icon?.classList.add('animate');
      setTimeout(() => icon?.classList.remove('animate'), 600);
    });

    row.querySelector('.delete')?.addEventListener('click', async () => {
      if (!confirm("Supprimer cet utilisateur ?")) return;
      const res = await fetch('Backend/public/admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'delete_user', user_id: user.id })
      });
      const result = await res.json();
      showToast('error', '🗑️', result.message);
      if (deleteSound && 'play' in deleteSound) deleteSound.play();
      row.classList.add('fade-out');
      setTimeout(() => row.remove(), 400);
    });

    row.querySelector('.block')?.addEventListener('click', async () => {
      const action = user.is_active ? 'block_user' : 'unblock_user';
      const res = await fetch('Backend/public/admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action, user_id: user.id })
      });
      const result = await res.json();
      showToast('info', user.is_active ? '🔒' : '🔓', result.message);
      await loadUsers();
    });

    row.querySelector('.validate')?.addEventListener('click', async () => {
      const button = row.querySelector('.validate');
      const res = await fetch('Backend/public/admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'validate_user', user_id: user.id })
      });
      const result = await res.json();
      showToast('success', '✅', result.message);
      if (successSound && 'play' in successSound) successSound.play();
      if (button && 'disabled' in button) {
        button.classList.add('flash-validated');
        button.disabled = true;
        button.textContent = 'Validé ✅';
      }
    });

    tableBody.appendChild(row);
  });
}

// 📄 Pagination
function renderPagination() {
  paginationContainer.innerHTML = '';
  const searchTerm = searchInput && 'value' in searchInput ? searchInput.value.toLowerCase() : '';
  const filteredUsers = allUsers.filter(user =>
    user.username.toLowerCase().includes(searchTerm) ||
    user.email.toLowerCase().includes(searchTerm)
  );
  const pageCount = Math.ceil(filteredUsers.length / rowsPerPage);

  for (let i = 1; i <= pageCount; i++) {
    const btn = document.createElement('button');
    btn.textContent = String(i);
    btn.className = i === currentPage ? 'active' : '';
    btn.addEventListener('click', () => {
      currentPage = i;
      renderTable();
      renderPagination();
    });
    paginationContainer.appendChild(btn);
  }
}

// 🔍 Recherche instantanée
searchInput?.addEventListener('input', () => {
  currentPage = 1;
  renderTable();
  renderPagination();
});

// 🧮 Filtrage par rôle et profil
filterForm?.addEventListener('submit', async e => {
  e.preventDefault();
  const formData = new FormData(filterForm);
  const filters = {
    role: formData.get('role') || '',
    profil: formData.get('profil') || ''
  };
  await loadUsers(filters);
});

// ➕ Ajout utilisateur
addUserForm?.addEventListener('submit', async e => {
  e.preventDefault();
  const formData = new FormData(addUserForm);
  formData.append('action', 'add_user');

  const res = await fetch('Backend/public/admin.php', { method: 'POST', body: formData });
  const result = await res.json();
  showToast(result.success ? 'success' : 'error', result.success ? '👤' : '⚠️', result.message);

  if (result.success && addUserForm instanceof HTMLFormElement) {
    addUserForm.reset();
    await loadUsers();
  }
});