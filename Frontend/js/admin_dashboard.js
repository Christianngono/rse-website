// assets/js/admin_dashboard.js
fetch("Backend/public/admin_stats.php")
  .then(res => res.json())
  .then(data => {
    document.getElementById("totalParticipants").textContent = data.total_participants;
    document.getElementById("averageScore").textContent = data.average_score;

    const labels = [];
    const counts = [];
    const scores = [];

    const tbody = document.querySelector("#profilTable tbody");
    data.by_profil.forEach(row => {
      labels.push(row.profil);
      counts.push(row.count);
      scores.push(row.avg_score);

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${row.profil}</td>
        <td>${row.count}</td>
        <td>${row.avg_score}</td>
      `;
      tbody.appendChild(tr);
    });

    new Chart(document.getElementById("profilChart"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [{
          label: "Participants par profil",
          data: counts,
          backgroundColor: ["#007bff", "#28a745", "#ffc107"]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true }
        }
      }
    });
  });