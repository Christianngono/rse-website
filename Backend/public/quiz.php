<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

$profil = $_SESSION['profil'] ?? '';
$category = $_POST['category'] ?? '';

$difficulty = match($profil) {
    'novice' => 'facile',
    'confirmé' => 'moyen',
    'expert' => 'difficile',
    default => '',
};

$sql = "SELECT * FROM questions WHERE 1=1";
$params = [];

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}
if ($difficulty) {
    $sql .= " AND difficulty = ?";
    $params[] = $difficulty;
}

$sql .= " ORDER BY RAND() LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$questions = $stmt->fetchAll();

foreach ($questions as &$q) {
    $wrongStmt = $pdo->prepare("
        SELECT DISTINCT user_answer 
        FROM user_answers 
        WHERE question_id = ? AND is_correct = FALSE 
        ORDER BY RAND() LIMIT 3
    ");
    $wrongStmt->execute([$q['id']]);
    $wrongAnswers = $wrongStmt->fetchAll(PDO::FETCH_COLUMN);

    while (count($wrongAnswers) < 3) {
        $wrongAnswers[] = "Réponse fictive " . (count($wrongAnswers) + 1);
    }

    $q['answers'] = array_merge([$q['correct_answer']], $wrongAnswers);
    shuffle($q['answers']);
}

echo json_encode($questions);