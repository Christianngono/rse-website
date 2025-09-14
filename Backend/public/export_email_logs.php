<?php
require '../config/database.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="email_logs.csv"');

// Lire les filtres
$username = $_GET['username'] ?? '';
$startDate = $_GET['start'] ?? '';
$endDate = $_GET['end'] ?? '';

// Construire la requête filtrée
$query = "SELECT * FROM email_logs WHERE 1=1";
$params = [];

if ($username) {
  $query .= " AND username = ?";
  $params[] = $username;
}
if ($startDate) {
  $query .= " AND sent_at >= ?";
  $params[] = $startDate;
}
if ($endDate) {
  $query .= " AND sent_at <= ?";
  $params[] = $endDate;
}

$query .= " ORDER BY sent_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);

// Générer le CSV
$output = fopen('php://output', 'w');
fputcsv($output, ['Utilisateur', 'Destinataire', 'Objet', 'Date']);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  fputcsv($output, [$row['username'], $row['recipient'], $row['subject'], $row['sent_at']]);
}

fclose($output);