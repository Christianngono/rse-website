import { showNotification } from './notifications.js';
import Chart from 'https://cdn.jsdelivr.net/npm/chart.js';
import jsPDF from 'jspdf';    
import html2canvas from 'html2canvas';

let currentChartType = 'bar';

// Basculer entre barres et donuts
document.getElementById('toggleChartType')?.addEventListener('click', () => {
  currentChartType = currentChartType === 'bar' ? 'doughnut' : 'bar';
  fetchUserStats();
});


// Exporter le graphique en image
document.getElementById('exportChartImage')?.addEventListener('click', () => {
  const canvas = document.getElementById('categoryChart');
  const username = window.dashboardData?.user || 'utilisateur';
  const date = new Date().toISOString().slice(0, 10); // Format YYYY-MM-DD

  const link = document.createElement('a');
  link.download = `stats_${username}_${date}.png`;
  link.href = canvas.toDataURL('image/png');
  link.click();
});

// Partager le graphique (Web Share API)
document.getElementById('shareStats')?.addEventListener('click', async () => {
  const canvas = document.getElementById('categoryChart');
  const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));

  if (navigator.share && navigator.canShare && navigator.canShare({ files: [new File([blob], 'stats.png')] })) {
    await navigator.share({
      title: 'Statistiques RSE',
      text: 'Voici mes résultats au quiz RSE',
      files: [new File([blob], 'stats.png')]
    });
  } else {
    showNotification("Partage non pris en charge sur ce navigateur", "error");
  }
});

// Comparer avec un autre utilisateur
document.getElementById('compareSelect')?.addEventListener('change', async (e) => {
  const user = e.target.value;
  if (!user) return;

  const response = await fetch(`../../Backend/public/getUserStats.php?user=${user}`);
  const json = await response.json();
  if (json.error) return showNotification(json.error, 'error');

  drawChartForUser(json.data, json.labels, user);
});


// Exporter plusieurs graphiques en PDF
document.getElementById('exportMultiPDF')?.addEventListener('click', async () => {
  const pdf = new jsPDF();
  const username = window.dashboardData?.user || 'utilisateur';
  const date = new Date().toISOString().slice(0, 10);

  const charts = ['categoryChart', 'questionChart'];

  for (let i = 0; i < charts.length; i++) {
    const canvas = document.getElementById(charts[i]);
    const imgData = canvas.toDataURL('image/png');
    if (i > 0) pdf.addPage();
    pdf.text(`Graphique ${i + 1}`, 10, 10);
    pdf.addImage(imgData, 'PNG', 10, 20, 180, 100);
  }

  pdf.save(`stats_${username}_${date}.pdf`);
});

// Envoyer le graphique par email
document.getElementById('sendChartEmail')?.addEventListener('click', async () => {
  const canvas = document.getElementById('categoryChart');
  const imageData = canvas.toDataURL('image/png');

  const response = await fetch('../../Backend/public/sendChartEmail.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image: imageData, user: window.dashboardData?.user })
  });

  const result = await response.json();
  showNotification(result.message || "Email envoyé", result.success ? "success" : "error");
});

// Formuler un email avec message personnalisé

document.getElementById('emailForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const canvas = document.getElementById('categoryChart');
  const imageData = canvas.toDataURL('image/png');
  const emailTo = document.getElementById('emailTo').value;
  const subject = document.getElementById('emailSubject').value;
  const message = document.getElementById('emailMessage').value;
  const user = window.dashboardData?.user || 'Utilisateur';

  const response = await fetch('../../Backend/public/sendChartEmail.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image: imageData, user, emailTo, subject, message })
  });

  const result = await response.json();
  showNotification(result.message, result.success ? 'success' : 'error');
});

// Charger les stats d’envoi d’emails
export async function loadEmailStats() {
  const res = await fetch('../../Backend/public/email_stats_api.php');
  const json = await res.json();

  new Chart(document.getElementById('emailStatsChart'), {
    type: 'line',
    data: {
      labels: json.dates,
      datasets: [{
        label: 'Emails envoyés',
        data: json.counts,
        borderColor: 'rgba(75, 192, 192, 1)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          stepSize: 1
        }
      }
    }
  });
}

loadEmailStats();

// Charger les résultats d’emails avec pagination et filtres
export async function loadEmailPage(page = 1){
  const params = new URLSearchParams({
    page,
    username: document.querySelector('filterUsername')?.value || '',
    start: document.querySelector('filterStartDate')?.value || '',
    end: document.querySelector('filterEndDate')?.value || ''
  });
  const res = await fetch(`../../Backend/public/email_logs_api.php?${params.toString()}`);
  const html = await res.text();
  document.getElementById('emailLogsContainer').innerHTML = html;
  const json = await res.json();
}

