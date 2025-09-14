<?php
require '../config/database.php';


$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$username = $_GET['username'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

$countQuery = "SELECT * FROM email_logs WHERE subject LIKE '[Réexpédition]%' ";
$countParams = [];

if ($username) {
  $countQuery .= " AND username = ? ";
  $countParams[] = $username;
}
if ($start) {
  $countQuery .= " AND sent_at >= ? ";
  $countParams[] = $start;
}
if ($end) {
  $countQuery .= " AND sent_at <= ? ";
  $countParams[] = $end;
}

$countQuery .= " ORDER BY sent_at DESC";

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

//Générer Le HTML
foreach ($emails as  $row) {
    echo "<tr>";
    echo "<td><input type='checkbox' class='emailCheckbox' value='" . htmlspecialchars($row['id']) . "'></td>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['recipient']) . "</td>";
    echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
    echo "<td>" . htmlspecialchars($row['sent_at']) . "</td>";
    echo "</tr>";
}
