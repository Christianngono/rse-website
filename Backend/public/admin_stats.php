<?php

require_once '../config/database.php';
header('Content-Type: application/json');

$stats = [];

$stats['total_participants'] = $pdo->query("SELECT COUNT(*) FROM quiz_scores")->fetchColumn();
$stats['average_score'] = $pdo->query("SELECT ROUND(AVG(score),2) FROM quiz_scores")->fetchColumn();
$stats['by_profil'] = $pdo->query("
  SELECT u.profil, COUNT(*) as count, ROUND(AVG(q.score),2) as avg_score
  FROM quiz_scores q
  JOIN users u ON q.user_name = u.username
  GROUP BY u.profil
")->fetchAll();

echo json_encode($stats);