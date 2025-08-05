<?php
require_once '../config/db.php'; // ton fichier de connexion à MySQL

session_start();

// Initialisation
$profil = $_POST['profil'] ?? '';
$category = $_POST['category'] ?? '';
$difficulty = $_POST['difficulty'] ?? '';

// Gestion du parcours personnalisé
if ($profil) {
    $difficulty = match($profil) {
        'novice' => 'facile',
        'confirmé' => 'moyen',
        'expert' => 'difficile',
        default => $difficulty,
    };
}

// Requête filtrée
$sql = "SELECT * FROM questions WHERE 1=1";
$params = [];

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}
if (!empty($difficulty)) {
    $sql .= " AND difficulty = ?";
    $params[] = $difficulty;
}

$sql .= " ORDER BY RAND() LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$questions = $stmt->fetchAll();
?>

<!-- HTML -->
<!DOCTYPE html>
<html>
<head>
  <title>Quiz RSE</title>
  <meta charset="UTF-8">
</head>
<body>
  <h1>🎓 Quiz RSE</h1>

  <!-- Formulaire de sélection -->
  <form method="POST">
    <label>Profil :</label>
    <select name="profil">
      <option value="">-- Choisir --</option>
      <option value="novice">Novice</option>
      <option value="confirmé">Confirmé</option>
      <option value="expert">Expert</option>
    </select>

    <label>Thème :</label>
    <select name="category">
      <option value="">-- Catégorie --</option>
      <option value="Environnement">Environnement</option>
      <option value="Ethique">Éthique</option>
      <!-- Ajoute les autres ici -->
    </select>

    <button type="submit">Lancer le quiz</button>
  </form>

  <!-- Questions -->
  <?php if ($questions): ?>
    <form method="POST" action="results.php">
      <?php foreach ($questions as $q): ?>
        <p><?= htmlspecialchars($q['question_text']) ?></p>
        <input type="hidden" name="id[]" value="<?= $q['id'] ?>">
        <input type="text" name="answers[]">
      <?php endforeach; ?>
      <button type="submit">Soumettre</button>
    </form>
  <?php endif; ?>
</body>
</html>