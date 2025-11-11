<?php
require '../config/database.php';

$username = $_GET['username'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

$query = "
  SELECT e.username, u.profil, e.recipient, e.subject, e.sent_at
  FROM email_logs e
  JOIN users u ON u.username = e.username
  WHERE e.subject LIKE '[Réexpédition]%'
";
$params = [];

if ($username) {
  $query .= " AND e.username = ? ";
  $params[] = $username;
}
if ($start) {
  $query .= " AND e.sent_at >= ? ";
  $params[] = $start;
}
if ($end) {
  $query .= " AND e.sent_at <= ? ";
  $params[] = $end;
}

$query .= " ORDER BY e.sent_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// En-têtes CSV
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="logs_notifications.csv"');
header('Cache-Control: no-store, no-cache');
header('Pragma: no-cache');
header('Expires: 0');

// BOM UTF-8 pour Excel
echo chr(0xEF) . chr(0xBB) . chr(0xBF);

$output = fopen('php://output', 'w');
fputcsv($output, ['Profil', 'Utilisateur', 'Destinataire', 'Objet', 'Date']);

foreach ($rows as $row) {
  fputcsv($output, [
    $row['profil'],
    $row['username'],
    $row['recipient'],
    $row['subject'],
    $row['sent_at']
  ]);
}