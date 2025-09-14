<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

$user = $_SESSION['user'] ?? null;
if (!$user) {
  echo json_encode(['error' => 'Utilisateur non connecté']);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['answers'])) {
  echo json_encode(['error' => 'Données invalides']);
  exit;
}

foreach ($data['answers'] as $entry) {
  $questionId = $entry['id'];
  $userAnswer = $entry['answer'];

  // Vérifier la bonne réponse
  $stmt = $pdo->prepare("SELECT correct_answer FROM questions WHERE id = ?");
  $stmt->execute([$questionId]);
  $correct = $stmt->fetchColumn();
  $isCorrect = ($userAnswer === $correct);

  // Enregistrer ou mettre à jour
  $stmt = $pdo->prepare("REPLACE INTO user_answers (user_name, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?)");
  $stmt->execute([$user, $questionId, $userAnswer, $isCorrect]);
}

echo json_encode(['status' => 'progression enregistrée']);