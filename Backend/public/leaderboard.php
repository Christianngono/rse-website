<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$profil = $_GET['profil'] ?? '';
$date = $_GET['date'] ?? '';

$sql = "
  SELECT q.user_name, q.score, q.total, q.created_at, u.profil
  FROM quiz_scores q
  LEFT JOIN users u ON q.user_name = u.username
  WHERE 1=1
";
$params = [];

if ($profil) {
  $sql .= " AND u.profil = ?";
  $params[] = $profil;
}
if ($date) {
  $sql .= " AND DATE(q.created_at) = ?";
  $params[] = $date;
}

$sql .= " ORDER BY q.score DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());