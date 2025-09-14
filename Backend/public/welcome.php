<?php
session_start();
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bienvenue</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      text-align: center;
      padding: 50px;
    }
    h1 {
      color: #2c3e50;
    }
    .message {
      color: green;
      font-weight: bold;
      margin: 20px 0;
    }
    .btn {
      display: inline-block;
      padding: 10px 20px;
      margin: 10px;
      background-color: #3498db;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: background-color 0.3s ease;
    }
    .btn:hover {
      background-color: #2980b9;
    }
  </style>
</head>
<body>
  <h1>Bienvenue sur le site du Quiz RSE</h1>

  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <a href="index.php" class="btn">Retour à l’accueil</a>
  <a href="login.php" class="btn">Se connecter</a>
</body>
</html>