document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#logsTable tbody");
  const dateFilter = document.getElementById("dateFilter");
  const adminFilter = document.getElementById("adminFilter");
  const actionFilter = document.getElementById("actionFilter");
  const applyBtn = document.getElementById("applyFilters");

  const pageIndicator = document.getElementById("pageIndicator");
  const prevPageBtn = document.getElementById("prevPage");
  const nextPageBtn = document.getElementById("nextPage");

  let currentPage = 1;
  let sortField = "timestamp";
  let sortOrder = "DESC";

  const getFilters = () => {
    const params = new URLSearchParams();
    if (dateFilter.value) params.append("date", dateFilter.value);
    if (adminFilter.value) params.append("admin", adminFilter.value);
    if (actionFilter.value) params.append("action", actionFilter.value);
    return params;
  };

  const fetchLogs = () => {
    const params = getFilters();
    params.append("page", currentPage);
    params.append("sort", sortField);
    params.append("order", sortOrder);

    fetch(`Backend/public/admin_logs.php?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        tableBody.innerHTML = "";
        data.logs.forEach(log => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${log.timestamp}</td>
            <td>${log.admin_username}</td>
            <td>${log.action}</td>
            <td>${log.target_user_id}</td>
          `;
          tableBody.appendChild(tr);
        });
        pageIndicator.textContent = `Page ${currentPage}`;
      });
  };

  applyBtn.addEventListener("click", () => {
    currentPage = 1;
    fetchLogs();
  });

  prevPageBtn.addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      fetchLogs();
    }
  });

  nextPageBtn.addEventListener("click", () => {
    currentPage++;
    fetchLogs();
  });

  document.querySelectorAll("th[data-sort]").forEach(th => {
    th.addEventListener("click", () => {
      const field = th.getAttribute("data-sort");
      if (sortField === field) {
        sortOrder = sortOrder === "ASC" ? "DESC" : "ASC";
      } else {
        sortField = field;
        sortOrder = "ASC";
      }
      fetchLogs();
    });
  });

  // Export page CSV
  document.getElementById("exportPageCSV").addEventListener("click", () => {
    const rows = [["Date", "Administrateur", "Action", "ID cible"]];
    document.querySelectorAll("#logsTable tbody tr").forEach(tr => {
      const cells = Array.from(tr.children).map(td => td.textContent);
      rows.push(cells);
    });

    const csvContent = rows.map(e => e.join(",")).join("\n");
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `admin_logs_page_${currentPage}.csv`;
    link.click();
  });

  // Export page PDF
  document.getElementById("exportPagePDF").addEventListener("click", () => {
    import("https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js").then(({ jsPDF }) => {
      const doc = new jsPDF();
      doc.setFontSize(12);
      doc.text(`Journal des actions admin - Page ${currentPage}`, 14, 20);

      let y = 30;
      document.querySelectorAll("#logsTable tbody tr").forEach(tr => {
        const line = Array.from(tr.children).map(td => td.textContent).join(" | ");
        doc.text(line, 14, y);
        y += 8;
      });

      doc.save(`admin_logs_page_${currentPage}.pdf`);
    });
  });

  // Export tous les résultats CSV
  document.getElementById("exportAllCSV").addEventListener("click", () => {
    const params = getFilters();
    params.append("all", "true");

    fetch(`Backend/public/admin_logs.php?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        const rows = [["Date", "Administrateur", "Action", "ID cible"]];
        data.logs.forEach(log => {
          rows.push([log.timestamp, log.admin_username, log.action, log.target_user_id]);
        });

        const csvContent = rows.map(e => e.join(",")).join("\n");
        const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "admin_logs_filtered.csv";
        link.click();
      });
  });

  // Export tous les résultats PDF
  document.getElementById("exportAllPDF").addEventListener("click", () => {
    const params = getFilters();
    params.append("all", "true");

    fetch(`Backend/public/admin_logs.php?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        import("https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js").then(({ jsPDF }) => {
          const doc = new jsPDF();
          doc.setFontSize(12);
          doc.text("Journal des actions admin - Résultats filtrés", 14, 20);

          let y = 30;
          data.logs.forEach(log => {
            const line = `${log.timestamp} | ${log.admin_username} | ${log.action} | ${log.target_user_id}`;
            doc.text(line, 14, y);
            y += 8;
          });

          doc.save("admin_logs_filtered.pdf");
        });
      });
  });

  // Impression
  document.getElementById("printLogs").addEventListener("click", () => {
    const printWindow = window.open("", "_blank");
    const style = `
      <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #007bff; color: white; }
        h1 { font-size: 20px; margin-bottom: 10px; }
      </style>
    `;

    const rows = Array.from(document.querySelectorAll("#logsTable tbody tr"))
      .map(tr => `<tr>${Array.from(tr.children).map(td => `<td>${td.textContent}</td>`).join("")}</tr>`)
      .join("");

    const html = `
      <html>
        <head><title>Impression des logs admin</title>${style}</head>
        <body>
          <h1>Journal des actions administratives</h1>
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Administrateur</th>
                <th>Action</th>
                <th>ID cible</th>
              </tr>
            </thead>
            <tbody>${rows}</tbody>
          </table>
        </body>
      </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
  });

  // Envoi par email
  document.getElementById("emailLogs").addEventListener("click", () => {
    const email = prompt("Entrez l’adresse email du destinataire :");
    if (!email || !email.includes("@")) {
      alert("Adresse email invalide.");
      return;
    }

    const params = getFilters();
    params.append("email", email);

    fetch(`Backend/public/send_logs_email.php?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        alert(data.message);
      })
      .catch(() => {
        alert("Erreur lors de l’envoi de l’email.");
      });
  });

  fetchLogs(); // chargement initial
});





