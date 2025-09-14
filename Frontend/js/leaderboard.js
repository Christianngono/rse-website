document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#leaderboardTable tbody");
  const profilFilter = document.getElementById("profilFilter");
  const dateFilter = document.getElementById("dateFilter");
  const applyBtn = document.getElementById("applyFilters");

  const fetchData = () => {
    const params = new URLSearchParams();
    if (profilFilter.value) params.append("profil", profilFilter.value);
    if (dateFilter.value) params.append("date", dateFilter.value);

    fetch(`Backend/public/leaderboard.php?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        tableBody.innerHTML = "";
        data.forEach(row => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${row.username}</td>
            <td>${row.score}</td>
            <td>${row.profil}</td>
            <td>${row.created_at}</td>
            <td><a href="../../certificats/${row.certificat}" target="_blank">📄 Voir</a></td>
          `;
          tableBody.appendChild(tr);
        });
      });
  };

  applyBtn.addEventListener("click", fetchData);
  fetchData();
});