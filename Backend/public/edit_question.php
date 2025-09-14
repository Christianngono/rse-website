<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: admin.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$id]);
$question = $stmt->fetch();

if (!$question) {
    echo "Question introuvable.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['question_text'] ?? '';
    $answer = $_POST['correct_answer'] ?? '';
    $category = $_POST['category'] ?? '';
    $difficulty = $_POST['difficulty'] ?? 'moyen';

    $update = $pdo->prepare("UPDATE questions SET question_text = ?, correct_answer = ?, category = ?, difficulty = ? WHERE id = ?");
    $update->execute([$text, $answer, $category, $difficulty, $id]);

    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier la question</title>
  <link rel="stylesheet" href="../Frontend/assets/css/style.css">
</head>
<body>
  <div class="container">
    <h1>Modifier la question</h1>
    <form method="POST">
      <label>Texte de la question :</label><br>
      <textarea name="question_text" required><?= htmlspecialchars($question['question_text']) ?></textarea><br>
      <label>Réponse correcte :</label><br>
      <input type="text" name="correct_answer" value="<?= htmlspecialchars($question['correct_answer']) ?>" required><br>
      <label>Catégorie :</label><br>
      <input type="text" name="category" value="<?= htmlspecialchars($question['category']) ?>"><br>
      <label>Difficulté :</label><br>
      <select name="difficulty">
        <option value="facile" <?= $question['difficulty'] === 'facile' ? 'selected' : '' ?>>Facile</option>
        <option value="moyen" <?= $question['difficulty'] === 'moyen' ? 'selected' : '' ?>>Moyen</option>
        <option value="difficile" <?= $question['difficulty'] === 'difficile' ? 'selected' : '' ?>>Difficile</option>
      </select><br><br>
      <button type="submit" class="btn">Enregistrer</button>
      <a href="admin.php" class="btn">Annuler</a>
    </form>
  </div>
</body>
</html>