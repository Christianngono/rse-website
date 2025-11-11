<?php
require_once '../includes/init.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'Utilisateur';
$profil = $_SESSION['profil'] ?? 'novice';

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

$clickSoundMap = [
  'novice' => 'click-soft.mp3',
  'confirmé' => 'click-mid.mp3',
  'expert' => 'click-bold.mp3'
];

$videoFile = $videoMap[$profil] ?? 'rse-novice.mp4';
$soundFile = $soundMap[$profil] ?? 'novice.mp3';
$clickSoundFile = $clickSoundMap[$profil] ?? 'click-soft.mp3';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Bienvenue</title>
  <link rel="stylesheet" href="/assets/css/welcome.css" />
</head>
<body>
  <video autoplay muted loop id="backgroundVideo">
    <source src="/assets/videos/<?= htmlspecialchars($videoFile) ?>" type="video/mp4" />
  </video>

  <div class="overlay">
    <div class="container <?= htmlspecialchars($profil) ?>">
      <h1 class="fade-in">Bienvenue, <?= htmlspecialchars($username) ?> 🎉</h1>
      <p class="fade-in delay">Profil : <strong><?= htmlspecialchars($profil) ?></strong></p>
      <p class="fade-in delay2">Prêt à explorer les enjeux RSE ?</p>
      <a href="/quiz.php" class="btn pulse <?= htmlspecialchars($profil) ?>">Commencer le quiz</a>
      <audio id="welcomeSound" src="/assets/sounds/<?= htmlspecialchars($soundFile) ?>" autoplay></audio>
      <audio id="clickSound" src="/assets/sounds/<?= htmlspecialchars($clickSoundFile) ?>" preload="auto"></audio>
    </div>
  </div>

  <script src="/js/welcome.js"></script>
</body>
</html>