loadEmailPage();


// Charger les stats depuis l'API PHP
export async function fetchUserStats() {
  const loader = document.getElementById('categoryChartLoader');
  if (!loader) return;
  loader.style.display = 'block';

  try {
    const response = await fetch('../../Backend/public/getUserStats.php');
    const json = await response.json();

    if (json.error) {
      showNotification(json.error, 'error');
      return;
    }

    drawChartForUser(json.data, json.labels, user);
    showNotification("Statistiques chargées", "success");
  } catch (err) {
    showNotification("Erreur de chargement", "error");
  } finally {
    loader.style.display = 'none';
  }
}

// Dessiner ou mettre à jour le graphique
export function drawChartForUser(data = [], labels = [], user= '') {
  const ctx = document.getElementById('categoryChart')?.getContext('2d');
  if (!ctx) return;

  const chartData = {
    labels,
    datasets: [{
      label: `Bonnes réponses – ${user || 'Vous'}`,
      data,
      backgroundColor: [
        'rgba(75, 192, 192, 0.2)',
        'rgba(255, 99, 132, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(153, 102, 255, 0.2)'
      ],
      borderColor: [
        'rgba(75, 192, 192, 1)',
        'rgba(255, 99, 132, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(153, 102, 255, 1)'
      ],
      borderWidth: 1
    }]
  };

  const chartOptions = {
    animation: {
    duration: 800,
    easing: 'easeOutQuart'
  },
    plugins: {
      tooltip: {
        callbacks: {
          label: ctx => `${ctx.label} : ${ctx.parsed} bonnes réponses`
        }
      }
    },
    scales: currentChartType === 'bar' ? {
      y: {
        beginAtZero: true,
        stepSize: 1
      }
    } : {}
  };

  if (window.categoryChartInstance) {
    window.categoryChartInstance.destroy();
  }

  window.categoryChartInstance = new Chart(ctx, {
    type: currentChartType,
    data: chartData,
    options: chartOptions
  });
}

// Réenvoyer un email depuis l’interface admin
window.resendEmail = async function(event, id, form) {
  event.preventDefault();
  const button = form.querySelector('button');
  button.disabled = true;
  button.textContent = 'Envoi...';

  const response = await fetch('../../Backend/public/resend_email.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${encodeURIComponent(id)}`
  });

  const result = await response.json();
  showNotification(result.message, result.success ? 'success' : 'error');

  // Animer la confirmation de l’envoi
  if (result.success) {
    form.parentElement.classList.add('row-confirmed');
    setTimeout(() => {
      form.parentElement.classList.remove('row-confirmed');
      button.textContent = 'Réenvoyer';
      button.disabled = false;
    }, 3000);
  } else {
    button.textContent = 'Réessayer';
    button.disabled = false;
  }
  return false;
};

// Sélectionner ou désélectionner toutes les cases à cocher
window.toggleAll = function(masterCheckbox) {
  const checkboxes = document.querySelectorAll('input[name="delete_ids[]"]');
  checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
  const deleteBtn = document.getElementById('deleteSelectedBtn');
  if (deleteBtn) {
    deleteBtn.disabled = !masterCheckbox.checked;
  }
};

window.submitDeletion = async function(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);

  const response = await fetch(form.action, {
    method: 'POST',
    body: formData
  });

  const result = await response.json();
  showNotification(result.message, result.success ? 'success' : 'error');

  if (result.success) {
    // Optionnel : recharger la page ou retirer les lignes
    setTimeout(() => location.reload(), 1500);
  }
};

// Charger les stats au démarrage
export function loadUserStats() {
  const loader = document.getElementById('categoryChartLoader');
  if (!loader) return;
  loader.style.display = 'block';

  setTimeout(() => {
    loader.style.display = 'none';
    fetchUserStats();
  }, 500);
}

// Faire une mise à jour manuelle
export function updateUserStats() {
  const loader = document.getElementById('categoryChartLoader');
  if (!loader) return;
  loader.style.display = 'block';

  setTimeout(() => {
    fetchUserStats();
    loader.style.display = 'none';
    showNotification("Données mises à jour", "success");
  }, 500);
}

// Supprimer le graphique
export function deleteUserStats() {
  const chartContainer = document.getElementById('categoryChartContainer');
  if (chartContainer) {
    chartContainer.innerHTML = '<p>Données supprimées.</p>';
  }

  if (window.categoryChartInstance) {
    window.categoryChartInstance.destroy();
    window.categoryChartInstance = null;
  }

  showNotification("Données supprimées", "info");
}