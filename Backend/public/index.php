<?php
session_start();
require '../config/database.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profil'] = $user['profil'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['quiz_score'] = $user['quiz_score'];

        header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'quiz.php'));
        exit;
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="description" content="Quiz interactif sur la Responsabilité Sociétale des Entreprises (RSE)">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil – Quiz RSE</title>
  <link rel="stylesheet" href="../../Frontend/assets/css/index.css">
  <link rel="stylesheet" href="../../Frontend/assets/css/header.css">
  <link rel="stylesheet" href="../../Frontend/assets/css/footer.css">
</head>
<body>

  <header>
    <nav>
      <h1>Quiz RSE</h1>
      <button id="menuToggle">☰</button>
      <ul id="navMenu">
        <li><a href="quiz.php">Quiz</a></li>
        <li><a href="admin.php">Admin</a></li>
        <?php if (isset($_SESSION['user'])): ?>
          <li class="user-info">Connecté : <?= htmlspecialchars($_SESSION['user']) ?> (<?= htmlspecialchars($_SESSION['profil']) ?>)</li>
        <?php endif; ?>
        <li>
          <button id="themeToggle" title="Changer de thème">
            <span id="themeIcon">🌙</span>
          </button>
        </li>
      </ul>
    </nav>
  </header>

  <main>
    <section id="introOverlay">
      <p>Bienvenue sur le Quiz RSE</p>
      <p>Préparez-vous à tester vos connaissances !</p>
    </section>

    <section class="fade-in">
      <h1>Engagez-vous pour un monde responsable</h1>
      <p>Un quiz, une mission, un impact.</p>
      <video autoplay muted loop width="100%" style="max-width:720px;">
        <source src="../../Frontend/assets/videos/rse-background.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la lecture vidéo.
      </video>
      <br><br>
      <a href="quiz.php"><button>Commencer le Quiz</button></a>
    </section>

    <section class="fade-in">
      <h2>Qu'est-ce que la RSE ?</h2>
      <p>La Responsabilité Sociétale des Entreprises consiste à intégrer les préoccupations sociales, environnementales et économiques dans les activités d’une entreprise.</p>
      <video controls poster="../../Frontend/assets/images/preview.jpg" width="100%" style="max-width:720px;">
        <source src="../../Frontend/assets/videos/intro-video.mp4" type="video/mp4">
      </video>
    </section>

    <section class="fade-in">
      <h2>Connexion</h2>
      <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <form method="post">
        <label>Nom d'utilisateur : <input type="text" name="username" required></label><br><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br><br>
        <p><a href="forgot_password.php">Mot de passe oublié ?</a></p>
        <p><a href="register.php">Pas encore de compte ? Inscrivez-vous</a></p><br>
        <button type="submit">Se connecter</button>
      </form>
    </section>

    <?php if (isset($_SESSION['user']) && $_SESSION['role'] === 'admin'): ?>
      <section class="fade-in">
        <h2>Espace administrateur</h2>
        <a href="admin.php"><button>Accéder à l’administration</button></a>
      </section>
    <?php endif; ?>
  </main>

  <div id="notification" class="info">Bienvenue sur le site du Quiz RSE</div>
  <div id="loader">Chargement...</div>

  <script>
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');

    // Préférence système
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const savedTheme = localStorage.getItem('theme');

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
      body.classList.add('dark-mode');
      themeIcon.textContent = '☀️';
    }

    // Bascule du thème
    themeToggle.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      const isDark = body.classList.contains('dark-mode');
      themeIcon.textContent = isDark ? '☀️' : '🌙';
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    // Menu mobile
    menuToggle.addEventListener('click', () => {
      navMenu.classList.toggle('show');
    });

    // Notifier
    window.addEventListener('load', () => {
      const notif = document.getElementById('notification');
      notif.classList.add('show');
      setTimeout(() => notif.classList.remove('show'), 3000);
    });
  </script>
  <script>
  window.userSession = {
    isLoggedIn: <?= isset($_SESSION['user']) ? 'true' : 'false' ?>,
    role: "<?= $_SESSION['role'] ?? '' ?>",
    profil: "<?= $_SESSION['profil'] ?? '' ?>",
    email: "<?= $_SESSION['email'] ?? '' ?>",
    phone: "<?= $_SESSION['phone'] ?? '' ?>",
    quizScore: <?= $_SESSION['quiz_score'] ?? 0 ?>
  };
  </script>
  <!-- Module JS principal -->
  <script type="module" src="js/modules/index.js"></script>

</body>
</html>