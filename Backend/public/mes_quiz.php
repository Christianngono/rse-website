<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

// Vérification du token sécurisé
$token = $_GET['token'] ?? null;
$user = null;

if ($token) {
    // Vérification du format : 32 caractères hexadécimaux
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        echo json_encode(['error' => 'Format de token invalide']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT username FROM activation_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetchColumn();

    if (!$user) {
        echo json_encode(['error' => 'Token expiré ou invalide']);
        exit;
    }
} else {
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        echo json_encode(['error' => 'Accès refusé. Veuillez vous reconnecter.']);
        exit;
    }
}

// Paramètre optionnel : limit
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? intval($_GET['limit']) : null;

// Requête principale
$sql = "
  SELECT q.id AS question_id, q.question_text, q.correct_answer, q.category, q.difficulty,
         ua.user_answer, ua.is_correct, ua.user_name,
         COUNT(*) OVER(PARTITION BY ua.question_id) AS total_attempts
  FROM user_answers ua
  JOIN questions q ON ua.question_id = q.id
  WHERE ua.user_name = ?
  ORDER BY ua.answered_at DESC
";

if ($limit) {
    $sql .= " LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user, $limit]);
} else {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user]);
}

$quiz = $stmt->fetchAll();

echo json_encode($quiz);