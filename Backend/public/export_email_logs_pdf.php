<?php
require '../config/database.php';
require '../lib/fpdf186/fpdf.php';

$username = $_GET['username'] ?? '';
$startDate = $_GET['start'] ?? '';
$endDate = $_GET['end'] ?? '';

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

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Historique des envois d\'emails', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(40, 10, 'Utilisateur');
$pdf->Cell(50, 10, 'Destinataire');
$pdf->Cell(60, 10, 'Objet');
$pdf->Cell(40, 10, 'Date', 0, 1);

while ($row = $stmt->fetch()) {
  $pdf->Cell(40, 10, $row['username']);
  $pdf->Cell(50, 10, $row['recipient']);
  $pdf->Cell(60, 10, substr($row['subject'], 0, 40));
  $pdf->Cell(40, 10, $row['sent_at'], 0, 1);
}

$pdf->Output('D', 'email_logs.pdf');