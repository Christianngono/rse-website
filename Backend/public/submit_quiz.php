<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$nom = $data['nom'] ?? 'Participant';
$email = $data['email'] ?? '';
$reponses = $data['reponses'] ?? [];

if (!$nom || !$email || !is_array($reponses)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$score = 0;
$total = count($reponses);

foreach ($reponses as $question_id => $reponse) {
    $stmt = $pdo->prepare("SELECT correct_answer FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);
    $correct = $stmt->fetchColumn();
    if (strtolower(trim($reponse)) === strtolower(trim($correct))) {
        $score++;
    }
}

// Enregistrer dans quiz_scores
$stmt = $pdo->prepare("INSERT INTO quiz_scores (user_name, score, total, message) VALUES (?, ?, ?, ?)");
$message = "Score enregistré pour $nom : $score/$total";
$stmt->execute([$nom, $score, $total, $message]);

echo json_encode([
    'success' => true,
    'message' => $message,
    'score' => $score,
    'total' => $total
]);