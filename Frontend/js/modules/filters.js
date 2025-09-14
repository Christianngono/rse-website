export function filterUsers() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const rate = document.getElementById('successRateFilter').value;
  const category = document.getElementById('categoryFilter').value;
  const rows = document.querySelectorAll('#userTable tbody tr');

  rows.forEach(row => {
    const name = row.cells[0].textContent.toLowerCase();
    const successRate = parseFloat(row.cells[3].textContent.replace('%', '')) || 0;
    const rowCategory = row.getAttribute('data-category') || '';

    const matchSearch = name.includes(search);
    const matchRate = (
      rate === 'high' ? successRate >= 80 :
      rate === 'medium' ? successRate >= 50 && successRate < 80 :
      rate === 'low' ? successRate < 50 : true
    );
    const matchCategory = category ? rowCategory === category : true;

    row.style.display = (matchSearch && matchRate && matchCategory) ? '' : 'none';
  });
}

export function clearFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('successRateFilter').value = '';
  document.getElementById('categoryFilter').value = '';
  filterUsers();
}

export function populateCategoryFilter(data) {
  const select = document.getElementById('categoryFilter');
  select.innerHTML = '<option value="">Toutes les catégories</option>';

  data.categories?.forEach(cat => {
    const option = document.createElement('option');
    option.value = cat;
    option.textContent = cat;
    select.appendChild(option);
  });
}