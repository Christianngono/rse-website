<?php
require '../config/database.php';

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$username = $_GET['username'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

$query = "SELECT * FROM email_logs WHERE subject LIKE '[Réexpédition]%' ";
$params = [];

if ($username) {
  $query .= " AND username = ? ";
  $params[] = $username;
}
if ($start) {
  $query .= " AND sent_at >= ? ";
  $params[] = $start;
}
if ($end) {
  $query .= " AND sent_at <= ? ";
  $params[] = $end;
}

$query .= " ORDER BY sent_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$emails = $stmt->fetchAll();

// Générer le HTML
foreach ($emails as $row) {
  echo "<tr>
    <td>{$row['username']}</td>
    <td>{$row['recipient']}</td>
    <td>{$row['subject']}</td>
    <td>{$row['sent_at']}</td>
  </tr>";
}