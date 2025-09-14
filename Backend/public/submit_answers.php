<?php
require_once '../../Backend/config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    echo json_encode(["status" => "error", "message" => "Données invalides"]);
    exit;
}

foreach ($data as $answer) {
    $stmt = $pdo->prepare("
        INSERT INTO user_answers (user_name, question_id, user_answer, is_correct)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $answer['user_name'],
        $answer['question_id'],
        $answer['user_answer'],
        $answer['is_correct']
    ]);
}

echo json_encode(["status" => "success"]);
?>