<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

$userName = $_POST['user_name'] ?? 'invité';
$score = 0;
$total = 0;
$results = [];

foreach ($_POST as $key => $value) {
    if (str_starts_with($key, 'answer_')) {
        $questionId = str_replace('answer_', '', $key);
        $userAnswer = $value;
        $correctAnswer = $_POST['correct_' . $questionId] ?? '';

        $isCorrect = ($userAnswer === $correctAnswer);
        if ($isCorrect) $score++;
        $total++;

        $stmt = $pdo->prepare("INSERT INTO user_answers (user_name, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userName, $questionId, $userAnswer, $isCorrect]);

        $results[] = [
            'question_id' => $questionId,
            'user_answer' => $userAnswer,
            'correct_answer' => $correctAnswer,
            'is_correct' => $isCorrect
        ];
    }
}

$percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;

$feedback = match (true) {
    $percentage === 100 => "Parfait ! Vous avez répondu correctement à toutes les questions.",
    $percentage >= 75 => "Très bon score ! Vous maîtrisez bien les enjeux RSE.",
    $percentage >= 50 => "Score correct. Vous avez encore quelques notions à renforcer.",
    default => "Il semble que vous ayez besoin de revoir les bases de la RSE."
};

// 📝 Insertion du score
$stmt = $pdo->prepare("INSERT INTO quiz_scores (user_name, score, total, message) VALUES (?, ?, ?, ?)");
$stmt->execute([$userName, $score, $total, $feedback]);

// 🎓 Génération automatique du certificat si score ≥ 4
if ($score >= 4) {
    $certificatName = 'certificat_' . strtolower($userName) . '.pdf';
    $stmt = $pdo->prepare("UPDATE quiz_scores SET message = ?, certificat = ? WHERE user_name = ?");
    $stmt->execute([$feedback, $certificatName, $userName]);
}

echo json_encode([
    'user_name' => $userName,
    'score' => $score,
    'total' => $total,
    'feedback' => $feedback,
    'results' => $results
]);