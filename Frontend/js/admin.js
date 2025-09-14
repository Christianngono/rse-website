document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#adminTable tbody");
  const profilFilter = document.getElementById("profilFilter");
  const roleFilter = document.getElementById("roleFilter");
  const applyBtn = document.getElementById("applyFilters");

  const fetchAdmins = () => {
    const params = new URLSearchParams();
    if (profilFilter.value) params.append("profil", profilFilter.value);
    if (roleFilter.value) params.append("role", roleFilter.value);

    fetch(`Backend/public/admin.php?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        tableBody.innerHTML = "";
        data.forEach(user => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${user.username}</td>
            <td>
              <select data-id="${user.id}" class="roleSelect">
                <option value="user" ${user.role === 'user' ? 'selected' : ''}>Utilisateur</option>
                <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
              </select>
            </td>
            <td>${user.profil}</td>
            <td>${user.email}</td>
            <td>${user.quiz_score}</td>
            <td>${user.created_at}</td>
            <td><button data-id="${user.id}" class="deleteBtn">🗑️</button></td>
          `;
          tableBody.appendChild(tr);
        });
      });
  };

  applyBtn.addEventListener("click", fetchAdmins);
  fetchAdmins(); // initial load
});

tableBody.addEventListener("change", e => {
  if (e.target.classList.contains("roleSelect")) {
    const userId = e.target.dataset.id;
    const newRole = e.target.value;

    fetch("Backend/public/admin.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action: "change_role",
        user_id: userId,
        new_role: newRole
      })
    }).then(res => res.json()).then(data => {
      alert(data.message);
    });
  }
});

tableBody.addEventListener("click", e => {
  if (e.target.classList.contains("deleteBtn")) {
    const userId = e.target.dataset.id;
    if (confirm("Supprimer cet utilisateur ?")) {
      fetch("Backend/public/admin.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "delete_user",
          user_id: userId
        })
      }).then(res => res.json()).then(data => {
        alert(data.message);
        fetchAdmins();
      });
    }
  }
});