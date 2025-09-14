import Chart from 'chart.js/auto';

export function renderCategoryChart(data) {
  const ctx = document.getElementById('categoryChart').getContext('2d');
  const labels = data.categoryStats.map(c => c.category);
  const values = data.categoryStats.map(c => c.success_rate);

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: '% Réussite',
        data: values,
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
      }]
    },
    options: {
      animation: {
        duration: 1200,
        easing: 'easeOutQuart'
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100
        }
      },
      plugins: {
        tooltip: {
          backgroundColor: '#2c3e50',
          titleColor: '#fff',
          bodyColor: '#fff'
        }
      }
    }
  });
}