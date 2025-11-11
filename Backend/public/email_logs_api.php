<?php
require '../config/database.php';

header('Content-Type: application/json');

// Paramètres de filtrage
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$username = $_GET['username'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

// Construction de la requête principale
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

$query .= " ORDER BY e.sent_at DESC LIMIT $limit OFFSET $offset";

// Exécution de la requête
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$emails = $stmt->fetchAll();

// Requête pour le total (sans LIMIT)
$countQuery = "
  SELECT COUNT(*) FROM email_logs e
  JOIN users u ON u.username = e.username
  WHERE e.subject LIKE '[Réexpédition]%'
";

$countParams = [];
if ($username) {
  $countQuery .= " AND e.username = ? ";
  $countParams[] = $username;
}
if ($start) {
  $countQuery .= " AND e.sent_at >= ? ";
  $countParams[] = $start;
}
if ($end) {
  $countQuery .= " AND e.sent_at <= ? ";
  $countParams[] = $end;
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$total = $countStmt->fetchColumn();

// 🧾 Génération du HTML
ob_start();
foreach ($emails as $row) {
  echo "<tr>
    <td>" . htmlspecialchars($row['profil']) . "</td>
    <td>" . htmlspecialchars($row['recipient']) . "</td>
    <td>" . htmlspecialchars($row['subject']) . "</td>
    <td>" . htmlspecialchars($row['sent_at']) . "</td>
  </tr>";
}
$html = ob_get_clean();

// Réponse JSON
echo json_encode([
  'html' => $html,
  'total' => intval($total)
]);