<?php
require_once '../includes/init.php';
session_start();

// Récupération des données utilisateur
$username = $_SESSION['username'] ?? 'Utilisateur';
$profil = $_SESSION['profil'] ?? 'novice';

// Vidéos et sons par profil
$videoMap = [
  'novice' => 'rse-novice.mp4',
  'confirmé' => 'rse-confirme.mp4',
  'expert' => 'rse-expert.mp4'
];

$soundMap = [
  'novice' => 'novice.mp3',
  'confirmé' => 'confirme.mp3',
  'expert' => 'expert.mp3'
];

$videoFile = $videoMap[$profil] ?? 'rse-novice.mp4';
$soundFile = $soundMap[$profil] ?? 'novice.mp3';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Bienvenue</title>
  <link rel="stylesheet" href="/Frontend/assets/css/welcome.css" />
</head>
<body>
  <video autoplay muted loop id="backgroundVideo">
    <source src="/Frontend/assets/videos/<?= $videoFile ?>" type="video/mp4" />
  </video>

  <div class="overlay">
    <div class="container <?= htmlspecialchars($profil) ?>">
      <h1 class="fade-in">Bienvenue, <?= htmlspecialchars($username) ?> 🎉</h1>
      <p class="fade-in delay">Profil : <strong><?= htmlspecialchars($profil) ?></strong></p>
      <p class="fade-in delay2">Prêt à explorer les enjeux RSE ?</p>
      <a href="/Frontend/quiz.html" class="btn pulse">Commencer le quiz</a>
      <audio id="welcomeSound" src="/Frontend/assets/sounds/<?= $soundFile ?>" autoplay></audio>
    </div>
  </div>

  <script src="/Frontend/js/welcome.js"></script>
</body>
</html>
