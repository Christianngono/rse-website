<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';
require('diag.php'); // FPDF + BarDiagram

if (!isset($_SESSION['role'] !== 'user') || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Accès refusé.";
    exit;
}

$pdf = new PDF_Diag();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Statistiques des utilisateurs', 0, 1, 'C');
$pdf->Ln(10);

// Graphique en barres
$data = ['Christian' => 90, 'Sophie' => 66.7, 'Marc' => 88];
$pdf->BarDiagram(180, 70, $data, '%', [100, 150, 255]);
$pdf->Ln(10);

// En-tête du tableau
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(30, 10, 'Utilisateur', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Catégorie', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Réponses', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Correctes', 1, 0, 'C', true);
$pdf->Cell(30, 10, '% Réussite', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Lien RSE', 1, 1, 'C', true);

// Contenu du tableau
$pdf->SetFont('Arial', '', 11);

try {
    $stmt = $pdo->query("
        SELECT ua.user_name, q.category,
               COUNT(*) AS total,
               SUM(ua.is_correct) AS correct,
               ROUND(SUM(ua.is_correct)/COUNT(*)*100, 2) AS success_rate
        FROM user_answers ua
        JOIN questions q ON ua.question_id = q.id
        GROUP BY ua.user_name, q.category
        ORDER BY ua.user_name, q.category
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdf->Cell(30, 10, $row['user_name'], 1);
        $pdf->Cell(30, 10, $row['category'], 1);
        $pdf->Cell(25, 10, $row['total'], 1, 0, 'C');
        $pdf->Cell(25, 10, $row['correct'], 1, 0, 'C');
        $pdf->Cell(30, 10, $row['success_rate'] . '%', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Voir stats', 1, 1, 'C'); // Pas de lien cliquable dans FPDF
    }

} catch (PDOException $e) {
    $pdf->Cell(190, 10, 'Erreur : ' . $e->getMessage(), 1, 1, 'C');
}

$pdf->Output('D', 'stats_utilisateurs.pdf');
?>