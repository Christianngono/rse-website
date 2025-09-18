<?php
session_start();
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bienvenue – Quiz RSE</title>
  <link rel="stylesheet" href="../../Frontend/assets/css/welcome.css">
</head>
<body>
  <main>
    <h1>Bienvenue sur le site du Quiz RSE</h1>

    <?php if ($message): ?>
      <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="actions">
      <a href="index.php" class="btn">Retour à l’accueil</a>
      <a href="login.php" class="btn">Se connecter</a>
      <a href="register.php" class="btn">S'inscrire</a>
    </div>
  </main>

  <script>
    window.userSession = {
      isLoggedIn: <?= isset($_SESSION['user']) ? 'true' : 'false' ?>,
      role: "<?= $_SESSION['role'] ?? '' ?>",
      profil: "<?= $_SESSION['profil'] ?? '' ?>"
    };
  </script>
  <script src="../../Frontend/js/welcome.js" type="module"></script>
</body>
</html>