<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de bord Admin</title>
  <link rel="stylesheet" href="../../Frontend/assets/css/header.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="module" src="../../Frontend/js/modules/chartStats.js"></script>
  <script type="module" src="../../Frontend/js/modules/chartDonut.js"></script>
  <script type="module" src="../../Frontend/js/modules/dashboardUtils.js"></script>
  <script type="module" src="../../Frontend/js/modules/dashboardModules.js"></script>
</head>

<body>
  <form id="emailForm">
    <input type="email" id="emailTo" placeholder="Destinataire" required>
    <input type="text" id="emailSubject" placeholder="Objet" value="Statistiques RSE">
    <textarea id="emailMessage" placeholder="Message personnalisé">Veuillez trouver ci-joint mes statistiques RSE.</textarea>
    <button type="submit">Envoyer</button>
  </form>
  <div id="bravoMessage">Bravo pour ce score !</div>

  <header>
    <div class="top-bar">
      <div id="clock"></div>
      <div id="connectionStatus">Connecté</div>
      <button onclick="loadDashboard()">Recharger les données</button>
    </div>

    <nav>
      <h1>Quiz RSE</h1>
      <ul>
        <li><a href="index.php">Accueil</a></li>
        <li><a href="quiz.php">Quiz</a></li>
        <li><a href="results.php">Résultats</a></li>
        <?php if (isset($_SESSION['user'])): ?>
          <li><a href="../../Backend/config/stats_rse.php?user=<?= urlencode($_SESSION['user']) ?>">Mes statistiques</a></li>
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="admin.php">Administration</a></li>
          <?php endif; ?>
          <li class="user-info">Connecté en tant que <strong><?= htmlspecialchars($_SESSION['user']) ?></strong></li>
          <li><a href="logout.php">Déconnexion</a></li>
        <?php else: ?>
          <li><a href="login.php" class="btn">Se connecter</a></li>
        <?php endif; ?>
      </ul>
    </nav>

    <div id="profileHeader">
      <img src="../../Frontend/assets/images/christian.jpg" alt="Photo de Christian" id="profilePic">
      <div id="welcomeText">Bienvenue <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : '' ?> !</div>
    </div>
  </header>

  <div id="introOverlay">
    <h1>Bienvenue</h1>
    <p>Chargement du tableau de bord...</p>
  </div>

  <div id="notification">Données mises à jour</div>
    <div id="notificationBox" class="notification hidden">
      <span id="notificationIcon">✅</span>
      <span id="notificationText">Notification ici</span>
    </div>

  <main>
    <h1>Bienvenue dans l'espace administrateur</h1>
    <p>Voici les statistiques du jour...</p>
  </main>

  <button id="themeToggle">Mode sombre</button>
  <div id="loader">Chargement...</div>

  <section>
    <h2>Utilisateurs</h2>
    <div class="filter-bar">
      <input type="text" id="searchInput" placeholder="Rechercher un nom..." onkeyup="filterUsers()">
      <label for="successRateFilter">Taux de réussite :</label>
      <select id="successRateFilter" onchange="filterUsers()">
        <option value="">Tous les taux</option>
        <option value="high">≥ 80%</option>
        <option value="medium">50–79%</option>
        <option value="low">< 50%</option>
      </select>

      <label for="categoryFilter">Catégorie :</label>
      <select id="categoryFilter" onchange="filterUsers()">
        <option value="">Toutes les catégories</option>
      </select>
    </div>

    <table id="userTable">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Bonnes réponses</th>
          <th>Total réponses</th>
          <th>% Réussite</th>
          <th>Badge</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <div id="userPagination"></div>
    <div id="levelUpMessage"></div>
  </section>

  <section>
    <h2>Statistiques par utilisateur</h2>
    <label for="userSelect">Choisir un utilisateur :</label>
    <label for="compareSelect">Comparer avec :</label>
    <select id="compareSelect">
      <option value="">-- Aucun --</option>
      <option value="user1">user1</option>
      <option value="user2">user2</option>
    </select>
    
  <select id="userSelect" onchange="fetchUserStats()"></select>

  <!-- Bouton de bascule -->
  <button id="toggleChartType">Changer le type de graphique</button>

  <!-- Boutons d'exportation et de partage -->
  <button id="exportChartImage">Exporter en image</button>
  <button id="shareStats">Partager les stats</button>


  <div id="categoryChartLoader">Chargement du graphique...</div>
  <canvas id="categoryChart"></canvas>
  <canvas id="questionChart"></canvas>
  <button id="exportMultiPDF">Exporter PDF complet</button>
  <canvas id="categoryChart" width="400" height="200"></canvas>
  </section>

  <section>
    <h2>Statistiques par question</h2>
    <button onclick="printSection('questionTable')">Imprimer</button>
    <a href="../../Backend/export_stats_pdf.php" target="_blank">
      <button>Télécharger PDF</button>
    </a>
    <a href="../../Backend/public/export_email_logs.php" target="_blank">
      <button>Exporter l’historique en CSV</button>
    </a>
    <table id="questionTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Question</th>
          <th>Réponses</th>
          <th>Bonnes</th>
          <th>% Réussite</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </section>
  <section>
    <h3>Exporter l’historique filtré</h3>
    <form action="../../Backend/public/export_email_logs.php" method="get" target="_blank" style="margin-bottom: 20px;">
      <input type="text" name="username" placeholder="Nom d'utilisateur">
      <label>Du :</label>
      <input type="date" name="start">
      <label>Au :</label>
      <input type="date" name="end">
      <button type="submit">Exporter en CSV</button>
    </form>
    <form action="../../Backend/public/export_email_logs_pdf.php" method="get" target="_blank">
      <input type="text" name="username" placeholder="Nom d'utilisateur">
      <label>Du :</label>
      <input type="date" name="start">
      <label>Au :</label>
      <input type="date" name="end">
      <button type="submit">Exporter en PDF</button>
    </form>
  </section>
  <section>
    <h2>Historique des réexpéditions</h2>
    <form method="get" style="margin-bottom: 20px;">
      <input type="text" name="username" placeholder="Utilisateur">
      <input type="date" name="start">
      <input type="date" name="end">
      <button type="submit">Filtrer</button>
    </form>
  </section>
  <section>
    <h2>Historique des envois d’emails</h2>
      <form method="post" action="../../Backend/public/delete_emails.php">
    <table>
      <thead>
        <tr>
          <th><input type="checkbox" onclick="toggleAll(this)"></th>
          <th>Utilisateur</th>
          <th>Destinataire</th>
          <th>Objet</th>
          <th>Date d’envoi</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($emails as $row): ?>
          <tr>
            <td><input type="checkbox" name="delete_ids[]" value="<?= $row['id'] ?>"></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['recipient']) ?></td>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= htmlspecialchars($row['sent_at']) ?></td>
            <td>
              <form onsubmit="return resendEmail(event, <?= $row['id'] ?>, this)">
                <button type="submit">Réenvoyer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <!-- Liens de pagination -->
    <div class="pagination">
      <p>Page actuelle : <?= $page ?></p>
      <p>Page <?= $page ?> sur <?= $totalPages ?></p>
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&username=<?= urlencode($username) ?>&start=<?= $start ?>&end=<?= $end ?>">Précédent</a>
      <?php endif; ?>
      <a href="?page=<?= $page + 1 ?>&username=<?= urlencode($username) ?>&start=<?= $start ?>&end=<?= $end ?>">Suivant</a>
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&username=<?= urlencode($username) ?>&start=<?= $start ?>&end=<?= $end ?>">Suivant</a>
      <?php endif; ?>  
    </div>
    <div id="emailResults"></div>
    <div id="paginationLinks"></div>
    <button id="deleteSelectedBtn" type="submit" disabled>Supprimer sélection</button>
    <form method="post" action="../../Backend/public/delete_emails.php" onsubmit="submitDeletion(event)">
  </form>
  </section>
  <footer>
    &copy; <?= date('Y') ?> Quiz RSE. Tous droits réservés.
  <script>
    const toggleBtn = document.getElementById('themeToggle');
    const clickSound = new Audio('../../Frontend/assets/sounds/correct.mp3');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const savedTheme = localStorage.getItem('theme');

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
      document.body.classList.add('dark-mode');
      toggleBtn.textContent = 'Mode clair';
    }

    toggleBtn.addEventListener('click', () => {
      clickSound.play();
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      toggleBtn.textContent = isDark ? 'Mode clair' : 'Mode sombre';
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    function launchConfetti() {
      const confettiContainer = document.createElement('div');
      confettiContainer.id = 'confetti';
      document.body.appendChild(confettiContainer);

      for (let i = 0; i < 100; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti-piece';
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.animationDelay = Math.random() * 2 + 's';
        confettiContainer.appendChild(confetti);
      }

      setTimeout(() => {
        confettiContainer.remove();
      }, 3000);
    }

    function showLevelUpMessage(message = "🎉 Niveau supérieur atteint !") {
      const msgBox = document.getElementById('levelUpMessage');
      msgBox.textContent = message;
      msgBox.style.display = 'block';
      msgBox.classList.remove('levelUpBounce');
      void msgBox.offsetWidth;
      msgBox.classList.add('levelUpBounce');

      setTimeout(() => {
        msgBox.style.display = 'none';
      }, 4000);
    }
  </script>
  <script>
  window.dashboardData = {
    user: <?= json_encode($_SESSION['user'] ?? null) ?>,
    role: <?= json_encode($_SESSION['role'] ?? null) ?>,
    badge: <?= json_encode($_SESSION['badge'] ?? null) ?>,
    score: <?= json_encode($_SESSION['score'] ?? null) ?>
  };
  </script>
</body>
</html>
