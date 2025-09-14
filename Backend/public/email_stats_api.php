<?php
require '../config/database.php';

$stmt = $pdo->query("
  SELECT DATE(sent_at) AS date, COUNT(*) AS total
  FROM email_logs
  GROUP BY DATE(sent_at)
  ORDER BY DATE(sent_at)
");

$dates = [];
$counts = [];

while ($row = $stmt->fetch()) {
  $dates[] = $row['date'];
  $counts[] = (int)$row['total'];
}

echo json_encode(['dates' => $dates, 'counts' => $counts]);