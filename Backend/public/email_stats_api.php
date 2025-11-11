<?php
require '../config/database.php';

$stmt = $pdo->query("
  SELECT DATE(sent_at) AS day, username, COUNT(*) AS total
  FROM email_logs
  GROUP BY day, username
  ORDER BY day DESC
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['by_day_user' => $data]);