import { showStatus } from "./status_feedback.js";

document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.querySelector("#historyTable tbody");
  const dateFilter = document.getElementById("dateFilter");
  const statusFilter = document.getElementById("statusFilter");
  const applyBtn = document.getElementById("applyFilters");
  const exportCSVBtn = document.getElementById("exportCSV");
  const exportPDFBtn = document.getElementById("exportPDF");

  let currentData = [];

  const fetchHistory = () => {
    const params = new URLSearchParams();
    if (dateFilter.value) params.append("date", dateFilter.value);
    if (statusFilter.value) params.append("status", statusFilter.value);

    fetch(`Backend/public/get_report_history.php?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        currentData = data;
        tableBody.innerHTML = "";
        data.forEach(entry => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${entry.sent_at}</td>
            <td>${entry.recipient}</td>
            <td>${entry.format}</td>
            <td>${entry.frequency}</td>
            <td>${entry.status}</td>
            <td>${entry.message}</td>
          `;
          tableBody.appendChild(tr);
        });
      });
  };

  applyBtn.addEventListener("click", fetchHistory);
  fetchHistory();

  exportCSVBtn.addEventListener("click", () => {
    try {
      const rows = [["Date", "Destinataire", "Format", "Fréquence", "Statut", "Message"]];
      currentData.forEach(entry => {
        rows.push([
          entry.sent_at,
          entry.recipient,
          entry.format,
          entry.frequency,
          entry.status,
          entry.message
        ]);
      });

      const csvContent = rows.map(e => e.join(",")).join("\n");
      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = "report_history.csv";
      link.click();

      showStatus("Export CSV terminé avec succès", {
        success: true,
        targetId: "status",
        duration: 4000,
        icon: true
      });
    } catch {
      showStatus("Erreur lors de l’export CSV", {
        success: false,
        targetId: "status",
        duration: 4000,
        icon: true
      });
    }
  });

  exportPDFBtn.addEventListener("click", () => {
    import("https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js").then(({ jsPDF }) => {
      const doc = new jsPDF();
      doc.setFontSize(12);
      doc.text("Historique des rapports envoyés", 14, 20);

      let y = 30;
      currentData.forEach(entry => {
        const line = `${entry.sent_at} | ${entry.recipient} | ${entry.format} | ${entry.frequency} | ${entry.status} | ${entry.message}`;
        doc.text(line, 14, y);
        y += 8;
      });

      doc.save("report_history.pdf");

      showStatus("Export PDF terminé avec succès", {
        success: true,
        targetId: "status",
        duration: 4000,
        icon: true
      });
    }).catch(() => {
      showStatus("Erreur lors de l’export PDF", {
        success: false,
        targetId: "status",
        duration: 4000,
        icon: true
      });
    });
  });

  // Exemple autonome
  showStatus("Export CSV terminé avec succès", {
    success: true,
    targetId: "status",
    duration: 4000,
    icon: true
  });
});