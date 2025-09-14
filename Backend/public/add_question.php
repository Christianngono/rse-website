<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['question_text'] ?? '';
    $answer = $_POST['correct_answer'] ?? '';
    $category = $_POST['category'] ?? '';
    $difficulty = $_POST['difficulty'] ?? 'moyen';

    if ($text && $answer) {
        $stmt = $pdo->prepare("INSERT INTO questions (question_text, correct_answer, category, difficulty) VALUES (?, ?, ?, ?)");
        $stmt->execute([$text, $answer, $category, $difficulty]);
        header('Location: admin.php?message=Question ajoutée avec succès');
        exit;
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter une question</title>
  <link rel="stylesheet" href="../Frontend/assets/css/style.css">
</head>
<body>
  <div class="container">
    <h1>Ajouter une question</h1>
    <?php if ($message): ?><p><?= $message ?></p><?php endif; ?>
    <form method="POST">
      <label>Texte de la question :</label><br>
      <textarea name="question_text" required></textarea><br>
      <label>Réponse correcte :</label><br>
      <input type="text" name="correct_answer" required><br>
      <label>Catégorie :</label><br>
      <input type="text" name="category"><br>
      <label>Difficulté :</label><br>
      <select name="difficulty">
        <option value="facile">Facile</option>
        <option value="moyen" selected>Moyen</option>
        <option value="difficile">Difficile</option>
      </select><br><br>
      <button type="submit" class="btn">Ajouter</button>
      <a href="admin.php" class="btn">Retour</a>
    </form>
  </div>
</body>
</html>