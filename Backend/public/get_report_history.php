<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$date   = $_GET['date'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT sent_at, recipient, format, frequency, status, message FROM report_history WHERE 1=1";
$params = [];

if ($date) {
  $sql .= " AND DATE(sent_at) = ?";
  $params[] = $date;
}
if ($status) {
  $sql .= " AND status = ?";
  $params[] = $status;
}

$sql .= " ORDER BY sent_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));